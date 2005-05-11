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
	        $this->order			= get_var('order', 'any', 'wf_flow_num');
	        $this->sort				= get_var('sort', 'any', 'asc');
	        $this->sort_mode		= $this->order . '__'. $this->sort;
	        $this->search_str 		= get_var('search_str','any','');

			$filter_is_interactive	= get_var('filter_is_interactive', 'any', '');
		    $filter_is_autorouted	= get_var('filter_is_autorouted', 'any', '');
			$filter_process			= (int)get_var('filter_process','any','');
		    $filter_activity		= (int)get_var('filter_activity', 'any', 0);
		    $filter_type			= get_var('filter_type', 'any', '');
			
			$this->extra = array();
			if ($filter_is_interactive) {
				$this->wheres[] = "wf_is_interactive='" . $filter_is_interactive . "'"; 
				$this->extra['filter_is_interactive'] = $filter_is_interactive;
			}
			if ($filter_is_autorouted) {
				$this->wheres[] = "wf_is_autorouted='" . $filter_is_autorouted . "'"; 
				$this->extra['filter_is_autorouted'] = $filter_is_autorouted;
			}
			if ($filter_process) {
				$this->wheres[] = "ga.wf_p_id='" .$filter_process. "'"; 
				$this->extra['filter_process'] = $filter_process;
			}		
			if ($filter_activity) {
				$this->wheres[] = "wf_activity_id='" .$filter_activity. "'"; 
				$this->extra['filter_activity'] = $filter_activity;
			}
			if ($filter_type) {
				$this->wheres[] = "wf_type= '" . $filter_type . "'"; 
				$this->extra['filter_type'] = $filter_type;
			}

			if( count($this->wheres) > 0 ) {
		        $this->where = implode(' and ', $this->wheres);
			}
			else {
				$this->where = '';
			}

			if( count($this->extra) == 0 ) {
				$this->extra = '';
			}

			
	        $activities		= $this->process_monitor->monitor_list_activities($this->start, -1, $this->sort_mode, $this->search_str,$this->where);
	        $all_types		= $this->process_monitor->monitor_list_activity_types();
	        $this->stats	= $this->process_monitor->monitor_stats();

	        $this->show_filter_process();
	        $this->show_filter_unique_activities($this->where);
	        $this->show_filter_types($all_types, $filter_type);
	        $this->show_filter_is_interactive($filter_is_interactive);
	        $this->show_filter_is_autorouted($filter_is_autorouted);
	        $this->show_activities_table($activities['data']);

	        $this->fill_general_variables();
	        $this->finish();
		}

		function show_activities_table($activities_data)
		{
			//_debug_array($activities_data);
			$this->t->set_var(array(
				'header_process'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_p_id', $this->order, '', lang('Process'), $this->extra),
				'header_name'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_name', $this->order, '', lang('Name'), $this->extra),
				'header_type'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_type', $this->order, '', lang('Type'), $this->extra),
				'header_int'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_is_interactive', $this->order, '', lang('Interactive'), $this->extra),
				'header_routing'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_is_autorouted', $this->order, '', lang('Routing'), $this->extra),
			));

			$this->t->set_block('monitor_activities', 'block_act_table', 'act_table');
			if (!$activities_data) {
				$this->t->set_var('act_table', '<tr><td colspan="6" align="center">'. lang('There are no activities available') .'</td></tr>');
			}
			else {
				foreach ($activities_data as $activity)
				{
					if ($activity['wf_type'] == 'standalone')
					{
						$this->t->set_var('act_run', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&activity_id='. $activity['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run activity') .'" title="'. lang('run activity') .'" /></a>');
					}
					elseif ($activity['wf_type'] == 'start')
					{
						$this->t->set_var('act_run', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&activity_id='. $activity['wf_activity_id'] .'&createInstance=1') .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run activity') .'" title="'. lang('run activity') .'" /></a>');
					}
					else
					{
						$this->t->set_var('act_run', '');
					}
	
					$this->t->set_var(array(
						'act_process'				=> $activity['wf_procname'],
						'act_process_version'		=> $activity['wf_version'],
						'act_icon'					=> $this->act_icon($activity['wf_type'],$activity['wf_is_interactive']),
						'act_href'					=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&p_id='. $activity['wf_p_id'] .'&activity_id='. $activity['wf_activity_id']),
						'act_name'					=> $activity['wf_name'],
						'act_type'					=> $activity['wf_type'],
						'act_is_interactive'		=> $activity['wf_is_interactive'],
						'act_is_autorouted'			=> $activity['wf_is_autorouted'],
						'act_active_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $activity['wf_p_id'] .'&filter_status=active&filter_activity='. $activity['wf_activity_id']),
						'act_completed_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $activity['wf_p_id'] .'&filter_status=completed&filter_activity='. $activity['wf_activity_id']),
						'act_aborted_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $activity['wf_p_id'] .'&filter_status=aborted&filter_activity='. $activity['wf_activity_id']),
						'act_exception_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $activity['wf_p_id'] .'&filter_status=exception&filter_activity='. $activity['wf_activity_id']),
						'active_instances'			=> $activity['active_instances'],
						'completed_instances'		=> $activity['completed_instances'],
						'aborted_instances'			=> $activity['aborted_instances'],
						'exception_instances'		=> $activity['exception_instances'],
						'color_line'				=> $this->nextmatchs->alternate_row_color($tr_color),
					));
					$this->t->parse('act_table', 'block_act_table', true);
				}
			}
		}

		function show_filter_types($all_types, $filter_type)
		{
			$this->t->set_var('filter_type_selected_all', (!$filter_type)? 'selected="selected"' : '');
			$this->t->set_block('monitor_activities', 'block_filter_type', 'FilterType');
			foreach ($all_types as $type)
			{

				$this->t->set_var(array(
					'filter_type_selected'	=> ($type == $filter_type)? 'selected="selected"' : '',
					'filter_type'			=> $type,
					'filter_types'                  => $type,

				));
				$this->t->parse('FilterType', 'block_filter_type', true);
			}
		}

		function show_filter_is_interactive($filter_is_interactive)
		{
			$this->t->set_var(array(
				'filter_interac_selected_all'	=> ($filter_is_interactive)? '' : 'selected="selected"',
				'filter_interac_selected_y'		=> ($filter_is_interactive == 'y')? 'selected="selected"' : '',
				'filter_interac_selected_n'		=> ($filter_is_interactive == 'n')? 'selected="selected"' : '',
			));
		}

		function show_filter_is_autorouted($filter_is_autorouted)
		{
			$this->t->set_var(array(
				'filter_route_selected_all'	=> ($filter_is_autorouted)? '' : 'selected="selected"',
				'filter_route_selected_y'		=> ($filter_is_autorouted == 'y')? 'selected="selected"' : '',
				'filter_route_selected_n'		=> ($filter_is_autorouted == 'n')? 'selected="selected"' : '',
			));
		}
	}
?>
