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
			$activity_id		= get_var('aid', 'GET', 0);
			$instance_id		= get_var('iid', 'GET', 0);
			$this->sort			= get_var('sort', 'any', 'asc');
			$this->order		= get_var('order', 'any', 'wf_procname');
			$this->sort_mode	= $this->order . '__' . $this->sort;
			$this->search_str	= get_var('search_str', 'any', '');

			// exception instance
			if (isset($_GET['exception']))
			{
				$this->GUI->gui_exception_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id);
			}

			// abort instance
			if (isset($_GET['abort']))
			{
				$this->GUI->gui_abort_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id);
			}

			// release instance
			if (isset($_GET['release']))
			{
				$this->GUI->gui_release_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id);
			}

			// grab instance
			if (isset($_GET['grab']))
			{
				$this->GUI->gui_grab_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id);
			}

			// send instance (needed when an activity is not autorouted)
			if (isset($_GET['send']))
			{
				$this->GUI->gui_send_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id);
			}

		    $where = '';
		    $wheres = array();


		    if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {  
	        	$filter_process= (int)get_var('filter_process','any','');
		    	$wheres[] = "ga.wf_p_id=" .$filter_process. "";
		    }  
/*
		    if (isset($_REQUEST['filter_status']) && $_REQUEST['filter_status']) {  
		    	$filter_status= (int)get_var('filter_status', 'any', 0);
		    	$wheres[] = "wf_activity_id=" .$filter_status. "";
		    }
  
		        if (isset($_REQUEST['filter_type']) && $_REQUEST['filter_type'])
		        { 
		          $filter_type= get_var('filter_type', 'any', '');
		          $wheres[] = "type= '".$filter_type."'";
		        }  
		        if (isset($_REQUEST['search_str']))
		        {  
		          $this->search_str = get_var('search_str','any','');
		        } 
		        else {
		          $this->search_str = '';
		        }
		        */
			if( count($wheres) > 0 ) {
		        $where = implode(' and ', $wheres);
				//echo "where: <pre>";print_r($where);echo "</pre>";
			}
			
			
			// retrieve all user processes info
			$all_processes = $this->GUI->gui_list_user_processes($GLOBALS['phpgw_info']['user']['account_id'], 0, -1, 'wf_procname__asc', '', $where);
				
			//echo "all_processes: <pre>";print_r($all_processes);echo "</pre>";
			//echo "filter_act_status: <pre>";print_r($filter_act_status);echo "</pre>";
			
		    $where = '';
		    $wheres = array();
		    if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {  
	        	$filter_process= (int)get_var('filter_process','any','');
		    	$wheres[] = "ga.wf_p_id=" .$filter_process. "";
		    }  
		    if (isset($_REQUEST['filter_act_status']) && $_REQUEST['filter_act_status']) {  
	        	$filter_act_status= get_var('filter_act_status','any','');
		    	$wheres[] = "gia.wf_status='" .$filter_act_status. "'";
		    }  
		    if (isset($_REQUEST['filter_status']) && $_REQUEST['filter_status']) {  
	        	$filter_status= get_var('filter_status','any','');
		    	$wheres[] = "gi.wf_status='" .$filter_status. "'";
		    }  
			
			if( count($wheres) > 0 ) {
		        $where = implode(' and ', $wheres);
				//echo "where: <pre>";print_r($where);echo "</pre>";
			}

			// retrieve user instances
			$instances = $this->GUI->gui_list_user_instances($GLOBALS['phpgw_info']['user']['account_id'], $this->start, -1, $this->sort_mode, $this->search_str, $where);
			//echo "instances: <pre>";print_r($instances);echo "</pre>";

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
			//_debug_array($instances_data);
			// show table headers
			$this->t->set_var(array(
				'header_id'				=> $this->nextmatchs->show_sort_order($this->sort, 'wf_instance_id', $this->order, 'index.php', lang('id')),
				'header_owner'			=> $this->nextmatchs->show_sort_order($this->sort, 'wf_owner', $this->order, 'index.php', lang('Owner')),
				'header_inst_status'	=> $this->nextmatchs->show_sort_order($this->sort, 'wf_status', $this->order, 'index.php', lang('Inst. Status')),
				'header_process'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_procname', $this->order, 'index.php', lang('Process')),
				'header_activity'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_name', $this->order, 'index.php', lang('Activity')),
				'header_user'			=> $this->nextmatchs->show_sort_order($this->sort, 'wf_user', $this->order, 'index.php', lang('User')),
				'header_act_status'		=> $this->nextmatchs->show_sort_order($this->sort, 'wf_act_status', $this->order, 'index.php', lang('Act. Status')),
			));
			$this->t->set_block('user_instances', 'block_list_instances', 'list_instances');
			foreach ($instances_data as $instance)
			{
				if ($instance['wf_status'] != 'aborted' && $instance['wf_status'] != 'exception' && $instance['wf_user'] == $GLOBALS['phpgw_info']['user']['account_id'])
				{
					$this->t->set_var('exception', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&exception=1&iid='. $instance['wf_instance_id'] .'&aid='. $instance['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'stop') .'" alt="'. lang('exception instance') .'" title="'. lang('exception instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('exception', '');
				}

				if ($instance['wf_is_autorouted'] == 'n' && $instance['wf_act_status'] == 'completed')
				{
					$this->t->set_var('send', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&send=1&iid='. $instance['wf_instance_id'] .'&aid='. $instance['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'linkto') .'" alt="'. lang('send instance') .'" title="'. lang('send instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('send', '');
				}

				if ($instance['wf_is_interactive'] == 'y' && $instance['wf_status'] == 'active')
				{
					$this->t->set_var('run', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.run_activity.go&iid='. $instance['wf_instance_id'] .'&activity_id='. $instance['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next') .'" alt="'. lang('run instance') .'" title="'. lang('run instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('run', '');
				}

				if ($instance['wf_status'] != 'aborted' && $instance['wf_user'] == $GLOBALS['phpgw_info']['user']['account_id'])
				{
					$this->t->set_var('abort', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&abort=1&iid='. $instance['wf_instance_id'] .'&aid='. $instance['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'trash') .'" alt="'. lang('abort instance') .'" title="'. lang('abort instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('abort', '');
				}

				if ($instance['wf_user'] == '*' && $instance['wf_status'] == 'active')
				{
					$this->t->set_var('grab_or_release', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&grab=1&iid='. $instance['wf_instance_id'] .'&aid='. $instance['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'fix') .'" alt="'. lang('grab instance') .'" title="'. lang('grab instance') .'" /></a>');
				}
				elseif ($instance['wf_status'] == 'active')
				{
					$this->t->set_var('grab_or_release', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&release=1&iid='. $instance['wf_instance_id'] .'&aid='. $instance['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'float') .'" alt="'. lang('release instance') .'" title="'. lang('release instance') .'" /></a>');
				}
				else
				{
					$this->t->set_var('grab_or_release', '');
				}

				$GLOBALS['phpgw']->accounts->get_account_name($instance['wf_owner'],$lid,$fname_owner,$lname_owner);
				$GLOBALS['phpgw']->accounts->get_account_name($instance['wf_user'],$lid,$fname_user,$lname_user);
				$this->t->set_var(array(
					'instance_id'		=> $instance['wf_instance_id'],
					'owner'				=> $fname_owner . ' ' . $lname_owner,
					'status'			=> $instance['wf_status'],
					'wf_procname'			=> $instance['wf_procname'],
					'version'			=> $instance['wf_version'],
					'act_icon'			=> $this->act_icon($instance['wf_type']),
					'name'				=> $instance['wf_name'],
					'user'				=> $fname_user . ' ' . $lname_user,
					'act_status'		=> $instance['wf_act_status'],
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
					'selected_filter_process'	=> ($filter_process == $process_data['wf_p_id'])? 'selected="selected"' : '',
					'filter_process_id'			=> $process_data['wf_p_id'],
					'filter_process_name'		=> $process_data['wf_procname'],
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
