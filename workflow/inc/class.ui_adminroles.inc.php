<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_adminroles extends workflow
	{
		var $public_functions = array(
			'form'	=> true
		);

		var $process_manager;

		var $activity_manager;

		function ui_adminroles()
		{
			parent::workflow();
			$this->process_manager	= CreateObject('phpgwapi.workflow_processmanager');
			$this->activity_manager	= CreateObject('phpgwapi.workflow_activitymanager');
			$this->role_manager		= CreateObject('phpgwapi.workflow_rolemanager');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Process Roles');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('admin_roles', 'admin_roles.tpl');

			$this->order		= get_var('order', 'GET', 'name');
			$this->sort			= get_var('sort', 'GET', 'asc');
			$this->sort_mode	= $this->order . '_'. $this->sort;
			$sort_mode2			= get_var('sort_mode2', 'any', 'name_asc');
			$roleId				= (int)get_var('roleId', 'any', 0);

			if (!$this->pId) die(lang('No process indicated'));

			// save new role
			if (isset($_POST['save'])) $this->save_role($roleId, $_POST['name'], $_POST['description']);

			// save new mapping
			if (isset($_POST['save_map']))
			{
				$this->save_mapping($_POST['user'], $_POST['role']);
				$this->message[] = lang('New mapping added');
			}

			// delete roles
			if (isset($_POST['delete_roles'])) $this->delete_roles(array_keys($_POST['role']));
			
			// delete mappings
			if (isset($_POST['delete_map'])) 
			{
			  $this->delete_maps(array_keys($_POST['map']));
			}

			// retrieve process info
			$proc_info = $this->process_manager->get_process($this->pId);

			// check process validity and show errors if necessary
			$proc_info['isValid'] = $this->show_errors($this->activity_manager, $error_str);

			// fill proc_bar
			$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));
			
			// retrieve role info
			if ($roleId || isset($_POST['new_role']))
			{
				$role_info = $this->role_manager->get_role($this->pId, $_GET['roleId']);
			}
			else
			{
				$role_info = array(
					'name'			=> '',
					'description'	=> '',
					'roleId'		=> 0
				);
			}

			// retrieve all roles info
			$all_roles = $this->role_manager->list_roles($this->pId, 0, -1, 'name_asc', '');

			// fill the general varibles of the template
			$this->t->set_var(array(
				'message'				=> implode('<br>', $this->message),
				'errors'				=> $error_str,
				'form_action_adminroles'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form'),
				'role_info_roleId'		=> $role_info['roleId'],
				'role_info_name'		=> $role_info['name'],
				'role_info_description'	=> $role_info['description'],
				'pid'					=> $this->pId,
				'start'					=> $this->start,
			));

			$this->show_process_roles_list($all_roles['data']);

			// build users and roles multiple select boxes
			$this->show_users_roles_selects($all_roles['data']);

			// retrieve and show mappings
			$this->show_mappings();

			$this->translate_template('admin_roles');
			$this->t->pparse('output', 'admin_roles');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function save_role($roleId, $name, $description)
		{
			$vars = array(
				'name'			=> $name,
				'description'	=> $description,
			);
			$this->role_manager->replace_role($this->pId, $roleId, $vars);
			$this->message[] = lang('Role saved');
		}

		function delete_roles($roles_ids)
		{
			foreach ($roles_ids as $role_id)
			{
				$this->role_manager->remove_role($this->pId, $role_id);
			}
			$this->message[] = lang('Roles deleted');
		}
		
		function delete_maps($mappings)
		{
		        foreach($mappings as $map)
		        {
		               $pos = strpos($map,":::");
		               $user=substr($map,0,$pos);
		               $roleId=substr($map,$pos+3);
		               $this->role_manager->remove_mapping($user,$roleId);
		        }
			$this->message[] = lang('Mappings deleted');
		}
		
		function show_mappings()
		{
			$this->t->set_block('admin_roles', 'block_list_mappings', 'list_mappings');
			$mappings = $this->role_manager->list_mappings($this->pId, $this->start, -1, $this->sort_mode, '');
			//echo "mappings: <pre>";print_r($mappings);echo "</pre>";
			foreach ($mappings['data'] as $mapping)
			{
				$GLOBALS['phpgw']->accounts->get_account_name($mapping['user'], $lid, $fname, $lname);
				$this->t->set_var(array(
					'map_user_id'	=> $mapping['user'],
					'map_role_id'	=> $mapping['roleId'],
					'map_role_name'	=> $mapping['name'],
					'map_user_name'	=> $fname . ' ' . $lname,
				));
				$this->t->parse('list_mappings', 'block_list_mappings', true);
			}
			if (!count($mappings['data'])) $this->t->set_var('list_mappings', '<tr><td colspan="3" align="center">'. lang('There are no mappings defined for this process')  .'</td></tr>');
		}

		function save_mapping($users, $roles)
		{
			foreach ($users as $user)
			{
				foreach ($roles as $role)
				{
					$this->role_manager->map_user_to_role($this->pId, $user, $role);
				}
			}
		}

		function show_users_roles_selects($all_roles_data)
		{
			$this->t->set_block('admin_roles', 'block_select_users', 'select_users');
			$users = $GLOBALS['phpgw']->accounts->get_list('accounts');
			//echo "users: <pre>"; print_r($users); echo "</pre>";
			$groups = $GLOBALS['phpgw']->accounts->get_list('groups');
			//echo "groups: <pre>"; print_r($groups); echo "</pre>";
			foreach ($users as $user)
			{
				$this->t->set_var(array(
					'account_id'	=> $user['account_id'],
					'account_name'	=> $user['account_firstname'] . ' ' . $user['account_lastname'],
				));
				$this->t->parse('select_users', 'block_select_users', true);
			}
			foreach ($groups as $group)
			{
				$this->t->set_var(array(
					'account_id'	=> $group['account_id'],
					'account_name'	=> $group['account_firstname'] . ' ' . lang('Group'),
				));
				$this->t->parse('select_users', 'block_select_users', true);
			}

			$this->t->set_block('admin_roles', 'block_select_roles', 'select_roles');
			foreach ($all_roles_data as $role)
			{
				$this->t->set_var(array(
					'select_roleId'		=> $role['roleId'],
					'select_roleName'	=> $role['name']
				));
				$this->t->parse('select_roles', 'block_select_roles', true);
			}
		}

		function show_process_roles_list($all_roles_data)
		{
			$this->t->set_block('admin_roles', 'block_process_roles_list', 'process_roles_list');
			$this->translate_template('block_process_roles_list');

			foreach ($all_roles_data as $role)
			{
				$this->t->set_var(array(
					'all_roles_roleId'		=> $role['roleId'],
					'all_roles_href'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form&sort_mode='. $this->sort_mode .'&start='. $this->start .'&find='. $find .'&pid='. $this->pId .'&sort_mode2='. $sort_mode2 .'&roleId='. $role['roleId']),
					'all_roles_name'		=> $role['name'],
					'all_roles_description'	=> $role['description'],
					'color_line'			=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('process_roles_list', 'block_process_roles_list', true);
			}
			if (!count($all_roles_data)) $this->t->set_var('process_roles_list', '<tr><td colspan="3">'. lang('There are no roles defined for this process') .'</td></tr>');

		}
	}
?>
