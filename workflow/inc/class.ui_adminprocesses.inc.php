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
			$is_active				= get_var('isActive', 'POST', '');
			$filter					= get_var('filter', 'any', '');
			$this->filter_active	= get_var('filter_active', 'any', '');
			$where					= get_var('where', 'any', '');
			$newminor				= get_var('newminor', 'GET', 0);
			$newmajor				= get_var('newmajor', 'GET', 0);
			$this->order			= get_var('order', 'GET', 'wf_last_modif');
			$this->sort				= get_var('sort', 'GET', 'desc');
			$this->sort_mode		= $this->order . '__'. $this->sort;

			// filtering options
			$where_str = '';
			$wheres = array();

			if ($filter_active)	$wheres[] = " is_active='". $filter_active ."'";
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
				$this->wf_p_id  = get_var('wf_p_id','POST');
				$this->wf_p_id = $this->save_process($name, $version, $description, $is_active);
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
			if ($this->wf_p_id)
			{
				$proc_info = $this->process_manager->get_process($this->wf_p_id);
				$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));
			}
			else
			{
				$proc_info = array(
					'wf_name'			=> '',
					'wf_description'	=> '',
					'wf_version'		=> '1.0',
					'wf_is_active'		=> 'n',
					'wf_p_id'		=> 0
				);
				$this->t->set_var('proc_bar', '');
			}

			// show list of processes
			$items = $this->process_manager->list_processes($this->start, -1, $this->sort_mode, $find, $where_str);
			//echo "list of processes: <pre>";print_r($items);echo "</pre>";
			$this->show_list_processes($items['data']);

			if ($this->wf_p_id)
			{
				// check process validity and show errors if necessary
				$proc_info['wf_is_valid'] = $this->show_errors($this->activity_manager, $error_str);
			}

			// show current process
			$this->t->set_var(array(
				'message'			=> implode('<br>', $this->message),
				'errors'			=> $error_str,
				'link_new'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&where='. $where .'&start='. $this->start .'&sort_mode='. $this->sort_mode .'&p_id=0'),
				'wf_p_id'				=> $proc_info['wf_p_id'],
				'name'				=> $proc_info['wf_name'],
				'version'			=> $proc_info['wf_version'],
				'description'		=> $proc_info['wf_description'],
				'is_active'			=> ($proc_info['wf_is_active'] == 'y')? 'checked="checked"' : '',
				'where'				=> $where,
				'find'				=> $find,
				'sort_mode'			=> $this->sort_mode,
				'btn_update_create'=> ($this->wf_p_id)? lang('update') : lang('create'),
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
				'header_name'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_name', $this->order, 'index.php', lang('Name'), $filters),
				'header_version'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_version', $this->order, 'index.php', lang('Version'), $filters),
				'header_active'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_is_active', $this->order, 'index.php', lang('Active'), $filters),
				'header_valid'		=> $this->nextmatchs->show_sort_order($this->sort, 'is_valid', $this->order, 'index.php', lang('Valid'), $filters),
			));
			foreach ($items as $item)
			{
				if ($item['wf_is_valid'] == 'y')
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
					'item_wf_p_id'			=> $item['wf_p_id'],
					'href_item_name'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&find='. $find .'&where='. $where .'&start='. $this->start.'&sort_mode='. $this->sort_mode .'&p_id='. $item['wf_p_id']),
					'item_name'			=> $item['wf_name'],
					'item_version'		=> $item['wf_version'],
					'img_active'		=> ($item['wf_is_active'] == 'y')? '<img src="'. $GLOBALS['phpgw']->common->image('workflow', 'refresh2') .'" alt="'. lang('active') .'" title="'. lang('active') .'" />' : '',
					'img_valid'			=> '<img src="'. $GLOBALS['phpgw']->common->image('workflow', $dot.'_dot') .'" alt="'. $alt .'" title="'. $alt .'" />',
					'href_item_minor'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&find='. $find .'&where='. $where .'&start='. $this->start .'&sort_mode='. $this->sort_mode .'&newminor='. $item['wf_p_id']),
					'img_new'		=> $GLOBALS['phpgw']->common->image('workflow', 'new'),
					'href_item_mayor'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&find='. $find .'&where='. $where .'&start='. $this->start .'&sort_mode='. $this->sort_mode .'&newmajor='. $item['wf_p_id']),
					'href_item_activities'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&p_id='. $item['wf_p_id']),
					'img_activities'	=> $GLOBALS['phpgw']->common->image('workflow', 'Activity'),
					'href_item_code'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&p_id='. $item['wf_p_id']),
					'img_code'			=> $GLOBALS['phpgw']->common->image('workflow', 'code'),
					'href_item_save'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.save_process&p_id='. $item['wf_p_id']),
					'img_save'			=> $GLOBALS['phpgw']->common->image('workflow', 'save'),
					'href_item_roles'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form&p_id='. $item['wf_p_id']),
					'img_roles'			=> $GLOBALS['phpgw']->common->image('workflow', 'roles'),
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('items', 'block_items', True);
			}
			if (!count($items)) $this->t->set_var('items', '<tr><td colspan="5" align="center">'. lang('There are no processes defined')  .'</td></tr>');
			$this->translate_template('block_items');
		}

		function save_process($name, $version, $description, $is_active)
		{
			if ($this->process_manager->process_name_exists($name, $version) && $this->wf_p_id==0)
			{
				$this->message[] = lang('Process name already exists');
				return 0;
			}
			else
			{
				$proc_info = array(
					'wf_name'			=> $name,
					'wf_description'	=> $description,
					'wf_version'		=> $version,
					'wf_is_active'		=> ($is_active == 'on')? 'y' : 'n'
				);
				$this->wf_p_id = $this->process_manager->replace_process($this->wf_p_id, $proc_info);
				$valid = $this->activity_manager->validate_process_activities($this->wf_p_id);
				if (!$valid)
				{
					$this->process_manager->deactivate_process($this->wf_p_id);
				}
				return $this->wf_p_id;
			}
		}
	}
?>
