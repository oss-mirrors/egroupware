<?php
	
	/*
	eGroupWare - http://www.egroupware.org
	written by Pim Snel <pim@lingewoud.nl>
	modified by Vinicius Cubas Brand <viniciuscb@users.sourceforge.net>
	License: LGPL v2
	*/


	$phpgw_flags = Array(
		'currentapp'    =>      'filescenter',
		'noheader'      =>      True,
		'nonavbar'      =>      True,
		'noappheader'   =>      True,
		'noappfooter'   =>      True,
		'nofooter'      =>      True
	);

	$GLOBALS['egw_info']['flags'] = $phpgw_flags;

	include('../header.inc.php');

	Header('Location: '.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index'));
	$GLOBALS['egw']->common->egw_exit();
?>
