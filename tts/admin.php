<?php
  /**************************************************************************\
  * phpGroupWare - TTS                                                       *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp'  => 'tts', 
		'noheader'    => True, 
		'nonavbar'    => True, 
		'noappheader' => True,
		'noappfooter' => True,
		'enable_config_class'     => True,
		'enable_nextmatchs_class' => True
	);

	include('../header.inc.php');

	$option_names = array(lang('Disabled'), lang('Users choice'), lang('Force'));
	$owner_selected = array ();
	$group_selected = array ();
	$assigned_selected = array ();

	$phpgw->config->read_repository();

	if ($submit)
	{
		if ($usemailnotification)
		{
			$phpgw->config->config_data['mailnotification'] = True;
		} else {
			unset($phpgw->config->config_data['mailnotification']);
		}

		if ($ownernotification)
		{
			$phpgw->config->config_data['ownernotification'] = $ownernotification;
		} else {
			unset($phpgw->config->config_data['ownernotification']);
		}

		if ($groupnotification)
		{
			$phpgw->config->config_data['groupnotification'] = $groupnotification;
		} else	{
			unset($phpgw->config->config_data['groupnotification']);
		}

		if ($assignednotification)
		{
			$phpgw->config->config_data['assignednotification'] = $assignednotification;
		} else {
			unset($phpgw->config->config_data['assignednotification']);
		}

		$phpgw->config->save_repository(True);
		Header('Location: ' . $phpgw->link('/admin/index.php'));
	}

	$phpgw->common->phpgw_header();
	echo parse_navbar();

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('admin' => 'admin.tpl'));
	$t->set_block('admin', 'tts_select_options','tts_select_options');

	$t->set_var('action_url',$phpgw->link('/tts/admin.php'));

	$tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
	$t->set_var('tr_color',$tr_color);

	$t->set_var('lang_mailnotification',lang('Use email notification'));
	if ($phpgw->config->config_data['mailnotification'])
	{
		$t->set_var('mailnotification',' checked');
	} else {
		$t->set_var('mailnotification','');
	}

	$t->set_var('lang_ownernotification',lang('Owner'));
	if ($phpgw->config->config_data['ownernotification'])
	{
		$owner_selected[$phpgw->config->config_data['ownernotification']]=' selected';
	//	$t->set_var('ownernotification',' checked');
	} else {
	//	$t->set_var('ownernotification','');
	}

	$t->set_var('lang_groupnotification',lang('Group'));
	if ($phpgw->config->config_data['groupnotification'])
	{
		$group_selected[$phpgw->config->config_data['groupnotification']]=' selected';
	//	$t->set_var('groupnotification',' checked');
	} else {
	//	$t->set_var('groupnotification','');
	}
	$t->set_var('lang_assignednotification',lang('Assigned to'));
	if ($phpgw->config->config_data['assignednotification'])
	{
		$assigned_selected[$phpgw->config->config_data['assignednotification']]=' selected';
	//	$t->set_var('assignednotification',' checked');
	} else {
	//	$t->set_var('assignednotification','');
	}

        for ($i=0; $i<3; $i++) {
	    $t->set_var('tts_optionname', $option_names[$i]);
	    $t->set_var('tts_optionvalue', $i);
	    $t->set_var('tts_optionselected', $owner_selected[$i]);
	    $t->parse('tts_owneroptions','tts_select_options',true);
	}
	
        for ($i=0; $i<3; $i++) {
	    $t->set_var('tts_optionname', $option_names[$i]);
	    $t->set_var('tts_optionvalue', $i);
	    $t->set_var('tts_optionselected', $group_selected[$i]);
	    $t->parse('tts_groupoptions','tts_select_options',true);
	}
	
        for ($i=0; $i<3; $i++) {
	    $t->set_var('tts_optionname', $option_names[$i]);
	    $t->set_var('tts_optionvalue', $i);
	    $t->set_var('tts_optionselected', $assigned_selected[$i]);
	    $t->parse('tts_assignedoptions','tts_select_options',true);
	}

	$t->set_var('lang_admin',lang('TTS').' '.lang('Admin'));
	$t->set_var('lang_submit',lang('submit'));
	$t->set_var('tts_select_options','');
	
	$t->pparse('out','admin');
	$phpgw->common->phpgw_footer();
?>
