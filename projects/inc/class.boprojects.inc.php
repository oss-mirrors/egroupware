<?php
	/*******************************************************************\
	* phpGroupWare - Projects                                           *
	* http://www.phpgroupware.org                                       *
	*                                                                   *
	* Project Manager                                                   *
	* Written by Bettina Gille [ceb@phpgroupware.org]                   *
	* -----------------------------------------------                   *
	* Copyright (C) 2000,2001,2002 Bettina Gille                        *
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
			$this->soprojects	= CreateObject('projects.soprojects');
			$this->contacts		= CreateObject('phpgwapi.contacts');

			if ($session)
			{
				$this->read_sessiondata($action);
				$this->use_session = True;
			}

			$start	= $GLOBALS['HTTP_POST_VARS']['start'] ? $GLOBALS['HTTP_POST_VARS']['start'] : $GLOBALS['HTTP_GET_VARS']['start'];
			$query	= $GLOBALS['HTTP_POST_VARS']['query'] ? $GLOBALS['HTTP_POST_VARS']['query'] : $GLOBALS['HTTP_GET_VARS']['query'];
			$sort	= $GLOBALS['HTTP_POST_VARS']['sort'] ? $GLOBALS['HTTP_POST_VARS']['sort'] : $GLOBALS['HTTP_GET_VARS']['sort'];
			$order	= $GLOBALS['HTTP_POST_VARS']['order'] ? $GLOBALS['HTTP_POST_VARS']['order'] : $GLOBALS['HTTP_GET_VARS']['order'];
			$cat_id	= $GLOBALS['HTTP_POST_VARS']['cat_id'] ? $GLOBALS['HTTP_POST_VARS']['cat_id'] : $GLOBALS['HTTP_GET_VARS']['cat_id'];
			$filter	= $GLOBALS['HTTP_POST_VARS']['filter'] ? $GLOBALS['HTTP_POST_VARS']['filter'] : $GLOBALS['HTTP_GET_VARS']['filter'];
			$status	= $GLOBALS['HTTP_POST_VARS']['status'] ? $GLOBALS['HTTP_POST_VARS']['status'] : $GLOBALS['HTTP_GET_VARS']['status'];

			if(isset($start)) { $this->start = $start; }
			if(isset($query)) { $this->query = $query; }
			if(!empty($filter)) { $this->filter = $filter; }
			if(isset($sort)) { $this->sort = $sort; }
			if(isset($order)) { $this->order = $order; }
			if(isset($cat_id)) { $this->cat_id = $cat_id; }

			if ($cat_id == 'none')
			{
				$this->cat_id = '';
			}

			if(isset($status)) { $this->status = $status; }
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
			$cached_data[$this->accounts->data['account_id']]['firstname']   	= $this->accounts->data['firstname'];
			$cached_data[$this->accounts->data['account_id']]['lastname']    	= $this->accounts->data['lastname'];

			return $cached_data;
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

			$entry = $this->contacts->read_single_entry($abid,$cols);
			return $entry;
		}

		function return_value($action,$item)
		{
			$thing = $this->soprojects->return_value($action,$item);
			return $thing;
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
			}
			return $prefs;
		}

		function save_prefs($prefs)
		{
			$GLOBALS['phpgw']->preferences->read_repository();

			if ($prefs)
			{
				$GLOBALS['phpgw']->preferences->change('projects','tax',$prefs['tax']);
				$GLOBALS['phpgw']->preferences->change('projects','abid',$prefs['abid']);
				$GLOBALS['phpgw']->preferences->change('projects','bill',$prefs['bill']);
				$GLOBALS['phpgw']->preferences->change('projects','ifont',$prefs['ifont']);
				$GLOBALS['phpgw']->preferences->change('projects','mysize',$prefs['mysize']);
				$GLOBALS['phpgw']->preferences->change('projects','allsize',$prefs['allsize']);
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
				$error[] = lang('Please set your global preferences');
			}

			if ($this->isprojectadmin('pad') || $this->isprojectadmin('pbo'))
			{
				if (! isset($prefs['abid']) || (! isset($prefs['tax'])) || (! isset($prefs['bill'])) || (! isset($prefs['ifont'])) || (! isset($prefs['mysize'])) || (! isset($prefs['allsize'])))
				{
					$error[] = lang('Please set your preferences for this application');
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


			if ($this->isprojectadmin('pad') || $this->isprojectadmin('pbo'))
			{
				if ($GLOBALS['phpgw_info']['user']['preferences']['projects'])
				{
					$prefs['abid']		= $GLOBALS['phpgw_info']['user']['preferences']['projects']['abid'];
					$prefs['tax']		= $GLOBALS['phpgw_info']['user']['preferences']['projects']['tax'];
					$prefs['bill']		= $GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'];
					$prefs['ifont']		= $GLOBALS['phpgw_info']['user']['preferences']['projects']['ifont'];
					$prefs['mysize']	= $GLOBALS['phpgw_info']['user']['preferences']['projects']['mysize'];
					$prefs['allsize']	= $GLOBALS['phpgw_info']['user']['preferences']['projects']['allsize'];
				}
			}
			return $prefs;
		}

		function employee_list()
		{		
			$employees = $GLOBALS['phpgw']->accounts->get_list('accounts');
			return $employees;
		}

		function read_admins($action, $type)
		{ 
			$admins = $this->soprojects->return_admins($action, $type);
			$this->total_records = $this->soprojects->total_records;
			return $admins;
		}

		function list_admins($action, $type, $start, $query, $sort, $order)
		{
			$admins = $this->read_admins($action, 'all');

			$allaccounts = $GLOBALS['phpgw']->accounts->get_list($type, $start, $sort, $order, $query);

			if (is_array($allaccounts))
			{
				while (list($null,$account) = each($allaccounts))
				{
					for ($i=0;$i<count($admins);$i++)
					{
						if ($account['account_id'] == $admins[$i]['account_id'])
						{
							$admin_data[$i]['account_id']	= $account['account_id'];
							$admin_data[$i]['lid']			= $account['account_lid'];
							$admin_data[$i]['firstname']	= $account['account_firstname'];
							$admin_data[$i]['lastname']		= $account['account_lastname'];
							$admin_data[$i]['type']			= $account['account_type'];
						}
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
			$this->soprojects->edit_admins($action, $users, $groups);
		}

		function isprojectadmin($action)
		{
			if ($action == 'pad')
			{
				$admin = $this->soprojects->isprojectadmin($action);
			}
			else
			{
				$admin = $this->soprojects->isbookkeeper($action);
			}
			return $admin;
		}

		function list_projects($start, $limit, $query, $filter, $sort, $order, $status, $cat_id, $type, $pro_parent)
		{
			$pro_list = $this->soprojects->read_projects($start, $limit, $query, $filter, $sort, $order, $status, $cat_id, $type, $pro_parent);
			$this->total_records = $this->soprojects->total_records;
			return $pro_list;
		}

		function read_single_project($project_id)
		{
			$single_pro = $this->soprojects->read_single_project($project_id);
			return $single_pro;
		}

		function read_single_activity($activity_id)
		{
			$single_act = $this->soprojects->read_single_activity($activity_id);
			return $single_act;
		}

		function exists($action, $check, $num, $pa_id)
		{
			$exists = $this->soprojects->exists($action, $check , $num, $pa_id);
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
			$act_list = $this->soprojects->read_activities($start, $limit, $query, $sort, $order, $cat_id);
			$this->total_records = $this->soprojects->total_records;
			return $act_list;
		}

		function activities_list($project_id, $billable)
		{
			$activities_list = $this->soprojects->activities_list($project_id, $billable);
			return $activities_list;
		}

		function select_activities_list($project_id, $billable)
		{
			$activities_list = $this->soprojects->select_activities_list($project_id, $billable);
			return $activities_list;
		}

		function select_pro_activities($project_id, $pro_parent, $billable)
		{
			$activities_list = $this->soprojects->select_pro_activities($project_id, $pro_parent, $billable);
			return $activities_list;
		}

		function select_hours_activities($project_id, $act)
		{
			$activities_list = $this->soprojects->select_hours_activities($project_id, $act);
			return $activities_list;
		}

		function check_values($action, $values, $book_activities, $bill_activities)
		{
			if (strlen($values['descr']) >= 8000)
			{
				$error[] = lang('Description can not exceed 8000 characters in length !');
			}

			if (strlen($values['title']) >= 255)
			{
				$error[] = lang('Title can not exceed 255 characters in length !');
			}

			if (! $values['choose'])
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
						$error[] = lang('ID can not exceed 25 characters in length !');
					}
				}
			}

			if ((! $book_activities) && (! $bill_activities))
			{
				$error[] = lang('Please choose activities for that project first !');
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

/*			if ($values['edate'] < $values['sdate'] && $values['edate'] && $values['sdate'])
			{
				$error[] = lang('Ending date can not be before start date');
			} */

			if ($action == 'subs')
			{
				$main_edate = $this->return_value('edate',$values['parent']);				

				if ($main_edate != 0)
				{
					$checkdate = mktime(0,0,0,$values['emonth'],$values['eday'],$values['eyear']);

					if ($checkdate > $main_edate)
					{
						$error[] = lang('Ending date can not be after main projects ending date !');
					}
				}

				$main_sdate = $this->return_value('sdate',$values['parent']);				

				if ($main_sdate != 0)
				{
					$checkdate = mktime(0,0,0,$values['smonth'],$values['sday'],$values['syear']);

					if ($checkdate < $main_sdate)
					{
						$error[] = lang('Start date can not be before main projects start date !');
					}
				}
			}

			if (is_array($error))
			{
				return $error;
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
						$error[] = lang('ID can not exceed 19 characters in length !');
					}
				}
			}

			if ((! $values['billperae']) || ($values['billperae'] == 0))
			{
				$error[] = lang('Please enter the bill !');
			}

			if ($GLOBALS['phpgw_info']['user']['preferences']['projects']['bill'] == 'wu')
			{
				if ((! $values['minperae']) || ($values['minperae'] == 0))
				{
					$error[] = lang('Please enter the minutes per workunit !');
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
					$values['number'] = $this->soprojects->create_projectid();
				}
				else
				{
					$values['number'] = $this->soprojects->create_jobid($values['parent']);
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

			if (!$values['budget'])
			{
				$values['budget'] = 0;
			}

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

			if ($values['project_id'])
			{
				if ($values['project_id'] != 0)
				{
					$this->soprojects->edit_project($values, $book_activities, $bill_activities);
				}
			}
			else
			{
				$this->soprojects->add_project($values, $book_activities, $bill_activities);
			}
		}

		function save_activity($values)
		{
			if ($values['choose'])
			{
				$values['number'] = $this->soprojects->create_activityid();
			}

			if ($values['activity_id'])
			{
				if ($values['activity_id'] != 0)
				{
					$this->soprojects->edit_activity($values);

					if ($values['minperae'])
					{
						$this->soprojecthours = CreateObject('projects.soprojecthours');
						$this->soprojecthours->update_hours_act($values['activity_id'],$values['minperae']);
					}
				}
			}
			else
			{
				$this->soprojects->add_activity($values);
			}
		}

		function select_project_list($type, $status, $project_id)
		{
			$list = $this->soprojects->select_project_list($type, $status, $project_id);
			return $list;
		}

		function delete_pa($action, $pa_id, $subs)
		{
			if ($action == 'account')
			{
				$this->soprojects->delete_account_project_data($pa_id);
			}
			else
			{
				$this->soprojects->delete_pa($action, $pa_id, $subs);
			}
		}

		function change_owner($old, $new)
		{
			$this->soprojects->change_owner($old, $new);
		}
	}
?>
