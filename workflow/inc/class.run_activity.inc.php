<?php

	include_once(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class run_activity extends workflow
	{
		var $public_functions = array(
			'go'	=> true,
		);

		var $base_activity;

		var $process;

		function run_activity()
		{
			parent::workflow();
			$this->base_activity	= CreateObject('workflow.workflow_baseactivity');
			$this->process		= CreateObject('workflow.workflow_process');
		}

		function go($activity_id=0, $iid=0, $auto=0)
		{
			if ($iid) $_REQUEST['iid'] = $iid;

			if (!$activity_id)
			{
				$activity_id	= (int)get_var('activity_id', 'GET', 0);
			}

			if (!$activity_id) die(lang('No activity indicated'));

			// load activity
			$activity = $this->base_activity->getActivity($activity_id);

			// load process
			$this->process->getProcess($activity->getProcessId());

			// instantiate instance class, but before set some global variables needed by it
			$GLOBALS['__activity_completed'] = false;
			$GLOBALS['user'] = $GLOBALS['phpgw_info']['user']['account_id'];
			$instance = CreateObject('workflow.workflow_instance');

			// load roles
			$act_roles = $activity->getRoles();
			//echo "activity roles: <pre>";print_r($act_roles);echo "</pre>";
			$user_roles = $activity->getUserRoles($GLOBALS['user']);
			//echo "current user roles: <pre>";print_r($user_roles);echo "</pre>";

			// Only check roles if this is an interactive activity
			if ($activity->isInteractive() == 'y' && !count(array_intersect($act_roles, $user_roles)))
			{
				die(lang('You have not permission to execute this activity'));
			}

			$act_role_names = $activity->getActivityRoleNames($GLOBALS['user']);

			// load code sources
			$source = GALAXIA_PROCESSES . SEP . $this->process->getNormalizedName(). SEP . 'compiled' . SEP . $activity->getNormalizedName(). '.php';
			$shared = GALAXIA_PROCESSES . SEP . $this->process->getNormalizedName(). SEP . 'code' . SEP . 'shared.php';

			// Activities' code will have at their disposition the $db object to handle database interactions
			// TODO: open a new connection to the database under a different username to allow privilege handling on tables
			$db = $GLOBALS['phpgw']->ADOdb;

			// run the shared code (just in case because each activity is supposed to include it)
			include_once($shared);

			// run the activity			
			if (!$auto && $activity->isInteractive())
			{
				$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Running Activity');
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
			
				// activities' code will have at their disposition the $template object to handle the corresponding activity template, but $GLOBALS['phpgw']->template will also be available, in case global scope for this is needed
				$template = CreateObject('phpgwapi.Template', GALAXIA_PROCESSES.SEP);
				$template->set_file('template', $this->process->getNormalizedName().SEP.'code'.SEP.'templates'.SEP.$activity->getNormalizedName().'.tpl');
				$GLOBALS['phpgw']->template =& $template;
			}
			//echo "<br><br><br><br><br>Including $source <br>In request: <pre>";print_r($_REQUEST);echo "</pre>";
			include_once ($source);
			
			// TODO: process instance comments

			// if activity is interactive and completed, display completed template
			if (!$auto && $GLOBALS['__activity_completed'] && $activity->isInteractive())
			{
				$this->t->set_file('activity_completed', 'activity_completed.tpl');

				$this->t->set_var(array(
					'wf_procname'		=> $this->process->getName(),
					'procversion'	=> $this->process->getVersion(),
					'actname'		=> $activity->getName(),
				));

				$this->translate_template('activity_completed');
				$this->t->pparse('output', 'activity_completed');
				$GLOBALS['phpgw']->common->phpgw_footer();
			}
			// but if it hasn't been completed, show the activities' template
			elseif (!$auto && !$GLOBALS['__activity_completed'] && $activity->isInteractive())
			{
				$template->pparse('output', 'template');
				$GLOBALS['phpgw']->common->phpgw_footer();
			}

		}

	}
?>
