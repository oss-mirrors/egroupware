<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_monitorprocesses extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);

		function ui_monitorprocesses()
		{
			parent::monitor('monitor_processes');
		}

		function form()
		{
			$filter_active		= get_var('filter_active', 'any', '');
			$filter_valid		= get_var('filter_valid', 'any', '');
			$this->sort_mode	= get_var('sort_mode', 'any', 'wf_last_modif__desc');

			$processes_list	= $this->process_monitor->monitor_list_processes($this->start, -1, $this->sort_mode, $this->search_str, '');
			$stats			= $this->process_monitor->monitor_stats();

			$this->show_filter_process();
			$this->show_filter_active($filter_active);
			$this->show_filter_valid($filter_valid);
			$this->show_process_table($processes_list['data']);

			$this->fill_general_variables();
			$this->finish();

		}

		function show_filter_active($filter_active)
		{
			$this->t->set_var(array(
				'selected_active_all'		=> ($filter_active == '')? 'selected="selected"' : '',
				'selected_active_active'	=> ($filter_active == 'y')? 'selected="selected"' : '',
				'selected_active_inactive'	=> ($filter_active == 'n')? 'selected="selected"' : '',
			));
		}

		function show_filter_valid($filter_valid)
		{
			$this->t->set_var(array(
				'selected_valid_all'		=> ($filter_valid == '')? 'selected="selected"' : '',
				'selected_valid_valid'		=> ($filter_valid == 'y')? 'selected="selected"' : '',
				'selected_valid_invalid'	=> ($filter_valid == 'n')? 'selected="selected"' : '',
			));
		}

		function show_process_table($processes_list_data)
		{
			$this->t->set_var(array(
				'header_name'		=> $this->nextmatchs->show_sort_order($this->sort, 'name', $this->order, '', lang('Name')),
				'header_act'		=> $this->nextmatchs->show_sort_order($this->sort, 'isActive', $this->order, '', lang('Active')),
				'header_val'		=> $this->nextmatchs->show_sort_order($this->sort, 'isValid', $this->order, '', lang('Valid')),
			));

			$this->t->set_block('monitor_processes', 'block_listing', 'listing');
			foreach ($processes_list_data as $process)
			{
				$this->t->set_var(array(
					'process_href'				=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&p_id='. $process['wf_p_id']),
					'process_name'				=> $process['wf_name'],
					'process_version'			=> $process['wf_version'],
					'process_href_activities'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitoractivities.form&filter_process='. $process['wf_p_id']),
					'process_activities'		=> $process['wf_activities'],
					'process_active_img'		=> ($process['wf_is_active'] == 'y')? '<img src="'. $GLOBALS['phpgw']->common->image('workflow', 'refresh2') .'" alt="'. lang('Active') .'" title="'. lang('Active') .'" />' : '',
					'process_valid_img'			=> $GLOBALS['phpgw']->common->image('workflow', ($process['wf_is_valid'] == 'y')? 'green_dot' : 'red_dot'),
					'process_valid_alt'			=> ($process['wf_is_valid'] == 'y')? lang('Valid') : lang('Invalid'),
					'process_href_inst_active'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $process['wf_p_id'] .'&filter_status=active'),
					'process_inst_active'		=> $process['active_instances'],
					'process_href_inst_comp'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $process['wf_p_id'] .'&filter_status=completed'),
					'process_inst_comp'			=> $process['completed_instances'],
					'process_href_inst_abort'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $process['wf_p_id'] .'&filter_status=aborted'),
					'process_inst_abort'		=> $process['aborted_instances'],
					'process_href_inst_excep'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $process['wf_p_id'] .'&filter_status=exception'),
					'process_inst_excep'		=> $process['exception_instances'],
					'color_line'				=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('listing', 'block_listing', true);
			}
		}
	}
?>
