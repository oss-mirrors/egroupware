<?php
	/**************************************************************************\
	* phpGroupWare - Projects                                                  *
	* http://www.phpgroupware.org                                              *
    * Written by Bettina Gille [ceb@phpgroupware.org]                          *
	* -----------------------------------------------                          *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/
	/* $Id$ */

	class boprojects
	{
		var $start;
		var $query;
		var $filter;
		var $order;
		var $sort;
		var $cat_id;

		var $public_functions = array
		(
			'cached_accounts'		=> True,
			'list_projects'			=> True,
			'check_perms'			=> True,
			'check_values'			=> True,
			'select_project_list'	=> True,
			'save_project'			=> True,
			'read_single_project'	=> True,
			'delete_project'		=> True,
			'exists'				=> True,
			'read_customer_data'	=> True,
			'isprojectadmin'		=> True,
			'select_activity_list'	=> True,
			'coordinator_list'		=> True	
		);

		function boprojects($session=False)
		{
			global $phpgw;

			$this->soprojects	= CreateObject('projects.soprojects');

			if ($session)
			{
				$this->read_sessiondata();
				$this->use_session = True;
			}

			global $start, $query, $filter, $order, $sort, $cat_id;

			if(isset($start)) { $this->start = $start; }
			if(isset($query)) { $this->query = $query; }
			if(!empty($filter)) { $this->filter = $filter; }
			if(isset($sort)) { $this->sort = $sort; }
			if(isset($order)) { $this->order = $order; }
			if(isset($cat_id)) { $this->cat_id = $cat_id; }
		}

		function save_sessiondata($data)
		{
			global $phpgw;

			if ($this->use_session)
			{
				$phpgw->session->appsession('session_data','projects',$data);
			}
		}

		function read_sessiondata()
		{
			global $phpgw;

			$data = $phpgw->session->appsession('session_data','projects');

			$this->start	= $data['start'];
			$this->query	= $data['query'];
			$this->filter	= $data['filter'];
			$this->order	= $data['order'];
			$this->sort		= $data['sort'];
			$this->cat_id	= $data['cat_id'];
		}

		function check_perms($has, $needed)
		{
			return (!!($has & $needed) == True);
		}

		function cached_accounts($account_id)
		{
			global $phpgw;

			$this->accounts = CreateObject('phpgwapi.accounts',$account_id);

			$this->accounts->read_repository();

			$cached_data[$this->accounts->data['account_id']]['account_lid'] = $this->accounts->data['account_lid'];
			$cached_data[$this->accounts->data['account_id']]['firstname']   = $this->accounts->data['firstname'];
			$cached_data[$this->accounts->data['account_id']]['lastname']    = $this->accounts->data['lastname'];

			return $cached_data;
		}

		function read_customer_data($ab_id)
		{
			$this->contacts = CreateObject('phpgwapi.contacts');

			$cols = array('n_given'=> 'n_given',
						'n_family' => 'n_family',
						'org_name' => 'org_name');

			$customer = $this->contacts->read_single_entry($ab_id,$cols);
			return $customer;
		}


		function coordinator_list()
		{
			global $phpgw;			

			$employees = $phpgw->accounts->get_list('accounts');
			return $employees;
		}

		function isprojectadmin()
		{
			global $phpgw, $phpgw_info;

			$admin_groups = $phpgw->accounts->membership($phpgw_info['user']['account_id']);
			$admins = $this->soprojects->return_admins();

			for ($i=0;$i<count($admins);$i++)
			{
				if ($admins[$i]['type']=='aa')
				{
					if ($admins[$i]['account_id'] == $phpgw_info['user']['account_id'])
					return True;
				}
				elseif ($admins[$i]['type']=='ag')
				{
					if (is_array($admin_groups))
					{
						for ($j=0;$j<count($admin_groups);$j++)
						{
							if ($admin_groups[$j]['account_id'] == $admins[$i]['account_id'])
							return True;
						}
					}
				}
				else
				{
					return False;
				}
			}
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

		function select_activities_list($project_id, $billable)
		{
			$activities_list = $this->soprojects->select_activities_list($project_id, $billable);
			return $activities_list;
		}

		function check_values($values, $book_activities, $bill_activities)
		{
			global $phpgw;

			if (strlen($values['descr']) >= 8000)
			{
				$error[] = lang('Description can not exceed 8000 characters in length');
			}

			if (! $values['choose'])
			{
				if (! $values['number'])
				{
					$error[] = lang('Please enter an ID !');
				}
				else
				{
					$exists = $this->soprojects->exists($values['number'], $values['project_id']);

					if ($exists)
					{
						$error[] = lang('That ID has been used already !');
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
						$error[] = lang('You have entered an starting invalid date');
					}
			}

			if ($values['emonth'] || $values['eday'] || $values['eyear'])
			{
				if (! checkdate($values['emonth'],$values['eday'],$values['eyear']))
				{
					$error[] = lang('You have entered an ending invalid date');
				}
			}

/*			if ($values['edate'] < $values['sdate'] && $values['edate'] && $values['sdate'])
			{
				$error[] = lang('Ending date can not be before start date');
			} */

			if (is_array($error))
			{
				return $error;
			}
		}

		function save_project($values, $book_activities, $bill_activities)
		{
			global $phpgw;

			if ($values['choose'])
			{
				$values['number'] = $this->soprojects->create_projectid();
			}

			if ($values['access'])
			{
				$values['access'] = 'private';
			}
			else
			{
				$values['access'] = 'public';
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
				$values['edate'] = mktime(2,0,0,$values['emonth'],$values['eday'],$values['eyear']);
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

		function select_project_list($type,$project_id)
		{
			$list = $this->soprojects->select_project_list($type,$project_id);
			return $list;
		}
	}
?>
