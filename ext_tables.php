<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Load the full TCA for tt_content

t3lib_div::loadTCA('tt_content');

// Disable the display of layout, pages and select_key fields

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_controller']='layout,pages,select_key';

// Activate the display of the plug-in flexform field and set FlexForm defintion

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_controller'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_controller', 'FILE:EXT:vge_processes/flexform_ds.xml');

// Add the plug-ins to the list of existing plug-ins

t3lib_extMgm::addPlugin(array('LLL:EXT:vge_processes/locallang_db.xml:tt_content.list_type_controller', $_EXTKEY.'_controller'),'list_type');

// Define the path to the static TS files

t3lib_extMgm::addStaticFile($_EXTKEY,'controller/static/','Online Processes');

// Add the plug-in to the new content elements wizard

if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_vgeprocesses_controller_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'controller/class.tx_vgeprocesses_controller_wizicon.php';
?>