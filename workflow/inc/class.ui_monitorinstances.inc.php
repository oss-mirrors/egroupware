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
			$this->order			= get_var('order', 'GET', 'wf_name');
			$this->sort				= get_var('sort', 'GET', 'asc');
			$this->sort_mode		= $this->order . '__'. $this->sort;
			$filter_process			= (int)get_var('filter_process', 'any', 0);
			$filter_activity		= (int)get_var('filter_activity', 'any', 0);
			$filter_status			= get_var('filter_status', 'any', '');
			$filter_user			= get_var('filter_user', 'any', '');
		    $this->stats			= $this->process_monitor->monitor_stats();

			if ($filter_process) $this->wheres[] = "gi.`wf_p_id`='" . $filter_process . "'";
			if ($filter_activity) $this->wheres[] = "ga.`wf_activity_id`='" . $filter_activity . "'";
			if ($filter_status) $this->wheres[] = "gi.`wf_status`='" . $filter_status . "'";
			if ($filter_user) $this->wheres[] = "wf_owner='" . $filter_user . "'";
			$this->wheres = implode(' and ', $this->wheres);

			$all_statuses	= array('aborted', 'active', 'completed', 'exception');
			$users			= $this->process_monitor->monitor_list_users();
			$instances		= $this->process_monitor->monitor_list_instances($this->start, -1, $this->sort_mode, $this->search_str, $this->wheres);

			$this->show_filter_process();
			$this->show_filter_activities();
			$this->show_filter_status($all_statuses, $filter_status);
			$this->show_filter_user($users, $filter_user);
			$this->show_instances_table($instances['data']);

			$this->fill_general_variables();
			$this->finish();
		}

		function show_instances_table($instances_data)
		{
			//_debug_array($instances_data);
			$this->t->set_var(array(
				'header_id'			=> $this->nextmatchs->show_sort_order($this->sort, 'instanceId', $this->order, '', lang('Id')),
				'header_activity'	=> $this->nextmatchs->show_sort_order($this->sort, 'name', $this->order, '', lang('Activity')),
				'header_status'		=> $this->nextmatchs->show_sort_order($this->sort, 'status', $this->order, '', lang('Status')),
				'header_user'		=> $this->nextmatchs->show_sort_order($this->sort, 'user', $this->order, '', lang('User')),
			));

			$this->t->set_block('monitor_instances', 'block_inst_table', 'inst_table');

			foreach ($instances_data as $instance)
			{
				$this->t->set_var(array(
					'inst_id_href'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance.form&iid='. $instance['wf_instance_id']),
					'inst_id'		=> $instance['wf_instance_id'],
					'inst_name'		=> $instance['wf_name'],
					'inst_status'	=> $instance['wf_status'],
					'inst_user'		=> $instance['wf_user'],
					'color_line'	=> $this->nextmatchs->alternate_row_color($tr_color),
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

		function show_filter_user($users, $filter_user)
		{
			$this->t->set_var('filter_user_selected_all', (!$this->filter_user)? 'selected="selected"' : '');
			$this->t->set_block('monitor_instances', 'block_filter_user', 'filter_user');
			foreach ($users as $user)
			{
				$this->t->set_var(array(
					'filter_user_selected'	=> ($user == $filter_user)? 'selected="selected"' : '',
					'filter_user_value'		=> $user,
					'filter_user_name'		=> $user,
				));
				$this->t->parse('filter_user', 'block_filter_user', true);
			}
		}
	}
?>
