<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_userinstances extends workflow
	{
		var $public_functions = array(
			'form'	=> true,
		);

		var $GUI;

		function ui_userinstances()
		{
			parent::workflow();
			$this->GUI	= CreateObject('phpgwapi.workflow_gui');
		}

		function form()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('User Instances');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('user_instances', 'user_instances.tpl');

			$filter_status		= get_var('filter_status', 'any', '');
			$filter_process		= get_var('filter_process', 'any', '');
			$filter_user		= get_var('filter_user', 'any', '');
			$filter_act_status	= get_var('filter_act_status', 'any', '');
			$this->sort			= get_var('sort', 'any', 'asc');
			$this->order		= get_var('order', 'any', 'procname');
			$this->sort_mode	= $this->order . '_' . $this->sort;
			$this->search_str	= get_var('search_str', 'any', '');

			// retrieve all user processes info
			$all_processes = $this->GUI->gui_list_user_processes($GLOBALS['phpgw_info']['user']['account_id'], 0, -1, 'procname_asc', '', '');

			// retrieve user instances
			$instances = $this->GUI->gui_list_user_instances($GLOBALS['phpgw_info']['user']['account_id'], $this->start, -1, $this->sort_mode, $this->search_str, '');

			$this->show_select_status($filter_status);
			$this->show_select_process($all_processes['data'], $filter_process);
			$this->show_select_user($filter_user);
			$this->show_select_act_status($filter_act_status);

			$this->show_list_instances($instances['data']);


			// fill the general varibles of the template
			$this->t->set_var(array(
				'message'		=> implode('<br>', $this->message),
				'form_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form'),
				'search_str'	=> $this->search_str,
			));

			$this->translate_template('user_instances');
			$this->t->pparse('output', 'user_instances');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		function show_list_instances($instances_data)
		{
			// show table headers
			$this->t->set_var(array(
				'header_id'				=> $this->nextmatchs->show_sort_order($this->sort, 'instanceId', $this->order, 'index.php', lang('id')),
				'header_owner'			=> $this->nextmatchs->show_sort_order($this->sort, 'owner', $this->order, 'index.php', lang('Owner')),
				'header_inst_status'	=> $this->nextmatchs->show_sort_order($this->sort, 'status', $this->order, 'index.php', lang('Inst. Status')),
				'header_process'		=> $this->nextmatchs->show_sort_order($this->sort, 'procname', $this->order, 'index.php', lang('Process')),
				'header_activity'		=> $this->nextmatchs->show_sort_order($this->sort, 'name', $this->order, 'index.php', lang('Activity')),
				'header_user'			=> $this->nextmatchs->show_sort_order($this->sort, 'user', $this->order, 'index.php', lang('User')),
				'header_act_status'		=> $this->nextmatchs->show_sort_order($this->sort, 'actstatus', $this->order, 'index.php', lang('Act. Status')),
			));
			$this->t->set_block('user_instances', 'block_list_instances', 'list_instances');
			foreach ($instances_data as $instance)
			{
				if ($instance['status'] != 'aborted' && $instance['status'] != 'exception' && $instance['user'] != $GLOBALS['phpgw_info']['user']['account_id'])
				{
					$this->t->set_var('exception', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&abort=1&iid='. $instance['instanceId'] .'&aid='. $instance['activityId']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'stop') .'" alt="'. lang('exception instance') .'" title="'. lang('exception instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('exception', '');
				}

				if ($instance['isAutorouted'] == 'n' && $instance['actstatus'] == 'completed')
				{
					$this->t->set_var('send', '<a href="'. $GLOBALS['phgpw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&send=1&iid='. $instance['instanceId'] .'&aid='. $instance['activityId']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'linkto') .'" alt="'. lang('send instance') .'" title="'. lang('send instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('send', '');
				}

				if ($instance['isInteractive'] == 'y' && $instance['status'] == 'active')
				{
					$this->t->set_var('run', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&iid='. $instance['instanceId'] .'&activityId='. $instance['activityId']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run instance') .'" title="'. lang('run instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('run', '');
				}

				if ($instance['status'] != 'aborted' && $instance['user'] == $GLOBALS['phpgw_info']['user']['account_id'])
				{
					$this->t->set_var('abort', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&abort=1&iid='. $instance['instanceId'] .'&aid='. $instance['activityId']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'trash') .'" alt="'. lang('abort instance') .'" title="'. lang('abort instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('abort', '');
				}

				if ($instance['user'] == '*' && $instance['status'] == 'active')
				{
					$this->t->set_var('grab_or_release', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&grab=1&iid='. $instance['instanceId'] .'&aid='. $instance['activityId']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'fix') .'" alt="'. lang('grab instance') .'" title="'. lang('grab instance') .'" /></a>');
				}
				elseif ($instance['status'] == 'active')
				{
					$this->t->set_var('grab_or_release', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&release=1&iid='. $instance['instanceId'] .'&aid='. $instance['activityId']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'float') .'" alt="'. lang('release instance') .'" title="'. lang('release instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('grab_or_release', '');
				}

				$GLOBALS['phpgw']->accounts->get_account_name($instance['owner'],$lid,$fname_owner,$lname_owner);
				$GLOBALS['phpgw']->accounts->get_account_name($instance['user'],$lid,$fname_user,$lname_user);
				$this->t->set_var(array(
					'instanceId'		=> $instance['instanceId'],
					'owner'				=> $fname_owner . ' ' . $lname_owner,
					'status'			=> $instance['status'],
					'procname'			=> $instance['procname'],
					'version'			=> $instance['version'],
					'act_icon'			=> $this->act_icon($instance['type']),
					'name'				=> $instance['name'],
					'user'				=> $fname_user . ' ' . $lname_user,
					'actstatus'			=> $instance['actstatus'],
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('list_instances', 'block_list_instances', true);
			}
			if (!count($instances_data)) $this->t->set_var('list_instances', '<tr><td colspan="8" align="center">'. lang('There are no instances available') .'</td></tr>');
		}

		function show_select_status($filter_status)
		{
			$statuses = array('active', 'aborted', 'exception');
			foreach ($statuses as $status)
			{
				$this->t->set_var('selected_filter_status_'. $status, ($filter_status == $status)? 'selected="selected"' : '');
			}
		}

		function show_select_process($all_processes_data, $filter_process)
		{
			$this->t->set_block('user_instances', 'block_select_process', 'select_process');
			$this->t->set_var('selected_filter_process_all', (!$filter_process)? 'selected="selected"' : '');

			foreach ($all_processes_data as $process_data)
			{
				$this->t->set_var(array(
					'selected_filter_process'	=> ($filter_process == $process_data['pId'])? 'selected="selected"' : '',
					'filter_process_id'			=> $process_data['pId'],
					'filter_process_name'		=> $process_data['procname'],
					'filter_process_version'	=> $process_data['version'],
				));
				$this->t->parse('select_process', 'block_select_process', true);
			}
		}

		function show_select_user($filter_user)
		{
			$GLOBALS['phpgw']->accounts->get_account_name($GLOBALS['phpgw_info']['user']['account_id'], $lid, $fname, $lname);

			$this->t->set_var(array(
				'filter_user_all'	=> ($filter_user == '')? 'selected="selected"' : '',
				'filter_user_star'	=> ($filter_user == '*')? 'selected="selected"' : '',
				'filter_user_user'	=> ($filter_user == $GLOBALS['phpgw_info']['user']['account_id'])? 'selected="selected"' : '',
				'filter_user_id'	=> $GLOBALS['phpgw_info']['user']['account_id'],
				'filter_user_name'	=> $fname . ' ' . $lname,
			));
		}

		function show_select_act_status($filter_act_status)
		{
			$this->t->set_var(array(
				'filter_act_status_all'			=> ($filter_act_status == '')? 'selected="selected"' : '',
				'filter_act_status_running'		=> ($filter_act_status == 'running')? 'selected="selected"' : '',
				'filter_act_status_completed'	=> ($filter_act_status == 'completed')? 'selected="selected"' : '',
			));
		}
	}
?>

