<?php
	$phpgw_info = array();
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'workflow',
		'noheader'   => True,
		'nonavbar'   => True
	);
	require_once('../header.inc.php');

	$obj = CreateObject('workflow.ui_userprocesses');
	$obj->form();

	$GLOBALS['phpgw']->common->phpgw_footer();

?>
