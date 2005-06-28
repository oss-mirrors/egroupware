<?php
	$phpgw_info = array();
	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp' => 'workflow',
		'noheader'   => True,
		'nonavbar'   => True
	);
	require_once('../header.inc.php');
	
	$GLOBALS['phpgw']->preferences->read_repository();
        $startscreen = $GLOBALS['phpgw_info']['user']['preferences']['workflow']['startpage'];
        if (!isset($startscreen))
        {
        	$startscreen='workflow.ui_userprocesses';
        	$form_args = Array();
        }
        else
        {
        	if ($startscreen == 'workflow.ui_useractivities2')
        	{
        		$startscreen = 'workflow.ui_useractivities';
        		$form_args = 1;
        	}
        	else
        	{
        		$form_args = array();
        	}
        }
        ExecMethod($startscreen.'.form',$form_args);
        
//	$obj = CreateObject($startscreen);
//	$obj->form();

//	$GLOBALS['phpgw']->common->phpgw_footer();

?>
