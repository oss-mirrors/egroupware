<?php
	/**************************************************************************\
	* phpGroupWare - E-Mail                                                    *
	* http://www.phpgroupware.org                                              *
	* Based on Aeromail by Mark C3ushman <mark@cushman.net>                    *
	*          http://the.cushman.net/                                         *
	* Currently maintained by Angles <angles@phpgroupware.org>                 *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	Header('Cache-Control: no-cache');
	Header('Pragma: no-cache');
	Header('Expires: Sat, Jan 01 2000 01:01:01 GMT');

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'  => 'email',
		'noheader'    => True,
		'nofooter'    => True,
		'nonavbar'    => True,
		'noappheader' => True,
		'noappfooter' => True
	);
	include('../header.inc.php');
	// time limit should be controlled elsewhere
	//@set_time_limit(0);

	// this index page is acting like a calling app which wants the HTML produced by mail.uiindex.index
	// but DOES NOT want mail.uiindex.index to actually echo or print out any HTML
	// we, the calling app, will handle the outputting of the HTML
	//$is_modular = True;
	$is_modular = False;

	header('Location: ' . $GLOBALS['phpgw']->link(
			'/index.php',
			'menuaction=email.uiindex.index'
		)
	);
	return;
	/*
	if ($is_modular == True)
	{
		// pretend we are a calling app outputting some HTML, including the header and navbar
		$GLOBALS['phpgw']->common->phpgw_header();
		// retrieve the html data from class uiindex
		$obj = CreateObject('email.uiindex');
		$obj->set_is_modular(True);
		$retured_html = $obj->index();
		// time for us to output the returned html data
		echo $retured_html;
		// now as the calling app, it's time to output the bottom of the page
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
	else
	{
		// this NON-modular usage will have class uiindex itself output the header, navbar, class HTML data, and footer
		$obj = CreateObject('email.uiindex');
		$obj->index();
		// STRANGEly enough, menuaction=email.uiindex.index as non-module STILL requires an
		// outside-the-class entity to call common->phpgw_footer(), eventhough the class itself will
		// output the header and navbar, but it may not output common->phpgw_footer() else page gets 2 footers
		$GLOBALS['phpgw']->common->phpgw_footer();
	}
	*/
?>
