<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_monitorworkitems extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);

		function ui_monitorworkitems()
		{
			parent::monitor('monitor_workitems');
		}

		function form()
		{
			$this->order			= get_var('order', 'any', 'wf_order_id');
			$this->sort				= get_var('sort', 'any', 'asc');
			$this->sort_mode		= $this->order . '__'. $this->sort;
			$filter_instance		= get_var('filter_instance', 'any', '');
			$filter_user			= get_var('filter_user', 'any', '');
			
				//echo "order: <pre>";print_r($this->order);echo "</pre>";
				//echo "where: <pre>";print_r($where);echo "</pre>";
				
		        $where = '';
		        $wheres = array();
				
			if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {  
		    	$filter_process = (int)get_var('filter_process', 'any', 0);
		        $wheres[] = "gp.wf_p_id='" .$filter_process. "'";
		    }
			if (isset($_REQUEST['filter_instance']) && $_REQUEST['filter_instance']) {  
		    	$filter_instance = (int)get_var('filter_instance', 'any', 0);
		        $wheres[] = "wf_instance_id=" .$filter_instance;
		    }
			if (isset($_REQUEST['filter_activity']) && $_REQUEST['filter_activity']) {  
		    	$filter_activity = (int)get_var('filter_activity', 'any', 0);
		        $wheres[] = "wf_activity_id='" .$filter_activity. "'";
		    }
			if( count($wheres) > 0 ) {
		        $where = implode(' and ', $wheres);
				//echo "where: <pre>";print_r($where);echo "</pre>";
			}
			else {
				$where = '';
			}

			$wi_users	= $this->process_monitor->monitor_list_wi_users();
			$workitems	= $this->process_monitor->monitor_list_workitems($this->start, -1, $this->sort_mode, $this->search_str, $where);
		    $this->stats= $this->process_monitor->monitor_stats();

			$this->show_filter_process();
			//$this->show_filter_activities();
			$this->show_filter_unique_activities();
			/*
			if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {  
		    	$filter_process = (int)get_var('filter_process', 'any', 0);
				$this->show_filter_unique_activities("ga.wf_p_id=" .$filter_process);
		    }
			else {
				$this->show_filter_unique_activities();
			}
			*/
			$this->show_filter_user($wi_users, $filter_user);
			$this->show_workitems_table($workitems['data']);

			$this->t->set_var('filter_instance', $filter_instance);
			$this->fill_general_variables();
			$this->finish();
		}

		function show_workitems_table($workitems_data)
		{
			//_debug_array($workitems_data);
			$this->t->set_var(array(
				'header_id'			=> $this->nextmatchs->show_sort_order($this->sort, 'wf_item_id', $this->order, '', lang('Id')),
				'header_process'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_procname', $this->order, '', lang('Process')),
				'header_activity'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_act_name', $this->order, '', lang('Activity')),
				'header_ins'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_instance_id', $this->order, '', lang('Instance')),
				'header_num'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_order_id', $this->order, '', '#'),
				'header_start'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_started', $this->order, '', lang('Start')),
				'header_duration'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_duration', $this->order, '', lang('Duration')),
				'header_user'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_user', $this->order, '', lang('User')),
			));

			$this->t->set_block('monitor_workitems', 'block_workitems_table', 'workitems_table');

			foreach ($workitems_data as $workitem)
			{
				$this->t->set_var(array(
					'wi_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_viewworkitem.form&itemId='. $workitem['wf_item_id']),
					'wi_id'				=> $workitem['wf_item_id'],
					'wi_wf_procname'	=> $workitem['wf_procname'],
					'wi_version'		=> $workitem['wf_version'],
					'act_icon'			=> $this->act_icon($workitem['wf_type']),
					'wi_actname'		=> $workitem['wf_act_name'],
					'wi_adm_inst_href'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance.form&iid='. $workitem['wf_instance_id']),
					'wi_inst_id'		=> $workitem['wf_instance_id'],
					'wi_order_id'		=> $workitem['wf_order_id'],
					//'wi_started'		=> $workitem['wf_started'],
					'wi_started'		=> $GLOBALS['phpgw']->common->show_date($workitem['wf_started'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])),
					'wi_duration'		=> $this->time_diff($workitem['wf_duration']),
					//'wi_duration'		=> $workitem['wf_duration'],
					//'wi_user'			=> $workitem['wf_user'],
					//'wi_user'		=> $GLOBALS['phpgw']->common->grab_owner_name($workitem['wf_user']), 
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				if( $workitem['wf_user'] == '*') {
					$this->t->set_var('wi_user', $workitem['wf_user']);
				}
				else {
					$this->t->set_var('wi_user', $GLOBALS['phpgw']->common->grab_owner_name($workitem['wf_user']));
				}
				$this->t->parse('workitems_table', 'block_workitems_table', true);
			}
			if (!count($workitems_data)) $this->t->set_var('workitems_table', '<tr><td colspan="8" align="center">'. lang('There are no workitems available') .'</td></tr>');
		}

		function show_filter_user($wi_users, $filter_user)
		{
			$this->t->set_var('filter_user_select_all', (!$filter_user)? 'selected="selected"' : '');
			$this->t->set_block('monitor_workitems', 'block_filter_user', 'filter_user');
			foreach ($wi_users as $user)
			{
				$this->t->set_var(array(
					'filter_user_selected'	=> ($user == $filter_user)? 'selected="selected"' : '',
					'filter_user_value'		=> $user,
					//'filter_user_name'		=> $GLOBALS['phpgw']->common->grab_owner_name($user)
					//'filter_user_name'		=> $user,
				));
				if( $user == '*') {
					$this->t->set_var('filter_user_name', $user);
				}
				else {
					$this->t->set_var('filter_user_name', $GLOBALS['phpgw']->common->grab_owner_name($user));
				}
				$this->t->parse('filter_user', 'block_filter_user', true);
			}
		}
		function time_diff($to) {
			$days = (int)($to/(24*3600));
			$to = $to - ($days*(24*3600));
			$hours = (int)($to/3600);
			$to = $to - ($hours*3600);
			$min = date("i", $to);
			$to = $to - ($min*60);			
			$sec = date("s", $to);

			return "$days days, $hours:$min:$sec";
		}
	}
?>
