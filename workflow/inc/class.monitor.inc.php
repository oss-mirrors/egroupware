<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class monitor extends workflow
	{
		var $process_monitor;

		var $template_name;

		var $all_processes;

		var $all_activities;

		var $filter_process;

		function monitor($template_name)
		{
			parent::workflow();
			$this->process_monitor	= CreateObject('phpgwapi.workflow_processmonitor');
			$this->all_processes	= $this->process_monitor->monitor_list_processes(0, -1, 'wf_name__desc', '', '');
			$this->all_activities	= $this->process_monitor->monitor_list_activities(0, -1, 'wf_name__desc', '', '');
			$this->filter_process	= get_var('filter_process', 'any', '');
			$this->filter_activity	= get_var('filter_activity', 'any', '');
			$this->template_name = $template_name;

			$title = explode('_', $this->template_name);
			$title[0] = ucfirst($title[0]);
			$title[1] = ucfirst($title[1]);
			$title = implode(' ', $title);
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang($title);
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file($this->template_name, $this->template_name . '.tpl');
		}

		function show_filter_process()
		{
			$this->t->set_var('filter_process_selected_all', (!$this->filter_process)? 'selected="selected"' : '');
			$this->t->set_block($this->template_name, 'block_filter_process', 'filter_process');
			foreach ($this->all_processes['data'] as $process)
			{
				$this->t->set_var(array(
					'filter_process_selected'	=> ($process['wf_p_id'] == $this->filter_process)? 'selected="selected"' : '',
					'filter_process_value'		=> $process['wf_p_id'],
					'filter_process_name'		=> $process['wf_name'],
					'filter_process_version'	=> $process['wf_version'],

				));
				$this->t->parse('filter_process', 'block_filter_process', true);
			}
		}

		function show_filter_activities()
		{
			$this->t->set_var('filter_activity_selected_all', (!$this->filter_activity)? 'selected="selected"' : '');
			$this->t->set_block($this->template_name, 'block_filter_activity', 'filter_activity');
			foreach ($this->all_activities['data'] as $activity)
			{
				$this->t->set_var(array(
					'filter_activity_selected'	=> ($activity['wf_activity_id'] == $this->filter_activity)? 'selected="selected"' : '',
					'filter_activity_value'		=> $activity['wf_activity_id'],
					'filter_activity_name'		=> $activity['wf_name'],

				));
				$this->t->parse('filter_activity', 'block_filter_activity', true);
			}
		}


		function show_filter_unique_activities($where = '')
		{
			//echo "where: <pre>";print_r($where);echo "</pre>";
			
		    $unique_activities = $this->process_monitor->monitor_list_activities(0, -1, 'wf_name__desc', '', $where);
				
			//echo "unique_activities: <pre>";print_r($unique_activities);echo "</pre>";
			
			$this->t->set_var('filter_activity_selected_all', (!$this->filter_activity)? 'selected="selected"' : '');
			$this->t->set_block($this->template_name, 'block_filter_activity', 'filter_activity');
			foreach ($unique_activities['data'] as $activity)
			{
				$this->t->set_var(array(
					'filter_activity_selected'	=> ($activity['wf_activity_id'] == $this->filter_activity)? 'selected="selected"' : '',
					'filter_activity_value'		=> $activity['wf_activity_id'],
					'filter_activity_name'		=> $activity['wf_name'].' ('.$activity['wf_procname']. ' '.$activity['wf_version'].')'
				));
				$this->t->parse('filter_activity', 'block_filter_activity', true);
			}
		}
				
		function fill_general_variables()
		{
			$class_name = explode('_', $this->template_name);
			$class_name = implode('', $class_name);
			$this->t->set_var(array(
				'message'				=> implode('<br>', $this->message),
				'start'					=> $this->start,
				'search_str'			=> $this->search_str,
				'sort'					=> $this->sort,
				'order'					=> $this->order,
				'form_action'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_'. $class_name .'.form'),
				'monitor_stats'			=> $this->fill_monitor_stats($this->stats),
			));
		}

		function finish()
		{
			$this->translate_template($this->template_name);
			$this->t->pparse('output', $this->template_name);
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

	}
?>
