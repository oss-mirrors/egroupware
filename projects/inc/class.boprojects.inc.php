<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	* This program is part of the GNU project, see http://www.gnu.org/	*
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright 2000 - 2003 Free Software Foundation, Inc               *
	*                                                                   *
	* This program is free software; you can redistribute it and/or     *
	* modify it under the terms of the GNU General Public License as    *
	* published by the Free Software Foundation; either version 2 of    *
	* the License, or (at your option) any later version.               *
	*                                                                   *
	* This program is distributed in the hope that it will be useful,   *
	* but WITHOUT ANY WARRANTY; without even the implied warranty of    *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  *
	* General Public License for more details.                          *
	*                                                                   *
	* You should have received a copy of the GNU General Public License *
	* along with this program; if not, write to the Free Software       *
	* Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.         *
	\*******************************************************************/
	/* $Id$ */
	// $Source$

	class boprojects
	{
		var $action;
		var $start;
		var $query;
		var $filter;
		var $order;
		var $sort;
		var $cat_id;
		var $status;

		var $public_functions = array
		(
			'cached_accounts'			=> True,
			'list_projects'				=> True,
			'check_perms'				=> True,
			'check_values'				=> True,
			'select_project_list'		=> True,
			'check_act_values'			=> True,
			'save_project'				=> True,
			'read_single_project'		=> True,
			'delete_pa'					=> True,
			'exists'					=> True,
			'isprojectadmin'			=> True,
			'select_activity_list'		=> True,
			'employee_list'				=> True,
			'check_prefs'				=> True,
			'get_prefs'					=> True,
			'list_activities'			=> True,
			'read_single_activity'		=> True,
			'save_activity'				=> True,
			'read_abook'				=> True,
			'read_single_contact'		=> True,
			'read_prefs'				=> True,
			'save_prefs'				=> True,
			'return_value'				=> True,
			'select_activities_list'	=> True,
			'select_pro_activities'		=> True,
			'select_hours_activities'	=> True,
			'change_owner'				=> True,
			'activities_list'			=> True
		);

		function boprojects($session=False, $action = '')
		{
			$this->so		= CreateObject('projects.soprojects');
			$this->sohours	= CreateObject('projects.soprojecthours');
			$this->contacts	= CreateObject('phpgwapi.contacts');
			$this->cats		= CreateObject('phpgwapi.categories');

			if ($session)
			{
				$this->read_sessiondata($action);
				$this->use_session = True;
			}

			$start	= get_var('start',array('POST','GET'));
			$query	= get_var('query',array('POST','GET'));
			$sort	= get_var('sort',array('POST','GET'));
			$order	= get_var('order',array('POST','GET'));
			$cat_id	= get_var('cat_id',array('POST','GET'));
			$filter	= get_var('filter',array('POST','GET'));
			$status	= get_var('status',array('POST','GET'));

			if(!empty($start) || ($start == '0') || ($start == 0))
			{
				if($this->debug) { echo '<br>overriding $start: "' . $this->start . '" now "' . $start . '"'; }
				$this->start = $start;
			}

			if(isset($query)) { $this->query = $query; }
			if(!empty($filter)) { $this->filter = $filter; }
			if(isset($sort)) { $this->sort = $sort; }
			if(isset($order)) { $this->order = $order; }
			if(isset($status)) { $this->status = $status; }
			if(isset($cat_id) && !empty($cat_id))
			{
				$this->cat_id = $cat_id;
			}
			if($cat_id == '0' || $cat_id == 0 || $cat_id == 'none' || $cat_id == '')
			{
				unset($this->cat_id);
			}
		}

		function type($action)
		{
			switch ($action)
			{
				case 'mains'	: $column = 'projects_mains'; break;
				case 'subs'		: $column = 'projects_subs'; break;
				case 'act'		: $column = 'projects_act'; break;
				case 'pad'		: $column = 'projects_pad'; break;
				case 'pbo'		: $column = 'projects_pbo'; break;
				case 'amains'	: $column = 'projects_amains'; break;
				case 'asubs'	: $column = 'projects_asubs'; break;
				case 'ustat'	: $column = 'projects_ustat'; break;
				case 'bill'		: $column = 'projects_bill'; break;
				case 'del'		: $column = 'projects_del'; break;
			}
			return $column;
		}

		function save_sessiondata($data, $action)
		{
			if ($this->use_session)
			{
				$column = $this->type($action);
				$GLOBALS['phpgw']->session->appsession('session_data',$column, $data);
			}
		}

		function read_sessiondata($action)
		{
			$column = $this->type($action);
			$data = $GLOBALS['phpgw']->session->appsession('session_data',$column);

			$this->start	= $data['start'];
			$this->query	= $data['query'];
			$this->filter	= $data['filter'];
			$this->order	= $data['order'];
			$this->sort		= $data['sort'];
			$this->cat_id	= $data['cat_id'];
			$this->status	= $data['status'];
		}

		function check_perms($has, $needed)
		{
			return (!!($has & $needed) == True);
		}

		function cached_accounts($account_id)
		{
			$this->accounts = CreateObject('phpgwapi.accounts',$account_id);

			$this->accounts->read_repository();

			$cached_data[$this->accounts->data['account_id']]['account_id']		= $this->accounts->data['account_id'];
			$cached_data[$this->accounts->data['account_id']]['account_lid']	= $this->accounts->data['account_lid'];
			$cached_data[$this->accounts->data['account_id']]['firstname']		= $this->accounts->data['firstname'];
			$cached_data[$this->accounts->data['account_id']]['lastname']		= $this->accounts->data['lastname'];

			return $cached_data;
		}

		function return_date()
		{
			$date = array
			(
				'month'		=> $GLOBALS['phpgw']->common->show_date(time(),'n'),
				'day'		=> $GLOBALS['phpgw']->common->show_date(time(),'d'),
				'year'		=> $GLOBALS['phpgw']->common->show_date(time(),'Y')
			);

			$date['daydate']		= mktime(2,0,0,$date['month'],$date['day'],$date['year']);
			$date['monthdate']		= mktime(2,0,0,$date['month']+2,0,$date['year']);
			$date['monthformatted'] = $GLOBALS['phpgw']->common->show_date($date['monthdate'],'n/Y');
			return $date;
		}

		function read_abook($start, $query, $qfilter, $sort, $order)
		{
			$account_id = $GLOBALS['phpgw_info']['user']['account_id'];

			$cols = array('n_given'	=> 'n_given',
						'n_family'	=> 'n_family',
						'org_name'	=> 'org_name');

			$entries = $this->contacts->read($start,$GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'], $cols, $query, $qfilter, $sort, $order, $account_id);
			$this->total_records = $this->contacts->total_records;
			return $entries;
		}

		function read_single_contact($abid)
		{
			$cols = array('n_given' => 'n_given',
						'n_family' => 'n_family',
						'org_name' => 'org_name');

			return $this->contacts->read_single_entry($abid,$cols);
		}

		function return_value($action,$item)
		{
			return $this->so->return_value($action,$item);
		}

		function list_pcosts($project_id)
		{
			return $this->so->list_pcosts($project_id);
		}

		function read_prefs()
		{
			$GLOBALS['phpgw']->preferences->read_repository();

			$prefs = array();

			if ($GLOBALS['phpgw_info']['user']['preferences']['projects'])
			{
				$prefs['tax'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['tax'];
				$prefs['abid'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['abid'];
				$prefs['bill'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'];
				$prefs['ifont'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['ifont'];
				$prefs['mysize'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['mysize'];
				$prefs['allsize'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['allsize'];
				$prefs['notify_mstone'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['notify_mstone'];
				$prefs['notify_pro'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['notify_pro'];
				$prefs['notify_assign'] = $GLOBALS['phpgw_info']['user']['preferences']['projects']['notify_assign'];
			}
			return $prefs;
		}

		function save_prefs($prefs)
		{
			$GLOBALS['phpgw']->preferences->read_repository();

			if (is_array($prefs))
			{
				$GLOBALS['phpgw']->preferences->change('projects','tax',$prefs['tax']);
				$GLOBALS['phpgw']->preferences->change('projects','abid',$prefs['abid']);
				$GLOBALS['phpgw']->preferences->change('projects','bill',$prefs['bill']);
				$GLOBALS['phpgw']->preferences->change('projects','ifont',$prefs['ifont']);
				$GLOBALS['phpgw']->preferences->change('projects','mysize',$prefs['mysize']);
				$GLOBALS['phpgw']->preferences->change('projects','allsize',$prefs['allsize']);
				$GLOBALS['phpgw']->preferences->change('projects','notify_mstone',(isset($prefs['notify_mstone'])?'yes':''));
				$GLOBALS['phpgw']->preferences->change('projects','notify_pro',(isset($prefs['notify_pro'])?'yes':''));
				$GLOBALS['phpgw']->preferences->change('projects','notify_assign',(isset($prefs['notify_assign'])?'yes':''));

				$GLOBALS['phpgw']->preferences->save_repository(True);
		//	_debug_array($prefs);
		//	exit;
			}

			if ($prefs['oldbill'] == 'h' && $prefs['bill'] == 'wu')
			{
				return True;
			}
			else
			{
				return False;
			}
		}

		function check_prefs()
		{
			$prefs = $this->get_prefs();

			if (! isset($prefs['country']) || (! isset($prefs['currency'])))
			{
				$error[] = lang('please specify country and currency in the global preferences section');
			}

			if ($this->isprojectadmin('pad') || $this->isprojectadmin('pbo'))
			{
				if (! isset($prefs['abid']) || (! isset($prefs['tax'])) || (! isset($prefs['bill'])) || (! isset($prefs['ifont'])) || (! isset($prefs['mysize'])) || (! isset($prefs['allsize'])))
				{
					$error[] = lang('if you are an administrator, please set the preferences for this application');
					$error[] = lang('if you are not an administrator, please inform the administrator to set the preferences for this application');
				}
			}
			return $error;
		}

		function get_prefs()
		{
			$GLOBALS['phpgw']->preferences->read_repository();

			$prefs = array();

			$prefs['currency']	= $GLOBALS['phpgw_info']['user']['preferences']['common']['currency'];
			$prefs['country']	= $GLOBALS['phpgw_info']['user']['preferences']['common']['country'];

			if ($GLOBALS['phpgw_info']['user']['preferences']['projects'])
			{
				if ($this->isprojectadmin('pad') || $this->isprojectadmin('pbo'))
				{
					$prefs['abid']		= $GLOBALS['phpgw_info']['user']['preferences']['projects']['abid'];
					$prefs['tax']		= $GLOBALS['phpgw_info']['user']['preferences']['projects']['tax'];
					$prefs['bill']		= $GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'];
					$prefs['ifont']		= $GLOBALS['phpgw_info']['user']['preferences']['projects']['ifont'];
					$prefs['mysize']	= $GLOBALS['phpgw_info']['user']['preferences']['projects']['mysize'];
					$prefs['allsize']	= $GLOBALS['phpgw_info']['user']['preferences']['projects']['allsize'];
				}
				$prefs['notify_mstone']	= $GLOBALS['phpgw_info']['user']['preferences']['projects']['notify_mstone'];
				$prefs['notify_pro']	= $GLOBALS['phpgw_info']['user']['preferences']['projects']['notify_pro'];
				$prefs['notify_assign']	= $GLOBALS['phpgw_info']['user']['preferences']['projects']['notify_assign'];
			}
			return $prefs;
		}

		function employee_list()
		{		
			$employees = $GLOBALS['phpgw']->accounts->get_list('accounts');
			return $employees;
		}

		function get_acl_for_project($project_id)
		{
			return $GLOBALS['phpgw']->acl->get_ids_for_location($project_id, 7);
		}

		function selected_employees($project_id)
		{
			$emps = $this->get_acl_for_project($project_id);

			if (is_array($emps))
			{
				for($i=0;$i<count($emps);$i++)
				{
					$this->accounts = CreateObject('phpgwapi.accounts',$emps[$i]);
					$this->accounts->read_repository();

					$empl[] = array
					(
						'account_id'		=> $this->accounts->data['account_id'],
						'account_lid'		=> $this->accounts->data['account_lid'],
						'account_firstname'	=> $this->accounts->data['firstname'],
						'account_lastname'	=> $this->accounts->data['lastname']
					);
				}
			}
			return $empl;
		}

		function read_admins($action, $type)
		{ 
			$admins = $this->so->return_admins($action, $type);
			$this->total_records = $this->so->total_records;
			return $admins;
		}

		function list_admins($action, $type, $start, $query, $sort, $order)
		{
			$admins = $this->read_admins($action, 'all');
			$allaccounts = $GLOBALS['phpgw']->accounts->get_list($type, $start, $sort, $order, $query);

			$j = 0;
			while (is_array($allaccounts) && list($null,$account) = each($allaccounts))
			{
				for ($i=0;$i<count($admins);$i++)
				{
					if ($account['account_id'] == $admins[$i]['account_id'])
					{
						$admin_data[$j]['account_id']	= $account['account_id'];
						$admin_data[$j]['lid']			= $account['account_lid'];
						$admin_data[$j]['firstname']	= $account['account_firstname'];
						$admin_data[$j]['lastname']		= $account['account_lastname'];
						$admin_data[$j]['type']			= $account['account_type'];
						$j++;
					}
				}
			}
			return $admin_data;
		}

		function selected_admins($action, $type)
		{
			$is_admin = $this->read_admins($action, $type);

			if ($type == 'aa')
			{
				$alladmins = $GLOBALS['phpgw']->accounts->get_list('accounts');
			}
			else
			{
				$alladmins = $GLOBALS['phpgw']->accounts->get_list('groups');
			}

			while (list($null,$ad_account) = each($alladmins))
			{
				$selected_admins .= '<option value="' . $ad_account['account_id'] . '"';
				for ($i=0;$i<count($is_admin);$i++)
				{
					if($is_admin[$i]['account_id'] == $ad_account['account_id'])
					{
						$selected_admins .= ' selected';
					}
				}
				$selected_admins .= '>'
				. $ad_account['account_firstname'] . ' ' . $ad_account['account_lastname'] . ' [ ' . $ad_account['account_lid'] . ' ]' . '</option>';
			}
			return $selected_admins;
		}

		function edit_admins($action, $users, $groups)
		{
			$this->so->edit_admins($action, $users, $groups);
		}

		function isprojectadmin($action)
		{
			if ($action == 'pad')
			{
				$admin = $this->so->isprojectadmin($action);
			}
			else
			{
				$admin = $this->so->isbookkeeper($action);
			}
			return $admin;
		}

		function list_projects($type, $parent)
		{
			$pro_list = $this->so->read_projects(array
									(
										'start'		=> $this->start,
										'limit'		=> True,
										'query'		=> $this->query,
										'filter'	=> $this->filter,
										'sort'		=> $this->sort,
										'order'		=> $this->order,
										'status'	=> $this->status,
										'cat_id'	=> $this->cat_id,
										'type'		=> $type,
										'parent'	=> $parent
									));

			while (is_array($pro_list) && list(,$pro)=each($pro_list))
			{
				$cached_data = $this->cached_accounts($pro['coordinator']);
				$coordinatorout = $GLOBALS['phpgw']->strip_html($cached_data[$pro['coordinator']]['account_lid']
                                        . ' [' . $cached_data[$pro['coordinator']]['firstname'] . ' '
                                        . $cached_data[$pro['coordinator']]['lastname'] . ' ]');
				/*if ($pro['customer'])
				{
					$customer = $this->read_single_contact($pro['customer']);
            		if ($customer[0]['org_name'] == '') { $customerout = $customer[0]['n_given'] . ' ' . $customer[0]['n_family']; }
            		else { $customerout = $customer[0]['org_name'] . ' [ ' . $customer[0]['n_given'] . ' ' . $customer[0]['n_family'] . ' ]'; }
				}

				$pro['sdate'] = $pro['sdate'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
				$sdateout = $GLOBALS['phpgw']->common->show_date($pro['sdate'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);*/

				$mlist = '';
				$mstones = $this->get_mstones($pro['project_id']);
				if (is_array($mstones))
				{
					$mlist = '<table width="100%" border="0" cellpadding="0" cellspacing="0">' . "\n";
					for ($i=0;$i<count($mstones);$i++)
					{
						$mlist .= '<tr><td width="50%">' . $mstones[$i]['title'] . '</td><td width="50%" align="right">' . $this->formatted_edate($mstones[$i]['edate']) . '</td></tr>' . "\n";
					}
					$mlist .= '</table>';
				}

				$projects[] = array
				(
					'project_id'		=> $pro['project_id'],
					'parent'			=> $pro['parent'],
					'coordinator'		=> $pro['coordinator'], 
					'coordinatorout'	=> $coordinatorout,
					'customerout'		=> $customerout,
					'title'				=> $GLOBALS['phpgw']->strip_html($pro['title']),
					'number'			=> $GLOBALS['phpgw']->strip_html($pro['number']),
					'investment_nr'		=> $GLOBALS['phpgw']->strip_html($pro['investment_nr']),
					'descr'				=> $GLOBALS['phpgw']->strip_html($pro['descr']),
					'sdateout'			=> $sdateout,
					'budget'			=> $pro['budget'],
					'pcosts'			=> $pro['pcosts'],
					'edate'				=> $pro['edate'],
					'status'			=> $pro['status'],
					'level'				=> $pro['level'],
					'mstones'			=> $mlist
				);
			}

			$this->total_records = $this->so->total_records;
			return $projects;
		}

		function read_single_project($project_id)
		{
			$pro = $this->so->read_single_project($project_id);

			$project = array
			(
				'utime'				=> $this->sohours->get_time_used($project_id),
				'phours'			=> ($pro['ptime']/60),
				'title'				=> $GLOBALS['phpgw']->strip_html($pro['title']),
				'number'			=> $GLOBALS['phpgw']->strip_html($pro['number']),
				'investment_nr'		=> $GLOBALS['phpgw']->strip_html($pro['investment_nr']),
				'descr'				=> $GLOBALS['phpgw']->strip_html($pro['descr']),
				'budget'			=> $pro['budget'],
				'pcosts'			=> $pro['pcosts'],
				'project_id'		=> $pro['project_id'],
				'parent'			=> $pro['parent'],
				'cat'				=> $pro['cat'],
				'access'			=> $pro['access'],
				'coordinator'		=> $pro['coordinator'],
				'customer'			=> $pro['customer'],
				'status'			=> $pro['status'],
				'owner'				=> $pro['owner'],
				'processor'			=> $pro['processor'],
				'previous'			=> $pro['previous']
			);

			if ($project['utime'] > 0)
			{
				$project['uhours'] = ($project['utime']/60);
			}
			else
			{
				$project['uhours'] = 0;
			}

			if ($pro['edate'] == 0)
			{
				$project['edate_formatted'] = '&nbsp;';
			}
			else
			{
				$project['edate'] = $pro['edate'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
				$project['edate_formatted'] = $GLOBALS['phpgw']->common->show_date($pro['edate'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
			}

			$project['sdate'] = $pro['sdate'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
			$project['sdate_formatted'] = $GLOBALS['phpgw']->common->show_date($pro['sdate'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);

			$project['udate'] = $pro['udate'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
			$project['udate_formatted'] = $GLOBALS['phpgw']->common->show_date($pro['udate'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);

			$project['cdate'] = $pro['cdate'] + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
			$project['cdate_formatted'] = $GLOBALS['phpgw']->common->show_date($pro['cdate'],$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);

			return $project;
		}

		function sum_budget($action = 'budget')
		{
			return $this->so->sum_budget($action);
		}

		function read_single_activity($activity_id)
		{
			$single_act = $this->so->read_single_activity($activity_id);
			return $single_act;
		}

		function exists($action, $check, $num, $pa_id)
		{
			$exists = $this->so->exists($action, $check , $num, $pa_id);
			if ($exists)
			{
				return True;
			}
			else
			{
				return False;
			}
		}

		function list_activities($start, $limit, $query, $sort, $order, $cat_id)
		{
			$act_list = $this->so->read_activities($start, $limit, $query, $sort, $order, $cat_id);
			$this->total_records = $this->so->total_records;
			return $act_list;
		}

		function activities_list($project_id, $billable)
		{
			$activities_list = $this->so->activities_list($project_id, $billable);
			return $activities_list;
		}

		function select_activities_list($project_id, $billable)
		{
			$activities_list = $this->so->select_activities_list($project_id, $billable);
			return $activities_list;
		}

		function select_pro_activities($project_id, $pro_parent, $billable)
		{
			$activities_list = $this->so->select_pro_activities($project_id, $pro_parent, $billable);
			return $activities_list;
		}

		function select_hours_activities($project_id, $act)
		{
			$activities_list = $this->so->select_hours_activities($project_id, $act);
			return $activities_list;
		}

		function check_values($action, $values, $book_activities, $bill_activities)
		{
			if (strlen($values['descr']) >= 8000)
			{
				$error[] = lang('Description can not exceed 8000 characters in length !');
			}

			if (!$values['coordinator'])
			{
				$error[] = lang('please choose a project coordinator');
			}

			if (strlen($values['title']) >= 255)
			{
				$error[] = lang('title can not exceed 255 characters in length');
			}

			if (!$values['choose'])
			{
				if (! $values['number'])
				{
					$error[] = lang('Please enter an ID !');
				}
				else
				{
					$exists = $this->exists($action, 'number', $values['number'], $values['project_id']);

					if ($exists)
					{
						$error[] = lang('That ID has been used already !');
					}

					if (strlen($values['number']) > 25)
					{
						$error[] = lang('id can not exceed 25 characters in length');
					}
				}
			}

			if ((! $book_activities) && (! $bill_activities))
			{
				$error[] = lang('please choose activities for the project');
			}

			if ($values['smonth'] || $values['sday'] || $values['syear'])
			{
				if (! checkdate($values['smonth'],$values['sday'],$values['syear']))
				{
					$error[] = lang('You have entered an invalid start date !');
				}
			}

			if ($values['emonth'] || $values['eday'] || $values['eyear'])
			{
				if (! checkdate($values['emonth'],$values['eday'],$values['eyear']))
				{
					$error[] = lang('You have entered an invalid end date !');
				}
			}

			if ($values['previous'])
			{
				$edate = $this->return_value('edate',$values['previous']);

				if (intval($edate) == 0)
				{
					$error[] = lang('the choosen previous project does not have an end date specified');
				}
			}

			if ($action == 'mains')
			{
				if ((!$values['budget'] || $values['budget'] == 0) && $values['pcosts'] > 0)
				{
					$error[] = lang('please specify the budget');
				}

				if (($values['budget'] && $values['budget'] > 0) && ($values['pcosts'] && $values['pcosts'] > 0))
				{
					if ($values['pcosts'] > $values['budget'])
					{
						$error[] = lang('pcosts can not be higher than the budget');
					}
				}
			}

			if ($action == 'subs')
			{
				$main_edate = $this->return_value('edate',$values['parent']);				

				if ($main_edate != 0)
				{
					$checkdate = mktime(0,0,0,$values['emonth'],$values['eday'],$values['eyear']);

					if ($checkdate > $main_edate)
					{
						$error[] = lang('ending date can not be after main projects ending date');
					}
				}

				$main_sdate = $this->return_value('sdate',$values['parent']);				

				if ($main_sdate != 0)
				{
					$checkdate = mktime(0,0,0,$values['smonth'],$values['sday'],$values['syear']);

					if ($checkdate < $main_sdate)
					{
						$error[] = lang('start date can not be before main projects start date');
					}
				}

				$ptime_parent	= $this->so->return_value('ptime',$values['parent']);
				$sum_ptime		= $this->so->get_planned_value(array('action' => 'tparent','parent_id' => $values['parent']
																	,'project_id' => $values['project_id']));
				$pminutes = intval($values['ptime'])*60;

				if (($pminutes+$sum_ptime) > $ptime_parent)
				{
					$error[] = lang('planned time sum of all sub projects is bigger than the planned time of the main project');
				}

				$budget_parent	= $this->so->return_value('budget',$values['parent']);
				$sum_budget		= $this->so->get_planned_value(array('action' => 'bparent','parent_id' => $values['parent']
																	,'project_id' => $values['project_id']));
				if (($values['budget']+$sum_budget) > $budget_parent)
				{
					$error[] = lang('budget sum of all sub projects is bigger than the budget of the main project');
				}
			}

			if (is_array($error))
			{
				return $error;
				//_debug_array($error);
			}
		}

		function check_pa_values($values)
		{
			if (strlen($values['descr']) >= 255)
			{
				$error[] = lang('Description can not exceed 255 characters in length !');
			}

			if (! $values['choose'])
			{
				if (! $values['number'])
				{
					$error[] = lang('Please enter an ID !');
				}
				else
				{
					$exists = $this->exists('act', 'number', $values['number'], $values['activity_id']);

					if ($exists)
					{
						$error[] = lang('That ID has been used already !');
					}

					if (strlen($values['number']) >= 20)
					{
						$error[] = lang('id can not exceed 19 characters in length');
					}
				}
			}

			if ((! $values['billperae']) || ($values['billperae'] == 0))
			{
				$error[] = lang('please enter the bill');
			}

			if ($GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'] == 'wu')
			{
				if ((! $values['minperae']) || ($values['minperae'] == 0))
				{
					$error[] = lang('please enter the minutes per workunit');
				}
			}

			if (is_array($error))
			{
				return $error;
			}
		}

		function save_project($action, $values, $book_activities, $bill_activities)
		{
			if ($values['choose'])
			{
				if ($action == 'mains')
				{
					$values['number'] = $this->so->create_projectid();
				}
				else
				{
					$values['number'] = $this->so->create_jobid($values['parent']);
				}
			}

			if ($values['access'])
			{
				$values['access'] = 'private';
			}
			else
			{
				$values['access'] = 'public';
			}

			$month = $this->return_date();
			$values['monthdate'] = $month['monthdate'];

			$values['ptime'] = intval($values['ptime'])*60;

			if ($values['smonth'] || $values['sday'] || $values['syear'])
			{
				$values['sdate'] = mktime(0,0,0,$values['smonth'], $values['sday'], $values['syear']);
			}

            if (!$values['sdate'])
            {
                $values['sdate'] = time();
            }

			if ($values['emonth'] || $values['eday'] || $values['eyear'])
			{
				$values['edate'] = mktime(0,0,0,$values['emonth'],$values['eday'],$values['eyear']);
			}

			if (!$values['previous'] && $values['parent'])
			{
				$values['previous'] = $this->return_value('previous',$values['parent']);
			}

			if (intval($values['project_id']) > 0)
			{
				$this->so->edit_project($values, $book_activities, $bill_activities);

				if(is_array($values['employees']))
				{
					$this->send_alarm($values,'pro');
				}
			}
			else
			{
				$values['project_id'] = $this->so->add_project($values, $book_activities, $bill_activities);

				if(is_array($values['employees']))
				{
					$this->send_alarm($values);
				}
			}

			$values['project_id'] = intval($values['project_id']);

			if (is_array($values['employees']))
			{
				$this->so->delete_acl($values['project_id']);
				for($i=0;$i<count($values['employees']);$i++)
				{
					$GLOBALS['phpgw']->acl->add_repository('projects',$values['project_id'],$values['employees'][$i],7);
				}
			}
			return $values['project_id'];
		}

		function save_activity($values)
		{
			if ($values['choose'])
			{
				$values['number'] = $this->so->create_activityid();
			}

			if ($values['activity_id'])
			{
				if ($values['activity_id'] && intval($values['activity_id']) > 0)
				{
					$this->so->edit_activity($values);

					if ($values['minperae'])
					{
						$this->soprojecthours = CreateObject('projects.soprojecthours');
						$this->soprojecthours->update_hours_act($values['activity_id'],$values['minperae']);
					}
				}
			}
			else
			{
				$this->so->add_activity($values);
			}
		}

		function select_project_list($values)
		{
			return $this->so->select_project_list($values);
		}

		function delete_pa($action, $pa_id, $subs)
		{
			if ($action == 'account')
			{
				$this->so->delete_account_project_data($pa_id);
			}
			else
			{
				$this->so->delete_pa($action, $pa_id, $subs);
			}
		}

		function change_owner($old, $new)
		{
			$this->so->change_owner($old, $new);
		}

		function get_mstones($project_id)
		{
			$mstones = $this->so->get_mstones($project_id);

			while (is_array($mstones) && list(,$ms) = each($mstones))
			{
				$stones[] = array
				(
					'title'		=> $GLOBALS['phpgw']->strip_html($ms['title']),
					'edate'		=> $ms['edate'],
					's_id'		=> $ms['s_id']
				);
			}
			return $stones;
		}

		function get_single_mstone($s_id)
		{
			return $this->so->get_single_mstone($s_id);
		}

		function save_mstone($values)
		{
			$values['edate'] = mktime(0,0,0,$values['emonth'],$values['eday'],$values['eyear']);

			if (isset($values['old_edate']) && intval($values['old_edate']) > 0)
			{
				if ($values['old_edate'] != $values['edate'])
				{
					$values['edateformatted']	= $this->formatted_edate($values['edate'],False);
					$values['pro_title']		= $this->return_value('pro',$values['project_id']);
					$values['employees']		= $this->get_acl_for_project($values['project_id']);

					$this->send_alarm($values,'mstone');

					unset($values['edateformatted']);
					unset($values['pro_title']);
					unset($values['employees']);
				}
			}

			if (intval($values['s_id']) > 0)
			{
				$this->so->edit_mstone($values);
			}
			else
			{
				return $this->so->add_mstone($values);
			}
		}

		function delete_mstone($s_id)
		{
			$this->so->delete_mstone($s_id);
		}

		function formatted_edate($edate = '',$colored = True)
		{
			$edate = intval($edate);

			$month  = $GLOBALS['phpgw']->common->show_date(time(),'n');
			$day    = $GLOBALS['phpgw']->common->show_date(time(),'d');
			$year   = $GLOBALS['phpgw']->common->show_date(time(),'Y');

			if ($edate > 0)
			{
				$edate = $edate + (60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'];
				$edateout = $GLOBALS['phpgw']->common->show_date($edate,$GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
			}

			if($colored)
			{
				if (mktime(2,0,0,$month,$day,$year) == $edate)
				{
					$edateout = '<b>' . $edateout . '</b>';
				}
				if (mktime(2,0,0,$month,$day,$year) >= $edate)
				{
					$edateout = '<font color="CC0000"><b>' . $edateout . '</b></font>';
				}
			}
			return $edateout;
		}

		function member($project_id = '')
		{
			return $this->so->member($project_id);
		}

		function send_alarm($values,$type = 'assign')
		{
			$GLOBALS['phpgw_info']['user']['preferences'] = $GLOBALS['phpgw']->preferences->create_email_preferences();
			$sender = $GLOBALS['phpgw_info']['user']['preferences']['email']['address'];
			//$msgtype = '"projects";';

			switch($type)
			{
				case 'assign':
					$subject = lang('assignment to project %1',$values['title']);
					$msg = lang('assignment to project %1',$values['title']);
					break;
				case 'update':
					$subject = lang('project %1 has been updated',$values['title']);
					$msg = lang('project %1 has been updated',$values['title']);
					break;
				case 'mstone':
					$action = lang('date due of milestone %1 of project %2 has been updated', $values['title'],$values['pro_title']);
					$msg = lang('new date due of milestone %1: %2', $values['title'], $values['edateformatted']);
					break;
			}

			if(!is_object($GLOBALS['phpgw']->send))
			{
				$GLOBALS['phpgw']->send = CreateObject('phpgwapi.send');
			}

			for($i=0;$i<count($values['employees']);$i++)
			{
				//$GLOBALS['phpgw']->preferences->account_id = $values['employees'][$i];
				//$GLOBALS['phpgw']->preferences->read_repository();

				$prefs = CreateObject('phpgwapi.preferences',$values['employees'][$i]);
				$prefs->read_repository();

				switch($type)
				{
					case 'assign':
						if ($prefs['projects']['notify_assign'] == 'yes')
						{
							$to_notify = True;
						}
						break;
					case 'pro':
						if($prefs['projects']['notify_pro'] == 'yes')
						{
							$to_notify = True;
						}
						break;
					case 'mstone':
						if($prefs['projects']['notify_mstone'] == 'yes')
						{
							$to_notify = True;
						}
						break;
				}

				if($to_notify)
				{
					/*print_debug('Msg Type',$msg_type);
					print_debug('UserID',$userid);

					$GLOBALS['phpgw']->accounts->get_account_name($userid,$lid,$details['to-firstname'],$details['to-lastname']);
					$details['to-fullname'] = $GLOBALS['phpgw']->common->display_fullname('',$details['to-firstname'],$details['to-lastname']);*/

					$to = $prefs->email_address($values['employees'][$i]);
					/*if (empty($to) || $to[0] == '@' || $to[0] == '$')	// we have no valid email-address
					{
						//echo "<p>boprojects::send_update: Empty email adress for user '".$details['to-fullname']."' ==> ignored !!!</p>\n";
						continue;
					}
					print_debug('Email being sent to',$to);*/

					$subject = $GLOBALS['phpgw']->send->encode_subject($subject);

					$returncode = $GLOBALS['phpgw']->send->msg('email',$to,$subject,$msg,''/*$msgtype*/,'','','',$sender);
					//echo "<p>send(to='$to', sender='$sender'<br>subject='$subject') returncode=$returncode<br>".nl2br($body)."</p>\n";

					if (!$returncode)	// not nice, but better than failing silently
					{
						echo '<p><b>boprojects::send_alarm</b>: '.lang("Failed sending message to '%1' #%2 subject='%3', sender='%4' !!!",$to,$values['employees'][$i],htmlspecialchars($subject), $sender)."<br>\n";
						echo '<i>'.$GLOBALS['phpgw']->send->err['desc']."</i><br>\n";
						echo lang('This is mostly caused by a not or wrongly configured SMTP server. Notify your administrator.')."</p>\n";
						echo '<p>'.lang('Click %1here%2 to return to projects.','<a href="'.$GLOBALS['phpgw']->link('/projects/').'">','</a>')."</p>\n";
					}
				}
				//unset($prefs);
			}
			return $returncode;
		}
	}
?>
