<?php
// $Id$
$action = $HTTP_GET_VARS['action'];
switch ($action) {
	case 'edit':
		$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
		$GLOBALS['phpgw_info']['cursor_focus'] = "document.editform.document.focus();";
	break;
	case 'save':
			if ($HTTP_POST_VARS['Preview'] == 'Preview') { 
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
				$GLOBALS['phpgw_info']['cursor_focus'] = "document.editform.document.focus();";
			} else {
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True,
 							 "noheader" => True );
				$GLOBALS['phpgw_info']['cursor_focus'] = "document.thesearch.find.focus();";
			}
	break;
	case 'prefs': 
			if (empty($Save)) {
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
			} else {
				$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True,
 							 "noheader" => True );
			}
		$GLOBALS['phpgw_info']['cursor_focus'] = "document.thesearch.find.focus();";
	break;
	default:
		$phpgw_info["flags"] = array ("currentapp" => "wiki",
							 "enable_nextmatchs_class" => True );
		$GLOBALS['phpgw_info']['cursor_focus'] = "document.thesearch.find.focus();";

}

include ("../header.inc.php"); #PHP Groupware header

require('lib/main.php');

?>
