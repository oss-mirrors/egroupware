<?php
	/**************************************************************************\
	* eGroupWare - switchuser Application                                        *
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
		   'index'     => true,
		   'nopermission' => true
		);

		function ui()
		{
			$this->t = $GLOBALS["phpgw"]->template;
			$this->bo = createobject('switchuser.bo',true);
			$this->nextmatchs = createobject('phpgwapi.nextmatchs');
		}

		function index()
		{
		   $this->bo->admincheck();
		   $GLOBALS['phpgw']->common->phpgw_header();
		   
		   echo parse_navbar();

		   $this->t->set_file(array
		   (
			  'form' => 'frm_selectuser.tpl',
		   ));

		   $form_action = $GLOBALS[phpgw]->link('/index.php',"menuaction=switchuser.bo.switchfrompost");
		   $this->t->set_var('which_user',lang('to which user you want to switch?'));
		   $this->t->set_var('switch_now',lang('Switch now'));
		   $this->t->set_var('form_action',$form_action);

		   $this->t->pparse('out','form');

		}

		function nopermission()
		{
		   $GLOBALS['phpgw']->common->phpgw_header();
		   echo parse_navbar();

		   $this->t->set_file(array
		   (
			  'noperm' => 'nopermission.tpl',
		   ));

		   $this->t->set_var('txt_no_permission',lang('You\'re not allowed to switch identity. '));

		   $this->t->pparse('out','noperm');
		}

	 }
  ?>
