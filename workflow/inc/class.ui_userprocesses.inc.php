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
			$this->GUI	= CreateObject('phpgwapi.workflow_gui');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('User Processes');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('user_processes', 'user_processes.tpl');
			$this->t->set_block('user_processes', 'block_table', 'table');

			$this->order		= get_var('order', 'GET', 'procname');
			$this->sort			= get_var('sort', 'GET', 'asc');
			$this->sort_mode	= $this->order . '_'. $this->sort;

			$processes = $this->GUI->gui_list_user_processes($GLOBALS['phpgw_info']['user']['account_id'], $this->start, -1, $this->sort_mode, '', '');
			//echo "user_id:";echo $GLOBALS['phpgw_info']['user']['account_id'];echo"<br>processes: <pre>";print_r($processes);echo "</pre>";

			// fill the table
			foreach ($processes['data'] as $process_data)
			{
				$this->t->set_var(array(
					'link_procname'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_useractivities.form&filter_process='. $process_data['pId']),
					'item_procname'		=> $process_data['procname'],
					'item_version'		=> $process_data['verion'],
					'link_activities'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_useractivities.form&filter_process='. $process_data['pId']),
					'item_activities'	=> $process_data['activities'],
					'link_instances'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&filter_process='. $process_data['pId']),
					'item_instances'	=> $process_data['instances'],
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
