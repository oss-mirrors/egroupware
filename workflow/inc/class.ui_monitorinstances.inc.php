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
			$this->order			= get_var('order', 'GET', 'name');
			$this->sort				= get_var('sort', 'GET', 'asc');
			$this->sort_mode		= $this->order . '_'. $this->sort;
			$filter_activity		= (int)get_var('filter_activity', 'any', 0);
			$filter_status			= get_var('filter_status', 'any', '');
			$filter_act_status		= get_var('filter_act_status', 'any', '');
			$filter_user			= get_var('filter_user', 'any', '');

			$all_statuses	= array('aborted', 'active', 'completed', 'exception');
			$users			= $this->process_monitor->monitor_list_users();
			$instances		= $this->process_monitor->monitor_list_instances($this->start, -1, $this->sort_mode, $this->search_str, '');

			$this->show_filter_process();
			$this->show_filter_activities();
			$this->show_filter_status($all_statuses, $filter_status);
			$this->show_filter_act_status($filter_act_status);
			$this->show_filter_user($users, $filter_user);
			$this->show_instances_table($instances['data']);

			$this->fill_general_variables();
			$this->finish();
		}

		function show_instances_table($instances_data)
		{
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
					'inst_id_href'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance.form&iid='. $instance['instanceId']),
					'inst_id'		=> $instance['instanceId'],
					'inst_name'		=> $instance['name'],
					'inst_status'	=> $instance['status'],
					'inst_user'		=> $instance['user'],
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
					'filter_user_name'		=> $user,
				));
				$this->t->parse('filter_user', 'block_filter_user', true);
			}
		}
	}
?>
