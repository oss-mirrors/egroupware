<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_monitorworkitems extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);
		var $filter_user;
		var $filter_instance;

		function ui_monitorworkitems()
		{
			parent::monitor('monitor_workitems');
		}

		function form()
		{
			//overrite monitor default sort values
			$this->order			= get_var('order', 'any', 'wf_item_id');
			$this->sort_mode		= $this->order . '__'. $this->sort;
			
			//get our own filters
			$this->filter_user		= (int)get_var('filter_user', 'any', 0);
			$this->filter_instance		= (int)get_var('filter_instance', 'any', 0);
			
			//echo "order: <pre>";print_r($this->order);echo "</pre>";
			//echo "sort_mode: <pre>";print_r($this->sort_mode);echo "</pre>";
			
				
			$this->link_data['search_str'] = $this->search_str;
			if ($this->filter_process) 
			{
		        	$this->wheres[] = "gp.wf_p_id=".$this->filter_process;
				$this->link_data['filter_process'] = $this->filter_process;
			}
			if ($this->filter_activity) 
			{  
		        	$this->wheres[] = "ga.wf_activity_id=" .$this->filter_activity;
				$this->link_data['filter_activity'] = $this->filter_activity;
			}
			if ($this->filter_user)
			{
			        $this->wheres[] = "wf_user =" .$this->filter_user;
				$this->link_data['filter_user'] = $this->filter_user;
			}
			if ($this->filter_instance != 0)
			{
		        	$this->wheres[] = "wf_instance_id =" .$this->filter_instance;
				$this->link_data['filter_instance'] = $this->filter_instance;
			}
			else 
			{
				$this->filter_instance = '';
			}
			if (count($this->wheres) > 0)
			{
			        $this->where = implode(' and ', $this->wheres);
			}
			else 
			{
				$this->where = '';
			}
			
			$wi_users	=& $this->process_monitor->monitor_list_wi_users();
			$workitems	=& $this->process_monitor->monitor_list_workitems($this->start, $this->offset, $this->sort_mode, $this->search_str, $this->where);

			$this->show_filter_process();
			$this->show_filter_unique_activities();

			$this->show_filter_user($wi_users, $this->filter_user);
			$this->show_workitems_table($workitems['data'], $workitems['cant']);

			$this->t->set_var('filter_instance', $this->filter_instance);
			$this->fill_general_variables();
			$this->finish();
		}

		function show_workitems_table(&$workitems_data, $total_number)
		{
			//_debug_array($workitems_data);
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
				'wf_item_id'		=> lang('Id'),
				'wf_procname'		=> lang('Process'),
				'wf_act_name'		=> lang('Activity'),
				'wf_instance_id'	=> lang('Inst.'),
				'wf_order_id'		=> lang('#'),
				'wf_started'		=> lang('Started'),
				'wf_duration'		=> lang('Duration'),
				'wf_user'		=> lang('User'),
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

			$this->t->set_block('monitor_workitems', 'block_workitems_table', 'workitems_table');

			foreach ($workitems_data as $workitem)
			{
				$this->t->set_var(array(
					'wi_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_viewworkitem.form&itemId='. $workitem['wf_item_id']),
					'wi_id'				=> $workitem['wf_item_id'],
					'wi_wf_procname'	=> $workitem['wf_procname'],
					'wi_version'		=> $workitem['wf_version'],
					'act_icon'			=> $this->act_icon($workitem['wf_type'],$workitem['wf_is_interactive']),
					'wi_actname'		=> $workitem['wf_act_name'],
					'wi_adm_inst_href'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance.form&iid='. $workitem['wf_instance_id']),
					'wi_inst_id'		=> $workitem['wf_instance_id'],
					'wi_order_id'		=> $workitem['wf_order_id'],
					//'wi_started'		=> $workitem['wf_started'],
					'wi_started'		=> $GLOBALS['phpgw']->common->show_date($workitem['wf_started'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])),
					'wi_duration'		=> $this->time_diff($workitem['wf_duration']),
					//'wi_duration'		=> $workitem['wf_duration'],
					//'wi_user'			=> $workitem['wf_user'],
					//'wi_user'		=> $GLOBALS['phpgw']->common->grab_owner_name($workitem['wf_user']), 
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				if( $workitem['wf_user'] == '*') {
					$this->t->set_var('wi_user', $workitem['wf_user']);
				}
				else {
					$this->t->set_var('wi_user', $GLOBALS['phpgw']->common->grab_owner_name($workitem['wf_user']));
				}
				$this->t->parse('workitems_table', 'block_workitems_table', true);
			}
			if (!count($workitems_data)) $this->t->set_var('workitems_table', '<tr><td colspan="8" align="center">'. lang('There are no workitems available') .'</td></tr>');
		}

		function show_filter_user(&$wi_users, $filter_user)
		{
			$this->t->set_var('filter_user_select_all', (!$filter_user)? 'selected="selected"' : '');
			$this->t->set_block('monitor_workitems', 'block_filter_user', 'filter_user');
			foreach ($wi_users as $user)
			{
				$this->t->set_var(array(
					'filter_user_selected'	=> ($user == $filter_user)? 'selected="selected"' : '',
					'filter_user_value'		=> $user,
					//'filter_user_name'		=> $GLOBALS['phpgw']->common->grab_owner_name($user)
					//'filter_user_name'		=> $user,
				));
				if( $user == '*') {
					$this->t->set_var('filter_user_name', $user);
				}
				else {
					$this->t->set_var('filter_user_name', $GLOBALS['phpgw']->common->grab_owner_name($user));
				}
				$this->t->parse('filter_user', 'block_filter_user', true);
			}
		}
		function time_diff($to) {
			$days = (int)($to/(24*3600));
			$to = $to - ($days*(24*3600));
			$hours = (int)($to/3600);
			$to = $to - ($hours*3600);
			$min = date("i", $to);
			$to = $to - ($min*60);			
			$sec = date("s", $to);

			return "$days days, $hours:$min:$sec";
		}
	}
?>
