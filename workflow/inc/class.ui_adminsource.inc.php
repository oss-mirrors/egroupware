<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_adminsource extends workflow
	{

		var $public_functions = array(
			'form'	=> true,
		);

		var $process_manager;

		var $activity_manager;

		function ui_adminsource()
		{
			parent::workflow();
			$this->process_manager = CreateObject('phpgwapi.workflow_processmanager');
			$this->activity_manager = CreateObject('phpgwapi.workflow_activitymanager');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Processes Sources');

			// js function to introduce commands in the textarea
			$GLOBALS['phpgw_info']['flags']['java_script_thirst'] = "<script>
				function setSomeElement(fooel, foo1) {\n
					document.getElementById(fooel).value = document.getElementById(fooel).value + foo1;\n
				}\n
			</script>";
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('admin_source', 'admin_source.tpl');
			$this->t->set_block('admin_source', 'block_select_activity', 'select_activity');

			$activityId			= (int)get_var('activityId', 'any', 0);
			$template			= (int)get_var('template', 'GET', 0);
			$switch_to_code		= get_var('switch_to_code', 'POST', false);
			$switch_to_tpl		= get_var('switch_to_tpl', 'POST', false);
			$source_type		= get_var('source_type', 'POST', false);
			$save				= get_var('save', 'POST', false);
			$source				= get_var('source', 'POST', false);
		
			if (!$this->pId) die(lang('No process indicated'));
			$proc_info = $this->process_manager->get_process($this->pId);

			// fetch activity info
			if ($activityId)
			{
				$activity_info = $this->activity_manager->get_activity($this->pId, $activityId);
			}
			else
			{
				$activity_info = array(
					'isInteractive'	=> 'n',
				);
			}

			// save template and stay in same view
			if ($save)
			{
				// security check
				if (!$source_type) die('Error: source_type not defined');
				$this->save_source($proc_info['normalized_name'], $activity_info['normalized_name'], $source_type, $source);
				if ($activityId) $this->activity_manager->compile_activity($this->pId, $activityId);
				$this->message[] = lang('Source saved');
			}
			// show source for template and don't save anything
			elseif ($template)
			{
				$source_type = 'template';
			}
			// save template if something was submited and show code
			elseif($switch_to_code)
			{
				if ($source) $this->save_source($proc_info['normalized_name'], $activityId, $source_type, 'template');
				$source_type = 'code';
			}
			// save code if something was submited and show template
			elseif($switch_to_tpl)
			{
				if ($source)
				{
					$this->save_source($proc_info['normalized_name'], $activityId, $source_type, 'code');
					if ($activityId) $this->activity_manager->compile_activity($this->pId, $activityId);
				}
				$source_type = 'template';
			}
			// show code. Nothing to save.
			else
			{
				$source_type = 'code';
			}

			// fetch source
			if ($activityId)
			{
				$data = $this->get_source($proc_info['normalized_name'], $activity_info['normalized_name'], $source_type);
				//echo "data: <pre>";print_r($data);echo "</pre>";
			}
			else
			{
				$data = $this->get_source($proc_info['normalized_name'], '', 'shared');
			}

			// check process validity and show errors if necessary
			$proc_info['isValid'] = $this->show_errors($this->activity_manager, $error_str);

			// fill proc_bar
			$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));


			// fill the general variables of the template
			$this->t->set_var(array(
				'message'				=> implode('<br>', $this->message),
				'errors'				=> $error_str,
				'form_editsource_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form'),
				'pid'					=> $this->pId,
				'selected_sharedcode'	=> ($activityId == 0)? 'selected="selected"' : '',
				'template'				=> $template,
				'data'					=> Htmlspecialchars($data),
				'source_type'			=> $source_type,
			));

			// fill activities select box
			$process_activities = $this->activity_manager->list_activities($this->pId, 0, -1, 'name_asc', '');
			foreach ($process_activities['data'] as $process_activity)
			{
				$this->t->set_var(array(
					'activityId'		=> $process_activity['activityId'],
					'selected_activity'	=> ($process_activity['activityId'] == $activityId)? 'selected="selected"' : '',
					'activity_name'		=> $process_activity['name'],
				));
				$this->t->parse('select_activity', 'block_select_activity', true);
			}

			// generate 'template' or 'code' submit button
			if ($source_type == 'template')
			{
				$this->t->set_var('code_or_tpl_btn', '<input type="submit" name="switch_to_code" value="'. lang('show code') .'" />');
			}
			elseif ($activity_info['isInteractive'] == 'y')
			{
				$this->t->set_var('code_or_tpl_btn', '<input type="submit" name="switch_to_tpl" value="'. lang('show template') .'" />');
			}
			else
			{
				$this->t->set_var('code_or_tpl_btn', '');
			}

			$this->show_side_commands($source_type, $activity_info);

			$this->translate_template('admin_source');
			$this->t->pparse('output', 'admin_source');
		}

		function show_side_commands($source_type, $activity_info)
		{
			if ($source_type == 'template')
			{
				$side_commands = lang('template');
			}
			else
			{
				$side_commands = "
					<a  href=\"javascript:setSomeElement('src','". '$instance' ."->setNextUser(\\'\\');');\">". lang('Set next user') ."</a><hr/>
					<a  href=\"javascript:setSomeElement('src','". '$instance' ."->get(\\'\\');');\">". lang('Get property') ."</a><hr/>
					<a  href=\"javascript:setSomeElement('src','". '$instance' ."->set(\\'\\',\\'\\');');\">". lang('Set property') ."</a><hr />";
				if ($activity_info['isInteractive'] == 'y')
				{
					$side_commands .= "
					<a href=\"javascript:setSomeElement('src','". '$instance' ."->complete();');\">". lang('Complete') ."</a><hr/>
					<a href=\"javascript:setSomeElement('src','if(isset(". '$_REQUEST' ."[\\'save\\']){\n ". ' $instance' ."->complete();\n}');\">". lang('Process form'). "</a><hr/>";
				}
				if ($activity_info['type'] == 'switch')
				{
					$side_commands .= "
						<a href=\"javascript:setSomeElement('src','". '$instance' ."->setNextActivity(\\'\\');');\">". lang('Set Next act') ."</a><hr />  		    
						<a href=\"javascript:setSomeElement('src','if() {\n  ". '$instance' ."->setNextActivity(\\'\\');\\n}');\">". lang('If:SetNextact') ."</a><hr />
						<a href=\"javascript:setSomeElement('src','switch(". '$instance' ."->get(\\'\\')){\\n  case:\\'\\':\\n  ". '$instance' ."->setNextActivity(\\'\\');\\n  break;\\n}');\">". lang('Switch construct') ."</a><hr />";
				}

			}
			$this->t->set_var('side_commands', $side_commands);
		}

	}
?>
