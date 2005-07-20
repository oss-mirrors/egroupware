<?php

	require_once(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class run_activity extends workflow
	{
		var $public_functions = array(
			'go'	=> true,
		);

		// Activity engine object. This is the object we'll be running
		var $base_activity;
		//Process engine object. Used to retrieve at least paths and configuration values
		var $process;
		// GUI engine object. Act carefully with it.
		var $GUI;
		// local process configuration cache
		var $conf = array();
		// local activity template
		var $wf_template;
		// The instance object we will manipulate
		var $instance;
		var $activity_type;
		// then we retain all usefull vars as members, to make them avaible in user's source code
		// theses are data which can be set before the user code and which are not likely to change because of the user code
		var $db;
		var $process_id;
		var $activity_id;
		var $process_name;
		var $process_version;
		var $activity_name;
		var $user_name;
		// theses 4 vars aren't avaible for the user code, they're set only after this user code was executed
		var $instance_id=0;
		var $instance_name='';
		var $instance_owner=0;
		var $owner_name='';
		// array used by automatic parsing:
		var $priority_array = Array();
		var $submit_array = Array();
		// vars used by automatic parsing
		var $display_owner=0; // if 0 draw nothing, 1 draw selected owner, else draw a select box for owner, see function descr
		var $display_next_user=0; // if 0 draw nothing, 1 draw selected next user, else draw a select box for next_user, see function descr
		// array of roles associated with the activity, usefull for lists of users associated with theses roles
		var $act_role_names= Array();
		
		function run_activity()
		{
			parent::workflow();
			$this->base_activity	=& CreateObject('workflow.workflow_baseactivity');
			$this->process		=& CreateObject('workflow.workflow_process');
			$this->GUI		=& CreateObject('workflow.workflow_gui');
			// TODO: open a new connection to the database under a different username to allow privilege handling on tables
			$this->db 		=& $GLOBALS['phpgw']->ADOdb;
		}

		function go($activity_id=0, $iid=0, $auto=0)
		{
			if ($iid)
			{
				$_REQUEST['iid'] = $iid;
			}
			

			if (!$activity_id)
			{
				$activity_id	= (int)get_var('activity_id', 'GET', 0);
			}

			if (!$activity_id) die(lang('No activity indicated'));
			// load activity
			$activity =& $this->base_activity->getActivity($activity_id);

			// load process
			$this->process->getProcess($activity->getProcessId());

			// instantiate instance class, but before set some global variables needed by it
			//TODO: move this global var in ['workflow']
			$GLOBALS['__activity_completed'] = false;
			$GLOBALS['workflow']['__leave_activity']=false;
			$GLOBALS['user'] = $GLOBALS['phpgw_info']['user']['account_id'];
			// the instance is avaible with $instance or $this->instance
			$instance =& CreateObject('workflow.workflow_instance');
			$this->instance =& $instance;

			//tests for access rights-----------------------------------------
			
			// Only check roles if this is an interactive activity
			if ($activity->isInteractive() == 'y' 
				// then verify roles, ownership and all defined access rules
				&& !($activity->checkUserAccess($GLOBALS['phpgw_info']['user']['account_id'] ))
			)
			{
				die(lang('You have not permission to execute this activity'));
			}

			$this->act_role_names = $activity->getActivityRoleNames();

			// load code sources
			$source = GALAXIA_PROCESSES . SEP . $this->process->getNormalizedName(). SEP . 'compiled' . SEP . $activity->getNormalizedName(). '.php';
			$shared = GALAXIA_PROCESSES . SEP . $this->process->getNormalizedName(). SEP . 'code' . SEP . 'shared.php';

			// Activities' code will have at their disposition the $db object to handle database interactions
			// they can access it in 3 ways: $db $this->db or $GLOBALS['workflow']['db'] 
			$db 				=& $this->db;
			$GLOBALS['workflow']['db']	=& $this->db;
			//set some other usefull vars (note that $instance is empty at this time)
			$this->activity_type	= $activity->getType();
			$this->process_id 	= $activity->getProcessId();
			$this->activity_id 	= $activity_id;
			$this->process_name	= $this->process->getName();
			$this->process_version	= $this->process->getVersion();
			$this->activity_name	= $activity->getName();
			$this->user_name	= $GLOBALS['phpgw']->accounts->id2name($GLOBALS['user']);
			
			//we set them in $GLOBALS['workflow'] as well
			$GLOBALS['workflow']['wf_activity_type']	=& $this->activity_type;
			$GLOBALS['workflow']['wf_process_id'] 		=& $this->process_id;
			$GLOBALS['workflow']['wf_activity_id'] 		=& $this->activity_id;
			$GLOBALS['workflow']['wf_process_name']		=& $this->process_name;
			$GLOBALS['workflow']['wf_process_version']	=& $this->process_version;
			$GLOBALS['workflow']['wf_activity_name']	=& $this->activity_name;
			$GLOBALS['workflow']['wf_user_name']		=& $this->user_name;
			
			//FIXME: useless, we remove it
			// run the shared code (just in case because each activity is supposed to include it)
			//include_once($shared);

			// run the activity
			if (!$auto && $activity->isInteractive())
			{
				//get configuration options with default values if no init was done before
				$myconf = array(
					'display_please_wait_message'		=> 0,
					'use_automatic_parsing' 		=> 1,
					'show_activity_title' 			=> 1,
					'show_instance_name'			=> 0,
					'show_multiple_submit_as_select' 	=> 0,
					'show_activity_info_zone' 		=> 1,
				);
				$this->conf =& $this->process->getConfigValues($myconf);
				// if process conf says so we display a please wait message until the activity form is shown
				if ($this->conf['display_please_wait_message'])
				{
					$this->show_wait_message();
				}

				$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Running Activity');
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
			
				// activities' code will have at their disposition the $template object to handle the corresponding activity template, 
				// but $GLOBALS['phpgw']->template will also be available, in case global scope for this is needed
				// and we have as well the $this->wf_template for the same template
				$template =& CreateObject('phpgwapi.Template', GALAXIA_PROCESSES.SEP);
				
				$template->set_file('template', $this->process->getNormalizedName().SEP.'code'.SEP.'templates'.SEP.$activity->getNormalizedName().'.tpl');
				$GLOBALS['phpgw']->template =& $template;
				$this->wf_template =& $template;
				
				// They will also have at their disposition theses array, used for automatic parsing
				$GLOBALS['workflow']['priority_array']	=& $this->priority_array;
				$GLOBALS['workflow']['submit_array']	=& $this->submit_array;
				// and some vars for automatic parsing as well
				$GLOBALS['workflow']['display_owner']    =& $this->display_owner;
				$GLOBALS['workflow']['display_next_user']=& $this->display_next_user;
			}
			//echo "<br><br><br><br><br>Including $source <br>In request: <pre>";print_r($_REQUEST);echo "</pre>";
			//[__leave_activity] is setted if needed in the xxx_pre code or by the user in his code
			//[__activity_completed] will be setted if $instance->complete() is runned
			// HERE the user code is 'executed'
			require_once ($source);
			
			//Now that the instance is ready we can catch some others usefull vars
			$this->instance_id	= $instance->getInstanceId();
			$this->instance_name	= $instance->getName();
			$this->instance_owner	= $instance->getOwner();
			$this->owner_name	= $GLOBALS['phpgw']->accounts->id2name($this->instance_owner);
			$GLOBALS['workflow']['wf_instance_id'] 	=& $this->instance_id;
			$GLOBALS['workflow']['wf_instance_name']=& $this->instance_name;
			$GLOBALS['workflow']['wf_instance_owner']=& $this->instance_owner;
			$GLOBALS['workflow']['wf_owner_name']=& $this->owner_name;

			
			// TODO: process instance comments

			// for interactive activities in non-auto mode:
			if (!$auto && $activity->isInteractive())
			{
				if ($GLOBALS['__activity_completed'])
				{
					// activity is interactive and completed, 
					// we have to continue the workflow
					// and send any autorouted activity which could be after this one
					// this is not done in the $instance->complete() to let
					// xxx_pos.php code be executed before sending the instance
					$instance->sendAutorouted($activity_id);
					// re-retrieve instance data which could have been modified by an automatic activity
					$this->instance_id	= $instance->getInstanceId();
					$this->instance_name	= $instance->getName();

					// and display completed template
					$this->show_completed_page();
				}
				// it hasn't been completed
				else
				{
					if ($GLOBALS['workflow']['__leave_activity'])
					{
						// activity is interactive and the activity source set the 
						// $GLOBALS[workflow][__leave_activity] it's a 'cancel' mode.
						// we redirect the user to the leave activity page
						$this->show_leaving_page();
					}
					else
					{ 
						//the activity is not completed and the user doesn't want to leave
						// we loop on the form
						$this->show_form();
					}
				}
			}
		}
		
		//! show a waiting message using css and script to hide it on onLoad events. 
		/*!
		You can enable/disable it in process configuration.
		Css for the please wait message is defined in app.css, a css automatically included by egroupware
		*/
		function show_wait_message()
		{
			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw_info']['flags']['java_script'] .= '<script type="text/javascript">
				document.write(\'<DIV id="loading"><BR><BR>Please wait, task in progress ...*</DIV>\');
				function hide_loading()
				{
					document.getElementById("loading").style.display="none";
				}</script>';
			$GLOBALS['phpgw']->js->set_onload('hide_loading();');
		}

		//! show the page avaible when completing an activity
		function show_completed_page()
		{
			$this->t->set_file('activity_completed', 'activity_completed.tpl');

			$this->t->set_var(array(
				'wf_procname'	=> $this->process_name,
				'procversion'	=> $this->process_version,
				'actname'	=> $this->activity_name,
			));

			$this->translate_template('activity_completed');
			$this->t->pparse('output', 'activity_completed');
			$this->show_after_running_page();
		}
		
		//! show the page avaible when leaving an activity (with a Cancel or Quit button)
		function show_leaving_page()
		{
			$this->t->set_file('leaving_activity', 'leaving_activity.tpl');
			$this->t->set_var(array(
				'wf_procname'	=> $this->process_name,
				'procversion'	=> $this->process_version,
				'actname'	=> $this->activity_name,
			));
			
			//check real avaible actions on this instance
			$actions = $this->GUI->getUserActions($GLOBALS['user'],$this->instance_id,$this->activity_id);
			if (isset($actions['release']))
			{
				//prepare a release command on the user_instance form
				$link_array = array(
					'menuaction'		=> 'workflow.ui_userinstances.form',
					'filter_process'	=> $this->process_id,
					'filter_instance'	=> $this->instance_id,
					'iid'			=> $this->instance_id,
					'aid'			=> $this->activity_id,
					'release'		=> 1,
				);
				$releasetxt = lang('release activity for this instance');
				$this->t->set_var(array(
					'release_text'	=> lang('This activity for this instance is actually avaible for you.'),
					'release_button'=> '<a href="'.$GLOBALS['phpgw']->link('/index.php',$link_array)
						.'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'fix')
						.'" alt="'.$releasetxt.'" title="'.$releasetxt.'" width="16" >'
						.$releasetxt.'</a>',
				));
			}
			else
			{
				$this->t->set_var(array(
					'release_text'	=> lang('It seems this activity for this instance is not assigned to you anymore.'),
					'release_button'=> '',
				));

			}
			$this->translate_template('leaving_activity');
			$this->t->pparse('output', 'leaving_activity');
			$this->show_after_running_page();
		}
		
		//! show the bottom of end run_activity interactive pages with links to user_instance form
		/*!
		for start activities we link back to user_openinstance form
		and for standalone activities we loop back to global activities form
		*/
		function show_after_running_page()
		{
			$this->t->set_file('after_running', 'after_running.tpl');
			
			//prepare the links form
			$link_data_proc = array(
				'menuaction'		=> 'workflow.ui_userinstances.form',
				'filter_process'	=> $this->process_id,
			);
			$link_data_inst = array(
				'menuaction'		=> 'workflow.ui_userinstances.form',
				'filter_instance'	=> $this->instance_id,
			);
			if ($this->activity_type == 'start')
			{
				$activitytxt = lang('get back to instance creation');
				$act_button_name = lang('New instance');
				$link_data_act = array(
					'menuaction'		=> 'workflow.ui_useropeninstance.form',
				);
			}
			elseif  ($this->activity_type == 'standalone')
			{
				$activitytxt = lang('get back to global activities');
				$act_button_name = lang('Global activities');
				$link_data_act = array(
					'menuaction'		=> 'workflow.ui_useractivities.form',
					'show_globals'		=> true,
				);
			}
			else
			{
				$activitytxt = lang('go to same activities for other instances of this process');
				$act_button_name = lang('activity %1', $this->activity_name);
				$link_data_act = array(
					'menuaction'		=> 'workflow.ui_userinstances.form',
					'filter_process'        => $this->process_id,
					'filter_activity_name'	=> $this->activity_name,
				);
			}
			$button='<img src="'. $GLOBALS['phpgw']->common->image('workflow', 'next')
				.'" alt="'.lang('go').'" title="'.lang('go').'" width="16" >';
			$this->t->set_var(array(
				'same_instance_text'	=> ($this->activity_type=='standalone')? '-' : lang('go to the actual state of this instance'),
				'same_activities_text'	=> $activitytxt,
				'same_process_text'	=> lang('go to same process activities'),
				'same_instance_button'	=> ($this->activity_type=='standalone')? '-' : '<a href="'.$GLOBALS['phpgw']->link('/index.php',$link_data_inst).'">'
					.$button.lang('instance %1', ($this->instance_name=='')? $this->instance_id: $this->instance_name).'</a>',
				'same_activities_button'=> '<a href="'.$GLOBALS['phpgw']->link('/index.php',$link_data_act).'">'
					.$button.$act_button_name.'</a>',
				'same_process_button'	=> '<a href="'.$GLOBALS['phpgw']->link('/index.php',$link_data_proc).'">'
					.$button.lang('process %1', $this->process_name).'</a>',
			));
			$this->translate_template('after_running');
			$this->t->pparse('output', 'after_running');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}

		//! show the activity form with automated parts if needed
		function show_form()
		{
			
			//set a global template for interactive activities
			$this->t->set_file('run_activity','run_activity.tpl');
			
			//set the css style files links
			$this->t->set_var(array(
				'run_activity_css_link'	=> $this->get_css_link('run_activity.css'),
			));
			
			
			// draw the activity's title zone
			$this->parse_title($this->activity_name);

			//draw the instance_name input or label
			// init wf_name to the requested one or the stored name
			// the requested one handle the looping in activity form
			$wf_name = get_var('wf_name','post',$this->instance->getName());
			$this->parse_instance_name($wf_name);

			//draw the instance_name input or label
			// init wf_set_owner to the requested one or the stored owner
			// the requested one handle the looping in activity form
			$wf_set_owner = get_var('wf_set_owner','post',$this->instance->getOwner());
			$this->parse_instance_owner($wf_set_owner);
			
			// draw the activity central user form
			$this->t->set_var(array('activity_template' => $this->wf_template->parse('output', 'template')));
			
			//draw the select priority box
			// init priority to the requested one or the stored priority
			// the requested one handle the looping in activity form
			$priority = get_var('wf_priority','post',$this->instance->getPriority());
			$this->parse_priority($priority);
			
			//draw the select next_user box
			// init next_user to the requested one or the stored one
			// the requested one handle the looping in activity form
			$next_user = get_var('wf_next_user','POST',$this->instance->getNextUser());
			$this->parse_next_user($next_user);
			
			//draw the activity submit buttons	
			$this->parse_submit();
			
			//draw the info zone
			$this->parse_info_zone();
			
			$this->translate_template('run_activity');
			$this->t->pparse('output', 'run_activity');
			$GLOBALS['phpgw']->common->phpgw_footer();
		}
		
		//!Parse the title in the activity form, the user can decide if he want this title to be shown or not
		/*!
		* if you do not want thuis to be displayed set your process config value for show_activity_title to false
		* @param title is by default empty, You can give a title as a parameter. 
		*/
		function parse_title($title='')
		{
			$this->t->set_block('run_activity', 'block_title_zone', 'title_zone');
			
			if (($this->conf['use_automatic_parsing']) && ($this->conf['show_activity_title']))
			{
				$this->t->set_var(array('activity_title'=> $title));
				$this->t->parse('title_zone', 'block_title_zone', true);
			}
			else
			{
				$this->t->set_var(array( 'title_zone' => ''));
			}
		}
		
		//!Parse the instance name input in the activity form, the user can decide if he want this name to be shown or not
		/*!
		* if you do not want this to be displayed set your process config value for show_instance_name to false
		* @param instance_name is the name we will display. 
		*/
		function parse_instance_name($instance_name)
		{
			$this->t->set_block('run_activity', 'block_instance_name_zone', 'instance_name_zone');
			
			if (($this->conf['use_automatic_parsing']) && ($this->conf['show_instance_name']))
			{
				$this->t->set_var(array('wf_name'=> $instance_name));
				$this->t->parse('instance_name_zone', 'block_instance_name_zone', true);
			}
			else
			{
				$this->t->set_var(array( 'instance_name_zone' => ''));
			}
		}
		
		//!Parse the set_owner select/display in the activity form, the user can decide if he want this name to be shown or not
		/*!
		* if $this->display_owner is 0 we draw nothing (default value) 
		* if $this->display_owner is 1 the owner is just shown 
		* if $this->display_owner is anything else we draw a select box
		* this 'anything else' can be an associative array containing the 'role' and/or 'activity' key
		* the values associated with theses keys can be strings or array of strings containing roles and/or
		* activities's names. Users displayed in the select will then be the users having access to theses activities
		* and users which are mapped  to theses roles (one match per user is enought to be displayed).
		* ie: $this->display_owner = 2; will display all users mapped to roles on the process
		* $this->display_owner = array('role' => array('Chiefs','assistant'), 'activity' => 'updating foo'); will
		* display users having access to activity 'updating foo' AND which are mapped to 'Chief' OR 'assistant' roles
		* of course roles and activities names must be matching the current process's roles and activities names.
		* @param actual_owner is the selected owner in the select list we will display or the shown owner. 
		*/
		function parse_instance_owner($actual_owner)
		{
		//echo "DEBUG parse_instance_owner:actual_owner:".$actual_owner.'display_owner:'
		//_debug_array($this->display_owner);
			
			//inside the select
			$this->t->set_block('run_activity', 'block_owner_options', 'owner_options');			
			//the select
			$this->t->set_block('run_activity', 'block_select_owner', 'wf_select_owner');
			// the whole area
			$this->t->set_block('run_activity', 'block_set_owner_zone', 'set_owner_zone');
			if ( 	(!$this->conf['use_automatic_parsing']) 
				|| ( empty($this->display_owner) || (!($this->display_owner)) ))
			{
				//hide the instance owner zone
				$this->t->set_var(array( 'set_owner_zone' => ''));
			}
			else
			{
				// a little label before the select box
                                $this->t->set_var(array('set_owner_text' => lang('Owner:')));
				if ((!(is_array($this->display_owner))) && ($this->display_owner==1))
				{
					//we will just display the owner
					$this->t->set_var(array('wf_select_owner' => $this->owner_name));
				}
				else
				{	//we will display a select

					//prepare retrieval of datas
					$subset=Array();
					if (is_array($this->display_owner))
					{
						foreach($this->display_owner as $key => $value)
						{
							if ($key=='role')
							{
								if (!(is_array($value)))
								{
									$value = explode(';',$value);
								}
								$subset[wf_role_name]= $value;
							}
							elseif ($key=='activity')
							{
								if (!(is_array($value)))
								{
									$value = explode(';',$value);
								}
								$subset[wf_activity_name]= $value;
							}
						}
					}
					//we'll ask the role_manager for it
					$role_manager =& CreateObject('workflow.workflow_rolemanager');
					// we expand groups to real users and want users mapped for a subset of the process
					// which is given by a user defined value
					$authorized_users = $role_manager->list_mapped_users($this->process_id, true, $subset );
					//first line of the select
					$this->t->set_var(array(
						'selected_owner_options'=> (!!$actual_owner)? 'selected="selected"' :'',
						'lang_default_owner'	=> lang('Default owner'),
					));
					//other lines
					foreach ($authorized_users as $user_id => $user_name)
					{
						$this->t->set_var(array(
							'owner_option_id'		=> $user_id,
							'owner_option_value'		=> $user_name,
							'selected_owner_options'	=> ($user_id == $actual_owner)? 'selected="selected"' :'',
						));
						//show the select line
						$this->t->parse('owner_options','block_owner_options',true);
					}
					//show the select
					$this->t->parse('wf_select_owner','block_select_owner',true);
				}
				//show the set owner zone
				$this->t->parse('set_owner_zone', 'block_set_owner_zone', true);
			}
		}
		
		//! Draw the priority select box in the activity form
		/*!
		* Parse the priority select box in the activity form. The user can decide if he want this select box to be shown or not
		* by completing $this->priority_array.
		* For example like that : $this->priority_array = array(1 => '1-Low',2 =>'2', 3 => '3-High');
		* If the array is empty or the conf values says the user does not want automatic parsing no select box will be shown
		* @param actual_priority is by default at 1 and will be the selected activity level.
		*/
		function parse_priority($actual_priority=1)
		{
			$this->t->set_block('run_activity', 'block_priority_options', 'priority_options');
			$this->t->set_block('run_activity', 'block_priority_zone', 'priority_zone');
			if ((!$this->conf['use_automatic_parsing']) || (count($this->priority_array)==0))
			{
				//hide the priority zone
				$this->t->set_var(array( 'priority_zone' => ''));
			}
			else
			{
				if (!is_array($this->priority_array))
				{
					$this->priority_array = explode(" ",$this->priority_array);
				}
				//handling the select box 
				foreach ($this->priority_array as $priority_level => $priority_label)
				{
					$this->t->set_var(array(
						'priority_option_name'		=> $priority_level,
 						'priority_option_value'		=> $priority_label,
 						'selected_priority_options'	=> ($priority_level == $actual_priority)? 'selected="selected"' :'',
					));
					//show the select box
					$this->t->parse('priority_options','block_priority_options',true);
				}
				// a little label before the select box
				$this->t->set_var(array('Priority_text' => lang('Priority level:')));
				//show the priority zone
				$this->t->parse('priority_zone', 'block_priority_zone', true);
			}
		}

		//!Parse the next_user select/display in the activity form, the user can decide if he want this to be shown or not
		/*!
		* if $this->display_next_user is 0 we draw nothing (default value) 
		* if $this->display_next_user is 1 the next_user is just shown 
		* if $this->display_next_user is anything else we draw a select box
		* this 'anything else' can be an associative array containing the 'role' and/or 'activity' key
		* the values associated with theses keys can be strings or array of strings containing roles and/or
		* activities's names. Users displayed in the select will then be the users having access to theses activities
		* and users which are mapped to theses roles (one match per user is enought to be displayed).
		* ie: $this->display_next_user = 2; will display all users mapped to roles on the process
		* $this->display_next_user = array('role' => array('Chiefs','assistant'), 'activity' => 'updating foo'); will
		* display users having access to activity 'updating foo' AND which are mapped to 'Chief' OR 'assistant' roles
		* of course roles and activities names must be matching the current process's roles and activities names.
		* @param actual_next_user is the selected next_user in the select list we will display or the shown next_user. 
		*/
		function parse_next_user($actual_next_user)
		{
		//echo "DEBUG parse_instance_next_user:actual_next_user:".$actual_next_user.'display_next_user:'
		//_debug_array($this->display_next_user);
			
			//inside the select
			$this->t->set_block('run_activity', 'block_next_user_options', 'next_user_options');			
			//the select
			$this->t->set_block('run_activity', 'block_select_next_user', 'wf_select_next_user');
			// the whole area
			$this->t->set_block('run_activity', 'block_set_next_user_zone', 'set_next_user_zone');
			if ( 	(!$this->conf['use_automatic_parsing']) 
				|| ( empty($this->display_next_user) || (!($this->display_next_user)) ))
			{
				//hide the instance next_user zone
				$this->t->set_var(array( 'set_next_user_zone' => ''));
			}
			else
			{
				// a little label before the select box
                                $this->t->set_var(array('set_next_user_text' => lang('Next user:')));
				if ((!(is_array($this->display_next_user))) && ($this->display_next_user==1))
				{
					//we will just display the next_user
					$next_user_name = $GLOBALS['phpgw']->accounts->id2name($actual_next_user);
					$this->t->set_var(array('wf_select_next_user' => $next_user_name));
				}
				else
				{	//we will display a select

					//prepare retrieval of datas
					$subset=Array();
					if (is_array($this->display_next_user))
					{
						foreach($this->display_next_user as $key => $value)
						{
							if ($key=='role')
							{
								if (!(is_array($value)))
								{
									$value = explode(';',$value);
								}
								$subset[wf_role_name]= $value;
							}
							elseif ($key=='activity')
							{
								if (!(is_array($value)))
								{
									$value = explode(';',$value);
								}
								$subset[wf_activity_name]= $value;
							}
						}
					}
					//we'll ask the role_manager for it
					$role_manager =& CreateObject('workflow.workflow_rolemanager');
					// we expand groups to real users and want users mapped for a subset of the process
					// which is given by a user defined value
					$authorized_users = $role_manager->list_mapped_users($this->process_id, true, $subset );
					//first line of the select
					$this->t->set_var(array(
						'selected_next_user_options'=> (!!$actual_next_user)? 'selected="selected"' :'',
						'lang_default_next_user'	=> lang('Default next user'),
					));
					//other lines
					foreach ($authorized_users as $user_id => $user_name)
					{
						$this->t->set_var(array(
							'next_user_option_id'		=> $user_id,
							'next_user_option_value'		=> $user_name,
							'selected_next_user_options'	=> ($user_id == $actual_next_user)? 'selected="selected"' :'',
						));
						//show the select line
						$this->t->parse('next_user_options','block_next_user_options',true);
					}
					//show the select
					$this->t->parse('wf_select_next_user','block_select_next_user',true);
				}
				//show the set next_user zone
				$this->t->parse('set_next_user_zone', 'block_set_next_user_zone', true);
			}
		}

		//! Draw the submit buttons on the activity form
		/*!
		In this function we'll draw the command buttons asked for this activity.
		else we'll check $this->submit_array which should be completed in the activity source
		and is an array with the names of the submit options corresponding to the value like this: 
		$this->submit_array['the_value_you_want']=lang('the label you want');
		if this array is empty we'll draw a simple submit button.
		The poweruser can decide to handle theses buttons in his own way in the config section
		He'll then have to draw it himself in his activity template.
		Note that the special value '__Cancel' is automatically handled and set the ['__leaving_activity']
		var to true.
		*/
		function parse_submit()
		{
			//inside the select box for submits
			$this->t->set_block('run_activity', 'block_submit_options', 'submit_options');
			//the select submit box
			$this->t->set_block('run_activity', 'block_submit_select_area', 'submit_select_area');
			//submit as buttons
			$this->t->set_block('run_activity', 'block_submit_buttons_area', 'submit_buttons_area');
			//the whole zone
			$this->t->set_block('run_activity', 'block_submit_zone', 'submit_zone');
			
			if (!($this->conf['use_automatic_parsing'])) 
			{
				// the user decided he'll do it his own way
				//empty the whole zone
				$this->t->set_var(array('submit_zone' => ''));
			}
			else
			{
				$buttons = '';
				if (count($this->submit_array)==0)
				{
					//the user didn't give us any instruction
					// we draw a simple submit button
					$this->t->set_var(array('submit_area',''));
					$buttons .= '<td class="wf_submit_buttons_button">';
					$buttons .= '<input name="wf_submit" type="submit" value="'.lang('Submit').'"/>';
					$buttons .= '</td>';
					//set the buttons
					$this->t->set_var(array('submit_buttons' => $buttons));
					// hide the select box zone
					$this->t->set_var(array('submit_select_area'=> ''));
					//show the buttons zone
					$this->t->parse('submit_buttons_area', 'block_submit_buttons_area', true);

				}
				else
				{
					//now we have another user choice. he can choose multiple submit buttons
					//or a select with only one submit
					if ( ($this->conf['show_multiple_submit_as_select']) && (count($this->submit_array) > 1) )
					{
						//multiple submits in a select box
						//handling the select box 
						foreach ($this->submit_array as $submit_button_name => $submit_button_value)
						{
							$this->t->set_var(array(
								'submit_option_value'	=> $submit_button_value,
								'submit_option_name'	=> $submit_button_name,
							));
						
							//show the select box
							$this->t->parse('submit_options','block_submit_options',true);
						}					
						//we need at least one submit button
						$this->t->set_var(array(
							'submit_button_name'	=> 'wf_submit',
							'submit_button_value'	=> lang('submit'),
						));
						// hide the multiple buttons zone
						$this->t->set_var(array('submit_buttons_area'=> ''));
						//show the select box zone
						$this->t->parse('submit_select_area', 'block_submit_select_area', true);
					}
					else
					{
						//multiple buttons with no select box or just one
						//draw input button for each entry
						foreach ($this->submit_array as $submit_button_name => $submit_button_value)
						{
						 	$buttons .= '<td class="wf_submit_buttons_button">';
							$buttons .= '<input name="'.$submit_button_name.'" type="submit" value="'.$submit_button_value.'"/>';
							$buttons .= '</td>';
						}
						//set the buttons
						$this->t->set_var(array('submit_buttons' => $buttons));
						// hide the select box zone
						$this->t->set_var(array('submit_select_area'=> ''));
						//show the buttons zone
						$this->t->parse('submit_buttons_area', 'block_submit_buttons_area', true);
					}
				}
				//show the whole submit zone
				$this->t->parse('submit_zone', 'block_submit_zone', true);
			}
		}
	
		//!Parse the activity info zone in the activity form, the user can decide if he want it or not
		function parse_info_zone()
		{
			$this->t->set_block('run_activity', 'workflow_info_zone', 'info_zone');
			
			if (($this->conf['use_automatic_parsing']) && ($this->conf['show_activity_info_zone']))
			{
				$this->t->set_var(array(
					'wf_process_name'	=> $this->process_name,
					'wf_process_version'	=> $this->process_version,
					'wf_instance_id'	=> $this->instance_id,
					'wf_instance_name'	=> $this->instance_name,
					'wf_owner'		=> $this->owner_name,
					'wf_activity_name'	=> $this->activity_name,
					'wf_user_name'		=> $this->user_name,
					'wf_date'		=> $GLOBALS['phpgw']->common->show_date(),
				));
				$this->translate_template('workflow_info_zone');
				$this->t->parse('info_zone', 'workflow_info_zone', true);
			}
			else
			{
				$this->t->set_var(array( 'info_zone' => ''));
			}
		}

	}
?>
