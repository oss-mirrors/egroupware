<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_monitoractivities extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);

		function ui_monitoractivities()
		{
			parent::monitor('monitor_activities');
		}

		function form()
		{
			$this->order			= get_var('order', 'GET', 'flowNum');
			$this->sort				= get_var('sort', 'GET', 'asc');
			$this->sort_mode		= $this->order . '_'. $this->sort;
			$filter_activity		= (int)get_var('filter_activity', 'any', 0);
			$filter_type			= (int)get_var('filter_type', 'any', '');
			$filter_isInteractive	= get_var('filter_isInteractive', 'any', '');
			$filter_isAutorouted	= get_var('filter_isInteractive', 'any', '');

			$activities		= $this->process_monitor->monitor_list_activities($this->start, -1, $this->sort_mode, $this->search_str,'');
			$all_types		= $this->process_monitor->monitor_list_activity_types();
			$stats			= $this->process_monitor->monitor_stats();

			$this->show_filter_process();
			$this->show_filter_activities();
			$this->show_filter_types($all_types, $filter_type);
			$this->show_filter_isInteractive($filter_isInteractive);
			$this->show_filter_isAutorouted($filter_isAutorouted);
			$this->show_activities_table($activities['data']);

			$this->fill_general_variables();
			$this->finish();
		}

		function show_activities_table($activities_data)
		{
			$this->t->set_var(array(
				'header_name'		=> $this->nextmatchs->show_sort_order($this->sort, 'name', $this->order, '', lang('Name')),
				'header_type'		=> $this->nextmatchs->show_sort_order($this->sort, 'type', $this->order, '', lang('Type')),
				'header_int'		=> $this->nextmatchs->show_sort_order($this->sort, 'isInteractive', $this->order, '', lang('Interactive')),
				'header_routing'	=> $this->nextmatchs->show_sort_order($this->sort, 'isAutoRouted', $this->order, '', lang('Routing')),
			));

			$this->t->set_block('monitor_activities', 'block_act_table', 'act_table');
			foreach ($activities_data as $activity)
			{
				if ($activity['type'] == 'standalone')
				{
					$this->t->set_var('act_run', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&activityId='. $activity['activityId']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run activity') .'" title="'. lang('run activity') .'" /></a>');
				}
				elseif ($activity['type'] == 'start')
				{
					$this->t->set_var('act_run', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&activityId='. $activity['activityId'] .'&createInstance=1') .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run activity') .'" title="'. lang('run activity') .'" /></a>');
				}
				else
				{
					$this->t->set_var('act_run', '');
				}

				$this->t->set_var(array(
					'act_icon'					=> $this->act_icon($activity['type']),
					'act_href'					=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&pid='. $activity['pId'] .'&activityId='. $activity['activityId']),
					'act_name'					=> $activity['name'],
					'act_type'					=> $activity['type'],
					'act_isInteractive'			=> $activity['isInteractive'],
					'act_isAutorouted'			=> $activity['isAutoRouted'],
					'act_active_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $activity['pId'] .'&filter_status=active&filter_activity='. $activity['activityId']),
					'act_completed_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $activity['pId'] .'&filter_status=completed&filter_activity='. $activity['activityId']),
					'act_aborted_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $activity['pId'] .'&filter_status=aborted&filter_activity='. $activity['activityId']),
					'act_exception_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $activity['pId'] .'&filter_status=exception&filter_activity='. $activity['activityId']),
					'act_active'				=> $activity['active_instances'],
					'act_completed'				=> $activity['completed_instances'],
					'act_aborted'				=> $activity['aborted_instances'],
					'act_exception'				=> $activity['exception_instances'],
					'color_line'				=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('act_table', 'block_act_table', true);
			}
			if (!$activities_data) $this->t->set_var('act_table', '<tr><td colspan="6" align="center">'. lang('There are no activities available') .'</td></tr>');
		}

		function show_filter_types($all_types, $filter_type)
		{
			$this->t->set_var('filter_type_selected_all', (!$filter_type)? 'selected="selected"' : '');
			$this->t->set_block('monitor_activities', 'block_filter_type', 'filter_type');
			foreach ($all_types as $type)
			{
				$this->t->set_var(array(
					'filter_type_selected'	=> ($type == $filter_type)? 'selected="selected"' : '',
					'filter_type'			=> $type,

				));
				$this->t->parse('filter_type', 'block_filter_type', true);
			}
		}

		function show_filter_isInteractive($filter_isInteractive)
		{
			$this->t->set_var(array(
				'filter_interac_selected_all'	=> ($filter_isInteractive)? '' : 'selected="selected"',
				'filter_interac_selected_y'		=> ($filter_isInteractive == 'y')? 'selected="selected"' : '',
				'filter_interac_selected_n'		=> ($filter_isInteractive == 'n')? 'selected="selected"' : '',
			));
		}

		function show_filter_isAutorouted($filter_isAutorouted)
		{
			$this->t->set_var(array(
				'filter_route_selected_all'	=> ($filter_isAutorouted)? '' : 'selected="selected"',
				'filter_route_selected_y'		=> ($filter_isAutorouted == 'y')? 'selected="selected"' : '',
				'filter_route_selected_n'		=> ($filter_isAutorouted == 'n')? 'selected="selected"' : '',
			));
		}
	}
?>
