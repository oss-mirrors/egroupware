<?php
	/**************************************************************************\
	* phpGroupWare - projects                                                  *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille  [ceb@phpgroupware.org]                         *
	*          & Jens Lentfoehr <sw@lf.shlink.de>                              *
	* --------------------------------------------------------                 *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	if (!$id)
	{ 
		Header('Location: ' . $phpgw->link('/projects/index.php','sort=' . $sort . '&order=' . $order . '&query=' . $query . '&start=' . $start
											. '&filter=' . $filter . '&cat_id=' . $cat_id)); 
		$phpgw->common->phpgw_exit();
	}

	$phpgw_info['flags'] = array('currentapp' => 'projects',
					'enable_categories_class' => True);

	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('projects_edit' => 'form_sub.tpl'));
	$t->set_block('projects_edit','add','addhandle');
	$t->set_block('projects_edit','edit','edithandle');

	$projects = CreateObject('projects.projects');
	$grants = $phpgw->acl->get_grants('projects');
	$grants[$phpgw_info['user']['account_id']] = PHPGW_ACL_READ + PHPGW_ACL_ADD + PHPGW_ACL_EDIT + PHPGW_ACL_DELETE;

	$hidden_vars = '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="' . $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n"
				. '<input type="hidden" name="pro_parent" value="' . $pro_parent . '">' . "\n";

	if ($submit)
	{
		$errorcount = 0;

		if (!$num)
		{
			$error[$errorcount++] = lang('Please enter an ID !');
		}

		$phpgw->db->query("select count(*) from phpgw_p_projects where num='$num' and id != '$id'");
		$phpgw->db->next_record();
		if ($phpgw->db->f(0) != 0)
		{
			$error[$errorcount++] = lang('That ID has been used already !');
		}

		if (checkdate($smonth,$sday,$syear))
		{
			$sdate = mktime(2,0,0,$smonth,$sday,$syear);
		}
		else
		{
			if ($smonth && $sday && $syear)
			{
				$error[$errorcount++] = lang('You have entered an invalid start date !') . '<br>' . $smonth . '/' . $sday . '/' . $syear;
			}
		}

		if (checkdate($emonth,$eday,$eyear))
		{
			$edate = mktime(2,0,0,$emonth,$eday,$eyear);
		}
		else
		{
			if ($emonth && $eday && $eyear)
			{
				$error[$errorcount++] = lang('You have entered an invalid end date') . '<br>' . $emonth . '/' . $eday . '/' . $eyear;
			}
		}

		if (! $error)
		{
			$owner = $phpgw_info['user']['account_id'];
			$num = addslashes($num);
			$descr = addslashes($descr);
			$title = addslashes($title);

			if ($access)
			{
				$access = 'private';
			}
			else
			{
				$access = 'public';
			}

			if (!$budget)
			{
				$budget = 0;
			}

			$phpgw->db->query("update phpgw_p_projects set access='$access', entry_date='" . time() . "', start_date='"
							. "$sdate', end_date='$edate', coordinator='$coordinator', status='$status', descr='$descr', title='$title', "
							. "budget='$budget', num='$num' where id='$id'");

		}
	}

	if ($errorcount)
	{
		$t->set_var('message',$phpgw->common->error_list($error));
	}

	if (($submit) && (! $error) && (! $errorcount))
	{
		$t->set_var('message',lang('Job x x has been updated !',$num,$title));
	}

	if ((! $submit) && (! $error) && (! $errorcount))
	{
		$t->set_var('message','');
	}

	$phpgw->db->query("select * from phpgw_p_projects where id='$id'");
	$phpgw->db->next_record();
     
	if (isset($phpgw_info['user']['preferences']['common']['currency']))
	{
		$currency = $phpgw_info['user']['preferences']['common']['currency'];
		$t->set_var('error','');
		$t->set_var('currency',$currency);
	}
	else
	{
		$t->set_var('error',lang('Please select your currency in preferences !'));
	}

	$t->set_var('actionurl',$phpgw->link('/projects/edit_sub.php','id=' . $id . '&pro_parent=' . $pro_parent));
	$t->set_var('lang_action',lang('Edit job'));
	$t->set_var('hidden_vars',$hidden_vars);

    if ($pro_parent)
    {
        $t->set_var('lang_parent',lang('Main project'));

        $parent = $projects->read_single_project($pro_parent);

        $t->set_var('pro_parent',$phpgw->strip_html($parent[0]['number']) . ' ' . $phpgw->strip_html($parent[0]['title']));
        $t->set_var('category',$phpgw->categories->id2name($parent[0]['category']));
    }

	$t->set_var('lang_num',lang('Project ID'));
	$t->set_var('num',$phpgw->strip_html($phpgw->db->f('num')));
	$t->set_var('lang_choose','');
	$t->set_var('choose','');
	$t->set_var('lang_title',lang('Title'));
	$title  = $phpgw->strip_html($phpgw->db->f('title'));
	if (! $title)  $title  = '&nbsp;';
	$t->set_var('title',$title);
	$t->set_var('lang_descr',lang('Description'));
	$descrval  = $phpgw->strip_html($phpgw->db->f('descr'));
	if (! $descrval) $descrval = '&nbsp;';
	$t->set_var('descrval',$descrval);
	$t->set_var('lang_category',lang('Category'));

	$t->set_var('lang_status',lang('Status'));

	switch ($phpgw->db->f('status'))
	{
		case 'active':	$stat_sel[0]=' selected'; break;
		case 'nonactive':	$stat_sel[1]=' selected'; break;
	}

	$status_list = '<option value="active"' . $stat_sel[0] . '>' . lang('Active') . '</option>' . "\n"
				. '<option value="nonactive"' . $stat_sel[1] . '>' . lang('Nonactive') . '</option>' . "\n";

	$t->set_var('status_list',$status_list);
	$t->set_var('lang_budget',lang('Budget'));
	$t->set_var('budget',$phpgw->db->f('budget'));
	$t->set_var('lang_start_date',lang('Start date'));
	$t->set_var('lang_end_date',lang('Date due'));

	$sdate = $phpgw->db->f('start_date');
	$edate = $phpgw->db->f('end_date');

	$sm = CreateObject('phpgwapi.sbox');

	if (!$sdate)
	{
		$smonth = date('m',time());
		$sday = date('d',time());
		$syear = date('Y',time());
	}
	else
	{
		$smonth = date('m',$sdate);
		$sday = date('d',$sdate);
		$syear = date('Y',$sdate);
	}

	$t->set_var('start_date_select',$phpgw->common->dateformatorder($sm->getYears('syear',$syear),$sm->getMonthText('smonth',$smonth),$sm->getDays('sday',$sday)));

	if (!$edate)
	{ 
		$emonth = 0;
		$eday = 0;
		$eyear = 0;
	}
	else
	{
		$emonth = date('m',$edate);
		$eday = date('d',$edate);
		$eyear = date('Y',$edate);
	}

	$t->set_var('end_date_select',$phpgw->common->dateformatorder($sm->getYears('eyear',$eyear),$sm->getMonthText('emonth',$emonth),$sm->getDays('eday',$eday)));

	$t->set_var('lang_access',lang('Private'));

	if ($phpgw->db->f('access')=='private')
	{
		$t->set_var('access', '<input type="checkbox" name="access" value="True" checked>');
	}
	else
	{
		$t->set_var('access', '<input type="checkbox" name="access" value="True">');
	}

	$t->set_var('lang_coordinator',lang('Coordinator'));

	$employees = $phpgw->accounts->get_list('accounts', $start = '', $sort = '', $order = '', $query = '');
	while (list($null,$account) = each($employees))
	{
		$coordinator_list .= '<option value="' . $account['account_id'] . '"';
		if($account['account_id']==$phpgw->db->f('coordinator'))
			$coordinator_list .= ' selected';
			$coordinator_list .= '>'
			. $account['account_firstname'] . ' ' . $account['account_lastname'] . ' [ ' . $account['account_lid'] . ' ]' . '</option>' . "\n";
	}

	$t->set_var('coordinator_list',$coordinator_list);  

	$t->set_var('lang_edit',lang('Edit'));

	if ($projects->check_perms($grants[$phpgw->db->f('coordinator')],PHPGW_ACL_DELETE) || $phpgw->db->f('coordinator') == $phpgw_info['user']['account_id'])
	{
		$t->set_var('delete','<form method="POST" action="' . $phpgw->link('/projects/delete.php','id=' . $id . '&cat_id=' . $cat_id . '&start=' . $start
								. '&sort=' . $sort . '&order=' . $order . '&query=' . $query . '&filter=' . $filter) . '"><input type="submit" value="' . lang('Delete') .'"></form>');
	}
	else
	{
		$t->set_var('delete','&nbsp;');
	}

	$t->set_var('lang_done',lang('Done'));
	$t->set_var('done_url',$phpgw->link('/projects/sub_projects.php','cat_id=' . $cat_id . '&sort=' . $sort . '&order=' . $order . '&query=' . $query
										. '&start=' . $start . '&filter=' . $filter));

	$t->set_var('edithandle','');
	$t->set_var('addhandle','');
	$t->pparse('out','projects_edit');
	$t->pparse('edithandle','edit');

	$phpgw->common->phpgw_footer();
?>
