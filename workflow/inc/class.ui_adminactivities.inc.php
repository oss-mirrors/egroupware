<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_adminactivities extends workflow
	{
		var $public_functions = array(
			'form'			=> true,
			'show_graph'	=> true
		);

		var $process_manager;

		var $activity_manager;

		var $role_manager;

		function ui_adminactivities()
		{
			parent::workflow();
			$this->process_manager	= CreateObject('phpgwapi.workflow_processmanager');
			$this->activity_manager	= CreateObject('phpgwapi.workflow_activitymanager');
			$this->role_manager		= CreateObject('phpgwapi.workflow_rolemanager');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Process Activities');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('admin_activities', 'admin_activities.tpl');
			$this->t->set_block('admin_activities', 'block_select_type', 'select_type');
			$this->t->set_block('admin_activities', 'block_activity_roles', 'activity_roles');
			$this->t->set_block('admin_activities', 'block_process_roles', 'process_roles');
			
			$activityId			= (int)get_var('activityId', 'any', 0);
			$name				= get_var('name', 'any', '');
			$description		= get_var('description', 'any', '');
			$type				= get_var('type', 'any', '');
			$isInteractive		= get_var('isInteractive', 'any', '');
			$isAutoRouted		= get_var('isAutoRouted', 'any', '');
			$userole			= get_var('userole', 'POST', '');
			$where				= get_var('where', array('GET', 'POST'), '');
			$where2				= get_var('where2', 'any', '');
			$find				= get_var('find', 'any', '');
			$find2				= get_var('find2', 'any', '');
			$sort_mode2			= get_var('sort_mode2', 'any', '');
			$filter_tran_name	= get_var('filter_tran_name', 'any', '');
			$this->order		= get_var('order', 'GET', 'flowNum');
			$this->sort			= get_var('sort', 'GET', 'asc');
			$this->sort_mode	= $this->order . '_'. $this->sort;

			if (!$this->pId) die(lang('No process indicated'));

			// *************************************   START OF OPERATIONS COMMANDED BY THIS SAME FORM ******************

			// add role to process roles
			if (isset($_POST['addrole']))
			{
				$this->add_process_role($_POST['rolename']);
				$this->message[] = lang('Role added to process');
			}

			// remove activity role
			if (isset($_GET['remove_role']) && $activityId)
			{
				$this->activity_manager->remove_activity_role($activityId, $_GET['remove_role']);
				$this->message[] = lang('Activity role removed');
			}

			// save activity
			if (isset($_POST['save_act']))
			{
				$activityId = $this->save_activity($activityId, $name, $description, $type, $isInteractive, $isAutoRouted, $userole, $rolename);
				$this->message[] = lang('Activity saved');
			}

			// delete activity
			if (isset($_POST['delete_act']))
			{
				if ($this->delete_activities(array_keys($_POST['activities']))) $this->message[] = lang('Delition successful');
			}

			// add transitions
			if (isset($_POST['add_trans'])) $this->message[] = $this->add_transition($_POST['actFromId'], $_POST['actToId']);

			// delete transitions
			if (isset($_POST['delete_tran'])) $this->delete_transitions($_POST['transition']);

			// *************************************   END OF OPERATIONS COMMANDED BY THIS SAME FORM ******************

			// retrieve activity info and its roles
			if (!$activityId || isset($_GET['new_activity']))
			{
				$activity_info = array(
					'name'			=> '',
					'description'	=> '',
					'activityId'	=> 0,
					'isInteractive'	=> 'y',
					'isAutoRouted'	=> 'n',
					'type'			=> 'activity'
				);
				$activity_roles = array();
			}
			else
			{
				$activity_info = $this->activity_manager->get_activity($this->pId, $activityId);
				$activity_roles = $this->activity_manager->get_activity_roles($activityId);
			}

			$proc_info = $this->process_manager->get_process($this->pId);
			$process_activities = $this->activity_manager->list_activities($this->pId, 0, -1, $this->sort_mode, $this->find, $where);
			if ($activityId) $this->search_transitions_act($process_activities, $activityId);
			$process_roles = $this->role_manager->list_roles($this->pId, 0, -1, 'name_asc', '');
			$process_transitions = $this->activity_manager->get_process_transitions($this->pId, $filter_tran_name);

			// update activities
			if (isset($_POST['update_act']))
			{
				$this->update_activities($process_activities, array_keys($_POST['activity_inter']), array_keys($_POST['activity_route']));
				$this->message[] = lang('Activities updated');
			}

			// activate process
			if (isset($_GET['activate_proc']))
			{
				$this->process_manager->activate_process($_GET['activate_proc']);
				$proc_info['isActive'] = 'y';
			}

			// deactivate process
			if (isset($_GET['deactivate_proc']))
			{
				$this->process_manager->deactivate_process($_GET['deactivate_proc']);
				$proc_info['isActive'] = 'n';
			}

			// check process validity and show errors if necessary
			$proc_info['isValid'] = $this->show_errors($this->activity_manager, $error_str);

			// fill proc_bar
			$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));

			// fill the general varibles of the template
			$this->t->set_var(array(
				'message'				=> implode('<br>', $this->message),
				'errors'				=> $error_str,
				'form_details_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
				'form_list_transitions_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
				'pid'					=> $this->pId,
				'where'					=> $where,
				'where2'				=> $where2,
				'sort_mode'				=> $this->sort_mode,
				'sort_mode2'			=> $sort_mode2,
				'find'					=> $find,
				'activityId'			=> $activity_info['activityId'],
				'new_act_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&pid='. $this->pId .'&new_activity=1'),
				'name'					=> $activity_info['name'],
				'description'			=> $activity_info['description'],
				'checked_interactive'	=> ($activity_info['isInteractive'] == 'y')? 'checked="checked"' : '',
				'checked_autorouted'	=> ($activity_info['isAutoRouted'] == 'y')? 'checked="checked"' : '',
				'add_trans_from'		=> $this->build_select_transition('add_tran_from[]', $process_activities['data'], true, false, 'from'),
				'add_trans_to'			=> $this->build_select_transition('add_tran_to[]', $process_activities['data'], true, false, 'to'),
				'filter_trans_from'		=> $this->build_select_transition('filter_tran_name', $process_activities['data'], false, true),
				'add_a_trans_from'		=> $this->build_select_transition('actFromId', $process_activities['data'], false, false),
				'add_a_trans_to'		=> $this->build_select_transition('actToId', $process_activities['data'], false, false),
			));

			// show process activities table
			$this->show_process_activities($process_activities['data']);

			// fill type select box
			$types = array('start', 'end', 'activity', 'switch', 'split', 'join', 'standalone');
			foreach ($types as $type)
			{
				$this->t->set_var(array(
					'type_value'	=> $type,
					'type_selected'	=> ($activity_info['type'] == $type)? 'selected="selected"' : '',
					'type_name'		=> lang($type)
				));
				$this->t->parse('select_type', 'block_select_type', true);
			}

			// fill activity roles
			if (!$activity_roles)
			{
				$this->t->set_var('activity_roles', lang('No roles asociated with this activity'));
			}
			else
			{
				foreach ($activity_roles as $role)
				{
					$this->t->set_var(array(
						'act_role_name'	=> $role['name'],
						'act_role_href'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&find='. $find .'&where='. $where .'&activityId='. $activity_info['activityId'] .'&pid='. $this->pId .'&remove_role='. $role['roleId'])
					));
					$this->t->parse('activity_roles', 'block_activity_roles', True);
				}
			}

			// fill process roles
			foreach ($process_roles['data'] as $role)
			{
				$this->t->set_var(array(
					'proc_roleId'	=> $role['roleId'],
					'proc_roleName'	=> $role['name']
				));
				$this->t->parse('process_roles', 'block_process_roles', True);
			}

			// fill list of transitions table
			$this->show_transitions_table($process_transitions);

			// create graph
			$this->activity_manager->build_process_graph($this->pId);

			$this->translate_template('admin_activities');
			$this->t->pparse('output', 'admin_activities');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function update_activities(&$process_activities, $activities_inter, $activities_route)
		{
			//echo "activities_data: <pre>";print_r($activities_data);echo "</pre>";
			//echo "activities_inter: <pre>";print_r($activities_inter);echo "</pre>";
			//echo "activities_route: <pre>";print_r($activities_route);echo "</pre>";
			$num_activities = count($process_activities['data']);
			for ($i=0; $i < $num_activities; $i++)
			{
				$act_id = $process_activities['data'][$i]['activityId'];
				if ($process_activities['data'][$i]['isInteractive'] == 'y' && !in_array($act_id, $activities_inter))
				{
					$process_activities['data'][$i]['isInteractive'] = 'n';
					$this->activity_manager->set_interactivity($this->pId, $act_id, 'n');
				}
				if ($process_activities['data'][$i]['isInteractive'] == 'n' && in_array($act_id, $activities_inter))
				{
					$process_activities['data'][$i]['isInteractive'] = 'y';
					$this->activity_manager->set_interactivity($this->pId, $act_id, 'y');
				}
				if ($process_activities['data'][$i]['isAutoRouted'] == 'y' && !in_array($act_id, $activities_route))
				{
					$process_activities['data'][$i]['isAutoRouted'] = 'n';
					$this->activity_manager->set_autorouting($this->pId, $act_id, 'n');
				}
				if ($process_activities['data'][$i]['isAutoRouted'] == 'n' && in_array($act_id, $activities_route))
				{
					$process_activities['data'][$i]['isAutoRouted'] = 'y';
					$this->activity_manager->set_autorouting($this->pId, $act_id, 'y');
				}
			}
		}

		function add_process_role($rolename)
		{
			$vars = array(
				'name'			=> $rolename,
				'description'	=> '',
			);
			$this->role_manager->replace_role($this->pId, 0, $vars);
		}

		function search_transitions_act(&$process_activities, $act_id)
		{
			for ($i=0; $i < $process_activities['cant']; $i++)
			{
				$id = $process_activities['data'][$i]['activityId'];
				$process_activities['data'][$i]['to'] = $this->activity_manager->transition_exists($this->pId, $act_id, $id)? 'y' : 'n';
				$process_activities['data'][$i]['from'] = $this->activity_manager->transition_exists($this->pId, $id, $act_id)? 'y' : 'n';
			}
			//echo "process_activities after adding transition: <pre>";print_r($process_activities);echo "</pre>";
		}

		function show_process_activities($process_activities_data)
		{
			$this->t->set_block('admin_activities', 'block_process_activities', 'process_activities');
			$this->t->set_var(array(
				'form_process_activities_action'=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
				'left_arrow'		=> $this->nextmatchs->left('index.php', $this->start, $this->total),
				'right_arrow'		=> $this->nextmatchs->right('index.php', $this->start, $this->total),
				'header_name'		=> $this->nextmatchs->show_sort_order($this->sort, 'name', $this->order, 'index.php', lang('Name'), array('pid'=>$this->pId)),
				'header_type'		=> $this->nextmatchs->show_sort_order($this->sort, 'type', $this->order, 'index.php', lang('type'), array('pid'=>$this->pId)),
				'header_interactive'	=> $this->nextmatchs->show_sort_order($this->sort, 'isInteractive', $this->order, 'index.php', lang('Interactive'),  array('pid'=>$this->pId)),
				'header_route'		=> $this->nextmatchs->show_sort_order($this->sort, 'isAutoRouted', $this->order, 'index.php', lang('Auto routed'),  array('pid'=>$this->pId)),
			));
			$this->translate_template('block_process_activities');

			foreach ($process_activities_data as $activity)
			{
				$this->t->set_var(array(
					'act_activityId'	=> $activity['activityId'],
					'act_flowNum'		=> $activity['flowNum'],
					'act_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&pid='. $this->pId .'&find='. $find .'&where='. $where .'&sort_mode='. $this->sort_mode .'&activityId='. $activity['activityId']),
					'act_name'			=> $activity['name'],
					'no_roles'			=> ($activity['roles'] < 1)? '<small>('.lang('no roles').')</small>' : '',
					'act_icon'			=> $this->act_icon($activity['type']),
					'act_inter_checked'	=> ($activity['isInteractive'] == 'y')? 'checked="checked"' : '',
					'act_route_checked'	=> ($activity['isAutoRouted'] == 'y')? 'checked="checked"' : '',
					'act_href_code'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&pid='. $this->pId .'&activityId='. $activity['activityId']),
					'act_template'		=> ($activity['isInteractive'] == 'y')? '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&pid='. $this->pId .'&activityId='. $activity['activityId'] .'&template=1') .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'template') .'" alt="' .lang('template') .'" title="' . lang('template') .'" /></a>' : '',
					'img_code'			=> $GLOBALS['phpgw']->common->image('workflow', 'code'),
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),

				));
				$this->t->parse('process_activities', 'block_process_activities', True);
			}
		}

		function show_transitions_table($process_transitions)
		{
			//echo "process transitions: <pre>";print_r($process_transitions);echo "</pre>";
			$this->t->set_block('admin_activities', 'block_transitions_table', 'transitions_table');
			$this->translate_template('block_transitions_table');

			foreach ($process_transitions as $transition)
			{
				$this->t->set_var(array(
					'trans_actFromId'	=> $transition['actFromId'],
					'trans_actToId'		=> $transition['actToId'],
					'trans_href_from'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&pid='. $this->pId .'&find='. $find .'&where='. $where .'&sort_mode'. $this->sort_mode .'&activity_id='. $transition['actFromId']),
					'trans_actFromName'	=> $transition['actFromName'],
					'trans_arrow'		=> $GLOBALS['phpgw']->common->image('workflow', 'next'),
					'trans_href_to'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&pid='. $this->pId .'&find='. $find .'&where='. $where .'&sort_mode'. $this->sort_mode .'&activity_id='. $transition['actToId']),
					'trans_actToName'	=> $transition['actToName'],
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('transitions_table', 'block_transitions_table', true);
			}
			if (!count($process_transitions)) $this->t->set_var('transitions_table', '<tr><td colspan="2" align="center">'. lang('There are no transitions defined')  .'</td></tr>');
		}

		function build_select_transition($var_name, $process_activities_data, $multiple=false, $show_all=true, $from_or_to=false)
		{
			//echo "process_activities_data: <pre>";print_r($process_activities_data);echo "</pre>";
			$select_str = "<select name='$var_name'" . (($multiple)? " multiple='multiple' size='5'" : "" ) . ">";
			if ($show_all) $select_str .= '<option value="">'. lang('All') .'</option>';
			foreach ($process_activities_data as $activity)
			{
				if ($from_or_to && isset($activity[$from_or_to]) && $activity[$from_or_to] == 'y')
				{
					$selected = 'selected="selected"';
				}
				else
				{
					$selected = '';
				}
				$select_str .= '<option value="'. $activity['activityId'] .'" '. $selected .'>'. $activity['name'] .'</option>';
			}
			$select_str .= "</select>\n";
			return $select_str;
		}

		function save_activity($activityId, $name, $description, $type, $isInteractive, $isAutoRouted, $userole)
		{
			$isInteractive = ($isInteractive == 'on') ? 'y' : 'n';
			$isAutoRouted = ($isAutoRouted == 'on') ? 'y' : 'n';
			$vars = array(
				'name' => $name,
				'description' => $description,
				'activityId' => $activityId,
				'isInteractive' => $isInteractive,
				'isAutoRouted' => $isAutoRouted,
				'type' => $type,
			);
			if ($this->activity_manager->activity_name_exists($this->pId, $name) && $activityId == 0)
			{
				die(lang('Activity name already exists'));
			}

			$activityId = $this->activity_manager->replace_activity($this->pId, $activityId, $vars);

			// assign role to activity
			if ($userole) 
			{
				$this->activity_manager->add_activity_role($activityId, $userole);
			}

			// add activity transitions
			if (isset($_POST['add_tran_from']))
			{
				foreach ($_POST['add_tran_from'] as $act_from)
				{
					$this->activity_manager->add_transition($this->pId, $act_from, $activityId);
				}
			}
			if (isset($_POST['add_tran_to']))
			{
				foreach ($_POST['add_tran_to'] as $act_to)
				{
					$this->activity_manager->add_transition($this->pId, $activityId, $act_to);
				}
			}

			return $activityId;
		}

		function delete_activities($activities_ids)
		{
			foreach ($activities_ids as $act_id)
			{
				$this->activity_manager->remove_activity($this->pId, $act_id);
			}
			return true;
		}

		function delete_transitions($transitions)
		{
			foreach (array_keys($transitions) as $transition)
			{
				$parts = explode('_', $transition);
				$this->activity_manager->remove_transition($parts[0], $parts[1]);
			}
			$this->message[] = lang('Transitions removed successfully');
		}

		function add_transition($from, $to)
		{
			if ($this->activity_manager->add_transition($this->pId, $from, $to))
			{
				return lang('New transition added');
			}
			return lang("Couldn't add transition");
		}

		function show_graph()
		{
			$proc_info = $this->process_manager->get_process($this->pId);
			$image_name = $proc_info['normalized_name'] . SEP . 'graph' . SEP . $proc_info['normalized_name'] . '.png';
			$image = GALAXIA_PROCESSES . SEP . $image_name;
			//die($image);
			$dims = getimagesize($image);
			header('content-disposition: inline; filename=' . $image_name);
			header('content-type: ' . $dims['mime']);
			header('content-length: ' . filesize($image));
			readfile($image);
		}

	}
?>
