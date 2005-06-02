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
		
		       //regis: acl check
			if ( !(($GLOBALS['phpgw']->acl->check('run',1,'admin')) || ($GLOBALS['phpgw']->acl->check('admin_workflow',1,'workflow'))) )
			{
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
				echo lang('access not permitted');
				$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_admininstance');
				$GLOBALS['phpgw']->log->commit();
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$this->instance_manager	=& CreateObject('workflow.workflow_instancemanager');
			$this->process_manager	=& CreateObject('workflow.workflow_processmanager');
			$this->activity_manager	=& CreateObject('workflow.workflow_activitymanager');
		}

		function form()
		{
			// FIXME: active user should be done in a per activity level, not per instance
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Instance');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('admin_instance', 'admin_instance.tpl');

			$iid			= (int)get_var('iid', 'any', 0);
			$instance_status	= get_var('status', 'POST', '');
			$instance_name          = get_var('instance_name', 'POST', '');
			$instance_owner		= (int)get_var('owner', 'POST', 0);
			$instance_priority      = (int)get_var('instance_priority', 'POST', 0);

			// save changes
			if (isset($_POST['save']))
			{
				//save changes only if the field really changed
				$old_instance_status = get_var('instance_previous_status', 'POST', '');
				$old_instance_name = get_var('instance_previous_name', 'POST', '');
				$old_instance_owner	= (int)get_var('instance_previous_owner', 'POST', 0);
				$old_instance_priority = (int)get_var('instance_previous_priority', 'POST', 0);
				
				if (!($instance_status == $old_instance_status))
				{
					$this->instance_manager->set_instance_status($iid, $instance_status);
				}
				if (!($instance_owner == $old_instance_owner))
				{
					$this->instance_manager->set_instance_owner($iid, $instance_owner);
				}
				if (!($instance_name == $old_instance_name))
				{
					$this->instance_manager->set_instance_name($iid, $instance_name);
				}
				if (!($instance_priority == $old_instance_priority))
				{
					$this->instance_manager->set_instance_priority($iid, $instance_priority);
				}

				// user reasignment
				if(count($_POST['acts']) != 0)
				{
				 	$activityusers = get_var('acts', 'POST', array());
				 	$oldactivityusers = get_var('previous_acts', 'POST', array());
				 	foreach (array_keys($activityusers) as $act)
				 	{
				 		$new_user = $activityusers[$act];
					  	$previous_user =$oldactivityusers[$act];
					  	if (!($new_user==$previous_user))
					  	{
					  		$this->instance_manager->set_instance_user($iid, $act , $new_user);
						}
					}
				}  

				if ($_POST['sendto'])
				{
					$this->instance_manager->set_instance_destination($iid, $_POST['sendto']);
				}
			}
			
			$instance		= $this->instance_manager->get_instance($iid);
			$process		= $this->process_manager->get_process($instance['wf_p_id']);
			$proc_activities	= $this->activity_manager->list_activities($instance['wf_p_id'], 0, -1, 'wf_flow_num__asc', '', '');
			$instance_acts		= $this->instance_manager->get_instance_activities($iid);
			$properties		= $this->instance_manager->get_instance_properties($iid);

			if (!$iid) die(lang('No instance indicated'));

			$this->show_select_owner($instance);
			$this->show_select_sendto($proc_activities['data']);
			$this->show_instance_acts($iid, $instance_acts);
			$this->show_properties($iid, $properties);

			// fill the general variables of the template
			$this->t->set_var(array(
				'message'		=> implode('<br>', $this->message),
				'iid'			=> $iid,
				'form_action'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance.form'),
				'instance_process'	=> lang('Instance: %1 (Process: %2)', $instance['wf_instance_id'], $process['wf_name'] . ' ' . $process['wf_version']),
				'inst_started'		=> $GLOBALS['phpgw']->common->show_date($instance['wf_started']),
				'inst_ended' 		=> $GLOBALS['phpgw']->common->show_date($instance['wf_ended']),
				'wi_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorworkitems.form&filter_instance='. $instance['wf_instance_id']),
				'wi_wi'			=> $instance['wf_workitems'],
				'instance_name'		=> $instance['wf_name'],
				'status'		=> $instance['wf_status'],
				'owner'			=> $instance['wf_owner'],
				'instance_priority'	=> $instance['wf_priority'],
				'status_active'		=> ($instance['wf_status'] == 'active')? 'selected="selected"' : '',
				'status_exception'	=> ($instance['wf_status'] == 'exception')? 'selected="selected"' : '',
				'status_completed'	=> ($instance['wf_status'] == 'completed')? 'selected="selected"' : '',
				'status_aborted'	=> ($instance['wf_status'] == 'aborted')? 'selected="selected"' : '',
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
					'select_owner_selected'	=> ($user['account_id'] == $instance['wf_owner'])? 'selected="selected"' : '',
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
					'sendto_act_value'	=> $activity['wf_activity_id'],
					'sendto_act_name'	=> $activity['wf_name'],
				));
				$this->t->parse('select_sendto', 'block_select_sendto', true);
			}
			if (!count($proc_activities_data)) $this->t->set_var('select_sendto', '');
		}

		function show_instance_acts($iid, $instance_acts)
		{
			$this->t->set_block('admin_instance', 'block_instance_acts_table_users', 'instance_acts_table_users');
			$users = $GLOBALS['phpgw']->accounts->get_list('accounts');
			
			if ($instance_acts)
			{
				$this->t->set_block('admin_instance', 'block_instance_acts_table', 'instance_acts_table');
				foreach ($instance_acts as $activity)
				{
				        foreach ($users as $user)
				        {
				                $this->t->set_var(array(
							'inst_act_usr_value'	=> $user['account_id'],
							'inst_act_usr_selected'	=> ($user['account_id'] == $activity['wf_user'])? 'selected="selected"' : '',
							'inst_act_usr_name'	=> $user['account_firstname'] . ' ' . $user['account_lastname'],
						 ));
						$this->t->parse('instance_acts_table_users', 'block_instance_acts_table_users', true);
					}

					if ($activity['wf_is_interactive'] == 'y')
					{
						$this->t->set_var('inst_act_run', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&activity_id='. $activity['wf_activity_id']).'&iid='. $iid. '"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run') .'" title="'. lang('run') .'" /></a>');
					}
					else
					{
						$this->t->set_var('inst_act_run', '');
					}

					$this->t->set_var(array(
						'activity_user'			=> $activity['wf_user'],
						'inst_act_name'			=> $activity['wf_name'],
						'inst_act_status'		=> $activity['wf_act_status'],
						'inst_act_id'			=> $activity['wf_activity_id'],
						'inst_act_star_selected'	=> ($activity['wf_user'] == '*')? 'selected="selected"' : '',
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
