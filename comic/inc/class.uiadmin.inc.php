<?php

/*************************************************************************\
* Daily Comics (phpGroupWare application)                                 *
* http://www.phpgroupware.org                                             *
* This file is written by: Sam Wynn <neotexan@wynnsite.com>               *
*                          Rick Bakker <r.bakker@linvision.com>           *
* --------------------------------------------                            *
* This program is free software; you can redistribute it and/or modify it *
* under the terms of the GNU General Public License as published by the   *
* Free Software Foundation; either version 2 of the License, or (at your  *
* option) any later version.                                              *
\*************************************************************************/

/* $Id$ */

class uiadmin
{
	var $bo;
	var $functions;

	var $public_functions = array(
		'uiadmin'		=> TRUE,
		'global_options'	=> TRUE,
		'get_form'		=> TRUE
	);

	function uiadmin()
	{
		$this->bo		= CreateObject('comic.boadmin');
		$this->functions	= CreateObject('comic.bofunctions');
	}

	function global_options()
	{
		$submit = get_var('submit',Array('POST'));
		if($submit!='')
		{
			if($submit==lang('Submit'))
			{
				$field = $this->get_form();
				// checks can be added here.
				$field['message'] = $this->bo->update_global_options($field);
			}
			if($submit==lang('Done'))
			{
				header('Location: '.$GLOBALS['phpgw']->link('/admin/index.php'));
			}
		}
		else
		{
			$field           = $this->bo->admin_global_options_data();
			$field['message'] = '';
		}

		$g_censor_level = $this->functions->select_box('g_censor_level');
		$g_image_source = $this->functions->select_box('g_image_source');

		$field['title']                  = lang('Daily Comics - Global Options');
		$field['image_source_label']     = lang('Image Source');
		$field['remote_enabled_label']   = lang('Remote (Parse/Snarf) Enabled');
		$field['censor_level_label']     = lang('Censorship Level');
		$field['filesize_label']         = lang('Max File size');
		$field['override_enabled_label'] = lang('Censorship Override Enabled');
		$field['submit']                 = lang('Submit');
		$field['reset']                  = lang('Reset');
		$field['done']                   = lang('Done');
		$field['action_url']             = $GLOBALS['phpgw']->link('/index.php','menuaction=comic.uiadmin.global_options');

		$GLOBALS['phpgw']->common->phpgw_header();
		// echo parse_navbar();
		print(parse_navbar());

		if ($field['remote_enabled'] == 1)
		{
			$field['remote_enabled'] = 'checked';
		}
		else
		{
			$field['remote_enabled'] = '';
		}

		if ($field['override_enabled'] == 1)
		{
			$field['override_enabled'] = 'checked';
		}
		else
		{
			$field['override_enabled'] = '';
		}

		$options_tpl = CreateObject('phpgwapi.Template',$GLOBALS['phpgw']->common->get_tpl_dir('comic'));
		$options_tpl->set_unknowns('remove');
		$options_tpl->set_file(array(coptions  => 'option.common.tpl'));

		for ($loop = 0; $loop < count($g_censor_level); $loop++)
		{
			$selected = '';
			if ($field['censor_level'] == $loop)
			{
				$selected = 'selected';
			}
			$options_tpl->set_var(array(OPTION_VALUE    => $loop,
				OPTION_SELECTED => $selected,
				OPTION_NAME     => $g_censor_level[$loop]));
			$options_tpl->parse(option_list, 'coptions', TRUE);
		}
		$field['censor_level_options'] = $options_tpl->get('option_list');

		for ($loop = 0; $loop < count($g_image_source); $loop++)
		{
			$selected = '';
			if ($field['image_source'] == $loop)
			{
				$selected = 'selected';
			}
			$options_tpl->set_var(array(OPTION_VALUE    => $loop,
				OPTION_SELECTED => $selected,
				OPTION_NAME     => $g_image_source[$loop]));
			$options_tpl->parse(option_list2, 'coptions', TRUE);
		}
		$field['image_source_options'] = $options_tpl->get('option_list2');

		$GLOBALS['phpgw']->template->set_file(array('main'=>'admin_global_options.tpl'));
		$GLOBALS['phpgw']->template->set_var(array(
			'action_url'			=> $field['action_url'],
			'title_color'			=> $GLOBALS['phpgw_info']['theme']['th_bg'],
			'title'				=> $field['title'],
			'row_1_color'			=> $this->functions->row_color(),
			'message'			=> $field['message'],
			'censor_level_color'		=> $this->functions->row_color(),
			'censor_level_label'		=> $field['censor_level_label'],
			'censor_level_options'		=> $field['censor_level_options'],
			'override_enabled_color' 	=> $this->functions->row_color(),
			'override_enabled_label'	=> $field['override_enabled_label'],
			'override_enabled'		=> $field['override_enabled'],
			'image_source_color'		=> $this->functions->row_color(),
			'image_source_label'		=> $field['image_source_label'],
			'image_source_options'		=> $field['image_source_options'],
			'remote_enabled_color'		=> $this->functions->row_color(),
			'remote_enabled_label'		=> $field['remote_enabled_label'],
			'remote_enabled'		=> $field['remote_enabled'],
			'filesize_color'		=> $this->functions->row_color(),
			'filesize_label'		=> $field['filesize_label'],
			'filesize'			=> $field['filesize'],
			'row_2_color'			=> $this->functions->row_color(),
			'submit'			=> $field['submit'],
			'reset'				=> $field['reset'],
			'done'				=> $field['done']));
		$GLOBALS['phpgw']->template->parse('out', 'main', TRUE);
		$GLOBALS['phpgw']->template->p('out');
	}

	function get_form()
	{
		$flist = Array(
			'censor_level',
			'override_enabled',
			'image_source',
			'remote_enabled',
			'filesize'
		);
		for($i=0;$i<count($flist);$i++)
		{
			$field[$flist[$i]] = get_var($flist[$i],Array('POST'));
		}
		return ($field);
	}
}

?>
