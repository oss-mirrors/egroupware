<?php

	class uisf_project_tracker
	{
		var $bo;
		var $template;
		var $public_functions = array(
				'display_tracker' => True
			);

		function uisf_project_tracker()
		{
			global $phpgw;

			$this->bo       = createobject('developer_tools.bosf_project_tracker');
			$this->template = $phpgw->template;
		}

		function display_tracker()
		{
			global $phpgw, $phpgw_info;
			$phpgw->common->phpgw_header();
			echo parse_navbar();
			include(PHPGW_APP_INC . '/header.inc.php');

			$this->template->set_file(array(
				'sf_project' => 'sf_project.tpl'
			));
			$this->template->set_var('lang_header',lang('Sourceforge project tracker'));
			$this->template->set_var('project_html',$this->bo->display_tracker());
			$this->template->pfp('out','sf_project');
		}

	}