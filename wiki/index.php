<?php
// $Id$

$GLOBALS['phpgw_info']['flags'] = array(
	'currentapp' => 'wiki',
	'noheader'   => True
);

// the phpGW header.inc.php got included later by lib/init.php

$action = $HTTP_GET_VARS['action'];
switch ($action) {
	case 'edit':
		$GLOBALS['phpgw_info']['cursor_focus'] = "document.editform.document.focus();";
	break;
	case 'save':
		if ($HTTP_POST_VARS['Preview'] == 'Preview') { 
			$GLOBALS['phpgw_info']['cursor_focus'] = "document.editform.document.focus();";
		} else {
			$GLOBALS['phpgw_info']['cursor_focus'] = "document.thesearch.find.focus();";
		}
	break;
	default:
		$GLOBALS['phpgw_info']['cursor_focus'] = "document.thesearch.find.focus();";

}

require('lib/main.php');

?>
