<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_adminprocesses extends workflow
	{

		var $public_functions = array(
			'form'	=> true,
		);

		var $process_manager;

		var $activity_manager;

		var $filter_active;

		function ui_adminprocesses()
		{
			parent::workflow();
			$this->process_manager	= CreateObject('phpgwapi.workflow_processmanager');
			$this->activity_manager	= CreateObject('phpgwapi.workflow_activitymanager');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Processes');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('admin_processes', 'admin_processes.tpl');
			$this->t->set_block('admin_processes', 'block_items', 'items');

			$name					= get_var('name', 'POST', '');
			$description			= get_var('description', 'POST', '');
			$version				= get_var('version', 'POST', '');
			$isActive				= get_var('version', 'POST', '');
			$filter					= get_var('filter', 'any', '');
			$this->filter_active	= get_var('filter_active', 'any', '');
			$where					= get_var('where', 'any', '');
			$newminor				= get_var('newminor', 'GET', 0);
			$newmajor				= get_var('newmajor', 'GET', 0);
			$this->order			= get_var('order', 'GET', 'lastModif');
			$this->sort				= get_var('sort', 'GET', 'desc');
			$this->sort_mode		= $this->order . '_'. $this->sort;

			// filtering options
			$where_str = '';
			$wheres = array();

			if ($filter_active)	$wheres[] = " isActive='". $filter_active ."'";
			$where_str = implode('and', $wheres);

			if ($wheres) $where_str = $where;

			// delete processes
			if (isset($_POST['delete']))
			{
				$this->delete_processes(array_keys($_POST['process']));
				$this->message[] = lang('Deletion successful');
			}


			// save new process
			if (isset($_POST['save']))
			{
				$this->pId = $this->save_process($name, $version, $description, $isActive);
			}

			// new minor
			if ($newminor)
			{
				$this->process_manager->new_process_version($newminor);
			}

			// new mayor
			if ($newmajor)
			{
				$this->process_manager->new_process_version($newmajor, false);
			}

			// retrieve current process
			if ($this->pId)
			{
				$proc_info = $this->process_manager->get_process($this->pId);
				$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));
			}
			else
			{
				$proc_info = array(
					'name'			=> '',
					'description'	=> '',
					'version'		=> '1.0',
					'isActive'		=> 'n',
					'pId'			=> 0
				);
				$this->t->set_var('proc_bar', '');
			}

			// show list of processes
			$items = $this->process_manager->list_processes($this->start, -1, $this->sort_mode, $find, $where_str);
			//echo "list of processes: <pre>";print_r($items);echo "</pre>";
			$this->show_list_processes($items['data']);

			if ($this->pId)
			{
				// check process validity and show errors if necessary
				$proc_info['isValid'] = $this->show_errors($this->activity_manager, $error_str);
			}

			// show current process
			$this->t->set_var(array(
				'message'			=> implode('<br>', $this->message),
				'errors'			=> $error_str,
				'link_new'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&where='. $where .'&start='. $this->start .'&sort_mode='. $this->sort_mode .'&pid=0'),
				'pid'				=> $proc_info['pId'],
				'name'				=> $proc_info['name'],
				'version'			=> $proc_info['version'],
				'description'		=> $proc_info['description'],
				'isActive'			=> ($proc_info['isActive'] == 'y')? 'checked="checked"' : '',
				'where'				=> $where,
				'find'				=> $find,
				'sort_mode'			=> $this->sort_mode,
				'btn_update_create'=> ($this->pId)? lang('update') : lang('create'),
				'list_processes'	=> lang('List of processes (%1)', $items['cant']),
				'form_details_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form'),
				'form_upload_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form'),
				'form_filters_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form'),
				'form_last_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form'),
			));

			$this->translate_template('admin_processes');
			$this->t->pparse('output', 'admin_processes');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function delete_processes($process_ids)
		{
			foreach ($process_ids as $process_id)
			{
				$this->process_manager->remove_process($process_id);
			}
		}

		function show_list_processes($items)
		{
			$filters = array(
				'filter_active'	=> $this->filter_active,
			);
			$this->t->set_var(array(
				'left_arrow'		=> $this->nextmatchs->left('index.php', $this->start, $this->total),
				'right_arrow'		=> $this->nextmatchs->right('index.php', $this->start, $this->total),
				'header_name'		=> $this->nextmatchs->show_sort_order($this->sort, 'name', $this->order, 'index.php', lang('Name'), $filters),
				'header_version'	=> $this->nextmatchs->show_sort_order($this->sort, 'version', $this->order, 'index.php', lang('Version'), $filters),
				'header_active'		=> $this->nextmatchs->show_sort_order($this->sort, 'isActive', $this->order, 'index.php', lang('Active'), $filters),
				'header_valid'		=> $this->nextmatchs->show_sort_order($this->sort, 'isValid', $this->order, 'index.php', lang('Valid'), $filters),
			));
			foreach ($items as $item)
			{
				if ($item['isValid'] == 'y')
				{
					$dot = 'green';
					$alt = lang('Valid Process');
				}
				else
				{
					$dot = 'red';
					$alt = lang('Invalid Process');
				}
				$this->t->set_var(array(
					'item_pId'			=> $item['pId'],
					'href_item_name'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&find='. $find .'&where='. $where .'&start='. $this->start.'&sort_mode='. $this->sort_mode .'&pid='. $item['pId']),
					'item_name'			=> $item['name'],
					'item_version'		=> $item['version'],
					'img_active'		=> ($item['isActive'] == 'y')? '<img src="'. $GLOBALS['phpgw']->common->image('workflow', 'refresh2') .'" alt="'. lang('active') .'" title="'. lang('active') .'" />' : '',
					'img_valid'			=> '<img src="'. $GLOBALS['phpgw']->common->image('workflow', $dot.'_dot') .'" alt="'. $alt .'" title="'. $alt .'" />',
					'href_item_minor'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&find='. $find .'&where='. $where .'&start='. $this->start .'&sort_mode='. $this->sort_mode .'&newminor='. $item['pId']),
					'img_new'		=> $GLOBALS['phpgw']->common->image('workflow', 'new'),
					'href_item_mayor'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&find='. $find .'&where='. $where .'&start='. $this->start .'&sort_mode='. $this->sort_mode .'&newmajor='. $item['pId']),
					'href_item_activities'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&pid='. $item['pId']),
					'img_activities'	=> $GLOBALS['phpgw']->common->image('workflow', 'Activity'),
					'href_item_code'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&pid='. $item['pId']),
					'img_code'			=> $GLOBALS['phpgw']->common->image('workflow', 'code'),
					'href_item_save'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.save_process&pid='. $item['pId']),
					'img_save'			=> $GLOBALS['phpgw']->common->image('workflow', 'save'),
					'href_item_roles'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form&pid='. $item['pId']),
					'img_roles'			=> $GLOBALS['phpgw']->common->image('workflow', 'roles'),
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('items', 'block_items', True);
			}
			if (!count($items)) $this->t->set_var('items', '<tr><td colspan="5" align="center">'. lang('There are no processes defined')  .'</td></tr>');
			$this->translate_template('block_items');
		}

		function save_process($name, $version, $description, $isActive)
		{
			if ($this->process_manager->process_name_exists($name, $version) && $this->pId==0)
			{
				$this->message[] = lang('Process name already exists');
				return 0;
			}
			else
			{
				$proc_info = array(
					'name'			=> $name,
					'description'	=> $description,
					'version'		=> $version,
					'isActive'		=> ($isActive == 'on')? 'y' : 'n'
				);
				$this->pId = $this->process_manager->replace_process($this->pId, $proc_info);
				$valid = $this->activity_manager->validate_process_activities($this->pId);
				if (!$valid)
				{
					$this->process_manager->deactivate_process($this->pId);
				}
				return $this->pId;
			}
		}
	}
?>
