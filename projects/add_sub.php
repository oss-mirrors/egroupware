<?php
	/**************************************************************************\
	* phpGroupWare - projects                                                  *
	* (http://www.phpgroupware.org)                                            *
	* Written by Bettina Gille [ceb@phpgroupware.org]                          *
	*          & Jens Lentfoehr <sw@lf.shlink.de>                              *
	* -----------------------------------------------                          *
	* This program is free software; you can redistribute it and/or modify it  *
	* under the terms of the GNU General Public License as published by the    *
	* Free Software Foundation; either version 2 of the License, or (at your   *
	* option) any later version.                                               *
	\**************************************************************************/
	/* $Id$ */

	$phpgw_info['flags'] = array('currentapp' => 'projects',
					'enable_categories_class' => True);
	include('../header.inc.php');

	$t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	$t->set_file(array('sub_add' => 'form_sub.tpl'));
	$t->set_block('sub_add','add','addhandle');
	$t->set_block('sub_add','edit','edithandle');

	$projects = CreateObject('projects.projects');

	if ($phpgw_info['server']['db_type']=='pgsql')
	{
		$join = " JOIN ";
	}
	else
	{
		$join = " LEFT JOIN ";
	}

	$db2 = $phpgw->db;

	if ($submit)
	{
		if ($choose)
		{
			$num = create_projectid($year);
		}
		else
		{
			$num = addslashes($num);
		}

		$errorcount = 0;

		if (!$num)
		{
			$error[$errorcount++] = lang('Please enter an ID !');
		}

		$phpgw->db->query("select count(*) from phpgw_p_projects where num='$num'");
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
				$error[$errorcount++] = lang('You have entered an invalid end date !') . '<br>' . $emonth . '/' . $eday . '/' . $eyear;
			}
		}

		if ((!$ba_activities) && (!$bill_activities))
		{
			$error[$errorcount++] = lang('Please choose activities for that project first !');
		}

		if (! $error)
		{
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

			$owner = $phpgw_info['user']['account_id'];
			$descr = addslashes($descr);
			$title = addslashes($title);

			$phpgw->db->query("insert into phpgw_p_projects (owner,access,category,entry_date,start_date,end_date,coordinator,customer,status,"
							. "descr,title,budget,num,parent) values ('$owner','$access','$cat_id','" . time() ."','$sdate','$edate',"
							. "'$coordinator','$abid','$status','$descr','$title','$budget','$num','$pro_parent')");

			$db2->query("SELECT max(id) AS max FROM phpgw_p_projects");
			if($db2->next_record())
			{
				$p_id = $db2->f('max');
				if (count($ba_activities) != 0)
				{
					while($activ=each($ba_activities))
					{
						$phpgw->db->query("insert into phpgw_p_projectactivities (project_id,activity_id,billable) values ('$p_id','$activ[1]','N')");
					}
				}

				if (count($bill_activities) != 0)
				{
					while($activ=each($bill_activities))
					{
						$phpgw->db->query("insert into phpgw_p_projectactivities (project_id,activity_id,billable) values ('$p_id','$activ[1]','Y')");
					}
				}
			}
		}
	}

	if ($errorcount)
	{
		$t->set_var('message',$phpgw->common->error_list($error));
	}

	if (($submit) && (! $error) && (! $errorcount))
	{
		$t->set_var('message',lang('Job x x has been added !',$num,$title)); break;
	}

	if ((! $submit) && (! $error) && (! $errorcount))
	{
		$t->set_var('message','');
	}

	$t->set_var('actionurl',$phpgw->link('/projects/add.php'));
	$t->set_var('addressbook_link',$phpgw->link('/projects/addressbook.php','query='));
	$t->set_var('lang_action',lang('Add job'));

	if (isset($phpgw_info['user']['preferences']['common']['currency']))
	{
		$currency = $phpgw_info['user']['preferences']['common']['currency'];
		$t->set_var('error','');
		$t->set_var('currency',$currency);
	}
	else
	{
		$t->set_var('error',lang('Please set your preferences for this application'));
	}

	$hidden_vars = '<input type="hidden" name="id" value="' . $id . '">' . "\n"
				. '<input type="hidden" name="sort" value="' . $sort . '">' . "\n"
				. '<input type="hidden" name="order" value="' . $order . '">' . "\n"
				. '<input type="hidden" name="query" value="' . $query . '">' . "\n"
				. '<input type="hidden" name="start" value="' . $start . '">' . "\n"
				. '<input type="hidden" name="filter" value="' . $filter . '">' . "\n"
				. '<input type="hidden" name="pro_parent" value="' . $pro_parent . '">' . "\n"
				. '<input type="hidden" name="cat_id" value="' . $cat_id . '">' . "\n";

	$t->set_var('hidden_vars',$hidden_vars);
	$t->set_var('lang_num',lang('Project ID'));
	$t->set_var('num',$num);

	if ($pro_parent)
	{
		$t->set_var('lang_parent',lang('Main project'));

		$parent = $projects->read_single_project($pro_parent);

		$t->set_var('pro_parent',$parent[0]['title']);
		$t->set_var('category_list',$phpgw->categories->id2name($parent[0]['category']));
	}

	if (! $submit)
	{
		$t->set_var('lang_choose',lang('Generate Job ID ?'));
		$t->set_var('choose','<input type="checkbox" name="choose" value="True">');
	}
	else
	{
		$t->set_var('lang_choose','');
		$t->set_var('choose',''); 
	}

	$t->set_var('lang_title',lang('Title'));
	$t->set_var('title',$title);
	$t->set_var('lang_descr',lang('Description'));
	$t->set_var('descrval',$descr);
	$t->set_var('lang_category',lang('Category'));

	$t->set_var('lang_status',lang('Status'));

	switch($status)
	{
		case 'active':		$stat_sel[0]=' selected'; break;
		case 'nonactive':	$stat_sel[1]=' selected'; break;
		case 'archive':		$stat_sel[2]=' selected'; break;
	}

	$status_list = '<option value="active"' . $stat_sel[0] . '>' . lang('Active') . '</option>' . "\n"
				. '<option value="nonactive"' . $stat_sel[1] . '>' . lang('Nonactive') . '</option>' . "\n"
				. '<option value="archive"' . $stat_sel[2] . '>' . lang('Archive') . '</option>' . "\n";

	$t->set_var('status_list',$status_list);
	$t->set_var('lang_budget',lang('Budget'));
	$t->set_var('lang_start_date',lang('Start date'));
	$t->set_var('lang_end_date',lang('Date due'));
	$t->set_var('budget',$budget);

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

	$t->set_var('lang_coordinator',lang('Coordinator'));

	$employees = $phpgw->accounts->get_list('accounts', $start, $sort, $order, $query);
	while (list($null,$account) = each($employees))
	{
		$coordinator_list .= '<option value="' . $account['account_id'] . '"';
		if($account['account_id']==$phpgw_info['user']['account_id'])
			$coordinator_list .= ' selected';
			$coordinator_list .= '>'
			. $account['account_firstname'] . ' ' . $account['account_lastname'] . ' [ ' . $account['account_lid'] . ' ]' . '</option>' . "\n";
	}

	$t->set_var('coordinator_list',$coordinator_list);

	$t->set_var('lang_select',lang('Select per button !'));
	$t->set_var('lang_customer',lang('Customer'));
	$t->set_var('abid',$abid);

	if (! $submit)
	{
		$t->set_var('name',$name);
	}
	else
	{
		$d = CreateObject('phpgwapi.contacts');
		$cols = array('n_given' => 'n_given',
					'n_family' => 'n_family',
					'org_name' => 'org_name');

		$customer = $d->read_single_entry($abid,$cols);

		if ($customer[0]['org_name'] == '')
		{
			$t->set_var('name',$customer[0]['n_given'] . ' ' . $customer[0]['n_family']);
		}
		else
		{
			$t->set_var('name',$customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]');
		}
	}

	$t->set_var('lang_bookable_activities',lang('Bookable activities'));
	$t->set_var('lang_billable_activities',lang('Billable activities'));
	$t->set_var('lang_access',lang('Private'));

	if ($access)
	{
		$t->set_var('access','<input type="checkbox" name="access" value="True" checked>');
	}
	else
	{
		$t->set_var('access','<input type="checkbox" name="access" value="True">');
	}

// ------------ activites bookable ----------------------

	$projects = CreateObject('projects.projects');

	$t->set_var('ba_activities_list',$projects->select_activities_list($p_id,False));

// -------------- activities billable ---------------------- 

    $t->set_var('bill_activities_list',$projects->select_activities_list($p_id,True));

	$t->set_var('lang_add',lang('Add'));
	$t->set_var('lang_reset',lang('Clear Form'));
	$t->set_var('lang_done',lang('Done'));
	$t->set_var('done_url',$phpgw->link('/projects/index.php','sort=' . $sort . '&order=' . $order . '&query=' . $query . '&start=' . $start
										. '&filter=' . $filter . '&cat_id=' . $cat_id));

	$t->set_var('edithandle','');
	$t->set_var('addhandle','');
	$t->pparse('out','projects_add');
	$t->pparse('addhandle','add');

	$phpgw->common->phpgw_footer();
?>
