<?php
	/**************************************************************************\
	* eGroupWare - Skeleton Application                                        *
	* http://www.egroupware.org                                                *
	* -----------------------------------------------                          *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class ui
	{
		var $t;
		var $bo;
		var $prefs;
		var $nextmatchs;

		var $debug = false;

		var $public_functions = array(
			'index' => true
		);

		function ui()
		{
			$this->t = $GLOBALS['egw']->template;
			$this->bo = CreateObject('skel.bo',true);
			$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
		}

		function index()
		{
			$GLOBALS['egw']->common->phpgw_header();
			echo parse_navbar();

			// get some information from $bo, then format it for display
		}
	}
?>
