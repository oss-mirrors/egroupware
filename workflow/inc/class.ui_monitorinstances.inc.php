<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_monitorinstances extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);

		function ui_monitorinstances()
		{
			parent::monitor('monitor_instances');
		}

		function form()
		{
			$this->order			= get_var('order', 'any', 'wf_name');
			$this->sort				= get_var('sort', 'any', 'asc');
			$this->sort_mode		= $this->order . '__'. $this->sort;
			$filter_activity		= (int)get_var('filter_activity', 'any', 0);
			$filter_status			= get_var('filter_status', 'any', '');
			$filter_act_status		= get_var('filter_act_status', 'any', '');
			$filter_user			= get_var('filter_user', 'any', '');

			//echo "order: <pre>";print_r($this->order);echo "</pre>";

			if (isset($_REQUEST['filter_status']) && $_REQUEST['filter_status']) {  
		    	$filter_status = get_var('filter_status', 'any', 'all');
		        $wheres[] = "gi.wf_status='" .$filter_status. "'";
		    }
			if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {  
		    	$filter_process = (int)get_var('filter_process', 'any', 0);
		        $wheres[] = "ga.wf_p_id='" .$filter_process. "'";
		    }
			if (isset($_REQUEST['filter_activity']) && $_REQUEST['filter_activity']) {  
		    	$filter_activity = (int)get_var('filter_activity', 'any', 0);
		        $wheres[] = "ga.wf_activity_id='" .$filter_activity. "'";
		    }
			if (isset($_REQUEST['filter_act_status']) && $_REQUEST['filter_act_status']) {  
		    	$filter_act_status = get_var('filter_act_status', 'any', '');
				if( $filter_act_status == "running") {
		        	$wheres[] = "gia.wf_status='" .$filter_act_status. "'";
				}
				else if( $filter_act_status == "completed" ) {
		        	$wheres[] = "gi.wf_status='" .$filter_act_status. "'";
				}
		    }

			if( count($wheres) > 0 ) {
		        $where = implode(' and ', $wheres);
				//echo "where: <pre>";print_r($where);echo "</pre>";
			}
			else {
				$where = '';
			}
			//echo "where: <pre>";print_r($where);echo "</pre>";

			//echo "filter_status: <pre>";print_r($filter_status);echo "</pre>";

			$all_statuses	= array('aborted', 'active', 'completed', 'exception');
			$users			= $this->process_monitor->monitor_list_users();
			$instances		= $this->process_monitor->monitor_list_instances($this->start, -1, $this->sort_mode, $this->search_str, $where);

			//echo "instances: <pre>";print_r($instances);echo "</pre>";

			$this->show_filter_process();
			if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {  
		    	$filter_process = (int)get_var('filter_process', 'any', 0);
				$this->show_filter_unique_activities("ga.wf_p_id=" .$filter_process);
		    }
			else {
				$this->show_filter_unique_activities();
			}
			$this->show_filter_status($all_statuses, $filter_status);
			$this->show_filter_act_status($filter_act_status);
			$this->show_filter_user($users, $filter_user);
			$this->show_instances_table($instances['data']);

			$this->fill_general_variables();
			$this->finish();
		}

		function show_instances_table($instances_data)
		{
			//_debug_array($instances_data);
			$this->t->set_var(array(
				'header_id'			=> $this->nextmatchs->show_sort_order($this->sort, 'wf_instance_id', $this->order, '', lang('Id')),
				'header_process'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_p_id', $this->order, '', lang('Process')),
				'header_activity'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_name', $this->order, '', lang('Activity')),
				'header_status'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_status', $this->order, '', lang('Status')),
				//'header_act_status'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_act_status', $this->order, '', lang('Act. Status')),
				'header_act_status'		=> lang('Act. Status'),
				'header_user'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_user', $this->order, '', lang('User')),
			));

			$this->t->set_block('monitor_instances', 'block_inst_table', 'inst_table');

			foreach ($instances_data as $instance)
			{
				$this->t->set_var(array(
					'inst_id_href'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance.form&iid='. $instance['wf_instance_id']),
					'inst_id'		=> $instance['wf_instance_id'],
					'inst_name'		=> $instance['wf_name'],
					'inst_status'	=> $instance['wf_status'],
					//'inst_user'		=> $instance['wf_user'],
					'inst_user'		=> $GLOBALS['phpgw']->common->grab_owner_name($instance['wf_user']), 
					'inst_procname'	=> $instance['wf_procname'],
					'inst_version'	=> $instance['wf_version'],
					'color_line'	=> $this->nextmatchs->alternate_row_color($tr_color),
					'inst_act_status'	=>$instance['wf_act_status']
				));
				$this->t->parse('inst_table', 'block_inst_table', true);
			}
			if (!$instances_data) $this->t->set_var('inst_table', '<tr><td colspan="4" align="center">'. lang('There are no instances available') .'</td></tr>');
		}

		function show_filter_status($all_statuses, $filter_status)
		{
			$this->t->set_var('filter_status_selected_all', (!$filter_status)? 'selected="selected"' : '');
			$this->t->set_block('monitor_instances', 'block_filter_status', 'filter_status');
			foreach ($all_statuses as $status)
			{
				$this->t->set_var(array(
					'filter_status_selected'	=> ($status == $filter_status)? 'selected="selected"' : '',
					'filter_status_value'		=> $status,
					'filter_status_name'		=> lang($status),
				));
				$this->t->parse('filter_status', 'block_filter_status', true);
			}
		}

		function show_filter_act_status($filter_act_status)
		{
			$this->t->set_var(array(
				'filter_act_status_selected_all'	=>(!$filter_act_status)? 'selected="selected"' : '',
				'filter_act_runnning'				=> ($filter_act_status == 'running')? 'selected="selected"' : '',
				'filter_act_completed'				=> ($filter_act_status == 'completed')? 'selected="selected"' : '',

			));
		}

		function show_filter_user($users, $filter_user)
		{
			$this->t->set_var('filter_user_selected_all', (!$this->filter_user)? 'selected="selected"' : '');
			$this->t->set_block('monitor_instances', 'block_filter_user', 'filter_user');
			foreach ($users as $user)
			{
				$this->t->set_var(array(
					'filter_user_selected'	=> ($user == $filter_user)? 'selected="selected"' : '',
					'filter_user_value'		=> $user,
					'filter_user_name'		=> $GLOBALS['phpgw']->common->grab_owner_name($user)
//					'filter_user_name'		=> $user					
				));
				$this->t->parse('filter_user', 'block_filter_user', true);
			}
		}
	}
?>
