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

	/* $Id$ */

	/* Note to self:
	** Self ... heres the query to use when limiting access to entrys within a group
	** The acl class *might* handle this instead .... not sure
	** select distinct group_ticket_id, phpgw_tts_groups.group_ticket_id, phpgw_tts_tickets.*
	** from phpgw_tts_tickets, phpgw_tts_groups where ticket_id = group_ticket_id and group_id in (14,15);
	*/

	/* ACL levels
	** 1 - Read ticket within your group only
	** 2 - Close ticket
	** 4 - Allow to make changes to priority, billing hours, billing rate, category, and assigned to
	*/

	$GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
	$GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
	$GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
	$GLOBALS['phpgw_info']['flags']['noheader'] = True;
	include('../header.inc.php');

	// select what rows
	$cat_group_id = intval(get_var('cat_group_id',array('POST','GET')));
    $cat_id = intval(get_var('cat_id',array('POST','GET')));
    $account_id = intval(get_var('account_id',array('POST','GET')));

	if($_POST['delete'] && $cat_group_id)
	{
		$GLOBALS['phpgw']->db->query("delete from phpgw_tts_categories_groups where cat_group_id=$cat_group_id",__LINE__,__FILE__);
	}

	if ($_POST['delete'] || $_POST['cancel'] || !$cat_group_id)
	{
		$GLOBALS['phpgw']->redirect_link('/tts/cat_group.php');
	}

	$GLOBALS['phpgw']->template->set_file('delete_cat_group','delete_cat_group.tpl');

	$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'].
		' - '.lang('Deleting the category and group association');
	$GLOBALS['phpgw']->common->phpgw_header();

	$s1=id2field('phpgw_categories','cat_name','cat_id',$cat_id);
    $s2=id2field('phpgw_accounts','account_lid','account_id',$account_id);
	$GLOBALS['phpgw']->template->set_var('lang_are_you_sure',lang('You want to delete the association between category %1 and group %2. Are you sure?',"'".$s1."'","'".$s2."'"));

	$GLOBALS['phpgw']->template->set_var('delete_cat_group_link',
		$GLOBALS['phpgw']->link('/tts/delete_cat_group.php','cat_group_id='.$cat_group_id));
	$GLOBALS['phpgw']->template->set_var('lang_delete',lang('Delete'));
	$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));

	$GLOBALS['phpgw']->template->pfp('out','delete_cat_group');

	$GLOBALS['phpgw']->common->phpgw_footer();
?>
