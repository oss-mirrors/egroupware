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
		
		var $where2;
		var $sort_mode2;
		

		function ui_adminactivities()
		{
			parent::workflow();
			
		       //regis: acl check
			if ( !(($GLOBALS['phpgw']->acl->check('run',1,'admin')) || ($GLOBALS['phpgw']->acl->check('admin_workflow',1,'workflow'))) )
			{
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
				echo lang('access not permitted');
				$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_adminactivities');
				$GLOBALS['phpgw']->log->commit();
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$this->process_manager	= CreateObject('workflow.workflow_processmanager');
			$this->activity_manager	= CreateObject('workflow.workflow_activitymanager');
			$this->role_manager	= CreateObject('workflow.workflow_rolemanager');

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
			$this->t->set_block('admin_activities', 'block_select_default_user', 'select_default_user');
			
			$activity_id		= (int)get_var('activity_id', 'any', 0);
			$name				= get_var('name', 'any', '');
						
			// TODO: not all variables below are still required.  clean up
			
			$description		= get_var('description', 'any', '');
			$type				= get_var('type', 'any', '');
			$is_interactive		= get_var('is_interactive', 'any', '');
			$is_autorouted		= get_var('is_autorouted', 'any', '');
			$default_user       = get_var('default_user', 'any', '');
			$userole			= get_var('userole', 'POST', '');
			$where				= get_var('where', array('GET', 'POST'), '');
			$this->where2		= get_var('where2', 'any', '');
			$find				= get_var('find', 'any', '');
			$find2				= get_var('find2', 'any', '');
			$this->sort_mode2	= get_var('sort_mode2', 'any', '');
			$filter_trans_from	= get_var('filter_trans_from', 'any', '');
			$this->order		= get_var('order', 'GET', 'wf_flow_num');
			$this->sort			= get_var('sort', 'GET', 'asc');
			$this->sort_mode	= $this->order . '__'. $this->sort;

			if (!$this->wf_p_id) die(lang('No process indicated'));

			// *************************************   START OF OPERATIONS COMMANDED BY THIS SAME FORM ******************

			// TODO: rolenames need to be valid.  Add a validity checking function
			// add role to process roles
			if (isset($_POST['addrole']))
			{
				if( isset($_POST['rolename']) ) 
				{
					$rolename = trim($_POST['rolename']);
					if( strlen($rolename) > 0 ) {
						$this->add_process_role($_POST['rolename']);
						$this->message[] = lang('Role added to process');
					}
					else 
					{
						$this->message[] = lang('Invalid role name');						
					}
				}
			}

			// remove activity role
			if (isset($_GET['remove_role']) && $activity_id)
			{
				$this->activity_manager->remove_activity_role($activity_id, $_GET['remove_role']);
				$this->message[] = lang('Activity role removed');
			}

			// TODO: activityname need to be valid.  Add a validity checking function?
			// save activity
			if (isset($_POST['save_act']))
			{
				$activity_id = $this->save_activity($activity_id, $name, $description, $type,  $default_user, $is_interactive, $is_autorouted, $userole, $rolename);
				if( $activity_id ) 
				{
					$this->message[] = lang('Activity saved');
				}
			}

			// delete activity
			if (isset($_POST['delete_act']))
			{
				if( isset($_POST['activities']) ) 
				{
					if ($this->delete_activities(array_keys($_POST['activities']))) $this->message[] = lang('Deletion successful');
				}
			}

			// add transitions
			if (isset($_POST['add_trans'])) $this->message[] = $this->add_transition($_POST['wf_act_from_id'], $_POST['wf_act_to_id']);

			// delete transitions
			if (isset($_POST['delete_tran'])) $this->delete_transitions($_POST['transition']);

			// *************************************   END OF OPERATIONS COMMANDED BY THIS SAME FORM ******************

			// retrieve activity info and its roles
			if (!$activity_id || isset($_POST['new_activity']))
			{
				$activity_info = array(
					'wf_name'		=> '',
					'wf_description'	=> '',
					'wf_activity_id'	=> 0,
					'wf_is_interactive'	=> 'y',
					'wf_is_autorouted'	=> 'n',
					'wf_default_user'       => '*',
					'wf_type'		=> 'activity'
				);
				$activity_roles = array();
			}
			else
			{
				$activity_info =& $this->activity_manager->get_activity($activity_id);
				$activity_roles =& $this->activity_manager->get_activity_roles($activity_id);
			}

			// fill type filter select box
			$activity_types = array('start', 'end', 'activity', 'switch', 'split', 'join', 'standalone', 'view');
			$filter_type = get_var('filter_type', 'any', '');
			$this->show_select_filter_type($activity_types, $filter_type);
			
			$filter_interactive		= get_var('filter_interactive', 'any', '');
			$activity_interactive = array('y' => lang('Interactive'), 'n'=>lang('Automatic'));
			$this->show_select_filter_interactive($activity_interactive, $filter_interactive);
			
			$filter_autoroute		= get_var('filter_autoroute', 'any', '');
			$activity_autoroute = array('y' => lang('Auto Routed'), 'n'=>lang('Manual'));
			$this->show_select_filter_autoroute($activity_autoroute, $filter_autoroute);
			
		    $where = '';
		    $wheres = array();
		    if( !($filter_type == '') ) 
		    {
		    	$wheres[] = "wf_type = '" .$filter_type. "'";
		    }
		    if( !($filter_interactive == '') ) 
		    {
		    	$wheres[] = "wf_is_interactive = '" .$filter_interactive. "'";
		    }
		    if( !($filter_autoroute == '') ) 
		    {
		    	$wheres[] = "wf_is_autorouted = '" .$filter_autoroute. "'";
		    }
		    if( count($wheres) > 0 ) 
		    {
				$where = implode(' and ', $wheres);
		    }
			
			$proc_info =& $this->process_manager->get_process($this->wf_p_id);
			$process_activities =& $this->activity_manager->list_activities($this->wf_p_id, 0, -1, $this->sort_mode, $find, $where);
			$all_transition_activities_from =& $this->activity_manager->get_transition_activities($this->wf_p_id, 'end');
			$all_transition_activities_to =& $this->activity_manager->get_transition_activities($this->wf_p_id, 'start');
			if ($activity_id) $this->search_transitions_act($process_activities, $activity_id);
			$process_roles = $this->role_manager->list_roles($this->wf_p_id, 0, -1, 'wf_name__asc', '');			
			$all_process_transitions = $this->activity_manager->get_process_transitions($this->wf_p_id);
			$process_transitions = $this->activity_manager->get_process_transitions($this->wf_p_id, $filter_trans_from);
			$process_activities_with_transitions =& $this->activity_manager->get_process_activities_with_transitions($this->wf_p_id);

			// update activities
			if (isset($_POST['update_act']))
			{
				if( is_array($process_activities['data']) && count($process_activities['data']) > 0 )
				{
					$this->update_activities($process_activities, array_keys($_POST['activity_inter']), array_keys($_POST['activity_route']));
					$this->message[] = lang('Activities updated');
				}
			}

			// activate process
			if (isset($_GET['activate_proc']))
			{
				$this->process_manager->activate_process($_GET['activate_proc']);
				$proc_info['wf_is_active'] = 'y';
			}

			// deactivate process
			if (isset($_GET['deactivate_proc']))
			{
				$this->process_manager->deactivate_process($_GET['deactivate_proc']);
				$proc_info['wf_is_active'] = 'n';
			}

			// check process validity and show errors if necessary
			$proc_info['wf_is_valid'] = $this->show_errors($this->activity_manager, $error_str);

			// fill proc_bar
			$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));

			// fill the general variables of the template
			$this->t->set_var(array(
				'message'				=> implode('<br>', $this->message),
				'errors'				=> $error_str,
				'form_details_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
				'form_list_transitions_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
				'p_id'					=> $this->wf_p_id,
				'where'					=> $where,
				'where2'				=> $this->where2,
				'sort_mode'				=> $this->sort_mode,
				'sort_mode2'			=> $this->sort_mode2,
				'find'				=> $find,
				'find2'				=> $find2,
				'activity_id'			=> $activity_info['wf_activity_id'],
				'new_act_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
				'name'					=> $activity_info['wf_name'],
				'description'			=> $activity_info['wf_description'],
				'checked_interactive'	=> ($activity_info['wf_is_interactive'] == 'y')? 'checked="checked"' : '',
				'checked_autorouted'	=> ($activity_info['wf_is_autorouted'] == 'y')? 'checked="checked"' : '',
				'img_transition_auto'           => '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'transition_interactive.gif') .'" alt="'. lang('transition mode') .'" />',
				'img_interactive'               => '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'mini_interactive.gif') .'" alt="'. lang('interactivity') .'" />',
				'img_transition'                => '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'transition.gif') .'" alt="'. lang('transitions') .'" />',
				'img_transition_add'            => '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'transition_add.gif') .'" alt="'. lang('add transition') .'" />',
				'img_transition_delete'         => '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'transition_remove.gif') .'" alt="'. lang('delete transition') .'" />',
				'add_trans_from'		=> $this->build_select_transition('add_tran_from[]', $all_transition_activities_from['data'], true, false, 'from'),
				'add_trans_to'			=> $this->build_select_transition('add_tran_to[]', $all_transition_activities_to['data'], true, false, 'to'),
				'add_a_trans_from'		=> $this->build_select_transition('wf_act_from_id', $all_transition_activities_from['data'], false, false),
				'add_a_trans_to'		=> $this->build_select_transition('wf_act_to_id', $all_transition_activities_to['data'], false, false)
			));

			if( $filter_trans_from ) {
				$this->t->set_var('filter_trans_from', $this->build_select_transition_filtered('filter_trans_from', $process_activities_with_transitions['data'], false, true, $filter_trans_from));
				$this->t->set_var('filter_trans_from_value', $filter_trans_from);
			}
			else {
				$this->t->set_var('filter_trans_from', $this->build_select_transition_filtered('filter_trans_from', $process_activities_with_transitions['data'], false, true, false));				
				$this->t->set_var('filter_trans_from_value', '');
			}

			// show process activities table
			$this->show_process_activities($process_activities['data']);

			
			foreach ($activity_types as $type)
			{
				$this->t->set_var(array(
					'type_value'	=> $type,
					'type_selected'	=> ($activity_info['wf_type'] == $type)? 'selected="selected"' : '',
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
						'act_role_name'	=> $role['wf_name'],
						'act_role_href'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&find='. $find .'&where='. $where .'&activity_id='. $activity_info['wf_activity_id'] .'&p_id='. $this->wf_p_id .'&remove_role='. $role['wf_role_id'])
					));
					$this->t->parse('activity_roles', 'block_activity_roles', True);
				}
			}

			// fill user list for the Default user menu
			$users = $GLOBALS['phpgw']->accounts->get_list('accounts');
				// if we have no default user
			$this->t->set_var(array(
				'default_user_selected_none'	=> ($activity_info['wf_default_user'] == '*')? 'selected="selected"' : '',
			));
				// now parse users
			foreach($users as $user)
			{
				if ($user['account_lid'] != '')
				{
					$username = $user['account_lastname'].' '. $user['account_firstname'].' ('.$user['account_lid'].')';
				}
				else
				{
					$username = $user['account_fullname'];
				}
				$this->t->set_var(array(
					'default_user_value'	=> $user['account_id'],
					'default_user_name'	=> $username,
					'default_user_selected'	=> ($activity_info['wf_default_user'] == $user['account_id'])? 'selected="selected"' : ''
				));
				$this->t->parse('select_default_user', 'block_select_default_user', True);
			}
			 	
			// fill process roles
			foreach ($process_roles['data'] as $role)
			{
				$this->t->set_var(array(
					'proc_roleId'	=> $role['wf_role_id'],
					'proc_roleName'	=> $role['wf_name']
				));
				$this->t->parse('process_roles', 'block_process_roles', True);
			}

			// fill list of transitions table
			$this->show_transitions_table($process_transitions);
			$this->t->set_var('filter_type_value', $filter_type);
			$this->t->set_var('filter_interactive_value', $filter_interactive);
			$this->t->set_var('filter_autoroute_value', $filter_autoroute);
			$this->t->set_var('find_value', $find);

			// create graph
			$this->activity_manager->build_process_graph($this->wf_p_id);

			$this->translate_template('admin_activities');
			$this->t->pparse('output', 'admin_activities');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}
		
		function show_select_filter_type($all_activity_types, $filter_type)
		{
			$this->t->set_block('admin_activities', 'block_select_filter_type', 'select_filter_type');
			$this->t->set_var('selected_filter_type_all', (!($filter_type))? 'selected="selected"' : '');

			foreach ($all_activity_types as $type)
			{
				$this->t->set_var(array(
					'selected_filter_type'	=> ($filter_type == $type)? 'selected="selected"' : '',
					'filter_type_name'		=> $type
				));
				$this->t->parse('select_filter_type', 'block_select_filter_type', true);
			}
		}
		function show_select_filter_interactive($all_activity_interactive, $filter_interactive)
		{
			$this->t->set_block('admin_activities', 'block_select_filter_interactive', 'select_filter_interactive');
			$this->t->set_var('selected_filter_interactive_all', (!($filter_interactive))? 'selected="selected"' : '');

			foreach ($all_activity_interactive as $value=>$name)
			{
				$this->t->set_var(array(
					'selected_filter_interactive'	=> ($filter_interactive == $value)? 'selected="selected"' : '',
					'filter_interactive_name'		=> $name,
					'filter_interactive_value'		=> $value
				));
				$this->t->parse('select_filter_interactive', 'block_select_filter_interactive', true);
			}
		}
		
		function show_select_filter_autoroute($all_activity_autoroute, $filter_autoroute)
		{
			$this->t->set_block('admin_activities', 'block_select_filter_autoroute', 'select_filter_autoroute');
			$this->t->set_var('selected_filter_autoroute_all', (!($filter_autoroute))? 'selected="selected"' : '');

			foreach ($all_activity_autoroute as $value=>$name)
			{
				$this->t->set_var(array(
					'selected_filter_autoroute'	=> ($filter_autoroute == $value)? 'selected="selected"' : '',
					'filter_autoroute_name'		=> $name,
					'filter_autoroute_value'		=> $value
				));
				$this->t->parse('select_filter_autoroute', 'block_select_filter_autoroute', true);
			}
		}
		
		function update_activities(&$process_activities, $activities_inter, $activities_route)
		{
			$num_activities = count($process_activities['data']);
			for ($i=0; $i < $num_activities; $i++)
			{
				$act_id = $process_activities['data'][$i]['wf_activity_id'];
				if ($process_activities['data'][$i]['wf_is_interactive'] == 'y' && !in_array($act_id, $activities_inter))
				{
					$process_activities['data'][$i]['wf_is_interactive'] = 'n';
					$this->activity_manager->set_interactivity($this->wf_p_id, $act_id, 'n');
				}
				if ($process_activities['data'][$i]['wf_is_interactive'] == 'n' && in_array($act_id, $activities_inter))
				{
					$process_activities['data'][$i]['wf_is_interactive'] = 'y';
					$this->activity_manager->set_interactivity($this->wf_p_id, $act_id, 'y');
				}
				if ($process_activities['data'][$i]['wf_is_autorouted'] == 'y' && !in_array($act_id, $activities_route))
				{
					$process_activities['data'][$i]['wf_is_autorouted'] = 'n';
					$this->activity_manager->set_autorouting($this->wf_p_id, $act_id, 'n');
				}
				if ($process_activities['data'][$i]['wf_is_autorouted'] == 'n' && in_array($act_id, $activities_route))
				{
					$process_activities['data'][$i]['wf_is_autorouted'] = 'y';
					$this->activity_manager->set_autorouting($this->wf_p_id, $act_id, 'y');
				}
			}
		}

		function add_process_role($rolename)
		{
			$vars = array(
				'wf_name'			=> $rolename,
				'wf_description'	=> '',
			);
			$this->role_manager->replace_role($this->wf_p_id, 0, $vars);
		}

		function search_transitions_act(&$process_activities, $act_id)
		{
			for ($i=0; $i < $process_activities['cant']; $i++)
			{
				$id = $process_activities['data'][$i]['wf_activity_id'];
				$process_activities['data'][$i]['to'] = $this->activity_manager->transition_exists($this->wf_p_id, $act_id, $id)? 'y' : 'n';
				$process_activities['data'][$i]['from'] = $this->activity_manager->transition_exists($this->wf_p_id, $id, $act_id)? 'y' : 'n';
			}
		}

		function show_process_activities($process_activities_data)
		{
			$this->t->set_block('admin_activities', 'block_process_activities', 'process_activities');
			$this->t->set_var(array(
				'form_process_activities_action'=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form'),
				'left_arrow'		=> $this->nextmatchs->left('index.php', $this->start, $this->total),
				'right_arrow'		=> $this->nextmatchs->right('index.php', $this->start, $this->total),
			));
			$this->translate_template('block_process_activities');

			$this->t->set_block('admin_activities', 'block_process_activities_header', 'process_activities_header');
			$this->t->set_block('admin_activities', 'block_process_activities_footer', 'process_activities_footer');
			if( is_array($process_activities_data) && count($process_activities_data) > 0 )
			{
				$this->t->set_var(array(
					'header_name'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_name', $this->order, 'index.php', lang('Name'), array('p_id'=>$this->wf_p_id)),
					'header_type'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_type', $this->order, 'index.php', lang('Type'), array('p_id'=>$this->wf_p_id)),
					'header_interactive'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_is_interactive', $this->order, 'index.php', lang('Interactive'),  array('p_id'=>$this->wf_p_id)),
					'header_route'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_is_autorouted', $this->order, 'index.php', lang('Auto routed'),  array('p_id'=>$this->wf_p_id)),
					'header_default_user'	=> lang('Default User')
				));
				$this->translate_template('block_process_activities_header');
				$this->t->parse('process_activities_header', 'block_process_activities_header', True);
				foreach ($process_activities_data as $activity)
				{
					if($activity['wf_default_user'] == '*' )
					{
						$act_default_user = lang('All');
					}
					else if($activity['wf_default_user'] != '*')
					{
						$act_default_user = $GLOBALS['phpgw']->accounts->id2name($activity['wf_default_user']);
					}
					
					$this->t->set_var(array(
						'act_activity_id'	=> $activity['wf_activity_id'],
						'act_flowNum'		=> $activity['wf_flow_num'],
						'act_href'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&p_id='. $this->wf_p_id .'&find='. $find .'&where='. $where .'&sort_mode='. $this->sort_mode .'&activity_id='. $activity['wf_activity_id']),
						'act_name'			=> $activity['wf_name'],
						'no_roles'			=> ($activity['wf_roles'] < 1)? '<small>('.lang('no roles').')</small>' : '',
						'act_icon'			=> $this->act_icon($activity['wf_type'],$activity['wf_is_interactive']),
						'act_inter_checked'	=> ($activity['wf_is_interactive'] == 'y')? 'checked="checked"' : '',
						'act_route_checked'	=> ($activity['wf_is_autorouted'] == 'y')? 'checked="checked"' : '',
						'act_default_user'      => $act_default_user,
						'act_href_edit'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&p_id='. $this->wf_p_id .'&activity_id='. $activity['wf_activity_id']),
						'act_template'		=> ($activity['wf_is_interactive'] == 'y')? '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&p_id='. $this->wf_p_id .'&activity_id='. $activity['wf_activity_id'] .'&template=1') .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'template') .'" alt="' .lang('template') .'" title="' . lang('template') .'" /></a>' : '',
						'img_code'		=> $GLOBALS['phpgw']->common->image('workflow', 'code'),
						'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
	
					));
					$this->t->parse('process_activities', 'block_process_activities', True);
				}
				$this->translate_template('block_process_activities_footer');
				$this->t->parse('process_activities_footer', 'block_process_activities_footer', True);
			}
			else 
			{
				$this->t->set_var('process_activities_header', '');
				$this->t->set_var('process_activities', '<tr><td colspan="7" align="center">'. lang('There are no processes with the current filter')  .'</td></tr>');
				$this->t->set_var('process_activities_footer', '');
			}
		}

		function show_transitions_table($process_transitions)
		{
			$this->t->set_block('admin_activities', 'block_transitions_table', 'transitions_table');
			$this->t->set_block('admin_activities', 'block_transitions_table_footer', 'transitions_table_footer');
			$this->translate_template('block_transitions_table');
			$this->translate_template('block_transitions_table_footer');

			foreach ($process_transitions as $transition)
			{
				$this->t->set_var(array(
					'trans_actFromId'	=> $transition['wf_act_from_id'],
					'trans_actToId'		=> $transition['wf_act_to_id'],
					'trans_href_from'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&p_id='. $this->wf_p_id .'&find='. $find .'&where='. $where .'&sort_mode='. $this->sort_mode .'&activity_id='. $transition['wf_act_from_id']),
					'trans_actFromName'	=> $transition['wf_act_from_name'],
					'trans_arrow'		=> $GLOBALS['phpgw']->common->image('workflow', 'next'),
					'trans_href_to'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&where2='. $where2 .'&sort_mode2='. $sort_mode2 .'&p_id='. $this->wf_p_id .'&find='. $find .'&where='. $where .'&sort_mode='. $this->sort_mode .'&activity_id='. $transition['wf_act_to_id']),
					'trans_actToName'	=> $transition['wf_act_to_name'],
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('transitions_table', 'block_transitions_table', true);
			}
			if (!count($process_transitions))
			{
				$this->t->set_var('transitions_table', '<tr><td colspan="2" align="center">'. lang('There are no transitions defined')  .'</td></tr>');
				$this->t->set_var('transitions_table_footer', '');
			} 
			else 
			{
				$this->t->parse('transitions_table_footer', 'block_transitions_table_footer', true);
			}
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
				$select_str .= '<option value="'. $activity['wf_activity_id'] .'" '. $selected .'>'. $activity['wf_name'] .'</option>';
			}
			$select_str .= "</select>\n";
			return $select_str;
		}
		function build_select_transition_filtered($var_name, $process_activities_data, $multiple=false, $show_all=true, $from=false)
		{
			$select_str = "<select name='$var_name'" . (($multiple)? " multiple='multiple' size='5'" : "" ) . ">";
			if ($show_all) $select_str .= '<option value="">'. lang('All') .'</option>';
			
			if( is_array($process_activities_data) && count($process_activities_data) > 0 ) 
			{
				foreach ($process_activities_data as $activity)
				{
					if ($from && $activity['wf_activity_id'] == $from )
					{
						$selected = 'selected="selected"';
					}
					else
					{
						$selected = '';
					}
					$select_str .= '<option value="'. $activity['wf_activity_id'] .'" '. $selected .'>'. $activity['wf_name'] .'</option>';
				}
			}
			$select_str .= "</select>\n";
			return $select_str;
		}

		//! save the edited activity. Return the activity_id or false in case of error, $this->message is set in case of error
		function save_activity($activity_id, $name, $description, $type, $default_user, $is_interactive, $is_autorouted, $userole)
		{
			$is_interactive = ($is_interactive == 'on') ? 'y' : 'n';
			$is_autorouted = ($is_autorouted == 'on') ? 'y' : 'n';
			$vars = array(
				'wf_name' => $name,
				'wf_description' => $description,
				'wf_activity_id' => $activity_id,
				'wf_is_interactive' => $is_interactive,
				'wf_is_autorouted' => $is_autorouted,
				'wf_default_user' => $default_user,
				'wf_type' => $type,
			);

			if( strlen($name) > 0 )
			{
				if ($this->activity_manager->activity_name_exists($this->wf_p_id, $name, $activity_id))
				{
					$this->message[] = ($name . ': '. lang('activity name already exists'));
					return false;
				}
			}
			else
			{
				$this->message[] = lang('Enter an activity name');
				return false;
			}

			$activity_id = $this->activity_manager->replace_activity($this->wf_p_id, $activity_id, $vars);

			// assign role to activity
			if ($userole) 
			{
				$this->activity_manager->add_activity_role($activity_id, $userole);
			}

			// add activity transitions
			if (isset($_POST['add_tran_from']))
			{
				foreach ($_POST['add_tran_from'] as $act_from)
				{
					$this->activity_manager->add_transition($this->wf_p_id, $act_from, $activity_id);
				}
			}
			if (isset($_POST['add_tran_to']))
			{
				foreach ($_POST['add_tran_to'] as $act_to)
				{
					$this->activity_manager->add_transition($this->wf_p_id, $activity_id, $act_to);
				}
			}
			$this->activity_manager->validate_process_activities($this->wf_p_id);
			
			//collect every error message after our actions on activity manager. If there is some we are in error
			//$this->message = $this->activity_manager->get_error(true);
			//if (!(count($this->message)==0))
			//{
			//	return false;
			//}
			return $activity_id;
		}

		function delete_activities($activities_ids)
		{
			foreach ($activities_ids as $act_id)
			{
				$this->activity_manager->remove_activity($this->wf_p_id, $act_id);
			}
			return true;
		}

		function delete_transitions($transitions)
		{
			if( is_array($transitions) && count($transitions) > 0 ) {
				foreach (array_keys($transitions) as $transition)
				{
					$parts = explode('_', $transition);
					$this->activity_manager->remove_transition($parts[0], $parts[1]);
				}
				$this->message[] = lang('Transitions removed successfully');
			}
			else 
			{
				$this->message[] = lang('Select a transition to remove');
			}
		}

		function add_transition($from, $to)
		{
			if ($this->activity_manager->add_transition($this->wf_p_id, $from, $to))
			{
				$this->activity_manager->validate_process_activities($this->wf_p_id);
				return lang('New transition added');
			}
			$error_msg =  $this->activity_manager->get_error();
			return lang("Couldn't add transition"). '; '. $error_msg[0];
		}

		function show_graph()
		{
			$proc_info = $this->process_manager->get_process($this->wf_p_id);
			$image_name = $proc_info['wf_normalized_name'] . SEP . 'graph' . SEP . $proc_info['wf_normalized_name'] . '.png';
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
