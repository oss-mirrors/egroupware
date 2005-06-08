<?php

	include_once(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class run_activity extends workflow
	{
		var $public_functions = array(
			'go'	=> true,
		);

		var $base_activity;
		
		var $conf = array();

		var $process;

		function run_activity()
		{
			parent::workflow();
			$this->base_activity	=& CreateObject('workflow.workflow_baseactivity');
			$this->process		=& CreateObject('workflow.workflow_process');
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
			$activity = $this->base_activity->getActivity($activity_id);

			// load process
			$this->process->getProcess($activity->getProcessId());

			// instantiate instance class, but before set some global variables needed by it
			$GLOBALS['__activity_completed'] = false;
			$GLOBALS['user'] = $GLOBALS['phpgw_info']['user']['account_id'];
			$instance =& CreateObject('workflow.workflow_instance');

			//tests for access rights-----------------------------------------
			
			// Only check roles if this is an interactive activity
			if ($activity->isInteractive() == 'y' 
				// then verify roles, ownership and all defined access rules
				&& !($activity->checkUserAccess($GLOBALS['phpgw_info']['user']['account_id'] ))
			)
			{
				die(lang('You have not permission to execute this activity'));
			}

			// FIXME: not used anywhere?
			//$act_role_names = $activity->getActivityRoleNames($GLOBALS['phpgw_info']['user']['account_id'] );

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
					// and display completed template
					$this->t->set_file('activity_completed', 'activity_completed.tpl');

					$this->t->set_var(array(
						'wf_procname'	=> $this->process->getName(),
						'procversion'	=> $this->process->getVersion(),
						'actname'	=> $activity->getName(),
					));

					$this->translate_template('activity_completed');
					$this->t->pparse('output', 'activity_completed');
					$GLOBALS['phpgw']->common->phpgw_footer();
				}
				// it hasn't been completed
				else
				{
					//the activity is not completed
					// we loop on the form
					
					//get configuration options with default values if no init was done before
					$myconf = array(
						'use_automatic_parsing' 		=> 1,
						'show_activity_title' 			=> 1,
						'show_multiple_submit_as_select' 	=> 0,
					);
					$this->conf =& $this->process->getConfigValues($myconf);
				
					//set a global template for interactive activities
					$this->t->set_file('run_activity','run_activity.tpl');
					
					// draw the activity's title zone
					$this->parse_title($activity->getName());
					
					// draw the activity central user form
					$this->t->set_var(array('activity_template' => $template->parse('output', 'template')));
				
					//draw the select priority box
					// init priority to the requested one or the stored priority
					// the requested one handle the looping in activity form
					$priority = get_var('wf_priority','post',$instance->getPriority());
					$this->parse_priority($priority);
				
					//draw the activity submit buttons	
					$this->parse_submit();
				
					$this->t->pparse('output', 'run_activity');
					$GLOBALS['phpgw']->common->phpgw_footer();
				}
			}
		}

		//!Parse the title in the activity form, the user can decide if he want this title to be shown or not
		/*!
		You can give a title as a parameter. 
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
		
		//! Draw the priority select box in the activity form
		/*!
		Parse the priority select box in the activity form. The user can decide if he want this select box to be shown or not
		by setting $GLOBALS['workflow']['priority_array'].
		For example like that $GLOBALS['workflow']['priority_array']= array(1 => '1-Low',2 =>'2', 3 => '3-High');
		If the array is not set or the conf values says the user does not want automatic parsing no select box will be shown
		you should give actual priority as a parameter, else priority 1 will be selected.
		*/
		function parse_priority($actual_priority=1)
		{
			$this->t->set_block('run_activity', 'block_priority_options', 'priority_options');
			$this->t->set_block('run_activity', 'block_priority_zone', 'priority_zone');
			
			if ((!$this->conf['use_automatic_parsing']) || (!isset($GLOBALS['workflow']['priority_array'])))
			{
				//hide the priority zone
				$this->t->set_var(array( 'priority_zone' => ''));
			}
			else
			{
				$priority_array=$GLOBALS['workflow']['priority_array'];
				if (!is_array($priority_array))
				{
					$priority_array= explode(" ",$priority_array);
				}
				//handling the select box 
				foreach ($priority_array as $priority_level => $priority_label)
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
		
		//! Draw the submit buttons on the activity form
		/*!
		In this function we'll draw the command buttons asked for this activity.
		else we'll check $GLOBALS['workflow']['submit_array'] which should be defined
		in the activity sources and should be an array with the names of the submit options 
		corresponding to the value like this: 
		$GLOBALS['workflow']['submit_array']['the_value_you_want']=lang('going to next stage');
		if this array is not existing we'll draw a simple submit button.
		The poweruser can decide to handle theses buttons in his own way in the config section
		He'll then have to draw it himself in his activity template
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
				if (!(isset($GLOBALS['workflow']['submit_array'])))
				{
					//the user didn't give us any instruction
					// we draw a simple submit button
					$this->t->set_var(array('submit_area',''));
					$buttons .= '<td style="font-weight:bold; text-align=right;">';
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
					//retrieve infos set by the user in the activity source
					$submit_array = $GLOBALS['workflow']['submit_array'];
					//now we have another user choice. he can choose multiple submit buttons
					//or a select with only one submit
					if ( ($this->conf['show_multiple_submit_as_select']) && (count($submit_array) > 1) )
					{
						//multiple submits in a select box
						//handling the select box 
						foreach ($submit_array as $submit_button_name => $submit_button_value)
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
						foreach ($submit_array as $submit_button_name => $submit_button_value)
						{
						 	$buttons .= '<td style="font-weight:bold; text-align=right;">';
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
	}
?>
