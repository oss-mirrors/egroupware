<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_monitorinstances extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);
		
		var $extra;
		var $extra_params;

		function ui_monitorinstances()
		{
			parent::monitor('monitor_instances');
		}

		function form()
		{
			$this->order			= get_var('order', 'any', 'wf_name');
			$this->sort				= get_var('sort', 'any', 'asc');
			$this->sort_mode		= $this->order . '__'. $this->sort;
			
			$filter_status			= get_var('filter_status', 'any', '');
			$filter_process 		= (int)get_var('filter_process', 'any', 0);			
			$filter_activity		= (int)get_var('filter_activity', 'any', 0);
			$filter_act_status		= get_var('filter_act_status', 'any', '');
			$filter_user			= get_var('filter_user', 'any', '');

			$this->stats			= $this->process_monitor->monitor_stats();

			//echo "order: <pre>";print_r($this->order);echo "</pre>";

			$this->extra = array();
			if ($filter_status) 
			{
				$this->wheres[] = "gi.`wf_status`='" . $filter_status . "'"; 
				$this->extra[] = "filter_status=" .$filter_status;
			}
			if ($filter_process) {
				$this->wheres[] = "ga.wf_p_id='" .$filter_process. "'"; 
				$this->extra[] = "filter_process=" .$filter_process;
			}		
			if ($filter_activity) {
				$this->wheres[] = "ga.wf_activity_id='" .$filter_activity. "'"; 
				$this->extra[] = "filter_activity=" .$filter_act_status;
			}
			if ($filter_act_status)
			{
				$this->extra[] = "filter_act_status=" .$filter_act_status;
				$this->wheres[] = "gia.`wf_status`='" . $filter_act_status . "'"; 
			}

			if( count($this->wheres) > 0 ) {
		        $this->where = implode(' and ', $this->wheres);
			}
			else {
				$this->where = '';
			}
			if( count($this->extra) > 0 ) {
		        $this->extra_params = implode('&', $this->extra);
				$this->extra_params = '&'.$this->extra_params;
			}
			else {
				$this->extra_params = '';
			}
			//echo "where: <pre>";print_r($this->where);echo "</pre>";
			//echo "extra: <pre>";print_r($this->extra_params);echo "</pre>";

			$all_statuses	= array('aborted', 'active', 'completed', 'exception');
			$users			= $this->process_monitor->monitor_list_users();
			$instances		= $this->process_monitor->monitor_list_instances($this->start, -1, $this->sort_mode, $this->search_str, $this->where);

			$this->show_filter_process();
			if ($filter_process) {  
				$this->show_filter_unique_activities("ga.wf_p_id=".$filter_process);
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
				'header_id'			=> $this->nextmatchs->show_sort_order($this->sort, 'wf_instance_id', $this->order, '', lang('Id'), $this->extra_params),
				'header_process'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_p_id', $this->order, '', lang('Process'), $this->extra_param),
				'header_activity'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_name', $this->order, '', lang('Activity'), $this->extra_param),
				'header_status'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_status', $this->order, '', lang('Status'), $this->extra_param),
				//'header_act_status'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_act_status', $this->order, '', lang('Act. Status')),
				'header_act_status'		=> lang('Act. Status'),
				'header_user'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_user', $this->order, '', lang('User'), $this->extra_param),
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
				'filter_act_status_selected_all'	=> (!$filter_act_status)? 'selected="selected"' : '',
				'filter_act_status_running'			=> ($filter_act_status == 'running')? 'selected="selected"' : '',
				'filter_act_status_completed'		=> ($filter_act_status == 'completed')? 'selected="selected"' : ''
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
