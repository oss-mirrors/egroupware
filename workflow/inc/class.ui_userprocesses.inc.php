<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_userprocesses extends workflow
	{
		var $public_functions = array(
			'form'	=> true
		);

		var $GUI;

		function ui_userprocesses()
		{
			parent::workflow();
			$this->GUI	= CreateObject('workflow.workflow_gui');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('User Processes');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('user_processes', 'user_processes.tpl');
			$this->t->set_block('user_processes', 'block_table', 'table');

			$this->order		= get_var('order', 'GET', 'wf_procname');
			$this->sort			= get_var('sort', 'GET', 'asc');
			$this->sort_mode	= $this->order . '__'. $this->sort;

			$processes = $this->GUI->gui_list_user_processes($GLOBALS['phpgw_info']['user']['account_id'], $this->start, -1, $this->sort_mode, '', '');
			//echo "user_id:";echo $GLOBALS['phpgw_info']['user']['account_id'];echo"<br>processes: <pre>";print_r($processes);echo "</pre>";

			// fill the table
			foreach ($processes['data'] as $process_data)
			{
				$this->t->set_var(array(
					'link_wf_procname'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_useractivities.form&filter_process='. $process_data['wf_p_id']),
					'item_wf_procname'		=> $process_data['wf_procname'],
					'item_version'		=> $process_data['wf_version'],
					'link_activities'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_useractivities.form&filter_process='. $process_data['wf_p_id']),
					'item_activities'	=> $process_data['wf_activities'],
					'link_instances'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&filter_process='. $process_data['wf_p_id']),
					'item_instances'	=> $process_data['wf_instances'],
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('table', 'block_table', true);
			}
			if (!count($processes['data'])) $this->t->set_var('table', '<tr><td colspan="3" align="center">'. lang('There are no processes available') .'</td></tr>');

			$this->translate_template('user_processes');
			$this->t->pparse('output', 'user_processes');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}
	}
?>
