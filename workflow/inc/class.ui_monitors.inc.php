<?php

	include(dirname(__FILE__) . SEP . 'class.monitor.inc.php');

	class ui_monitors extends monitor
	{

		var $public_functions = array(
			'form'	=> true,
		);

		function ui_monitors()
		{
			//parent monitor with template name
			parent::monitor('monitors');
		}

		function form()
		{
			$this->fill_local_variables();
			$this->fill_monitor_stats($this->stats);
                        $this->t->set_var(array('message' => implode('<br>', $this->message)));			
			$this->finish();
		}
		
		//! fill all images and links
		function fill_local_variables()
		{
			$improc	= $GLOBALS['phpgw']->common->image('workflow', 'monitorprocess');
			$imacti	= $GLOBALS['phpgw']->common->image('workflow', 'monitoractivity');
			$iminst	= $GLOBALS['phpgw']->common->image('workflow', 'monitorinstance');
			$imwork	= $GLOBALS['phpgw']->common->image('workflow', 'monitor');

			$this->t->set_var(array(
					'img_monitor_processes'		=> '<img src="'.$improc.'" alt="{lang_monitor_processes}" title="{lang_monitor_processes}">',
					'img_monitor_activities'	=> '<img src="'.$imacti.'" alt="{lang_monitor_activities}" title="{lang_monitor_activities}">',
					'img_monitor_instances'		=> '<img src="'.$iminst.'" alt="{lang_monitor_instances}" title="{lang_monitor_instances}">',
					'img_monitor_workitems'		=> '<img src="'.$imwork.'" alt="{lang_monitor_workitems}" title="{lang_monitor_workitems}">',
					'link_monitor_processes'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'workflow.ui_monitorprocesses.form')),
					'link_monitor_activities'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'workflow.ui_monitoractivities.form')),
					'link_monitor_instances'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'workflow.ui_monitorinstances.form')),
					'link_monitor_workitems'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'workflow.ui_monitorworkitems.form')),
					'help_monitor_processes'	=> lang('list of processes with status and validity and, for each, counters of instances by status'),
					'help_monitor_activities'	=> lang('list of all activities with, for each,  counters of instances by status'),
					'help_monitor_instances'	=> lang('list of all instances with info about current status and activities and link to administration of theses instances'),
					'help_monitor_workitems'	=> lang('list of all history items made by instances while they travel in the workflow with information about duration and date'),
			));
		}

	}
?>
