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

require_once(PATH_t3lib.'class.t3lib_svbase.php');

/**
 * Base model service 'vge_processes' extension.
 *
 * @author	Francois Suter (Cobweb) <support@cobweb.ch>
 * @package	TYPO3
 * @subpackage	tx_vgeprocesses
 */
abstract class tx_vgeprocesses_model_base extends t3lib_svbase {
	protected $processList; // List of processes handled by a particular service
	protected $locallangFile; // Path of the locallang file to load for labels, descriptions, etc.
	protected $controller; // Reference to the controller object

	public function __construct() {
		if (!empty($this->locallangFile)) {
			$GLOBALS['LANG']->includeLLFile($this->locallangFile);
		}
	}
	
	/**
	 * This methods returns true if the service can handle the given process
	 *
	 * @param	string	$name: name of the process
	 *
	 * @return	boolean
	 */
	public function hasProcess($name) {
		return in_array($name, $this->processList);
	}

	/**
	 * This method is expected to return a list of processes it is capable of handling,
	 * along with the parameters to use when calling up each process
	 * TODO: finalize array structure
	 *
	 * @return	array	list of available processes with parameters
	 */
	abstract public function listProcesses();

	/**
	 * This process returns all the data related to a given process at a given step
	 *
	 * @param	array		$parameters: list of parameters, including process name, step, etc.
	 * @param	array		$configuration: TypoScript configuration of the process subtype
	 * @param	reference	$controller: reference to the controller object
	 *
	 * @return	array	all data related to the process and step
	 */
	abstract public function getDataForStep($parameters, $configuration, &$controller);
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vge_processes/class.tx_vgeprocesses_model_base.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vge_processes/class.tx_vgeprocesses_model_base.php']);
}

?>