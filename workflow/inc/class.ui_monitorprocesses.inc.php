<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_monitorprocesses extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);
		//new filters for this monitor child
		var $filter_active;
		var $filter_valid;

		function ui_monitorprocesses()
		{
			parent::monitor('monitor_processes');
		}

		function form()
		{
			//already done in monitor.inc.php
			//$filter_process		= (int)get_var('filter_process', 'any', 0);
			$this->filter_active		= get_var('filter_active', 'any', '');
			$this->filter_valid		= get_var('filter_valid', 'any', '');
			$this->order			= get_var('order', 'any', 'wf_last_modif');

			if ($this->filter_process) $this->wheres[] = "wf_p_id='" . $this->filter_process . "'";
			if ($this->filter_active) $this->wheres[] = "wf_is_active='" . $this->filter_active . "'";
			if ($this->filter_valid) $this->wheres[] = "wf_is_valid='" . $this->filter_valid . "'";
			$this->wheres = implode(' and ', $this->wheres);

			$this->link_data = array(
				'filter_process'	=> $this->filter_process,
				'filter_valid'		=> $this->filter_valid,
				'filter_active'		=> $this->filter_active,
				'search_str'		=> $this->search_str,
				'offset'		=> $this->offset,
				'start'			=> $this->start,
			);
			$processes_list	=& $this->process_monitor->monitor_list_processes($this->start, $this->offset, $this->sort_mode, $this->search_str, $this->wheres);
			$this->stats	=& $this->process_monitor->monitor_stats();

			$this->show_filter_process();
			$this->show_filter_active($this->filter_active);
			$this->show_filter_valid($this->filter_valid);
			$this->show_process_table($processes_list['data'],$processes_list['cant']);

			$this->fill_general_variables();
			$this->finish();

		}

		function show_filter_active($filter_active)
		{
			//set variable for other forms
			$this->t->set_var(array('filter_active_up'=>$filter_active));
			//show the select
			$this->t->set_var(array(
				'selected_active_all'		=> ($filter_active == '')? 'selected="selected"' : '',
				'selected_active_active'	=> ($filter_active == 'y')? 'selected="selected"' : '',
				'selected_active_inactive'	=> ($filter_active == 'n')? 'selected="selected"' : '',
			));
		}

		function show_filter_valid($filter_valid)
		{
			//set variable for other forms
			$this->t->set_var(array('filter_valid_up'=>$filter_valid));
			//show the select
			$this->t->set_var(array(
				'selected_valid_all'		=> ($filter_valid == '')? 'selected="selected"' : '',
				'selected_valid_valid'		=> ($filter_valid == 'y')? 'selected="selected"' : '',
				'selected_valid_invalid'	=> ($filter_valid == 'n')? 'selected="selected"' : '',
			));
		}

		function show_process_table(&$processes_list_data, $total_number)
		{
		
			//------------------------------------------- nextmatch --------------------------------------------
			$this->total_records = $total_number;
			// left and right nextmatchs arrows
			$this->t->set_var('left',$this->nextmatchs->left(
				$this->form_action,$this->start,$this->total_records,$this->link_data));
			$this->t->set_var('right',$this->nextmatchs->right(
				$this->form_action,$this->start,$this->total_records,$this->link_data));
			//show table headers with sort
			//warning header names are header_[name or alias of the column in the query without a dot]
			//this is necessary for sorting
			$header_array = array(
				'wf_name'	=> lang('Name'),
				'wf_is_active'	=> lang('active'),
				'wf_is_valid'	=> lang('valid'),
			       );
			foreach($header_array as $col => $translation) 
			{
				$this->t->set_var('header_'.$col,$this->nextmatchs->show_sort_order(
					$this->sort,$col,$this->order,'/index.php',$translation,$this->link_data));
			}
			
			// info about number of rows
			if (($this->total_records) > $this->offset)	
			{
				$this->t->set_var('lang_showing',lang('showing %1 - %2 of %3',
					1+$this->start,
					(($this->start+$this->offset) > ($this->total_records))? $this->total_records : $this->start+$this->offset,
					$this->total_records));
			}
			else 
			{
				$this->t->set_var('lang_showing', lang('showing %1',$this->total_records));
			}
			// --------------------------------------- end nextmatch ------------------------------------------

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
			if (!count($processes_list_data)) $this->t->set_var('listing', '<tr><td colspan="5" align="center">'.lang('There are no processes').'</tr>');
		}
	}
?>
