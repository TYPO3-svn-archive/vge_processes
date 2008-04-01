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
 * Base view service 'vge_processes' extension.
 *
 * @author	Francois Suter (Cobweb) <support@cobweb.ch>
 * @package	TYPO3
 * @subpackage	tx_vgeprocesses
 */
abstract class tx_vgeprocesses_view_base extends t3lib_svbase {
	protected $processList; // List of processes handled by a particular service
	protected $locallangFile; // Path of the locallang file to load for labels, descriptions, etc.

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
	 * This method displays a running process given some data
	 *
	 * @param	array	$data: data relevant to the process, as provided by the model
	 * @param	object	$pObj: reference to the controller
	 *
	 * @return	string	HTML code to display
	 */
	abstract public function displayProcess($data, &$pObj);
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vge_processes/class.tx_vgeprocesses_view_base.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vge_processes/class.tx_vgeprocesses_view_base.php']);
}

?>