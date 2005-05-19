<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_useractivities extends workflow
	{
		var $public_functions = array(
			'form'	=> true
		);

		var $GUI;

		var $filter_process;

		function ui_useractivities()
		{
			parent::workflow();
			$this->GUI	= CreateObject('workflow.workflow_gui');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('User Activities');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('user_activities', 'user_activities.tpl');

			$this->order			= get_var('order', 'any', 'wf_procname');
			$this->sort				= get_var('sort', 'any', 'asc');
			$this->sort_mode		= $this->order . '__' . $this->sort;
			$this->filter_process	= (int)get_var('filter_process', 'any', 0);

			if ($this->filter_process) $this->wheres[] = 'gp.wf_p_id=' . $this->filter_process;
			$this->wheres = implode(' and ', $this->wheres);

			$all_processes = $this->GUI->gui_list_user_processes($GLOBALS['phpgw_info']['user']['account_id'], $this->start, -1, 'wf_procname__asc', '', '');
			$activities = $this->GUI->gui_list_user_activities($GLOBALS['phpgw_info']['user']['account_id'], $this->start, -1, $this->sort_mode, '', $this->wheres);

			// show process select box
			$this->show_process_select_box($all_processes['data']);

			// show activities list
			$this->show_activities_list($activities['data']);

			// fill the general varibles of the template
			$this->t->set_var(array(
				'message'				=> implode('<br>', $this->message),
				'form_filtering_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_useractivities.form'),
				'filter_process'		=> $this->filter_process,
				'sort'					=> $this->sort,
				'order'					=> $this->order,
			));

			$this->translate_template('user_activities');
			$this->t->pparse('output', 'user_activities');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function show_activities_list($activities_data)
		{
			//echo "activities_data: <pre>";print_r($activities_data);echo "<pre>";
			$filters = array(
				'filter_process'	=> $this->filter_process,
			);
			$this->t->set_var(array(
				'header_process'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_procname', $this->order, 'index.php', lang('Process'), $filters),
				'header_activity'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_name', $this->order, 'index.php', lang('Activity'), $filters),
			));

			$this->t->set_block('user_activities', 'block_activities_list', 'activities_list');
			foreach ($activities_data as $activity)
			{
				$act_name = '';
				if ($activity['wf_instances'] > 0) $act_name = '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&filter_process='. $activity['wf_p_id'] .'&filter_activity='. $activity['wf_activity_id']) .'">';
				$act_name .= $activity['wf_name'];
				if ($activity['wf_instances'] > 0) $act_name .= '</a>';

				if ($activity['wf_is_interactive'] == 'y' && ($activity['wf_type'] == 'start' || $activity['wf_type'] == 'standalone'))
				{
					$arrow = '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&activity_id='. $activity['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run activity') .'" title="'. lang('run activity') .'" /></a>';
				}
				else
				{
					$arrow = '';
				}
				$this->t->set_var(array(
					'act_wf_procname'		=> $activity['wf_procname'],
					'act_proc_version'	=> $activity['wf_version'],
					'act_icon'			=> $this->act_icon($activity['wf_type'],$activity['wf_is_interactive']),
					'act_name'			=> $act_name,
					'run_act'			=> $arrow,
					'act_instances'		=> $activity['wf_instances'],
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('activities_list', 'block_activities_list', true);
			}
			if (!count($activities_data)) $this->t->set_var('activities_list', '<tr><td colspan="3" align="center">'. lang('There are no user activites available') .'</td></tr>');
		}

		function show_process_select_box($processes_data)
		{
			if (!$this->filter_process)
			{
				$this->t->set_var('filter_process_all_selected', 'selected="selected"');
			}
			else
			{
				$this->t->set_var('filter_process_all_selected', '');
			}

			$this->t->set_block('user_activities', 'block_select_process', 'select_process');
			//echo "processes_data: <pre>";print_r($processes_data);echo "</pre>";
			foreach ($processes_data as $process_data)
			{
				//echo "process_data: <pre>";print_r($process_data);echo "</pre>";
				$this->t->set_var(array(
					'filter_process_selected'	=> ($process_data['wf_p_id'] == $this->filter_process)? 'selected="selected"' : '',
					'filter_process_value'		=> $process_data['wf_p_id'],
					'filter_process_name'		=> $process_data['wf_procname'],
					'filter_process_version'	=> $process_data['wf_version'],
				));
				$this->t->parse('select_process', 'block_select_process', true);
			}
			if (!count($processes_data)) $this->t->set_var('select_process', '');
		}
	}

?>
