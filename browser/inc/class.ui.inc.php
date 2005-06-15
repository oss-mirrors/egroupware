<?php
	/**************************************************************************\
	* eGroupWare - browsereton Application                                        *
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
			'index'     => true
		);

		function ui()
		{
			$this->t = $GLOBALS["phpgw"]->template;
			$this->bo = createobject('browser.bo',true);
			$this->nextmatchs = createobject('phpgwapi.nextmatchs');			
		}

		function index()
		{
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();
			
				$GLOBALS['tpl'] = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir('browser'));
				$GLOBALS['tpl']->set_unknowns('remove');
				
				$GLOBALS['tpl']->set_file(
					array(
						'browser' => 'browser.tpl'
					)
				);
				$vars["imgDir"]=$GLOBALS['phpgw_info']['server']['webserver_url']."/browser/templates/default/images/";
				//print_r($GLOBALS['phpgw_info']['user']['preferences']);
				$vars["homepage"]=$GLOBALS['phpgw_info']['user']['preferences']['browser']['0'];
				$GLOBALS['tpl']->set_var($vars);
				$GLOBALS['tpl']->set_block('browser','iframe','iframe');
				$GLOBALS['tpl']->pfp('out','iframe');
			// get some information from $bo, then format it for display
		}
		
		
	}
?>