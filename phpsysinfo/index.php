<?php
  /**************************************************************************\
  * phpGroupWare - PHPSysInfo                                                *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array
		(
			'currentapp' => 'phpsysinfo'
		);
	include('../header.inc.php');

	$lng = $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'];
	$template = $GLOBALS['phpgw_info']['user']['preferences']['common']['theme'];

	// default to english, but this is negotiable.
	if (!(isset($lng) && file_exists('includes/lang/' . $lng . '.php')))
	{
		$lng = 'en';
	}
	require('includes/lang/' . $lng . '.php');  // get our language include
	require('includes/color_scheme.php');        // our default color scheme
	#require('includes/class.Template.inc.php');  // template library
	require('includes/system_functions.php');    // finish defining all of our global functions and variables

	// figure out if we got a template passed in the url
	if (!(isset($template) && file_exists("templates/$template")))
	{
		// default template we should use if we don't get a argument.
		define('TEMPLATE_SET', 'default');
		$template = 'default';
	}
	else
	{
		define('TEMPLATE_SET', $template);
	}

	// fire up the template engine
	$tpl = new Template(dirname(__FILE__) . '/templates/' . TEMPLATE_SET);
	$tpl->set_file(array(
		'form' => 'form.tpl'
	));

	// print out a box of information
	function makebox ($title, $content)
	{
		$t = new Template(dirname(__FILE__) . '/templates/' . TEMPLATE_SET);

		$t->set_file(array(
			'box'  => 'box.tpl'
		));

		$t->set_var('title', $title);
		$t->set_var('content', $content);

		return $t->parse('out', 'box');
	}  

	// let the page begin.
	#require('includes/system_header.php');

	#$tpl->set_var('title', $text['title'] . ': ' . sys_chostname() . ' (' . sys_ip_addr() . ')');

	require('includes/table_vitals.php');
	require('includes/table_network.php');    
	require('includes/table_hardware.php');
	require('includes/table_memory.php');
	require('includes/table_filesystems.php');

	// parse our the template
	$tpl->pparse('out', 'form');

	// finally our print our footer
	#require('includes/system_footer.php');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
