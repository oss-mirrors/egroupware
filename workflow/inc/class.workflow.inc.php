<?php

	class workflow
	{
		var $t;

		var $pId;

		var $message = array();

		var $nextmatchs;

		var $start;

		var $total;

		var $order;

		var $sort;

		var $search_str;

		var $sort_mode;

		var $wheres = array();

		function workflow()
		{
			$this->t			= $GLOBALS['phpgw']->template;
			$this->pId			= (int)get_var('pid', 'any', 0);
			$this->start		= (int)get_var('start', 'GET', 0);
			$this->search_str	= get_var('find', 'any', '');
			$this->nextmatchs	= CreateObject('phpgwapi.nextmatchs');
		}

		function fill_proc_bar($proc_info)
		{
			//echo "proc_info: <pre>";print_r($proc_info);echo "</pre>";
			$this->t->set_file('proc_bar_tpl', 'proc_bar.tpl');

			if ($proc_info['isValid'] == 'y')
			{
				$dot_color = 'green';
				$alt_validity = lang('valid');
			}
			else
			{
				$dot_color = 'red';
				$alt_validity = lang('invalid');
			}

			// if process is active show stop button. Else show start button, but only if it is valid. If it's not valid, don't show any activation or stop button.
			if ($proc_info['isActive'] == 'y')
			{
				$start_stop = '<td><a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&pid='. $proc_info['pId'] .'&deactivate_proc='. $proc_info['pId']) .'"><img border ="0" src="'. $GLOBALS['phpgw']->common->image('workflow', 'stop') .'" alt="'. lang('stop') .'" title="'. lang('stop') .'" /></a></td>';
			}
			elseif ($proc_info['isValid'] == 'y')
			{
				$start_stop = '<td><a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&pid='. $proc_info['pId'] .'&activate_proc='. $proc_info['pId']) .'"><img border ="0" src="'. $GLOBALS['phpgw']->common->image('workflow', 'refresh2') .'" alt="'. lang('activate') .'" title="'. lang('activate') .'" /></a></td>';
			}
			else
			{
				$start_stop = '';
			}
			$this->t->set_var(array(
				'proc_name'				=> $proc_info['name'],
				'version'				=> $proc_info['version'],
				'img_validity'			=> $GLOBALS['phpgw']->common->image('workflow', $dot_color.'_dot'),
				'alt_validity'			=> $alt_validity,
				'start_stop'			=> $start_stop,
				'link_admin_activities'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.form&pid='. $proc_info['pId']),
				'img_activity'			=> $GLOBALS['phpgw']->common->image('workflow', 'Activity'),
				'link_admin_processes'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.form&pid='. $proc_info['pId']),
				'img_change'			=> $GLOBALS['phpgw']->common->image('workflow', 'change'),
				'link_admin_shared_source'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminsource.form&pid='. $proc_info['pId']),
				'img_code'				=> $GLOBALS['phpgw']->common->image('workflow', 'code'),
				'link_admin_roles'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminroles.form&pid='. $proc_info['pId']),
				'img_roles'				=> $GLOBALS['phpgw']->common->image('workflow', 'roles'),
				'link_graph'			=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminactivities.show_graph&pid=' . $proc_info['pId']),
				'img_process'			=> $GLOBALS['phpgw']->common->image('workflow', 'Process'),
				'link_save_process'		=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_adminprocesses.save_process&id='. $proc_info['pId']),
				'img_save'				=> $GLOBALS['phpgw']->common->image('workflow', 'save'),
				'link_monitor_process'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorprocesses.form&filter_process='. $proc_info['pId']),
				'img_process'			=> $GLOBALS['phpgw']->common->image('workflow', 'Process'),
				'link_monitor_activities'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitoractivities.form&filter_process='. $proc_info['pId']),
				'img_activity'			=> $GLOBALS['phpgw']->common->image('workflow', 'Activity'),
				'link_monitor_instances'=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances.form&filter_process='. $proc_info['pId']),
				'img_instance'			=> $GLOBALS['phpgw']->common->image('workflow', 'Instance'),

			));

			$this->translate_template('proc_bar_tpl');
			return $this->t->parse('proc_bar', 'proc_bar_tpl');
		}

		function act_icon($type)
		{
			switch($type)
			{
				case 'activity':
					$ic = "mini_rectangle.gif";
					break;
				case 'switch':
					$ic = "mini_diamond.gif";
					break;
				case 'start':
					$ic="mini_circle.gif";
					break;
				case 'end':
					$ic='mini_dbl_circle.gif';
					break;
				case 'split':
					$ic='mini_triangle.gif';
					break;
				case 'join':
					$ic='mini_inv_triangle.gif';
					break;
				case 'standalone':
					$ic='mini_hexagon.gif';
					break;
			}
			return '<img src="'. $GLOBALS['phpgw']->common->image('workflow', $ic) .'" alt="'. lang($type) .'" title="'. lang($type) .'" />';
		}

		function fill_monitor_stats($stats)
		{
			$this->t->set_file('monitor_stats_tpl', 'monitor_stats.tpl');
			$this->t->set_var(array(
				'processes'				=> $stats['processes'],
				'active_processes'		=> $stats['active_processes'],
				'running_processes'		=> $stats['running_processes'],
				'href_active_instances'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances&filter_status=active'),
				'active_instances'		=> $stats['active_instances'],
				'href_completed_instances'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances&filter_status=completed'),
				'completed_instances'	=> $stats['completed_instances'],
				'href_aborted_instances'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances&filter_status=aborted'),
				'aborted_instances'		=> $stats['aborted_instances'],
				'href_exception_instances'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_monitorinstances&filter_status=exception'),
				'exception_instances'	=> $stats['exception_instances'],
			));
			$this->translate_template('monitor_stats_tpl');
			return $this->t->parse('monitor_stats', 'monitor_stats_tpl');
		}

		function translate_template($template_name)
		{
			$undef = $this->t->get_undefined($template_name);
			foreach ($undef as $value)
			{
				$valarray = explode('_', $value);
				$type = array_shift($valarray);
				$newval = implode(' ', $valarray);
				if ($type == 'lang')
				{
					$this->t->set_var($value, lang($newval));
				}
			}
		}

		function show_errors(&$activity_manager, &$error_str)
		{
			$valid = $activity_manager->validate_process_activities($this->pId);
			if (!$valid)
			{
				$errors = $activity_manager->get_error();
				$error_str = '<b>' . lang('The following items must be corrected to be able to activate this process').':</b><br/><small><ul>';
				foreach ($errors as $error)
				{
					$error_str .= '<li>'. $error . '<br/>';
				}
				$error_str .= '</ul></small>';
				return 'n';
			}
			else
			{
				$error_str = '';
				return 'y';
			}
		}

		function get_source($proc_name, $act_name, $type)
		{
			switch($type)
			{
				case 'code':
					$path =  'activities' . SEP . $act_name . '.php';
					break;
				case 'template':
					$path = 'templates' . SEP . $act_name . '.tpl';
					break;
				default:
					$path = 'shared.php';
					break;
			}
			$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
			$fp = fopen($complete_path, 'r');
			$data = fread($fp, filesize($complete_path));
			fclose($fp);
			return $data;
		}

		function save_source($proc_name, $act_name, $type, $source)
		{
			switch($type)
			{
				case 'code':
					$path =  'activities' . SEP . $act_name . '.php';
					break;
				case 'template':
					$path = 'templates' . SEP . $act_name . '.tpl';
					break;
				default:
					$path = 'shared.php';
					break;
			}
			$complete_path = GALAXIA_PROCESSES . SEP . $proc_name . SEP . 'code' . SEP . $path;
			// In case you want to be warned when source code is changed:
			// mail('yourmail@domain.com', 'source changed', "PATH: $complete_path \n\n SOURCE: $source");
			$fp = fopen($complete_path, 'w');
			fwrite($fp, $source);
			fclose($fp);
		}
	}
?>
