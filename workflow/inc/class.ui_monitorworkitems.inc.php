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
			$this->order			= get_var('order', 'GET', 'orderId');
			$this->sort				= get_var('sort', 'GET', 'asc');
			$this->sort_mode		= $this->order . '_'. $this->sort;
			$filter_instance		= get_var('filter_instance', 'any', '');
			$filter_user			= get_var('filter_user', 'any', '');

			$wi_users	= $this->process_monitor->monitor_list_wi_users();
			$workitems	= $this->process_monitor->monitor_list_workitems($this->start, -1, $this->sort_mode, $this->search_str, '');

			$this->show_filter_process();
			$this->show_filter_activities();
			$this->show_filter_user($wi_users, $filter_user);
			$this->show_workitems_table($workitems['data']);

			$this->t->set_var('filter_instance', $filter_instance);
			$this->fill_general_variables();
			$this->finish();
		}

		function show_workitems_table($workitems_data)
		{
			$this->t->set_var(array(
				'header_id'			=> $this->nextmatchs->show_sort_order($this->sort, '', $this->order, 'itemId', lang('Id')),
				'header_process'	=> $this->nextmatchs->show_sort_order($this->sort, '', $this->order, 'procname', lang('Process')),
				'header_activity'	=> $this->nextmatchs->show_sort_order($this->sort, '', $this->order, 'actname', lang('Activity')),
				'header_ins'		=> $this->nextmatchs->show_sort_order($this->sort, '', $this->order, 'instanceId', lang('Instance')),
				'header_num'		=> $this->nextmatchs->show_sort_order($this->sort, '', $this->order, 'orderId', '#'),
				'header_start'		=> $this->nextmatchs->show_sort_order($this->sort, '', $this->order, 'started', lang('Start')),
				'header_duration'	=> $this->nextmatchs->show_sort_order($this->sort, '', $this->order, 'duration', lang('Duration')),
				'header_user'		=> $this->nextmatchs->show_sort_order($this->sort, '', $this->order, 'user', lang('User')),
			));

			$this->t->set_block('monitor_workitems', 'block_workitems_table', 'workitems_table');

			foreach ($workitems_data as $workitem)
			{
				$this->t->set_var(array(
					'wi_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_viewworkitem.form&itemId='. $workitem['itemId']),
					'wi_id'				=> $workitem['itemId'],
					'wi_procname'		=> $workitem['procname'],
					'wi_version'		=> $workitem['version'],
					'act_icon'			=> $this->act_icon($workitem['type']),
					'wi_actname'		=> $workitem['actname'],
					'wi_adm_inst_href'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance.form&iid='. $workitem['instanceId']),
					'wi_inst_id'		=> $workitem['instanceId'],
					'wi_order_id'		=> $workitem['orderId'],
					'wi_started'		=> $workitem['started'],
					'wi_duration'		=> $workitem['duration'],
					'wi_user'			=> $workitem['user'],
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
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
					'filter_user_name'		=> $user,
				));
				$this->t->parse('filter_user', 'block_filter_user', true);
			}
		}
	}
?>
