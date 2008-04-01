<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Francois Suter (Cobweb) <support@cobweb.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Online Processes' for the 'vge_processes' extension.
 *
 * @author	Francois Suter (Cobweb) <support@cobweb.ch>
 * @package	TYPO3
 * @subpackage	tx_vgeprocesses
 */
class tx_vgeprocesses_controller extends tslib_pibase {
	public $prefixId      = 'tx_vgeprocesses_controller';		// Same as class name
	public $scriptRelPath = 'controller/class.tx_vgeprocesses_controller.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'vge_processes';	// The extension key.
	public $conf; // Plugin configuration
	protected $ignoreVariables = array('viewType', 'subtype', 'process', 'step'); // List of variables that must not be touched by the session restoring mechanism (see init() method)
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	public function main($content, $conf) {
			// Load locallang file
//		$this->pi_loadLL();
			// Replace lang in LANG with lang in TSFE for services that will be called below (otherwise services get locallang always in default language)
			// TODO: this needs to be validated. Maybe a LOCAL_LANG object should be created
		$GLOBALS['LANG']->lang = $GLOBALS['TSFE']->lang;

			// Initialise the controller
		$this->init($conf);
//t3lib_div::debug($this->conf);
//t3lib_div::debug($this->piVars);

		$content = '';
//t3lib_div::debug($GLOBALS['T3_SERVICES']);
		switch ($this->conf['viewType']) {
			case 'run':
					// Get the model for the selected process
				$hasProcess = false;
					// Loop on all appropriate subtypes
				foreach ($GLOBALS['T3_SERVICES']['process_models'] as $serviceKey => $serviceData) {
					$serviceObject = t3lib_div::makeInstanceService('process_models', $serviceData['subtype']);
						// Check which subtype can handle the selected process and get the model from it
					if ($serviceObject->hasProcess($this->piVars['process'])) {
						$data = $serviceObject->getDataForStep($this->piVars, $this);
						$hasProcess = true;
						break;
					}
				}
					// If a service was found for the process, produce the display
				if ($hasProcess) {
						// Display the process' screen as appropriate for the current step
					foreach ($GLOBALS['T3_SERVICES']['process_views'] as $serviceKey => $serviceData) {
						$serviceObject = t3lib_div::makeInstanceService('process_views', $serviceData['subtype']);
							// Check which subtype can handle the selected process and get the model from it
						if ($serviceObject->hasProcess($this->piVars['process'])) {
							$content =  $serviceObject->displayProcess($data, $this);
						}
					}
				}
				else {
					$content = 'No such process';
				}
				break;

				// Defaut view: display list of available processes
			case 'list':
			default:
					// Get list of all services
				$processesList = array();
				foreach ($GLOBALS['T3_SERVICES']['process_models'] as $serviceKey => $serviceData) {
					$serviceObject = t3lib_div::makeInstanceService('process_models', $serviceData['subtype']);
					$list = $serviceObject->listProcesses();
					foreach ($list as $aProcess) {
						$linkParameters = array_merge(array('subtype' => $serviceData['subtype'], 'viewType' => 'run'), $aProcess['linkParameters']);
						$processesList[] = array('process' => $aProcess['process'], 'name' => $aProcess['name'], 'linkParameters' => $linkParameters);
					}
				}
					// Display the list of all services, with links to start them
				$content = '<ul>';
				foreach ($processesList as $processData) {
					$linkParameters = array();
					foreach ($processData['linkParameters'] as $key => $value) {
						$linkParameters[$this->prefixId.'['.$key.']'] = $value;
					}
					$content .= '<li>'.$this->pi_linkTP($processData['name'], $linkParameters).'</li>';
				}
				$content .= '</ul>';
				break;
		}

			// TODO: remove data stored in session if process is finished

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * This method performs various initialisations
	 *
	 * @param	array		$conf: plugin configuration, as received by the main() method
	 * @return	void
	 */
	private function init($conf) {
		$this->pi_USER_INT_obj = 1; // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->conf = $conf; // Base configuration is equal the the plugin's TS setup

// Load the flexform and loop on all its values to override TS setup values
// Some properties use a different test (more strict than not empty)

		$this->pi_initPIflexForm();
		foreach ($this->cObj->data['pi_flexform']['data'] as $sheet => $langData) {
			foreach ($langData as $lang => $fields) {
				foreach ($fields as $field => $value) {
					$value = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $field, $sheet);
					if (!empty($value)) $this->conf[$field] = $value;
				}
			}
		}

// Add piVars to configuration

		foreach ($this->piVars as $key => $value) {
			$this->conf[$key] = $value;
		}

//t3lib_div::debug($this->piVars);

			// Get values stored in session
		$storedValues = $GLOBALS['TSFE']->fe_user->getKey('ses', 'processData');
			// Put into piVars stored values that are not in piVars currently and are not part of the ignore values (which must be left untouched)
		if (isset($storedValues) && is_array($storedValues)) {
			foreach ($storedValues as $key => $value) {
				if (!in_array($key, $this->ignoreVariables) && !isset($this->piVars[$key])) $this->piVars[$key] = $value;
			}
		}
			// Store the (updated) piVars data in session
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'processData', $this->piVars);
//t3lib_div::debug($this->piVars);

// Load localized strings

		$this->pi_loadLL();
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vge_processes/controller/class.tx_vgeprocesses_controller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vge_processes/controller/class.tx_vgeprocesses_controller.php']);
}

?>