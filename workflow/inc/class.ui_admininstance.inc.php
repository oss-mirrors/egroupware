<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	// TODO: allow to enter comments

	class ui_admininstance extends workflow
	{
		var $public_functions = array(
			'form'	=> true,
		);

		var $instance_manager;

		var $process_manager;

		var $activity_manager;

		function ui_admininstance()
		{
			parent::workflow();
			$this->instance_manager	= CreateObject('phpgwapi.workflow_instancemanager');
			$this->process_manager	= CreateObject('phpgwapi.workflow_processmanager');
			$this->activity_manager	= CreateObject('phpgwapi.workflow_activitymanager');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Instance');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('admin_instance', 'admin_instance.tpl');

			$iid	= (int)get_var('iid', 'any', 0);

			$instance			= $this->instance_manager->get_instance($iid);
			$process			= $this->process_manager->get_process($instance['pId']);
			$proc_activities	= $this->activity_manager->list_activities($instance['pId'], 0, -1, 'flowNum_asc', '', '');
			$instance_acts		= $this->instance_manager->get_instance_activities($iid);
			$properties			= $this->instance_manager->get_instance_properties($iid);

			if (!$iid) die(lang('No instance indicated'));

			$this->show_select_owner($instance);
			$this->show_select_sendto($proc_activities['data']);
			$this->show_instance_acts($iid, $instance_acts);
			$this->show_properties($iid, $properties);

			// fill the general varibles of the template
			$this->t->set_var(array(
				'message'			=> implode('<br>', $this->message),
				'form_action'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstances.form'),
				'instance_process'	=> lang('Instance: %1 (Process: %2)', $instance['instanceid'], $process['name'] . ' ' . $process['version']),
				'inst_started'		=> $GLOBALS['phpgw']->common->show_date($instance['started']),
				'wi_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorworkitems.form&filter_instance='. $instance['instanceId']),
				'wi_wi'				=> $instance['workitems'],
				'status_active'		=> ($instance['status'] == 'active')? 'selected="selected"' : '',
				'status_exception'	=> ($instance['status'] == 'exception')? 'selected="selected"' : '',
				'status_completed'	=> ($instance['status'] == 'completed')? 'selected="selected"' : '',
				'status_aborted'	=> ($instance['status'] == 'aborted')? 'selected="selected"' : '',
			));


			$this->translate_template('admin_instance');
			$this->t->pparse('output', 'admin_instance');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function show_select_owner($instance)
		{
			$users = $GLOBALS['phpgw']->accounts->get_list('accounts');
			//echo "users: <pre>";print_r($users);echo "</pre>";

			$this->t->set_block('admin_instance', 'block_select_owner', 'select_owner');

			foreach ($users as $user)
			{
				$this->t->set_var(array(
					'select_owner_selected'	=> ($user['account_id'] == $instance['oner'])? 'selected="selected"' : '',
					'select_owner_value'	=> $user['account_id'],
					'select_owner_name'		=> $user['account_firstname'] . ' ' . $user['account_lastname'],
				));
				$this->t->parse('select_owner', 'block_select_owner', true);
			}
		}

		function show_select_sendto($proc_activities_data)
		{
			$this->t->set_block('admin_instance', 'block_select_sendto', 'select_sendto');
			foreach ($proc_activities_data as $activity)
			{
				$this->t->set_var(array(
					'sendto_act_value'	=> $activity['activityId'],
					'sendto_act_name'	=> $activity['name'],
				));
				$this->t->parse('select_sendto', 'block_select_sendto', true);
			}
			if (!count($proc_activities_data)) $this->t->set_var('select_sendto', '');
		}

		function show_instance_acts($iid, $instance_acts)
		{
			$this->t->set_block('admin_instance', 'block_instance_acts_table_users');
			$users = $GLOBALS['phpgw']->accounts->get_list('accounts');
			foreach ($users as $user)
			{
				$this->t->set_var(array(
					'inst_act_usr_value'	=> $user['account_id'],
					'inst_act_usr_selected'	=> ($user['account_id'] == $activity['user'])? 'selected="selected"' : '',
					'inst_act_usr_name'		=> $user['account_firstname'] . ' ' . $user['account_lastname'],
				));
				$this->t->parse('instance_acts_table_users', 'block_instance_acts_table_users', true);
			}

			if ($instance_acts)
			{
				$this->t->set_block('admin_instance', 'block_instance_acts_table', 'instance_acts_table');
				foreach ($instance_acts as $activity)
				{
					if ($activity['isInteractive'] == 'y')
					{
						$this->t->set_var('inst_act_run', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&activityId='. $activity['activityId']) .'&iid='. $iid. '"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run') .'" title="'. lang('run') .'" /></a>');
					}
					else
					{
						$this->t->set_var('inst_act_run', '');
					}

					$this->t->set_var(array(
						'inst_act_name'				=> $activity['name'],
						'inst_act_status'			=> $activity['actstatus'],
						'inst_act_id'				=> $activity['activityId'],
						'inst_act_star_selected'	=> ($activity['user'] == '*')? 'selected="selected"' : '',
					));


					$this->t->parse('instance_acts_table', 'block_instance_acts_table', true);
				}

				$this->t->set_block('admin_instance', 'block_instance_acts', 'instance_acts');
				$this->translate_template('block_instance_acts');
				$this->t->parse('instance_acts', 'block_instance_acts');
			}
			else
			{
				$this->t->set_block('admin_instance', 'block_instance_acts', 'instance_acts');
				$this->t->set_var('instance_acts', '');
			}
		}

		function show_properties($iid, $props)
		{
			$this->t->set_block('admin_instance', 'block_properties', 'properties');
			foreach ($props as $key=>$prop)
			{
				if (strlen($prop) > 80)
				{
					$this->t->set_var('prop_value', '<textarea name="props['. $key .']" cols="80" rows="5">'. $prop .'</textarea>');
				}
				else
				{
					$this->t->set_var('prop_value', '<input type="text" name="props['. $key .']" value="'. $prop .'" />');
				}
				$this->t->set_var(array(
					'prop_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance&iid'. $iid .'&unsetprop='. $key),
					'img_trash'		=> $GLOBALS['phpgw']->common->image('workflow', 'trash'),
					'prop_key'		=> $key,
					'color_line'	=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('properties', 'block_properties', true);
			}
			if (!count($props)) $this->t->set_var('properties', '<tr><td colspan="2" align="center">'. lang('There are no properties available') .'</td></tr>');
		}
	}
?>
