<?php
	/**************************************************************************\
	* eGroupWare - Trouble Ticket System                                       *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	// $Id$
	// $Source$

	$GLOBALS['phpgw_info']['flags']['currentapp']          = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_send_class']   = True;
	$GLOBALS['phpgw_info']['flags']['enable_config_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
	$GLOBALS['phpgw_info']['flags']['noheader']            = True;

	include('../header.inc.php');

	$cat_group_id = intval(get_var('cat_group_id',array('POST','GET')));

	if($_POST['cancel'])
	{
		$GLOBALS['phpgw']->redirect_link('/tts/cat_group.php');
	}

	$GLOBALS['phpgw']->config->read_repository();

	if($_POST['save'])
	{
		$cat_group = $_POST['cat_group'];
		if (get_magic_quotes_gpc())
		{
			foreach(array('name','description') as $name)
			{
				$transition[$name] = stripslashes($transition[$name]);
			}
		}

		if (!$cat_group_id)
		{
			$GLOBALS['phpgw']->db->query("insert into phpgw_tts_categories_groups (cat_id,account_id) values ("
			. intval($cat_group['cat_id']) . ", "
			. intval($cat_group['account_id']). ")",__LINE__,__FILE__);
		}
		else
		{
			$GLOBALS['phpgw']->db->query("update phpgw_tts_categories_groups "
				. " set cat_id=". intval($cat_group['cat_id']). ", "
				. " account_id=". intval($cat_group['account_id'])
				. " WHERE cat_group_id=".$cat_group_id,__LINE__,__FILE__);

		}
		$GLOBALS['phpgw']->redirect_link('/tts/cat_group.php');
	}
	else
	{
		$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'].
			' - '.($cat_group_id ? lang('Edit the category and group association') : lang('Create new category and group association'));
		$GLOBALS['phpgw']->common->phpgw_header();

		// select the ticket that you selected
		$GLOBALS['phpgw']->db->query("select * from phpgw_tts_categories_groups where cat_group_id='$cat_group_id'",__LINE__,__FILE__);
		$GLOBALS['phpgw']->db->next_record();

		$cat_group['cat_id']   = $GLOBALS['phpgw']->db->f('cat_id');
		$cat_group['account_id']    = $GLOBALS['phpgw']->db->f('account_id');

		$GLOBALS['phpgw']->template->set_file(array(
			'edit_cat_group'   => 'edit_cat_group.tpl'
		));
		$GLOBALS['phpgw']->template->set_block('edit_cat_group','form');

		$GLOBALS['phpgw']->template->set_var('form_action', $GLOBALS['phpgw']->link('/tts/edit_cat_group.php','&cat_group_id='.$cat_group_id));

		$GLOBALS['phpgw']->template->set_var('lang_cat_name',lang('Category'));
		$GLOBALS['phpgw']->template->set_var('lang_group_name', lang('Group'));
		$GLOBALS['phpgw']->template->set_var('lang_save',lang('Save'));
		$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));

		$GLOBALS['phpgw']->template->set_var('options_cat_id',listid_field('phpgw_categories','cat_name','cat_id',$cat_group['cat_id'],'cat_appname="tts" and cat_owner="-1"'));
		$GLOBALS['phpgw']->template->set_var('options_account_id',listid_field('phpgw_accounts','account_lid','account_id',$cat_group['account_id'],'account_type="g"'));

		$GLOBALS['phpgw']->template->pfp('out','form');
		$GLOBALS['phpgw']->common->phpgw_footer();
	}

?>
