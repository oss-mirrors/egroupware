<?php
	/**************************************************************************\
	* phpGroupWare - Developer Tools                                           *
	* http://www.phpgroupware.org                                              *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	class uisf_project_tracker
	{
		var $bo;
		var $template;
		var $public_functions = array(
			'display_tracker' => True,
			'preferences'     => True
		);

		function uisf_project_tracker()
		{
			global $phpgw, $phpgw_info;

			$this->bo       = createobject('developer_tools.bosf_project_tracker');
			$this->template = $phpgw->template;
			$this->template->set_file(array(
				'sf_project' => 'sf_project.tpl'
			));
			$this->template->set_block('sf_project','display');
			$this->template->set_block('sf_project','preferences');
		}

		function display_tracker()
		{
			global $phpgw;

			$phpgw->common->phpgw_header();
			echo parse_navbar();
			include(PHPGW_APP_INC . '/header.inc.php');

			$this->template->set_var('lang_header',lang('Sourceforge project tracker'));
			$this->template->set_var('project_html',$this->bo->display_tracker());
			$this->template->pfp('out','display');
		}

		function preferences($message = '')
		{
			global $phpgw, $phpgw_info;

			$phpgw->common->phpgw_header();
			echo parse_navbar();
			include(PHPGW_APP_INC . '/header.inc.php');

			$pref        = createobject('phpgwapi.preferences');
			$pref->read_repository();
			$preferences = $pref->data['developer_tools'];

			if (is_string($message) && $message)
			{
				$this->template->set_var('message',$message);
			}

			$this->template->set_var('form_action',$phpgw->link('/index.php','menuaction=developer_tools.bosf_project_tracker.preferences'));

			$this->template->set_var('th_bg',$phpgw_info['theme']['th_bg']);
			$this->template->set_var('row_on',$phpgw_info['theme']['row_on']);
			$this->template->set_var('row_off',$phpgw_info['theme']['row_off']);

			$this->template->set_var('header_message',lang('Developer tools - preferences'));
			$this->template->set_var('lang_header',lang('Sourceforge project tracker preferences'));
			$this->template->set_var('lang_sf_project_id',lang('Sourceforge project ID'));

			$this->template->set_var('input_sf_project_id','<input name="preferences[sf_project_id]" value="' . $preferences['sf_project_id'] . '">');
			$this->template->set_var('input_submit','<input type="submit" name="submit" value="' . lang('Submit') . '">');

			$this->template->pfp('content','preferences');
		}

	}
