<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_viewworkitem extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);

		function ui_viewworkitem()
		{
			parent::monitor('view_workitem');
		}

		function form()
		{
			$itemId	= (int)get_var('itemId', 'any', 0);

			if (!$itemId) die(lang('No work item indicated'));

			$wi	= $this->process_monitor->monitor_get_workitem($itemId);

			$GLOBALS['phpgw']->accounts->get_account_name($wi['user'],$lid,$fname,$lname);

			$this->t->set_var(array(
				'wi_itemId'		=> $wi['itemId'],
				'wi_orderId'	=> $wi['orderId'],
				'wi_procname'	=> $wi['procname'],
				'wi_version'	=> $wi['version'],
				'act_icon'		=> $this->act_icon($wi['type']),
				'wi_name'		=> $wi['name'],
				'wi_user'		=> $fname . ' ' . $lname,
				'wi_started'	=> $GLOBALS['phpgw']->common->show_date($wi['started']),
				'wi_duration'	=> $wi['duration'],
			));

			$this->t->set_block('view_workitem', 'block_properties', 'properties');
			foreach ($wi['properties'] as $key=>$prop)
			{
				$this->t->set_var(array(
					'key'			=> $key,
					'prop_value'	=> $prop,
					'color_line'	=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('properties', 'block_properties', true);
			}
			if (!count($wi['properties'])) $this->set_var('properties', '<tr><td colspan="2" align="center">'. lang('No properties defined') .'</td></tr>');



			$this->fill_general_variables();
			$this->finish();
		}
	}
?>
