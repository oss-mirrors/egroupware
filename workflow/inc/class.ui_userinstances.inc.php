<?php

	include(dirname(__FILE__) . SEP . 'class.workflow.inc.php');

	class ui_userinstances extends workflow
	{
		var $public_functions = array(
			'form'	=> true,
		);

		var $GUI;
		var $link_data;
		var $sort;
		var $sort_mode;
		var $order;
		var $start;
		var $offset;
		var $search_str;
		
		function ui_userinstances()
		{
			parent::workflow();
			$this->GUI =& CreateObject('workflow.workflow_gui');
		}

		function form()
		{
		//TODO: break it into small pieces
			// enable nextmatchs
			$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('User Instances');
			$GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
			//enable preferences
			$GLOBALS['phpgw']->preferences->read_repository();
			$myPrefs = $GLOBALS['phpgw_info']['user']['preferences'];
			// number of rows allowed
		        if ($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'] > 0) {
				$offset = $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'];
			}
			else{
				$offset = 15;
			}
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$this->t->set_file('user_instances', 'user_instances.tpl');

			$filter_process		= get_var('filter_process', 'any', '');
			//echo "<br>filter_process::".$filter_process;
				// we can filter activity by id (the list of activity call us with it) or by name
			$filter_activity	= get_var('filter_activity', 'any', 0);
			//echo "<br>filter_activity::".$filter_activity;
			$filter_activity_name	= get_var('filter_activity_name', 'any', '');
			//echo "<br>filter_activity_name::".$filter_activity_name;
			$filter_user		= get_var('filter_user', 'any', '');
			//echo "<br>filter_user::".$filter_user;
			$this->search_str		= get_var('search_str', 'any', '');
			//echo "<br>search_str::".$this->search_str;
			$advanced_search  	= get_var('advanced_search', 'any', false);
			if (!$advanced_search)
			{
				// check the Preferences of the workflow where the user can ask for the advanced mode
				$advanced_search = $myPrefs['workflow']['wf_instances_show_advanced_mode'];
			}
			//echo "<br>advanced::".$advanced_search;
			
			if ($advanced_search){
				$add_exception_instances	= get_var('add_exception_instances', 'any', false); 
				//echo "<br>add exception instances::".$add_exception_instances;
				$add_completed_instances	= get_var('add_completed_instances', 'any', false); 
				//echo "<br>add completed instances::".$add_completed_instances;
				$add_aborted_instances		= get_var('add_aborted_instances', 'any', false); 
				//echo "<br>add aborted instances::".$add_aborted_instances;
				$remove_active_instances	= get_var('remove_active_instances', 'any', false); 
				//echo "<br>remove_active instances::".$remove_active_instances;
				$filter_act_status		= get_var('filter_act_status', 'any', ''); 
				//echo "filter_act_status: ".$filter_act_status;
				$show_advanced_actions		= get_var('show_advanced_actions', 'any', false); 
				//echo "<br>show advanced actions::".$show_advanced_actions;
				if (!$show_advanced_actions)
				{
					// check the Preferences of the workflow where the user can ask for theses actions
					$show_advanced_actions= $myPrefs['workflow']['wf_instances_show_advanced_actions'];
				}
			} else {
				$add_exception_instances 	= false; 
				$add_completed_instances 	= false; 
				$add_aborted_instances 		= false; 
				$remove_active_instances 	= false; 
				$filter_act_status		= false;
				$show_advanced_actions		= false; 
			}
			$activity_id		= get_var('aid', 'any', 0);
			//echo "<br>activity id::".$activity_id;
			$instance_id		= get_var('iid', 'any', 0);
			//echo "<br>instance id::".$instance_id;
			$this->sort		= get_var('sort', 'any', 'asc');
			//echo "<br>sort::".$this->sort;
			$this->order		= get_var('order', 'any', 'wf_instance_id');
			//echo "<br>order::".$this->order;
			$this->start		= get_var('start', 'any', 0);
			//echo "<br>start::".$this->start;
			$this->sort_mode	= $this->order . '__' . $this->sort;	
			//echo "<br>sort_mode::".$this->sort_mode;
			$exception_comment	= get_var('exception_comment', 'any', '');
			//echo "<br>comment exception::".$exception_comment;
			// get user actions on the form
			$askGrab=get_var('grab','any',0);
			//echo "<br>grab::".$askGrab;
			$askRelease=get_var('release','any',0);
			//echo "<br>release::".$askRelease;
			$askAbort=get_var('abort','any',0);
			//echo "<br>abort::".$askAbort;
			$askSend=get_var('send','any',0);
			//echo "<br>send::".$askSend;
			$askException=get_var('exception','any',0);
			//echo "<br>exception::".$askException;
			$askResume=get_var('resume','any',0);
			//echo "<br>resume::".$askResume;
			// check preferences where the user can disable the view column
			$show_view_column= $myPrefs['workflow']['wf_instances_show_view_column'];
			//echo "<br>show_view_column::".$show_view_column;
			
			// this is not a POST or GET var, is defined in global prefs
			$this->offset		= $offset;
			//echo "<br>GLOBAL offset::".$this->offset;
			
		        // we have 2 different filters on activities, keeping only one
		        // we keep only activity_name as a valid filter, when asking for a particular id we assume that de process id
			// is set as well and we only keep the activity name
			// if someone sends us an activity id without the process id we could show him activities with the same name
			// on other processes (so with other id).
			if ($filter_activity != 0)
			{
				$tmpactivity =& CreateObject('workflow.workflow_baseactivity');
				$tmpact = $tmpactivity->getActivity($filter_activity);
				$filter_activity_name = $tmpact->getName();
				unset($tmpact);unset($tmpactivity);
			}
			$this->link_data = array
			(
				'menuaction' 			=> 'workflow.ui_userinstances.form',	
				'filter_process' 		=> $filter_process,
				'filter_activity_name' 		=> $filter_activity_name,
				'filter_user' 			=> $filter_user,
				'advanced_search' 		=> $advanced_search,
				'add_exception_instances' 	=> $add_exception_instances,
				'add_completed_instances' 	=> $add_completed_instances,
				'add_aborted_instances' 	=> $add_aborted_instances,
				'remove_active_instances' 	=> $remove_active_instances,
				'filter_act_status'		=> $filter_act_status,
				'show_advanced_actions'		=> $show_advanced_actions,
				'search_str' 			=> $this->search_str,
				'activity_id' 			=> $activity_id,
				'instance_id' 			=> $instance_id,
			);



                        // handling actions asked by the user on the form---------------------
			$GLOBALS['phpgw']->accounts->get_account_name($GLOBALS['phpgw_info']['user']['account_id'],$lid,$user_fname,$user_lname);

			//wf_message contains ui error messages
			$wf_message="";
			if ($askException)
			{
			        //TODO: add  a $system_comments = lang('exception raised by %1 %2: %3',$user_fname, $user_lname,$exception_comment);
			        // to the instance activity history
				if (!$this->GUI->gui_exception_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id)) 
				{
					$wf_message=lang("You don't have the rights necessary to exception this instance");
				}
			}

			// resume an exception instance
			if ($askResume)
			{
			        //TODO: add a $system_comments = lang('exception resumed by %1 %2: %3',$user_fname, $user_lname,$exception_comment);
			        // to the instance activity history  
				if (!$this->GUI->gui_resume_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id,system_comments)) 
				{
					$wf_message=lang("You don't have the rights necessary to resume this instance");
				}
			}

			// abort instance
			if ($askAbort)
			{
			        $system_comments = lang('aborted by %1 %2',$user_fname, $user_lname);
				if (!$this->GUI->gui_abort_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id, $system_comments)) 
				{
					$wf_message=lang("You don't have the rights necessary to abort this instance");
				}
			}

			// release instance
			if ($askRelease)
			{
				if (!$this->GUI->gui_release_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id)) 
				{
					$wf_message=lang("You don't have the rights necessary to release this instance");
				}
			}

			// grab instance
			if ($askGrab)
			{
				if (!$this->GUI->gui_grab_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id)) {
					$wf_message=lang("You don't have the rights necessary to grab this instance");
				}
			}

			// send instance (needed when an activity is not autorouted)
			if ($askSend)
			{
				if (!$this->GUI->gui_send_instance($GLOBALS['phpgw_info']['user']['account_id'], $activity_id, $instance_id)) 
				{
					$wf_message=lang("You don't have the rights necessary to send this instance");
				}
			}


		    // handling widgets on the form -------------------------------------------
		    $where = '';
		    $wheres = array();

		    // retrieve all user processes info - used by the 'select processes'
		    $all_processes = $this->GUI->gui_list_user_processes($GLOBALS['phpgw_info']['user']['account_id'],0, -1, 'wf_procname__asc', '', $where);
			//echo "all_processes: <pre>";print_r($all_processes);echo "</pre>";
			//echo "filter_act_status: <pre>";print_r($filter_act_status);echo "</pre>";

		    //(regis) adding a request for data in a select activity block
		    // we want only activities avaible for the selected process (filter on process to limit number of results)
		    // but when we are in advanced search mode we are not recomputing the search at every change on the processes select
		    // or on the activity select, so we can't recompute the select activity list every time the process changes
		    // in fact in this case we need __All__ avaible activities, but only in this case.
		    $where = '';
		    $wheres = array();
		    if (!($advanced_search)) 
		    {
			    if(isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) 
			    {
			    	$wheres[] = "gp.wf_p_id=" .$filter_process. "";
			    }
		    }
		    if( count($wheres) > 0 ) 
		    {
		   	$where = implode(' and ', $wheres);
		    }
  
		
		    // retrieve all user activities info (with the selected process) for the select
		    $all_activities = $this->GUI->gui_list_user_activities_by_unique_name($GLOBALS['phpgw_info']['user']['account_id'], 0, -1, 'ga.wf_name__asc', '', $where);

		    // there're 4 principal filters, process, activity (id/name), user and search --------------
		    // nothing to prepare for search, let's look the 3 others...
		    $where = '';
		    $wheres = array();
		    $or_wheres = array();
		    if(isset($_REQUEST['filter_process']) && $_REQUEST['filter_process'])
		    {  
		        // warning, need to filter process on instance table, not activity
		    	$wheres[] = "gi.wf_p_id=" .$filter_process. "";
		    }
		    if(isset($_REQUEST['filter_activity_name']) && $_REQUEST['filter_activity_name'])
		    {  
			$wheres[] = "ga.wf_name='" .$filter_activity_name. "'";
		    }
		    if(isset($_REQUEST['filter_user']) && $_REQUEST['filter_user'])
		    {
			$wheres[] = "gia.wf_user='".$filter_user."'";
		    }  
		    
		    // now adding special advanced search options or default values--------------------
		    
		    // TODO this should maybe go elsewhere, in a bo_ something or the engine
		    //instance selection :: instances can be active|exception|aborted|completed
		    if ($remove_active_instances) 
		    {
		    	// no active instances, it's an AND
			$wheres[] = "(gi.wf_status<>'active')";
		    } else 
		    {
			// default: we need active instances it's an OR with further instance selection	
			$or_wheres[]= "(gi.wf_status='active')";
		    }
		    // others are in OR mode
		    if ($add_exception_instances) 
		    {
			$or_wheres[] = "(gi.wf_status='exception')";
		    }
		    if ($add_aborted_instances) 
		    {
			$or_wheres[] = "(gi.wf_status='aborted')";
		    } 
		    if ($add_completed_instances) 
		    {
		    	$or_wheres[] = "(gi.wf_status='completed')";
		    }
		    $wheres[] = "(".implode(' or ', $or_wheres).")";
	
		    //activities selection :: activities are running OR completed OR NULL (for aborted instances for example) 
		    // and by default we keep all activities
		    if ($filter_act_status =='running') 
		    {
		    	$wheres[] = "(gia.wf_status='running')"; 
		    } 
		    elseif ($filter_act_status =='completed') 
		    { 
			$wheres[] = "(gia.wf_status='completed')"; 
		    } 
		    elseif ($filter_act_status =='empty') 
		    { 
		    	// we do not want completed or running activities
			$wheres[] = "(gia.wf_status is NULL)"; 
		    }

		    if( count($wheres) > 0 ) 
		    {
		        $where = implode(' and ', $wheres);
			//echo "<hr>where: <pre>";print_r($where);echo "</pre>";
		    }

		    // retrieve user instances
		    $instances = $this->GUI->gui_list_user_instances($GLOBALS['phpgw_info']['user']['account_id'], $this->start, $this->offset, $this->sort_mode, $this->search_str,$where);
		    $this->total_records = $instances['cant'];
		    //echo "instances: <pre>";print_r($instances);echo "</pre>";
		    
		    
		    //fill selection zones and vars------------------------------------------
		    $this->t->set_var('wf_message',$wf_message);
		    $this->t->set_var('advanced_search', ($advanced_search)? 'checked="checked"' : '');
		    $this->t->set_var('filters_on_change', ($advanced_search)? '' : 'onChange="this.form.submit();"');
		    $this->t->set_var('start',0);// comming back again to start point
		    // 3 selects 
		    $this->show_select_user($filter_user);	
		    $this->show_select_process($all_processes['data'], $filter_process);
		    $this->show_select_activity($all_activities['data'], $filter_activity_name);
		    // to keep informed of the 4 select values the second form (actions in the list)
		    // need additional vars4
		    $this->t->set_var('filter_user_id_set',$filter_user);
		    $this->t->set_var('filter_process_id_set',$filter_process);
		    $this->t->set_var('filter_activity_name_set',$filter_activity_name);
		    $this->t->set_var('filter_act_status_set',$filter_act_status);
		    // and the same for all checkboxes
		    $this->t->set_var('advanced_search_set', $advanced_search);
		    $this->t->set_var('add_exception_instances_set', $add_exception_instances);
		    $this->t->set_var('add_completed_instances_set', $add_completed_instances);
		    $this->t->set_var('add_aborted_instances_set', $add_aborted_instances);
		    $this->t->set_var('remove_active_instances_set', $remove_active_instances);
		    $this->t->set_var('show_advanced_actions_set', $show_advanced_actions);
		    // back to the first form, the advanced zone
		    if ($advanced_search) 
		    {
		        $this->t->set_file('Advanced_table_tpl','user_instances_advanced.tpl');
		     	$this->t->set_var('add_exception_instances', ($add_exception_instances)? 'checked="checked"' : '');
		     	$this->t->set_var('add_completed_instances', ($add_completed_instances)? 'checked="checked"' : '');
		     	$this->t->set_var('add_aborted_instances', ($add_aborted_instances)? 'checked="checked"' : '');
		     	$this->t->set_var('remove_active_instances', ($remove_active_instances)? 'checked="checked"' : '');
		     	$this->show_select_act_status($filter_act_status);
		     	$this->t->set_var('show_advanced_actions', ($show_advanced_actions)? 'checked="checked"' : '');
		     	$this->translate_template('Advanced_table_tpl');
		     	$this->t->parse('Advanced_table', 'Advanced_table_tpl');
		    } 
		    else 
		    {
		        $this->t->set_var('Advanced_table','');
		    }
		    //some lang text in javascript
		    $this->t->set_var('lang_Confirm_delete',lang('Confirm Delete'));
		    // and the view column defined in preferences
		    $this->t->set_var('header_view',($show_view_column)? '<td>'.lang('View').'</td><td>':'<td colspan="2">');

		    // Fill the final list of the instances we choosed in the template
		    $this->show_list_instances($instances['data'], $show_advanced_actions,$show_view_column);


		    // fill the general variables of the template
		    $this->t->set_var(array(
			'message'	=> implode('<br>', $this->message),
			'form_action'	=> $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form'),
			'search_str'	=> $this->search_str,
		    ));

		    $this->translate_template('user_instances');
		    $this->t->pparse('output', 'user_instances');
		    $GLOBALS['phpgw']->common->phpgw_footer();
		}



		function show_list_instances($instances_data, $show_advanced_actions = false, $show_view_column=true)
		{

			//------------------------------------------- nextmatch --------------------------------------------
			// left and right nextmatchs arrows
			$this->t->set_var('left',$this->nextmatchs->left(
				$link,$this->start,$this->total_records,$this->link_data));
			$this->t->set_var('right',$this->nextmatchs->right(
				$link,$this->start,$this->total_records,$this->link_data));
			//show table headers with sort
			//warning header names are header_[name or alias of the column in the query without a dot]
			//this is necessary for sorting
			foreach(array(
				'wf_instance_id'=> lang('id'),
				'wf_owner'	=> lang('Owner'),
				'insname'	=> lang('Name'),
				'wf_status'	=> lang('Inst. Status'),
				'wf_procname'	=> lang('Process'),
				'wf_name'	=> lang('Activity'),
				'wf_user'	=> lang('User'),
				'wf_act_status'	=> lang('Act. Status')
			       ) as $col => $translation) 
			{
				$this->t->set_var('header_'.$col,$this->nextmatchs->show_sort_order(
					$this->sort,$col,$this->order,'/index.php',$translation,$this->link_data));
			}
			
			// info about number of rows
			if (($this->total_records) > $this->offset)	
			{
				$this->t->set_var('lang_showing',lang('showing %1 - %2 of %3',
					1+$this->start,
					(($this->start+$this->offset) > ($this->total_records))? $this->total_records : $this->start+$this->offset,
					$this->total_records));
			}
			else 
			{
				$this->t->set_var('lang_showing', lang('showing %1',$this->total_records));
			}
			// --------------------------------------- end nextmatch ------------------------------------------

			$this->t->set_block('user_instances', 'block_list_instances', 'list_instances');
			foreach ($instances_data as $instance)
			{
			// all theses actions (most of them --monitor and run are GET links--) are handled by a javascript function 
			// 'submitAnInstanceLine' on the template which permit to send the activity and instance Ids 
			// (as we could do with a link) AND kepping all the others data filled in the form (using submit()) 

			  // Run instance
				// run the instance, the grab stuff is done in the run function
				if (   ($instance['wf_is_interactive'] == 'y') 
				    && ($instance['wf_status'] == 'active') 
				    && ( ($instance['wf_user'] == "*") || ($instance['wf_user'] == $GLOBALS['phpgw_info']['user']['account_id']) )
				   )
				{
				        $this->t->set_var('run',
				                          '<a href="'. $GLOBALS['phpgw']->link('/index.php', 
				                              'menuaction=workflow.run_activity.go&iid='.$instance['wf_instance_id'] 
				                              .'&activity_id='.$instance['wf_activity_id']).'"><img src="'
				                              .$GLOBALS['phpgw']->common->image('workflow', 'runform').'" alt="'.lang('run instance form') 
				                              .'" title="'.lang('run instance form').'"></a>');
				}
				else
				{
					$this->t->set_var('run', '');
				}
			// Send instance (no automatic routage)
				if ($instance['wf_is_autorouted'] == 'n' && $instance['wf_act_status'] == 'completed')
				{
					$this->t->set_var('send', 
						'<input type="image" src="'. $GLOBALS['phpgw']->common->image('workflow', 'linkto') 
						.'" name="send_instance" alt="'. lang('send instance') .'" title="'. lang('send instance') 
						.'" width="16" onClick="submitAnInstanceLine('. $instance['wf_instance_id'] .','. $instance['wf_activity_id'].',\'send\')">');
				}
				else
				{
					$this->t->set_var('send', '');
				}

				if ($show_advanced_actions) {
				// Resume exception instance
					if ($instance['wf_status'] == 'exception')
					{
					// for instances in exception state the owner or user can resume this instance 
						if (($instance['wf_owner'] == $GLOBALS['phpgw_info']['user']['account_id']) || ($instance['wf_user'] == $GLOBALS['phpgw_info']['user']['account_id'])) {
					        $this->t->set_var('resume', 
					        	'<input type="image" src="'. $GLOBALS['phpgw']->common->image('workflow', 'resume') 
					        	.'" name="resume_instance" alt="'. lang('resume instance') .'" title="'. lang('resume instance') 
					        	.'" width="16" onClick="submitAnInstanceLine('. $instance['wf_instance_id'] .','. $instance['wf_activity_id'].',\'resume\')">');
						} else {
					        	$this->t->set_var('resume', '');
						}
					}
					else
					{
						$this->t->set_var('resume', '');
					}
					
				// Exception instance
					// you can exception an instance which is not already exception, not aborted and if
					// you're the owner or user
					if ($instance['wf_status'] != 'aborted' && $instance['wf_status'] != 'exception' && (($instance['wf_owner'] == $GLOBALS['phpgw_info']['user']['account_id']) || ($instance['wf_user'] == $GLOBALS['phpgw_info']['user']['account_id'])))
					{
					        $this->t->set_var('exception', 
					        	'<input type="image" src="'. $GLOBALS['phpgw']->common->image('workflow', 'tostop') 
					        	.'" name="exception_instance" alt="'. lang('exception this instance') .'" title="'. lang('exception this instance') 
					        	.'" width="16" onClick="submitAnInstanceLine('. $instance['wf_instance_id'] .','. $instance['wf_activity_id'].',\'exception\')">');
					}
					else
					{
						$this->t->set_var('exception', '');
					}
				
				
				// Abort instance
					// aborting an instance is avaible for the owner or the user of an instance
					if ($instance['wf_status'] != 'aborted' && (($instance['wf_owner'] == $GLOBALS['phpgw_info']['user']['account_id']) || ($instance['wf_user'] == $GLOBALS['phpgw_info']['user']['account_id'])))
					{
					        $this->t->set_var('abort', 
					        	'<input type="image" src="'. $GLOBALS['phpgw']->common->image('workflow', 'totrash') 
					        	.'" name="abort_instance" alt="'. lang('abort instance') .'" title="'. lang('abort instance') 
					        	.'" width="16" onClick="submitAnInstanceLine('. $instance['wf_instance_id'] .','. $instance['wf_activity_id'].',\'abort\')">');
					}
					else
					{
						$this->t->set_var('abort', '');
					}
					
				// Grabb or Release instance
					if ($instance['wf_user'] == '*' && $instance['wf_status'] == 'active')
					{//the instance is not yet grabbed by anyone and we have rights to grabb it (if we don't we wont be able to do it)
					        //(regis) seems better for me to show a float status when you want to grab, cause this is the actual state
					        // and the user understand better this way the metaphore
					        $this->t->set_var('grab_or_release', 
					        	'<input type="image" src="'. $GLOBALS['phpgw']->common->image('workflow', 'float') 
					        	.'" name="grab_instance" alt="'. lang('grab instance') .'" title="'. lang('grab instance') 
					        	.'" width="16" onClick="submitAnInstanceLine('. $instance['wf_instance_id'] .','. $instance['wf_activity_id'].',\'grab\')">');
						//$this->t->set_var('grab_or_release', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&grab=1&iid='. $instance['wf_instance_id'] .'&aid='. $instance['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'float') .'" alt="'. lang('grab instance') .'" title="'. lang('grab instance') .'" /></a>');
					}
					elseif ($instance['wf_status'] == 'active')
					{
					        //(regis) seems better for me to show a fix status when you want to release, cause this is the actual state
					        $this->t->set_var('grab_or_release', 
					        	'<input type="image" src="'. $GLOBALS['phpgw']->common->image('workflow', 'fix') 
					        	.'" name="release_instance" alt="'. lang('release instance') .'" title="'. lang('release instance') 
					        	.'" width="16" onClick="submitAnInstanceLine('. $instance['wf_instance_id'] .','. $instance['wf_activity_id'].',\'release\')">');
						//$this->t->set_var('grab_or_release', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_userinstances.form&release=1&iid='. $instance['wf_instance_id'] .'&aid='. $instance['wf_activity_id']) .'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'fix') .'" alt="'. lang('release instance') .'" title="'. lang('release instance') .'" /></a>');
					}
					else
					{
						$this->t->set_var('grab_or_release', '');
					}

					// Monitor instances : we always show it in advanced mode, the user action will be bloacked
					// by acl if he has no rights on it.
					$this->t->set_var('monitor', '<a href="'. $GLOBALS['phpgw']->link('/index.php', 'menuaction=workflow.ui_admininstance.form&iid='. $instance['wf_instance_id']).'"><img src="'. $GLOBALS['phpgw']->common->image('workflow', 'monitorinstance') .'" alt="'. lang('monitor instance') .'" title="'. lang('monitor instance') .'" /></a>');
					
				} else //not in advanced_actions mode
				{
					$this->t->set_var('grab_or_release', '');
					$this->t->set_var('exception', '');
					$this->t->set_var('resume', '');
					$this->t->set_var('abort', '');
					$this->t->set_var('monitor', '');
				}
				$GLOBALS['phpgw']->accounts->get_account_name($instance['wf_owner'],$lid,$fname_owner,$lname_owner);
				$GLOBALS['phpgw']->accounts->get_account_name($instance['wf_user'],$lid,$fname_user,$lname_user);
				if ($instance['wf_user'] == "*") 
				{ // case for non assigned instances
				  $shownuser = "*";
				}
				elseif ($instance['wf_user'] == "") 
				{ // case for aborted instances
				  $shownuser = lang('none');
				}
				else 
				{ // all others
				  $shownuser = $fname_user . ' ' . $lname_user;
				}
				
				// managing 4 types of instance status in a graphical manner
				if ($instance['wf_status'] == 'active') 
				{
					$graphical_status = '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'ok') .'" alt="'. lang('active') .'" title="'. lang('active') .'" />';
				} 
				elseif ($instance['wf_status'] == 'exception') 
				{
					$graphical_status = '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'stop') .'" alt="'. lang('exception') .'" title="'. lang('exception') .'" />';				
				} 
				elseif ($instance['wf_status'] == 'aborted') 
				{
					$graphical_status = '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'trash') .'" alt="'. lang('aborted') .'" title="'. lang('aborted') .'" />';				
				} 
				elseif ($instance['wf_status'] == 'completed') 
				{
					$graphical_status = '<img src="'.$GLOBALS['phpgw']->common->image('workflow', 'completed') .'" alt="'. lang('completed') .'" title="'. lang('completed') .'" />';				
				}
				
				
				$this->t->set_var(array(
					'instance_id'		=> $instance['wf_instance_id'],
					'owner'			=> $fname_owner . ' ' . $lname_owner,
					'insname'		=> $instance['insname'],
					'status'		=> $graphical_status,
					'wf_procname'		=> $instance['wf_procname'],
					'version'		=> $instance['wf_version'],
					'act_icon'		=> $this->act_icon($instance['wf_type'],$instance['wf_is_interactive']),
					'name'			=> $instance['wf_name'],
					'user'			=> $shownuser,
					'act_status'		=> $instance['wf_act_status'],
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color)
				));

				// and the view column defined in preferences
				if ($show_view_column)
				{
					$mylink=$GLOBALS['phpgw']->link('/index.php','menuaction=workflow.ui_userviewinstance.form&iid='.$instance['wf_instance_id']);
					$this->t->set_var('column_view','<td align="center"><a href="'.$mylink.'">'.lang('View').'</a></td><td>');
				}
				else
				{
					$this->t->set_var('column_view','<td colspan="2">');
				}

				// finally parse this list
				$this->t->parse('list_instances', 'block_list_instances', true);
			}
				
			if (!count($instances_data)) $this->t->set_var('list_instances', '<tr><td colspan="8" align="center">'. lang('There are no instances available') .'</td></tr>');
		}

		function show_select_process($all_processes_data, $filter_process)
		{
			$this->t->set_block('user_instances', 'block_select_process', 'select_process');
			$this->t->set_var('selected_filter_process_all', (!$filter_process)? 'selected="selected"' : '');

			foreach ($all_processes_data as $process_data)
			{
				$this->t->set_var(array(
					'selected_filter_process'	=> ($filter_process == $process_data['wf_p_id'])? 'selected="selected"' : '',
					'filter_process_id'		=> $process_data['wf_p_id'],
					'filter_process_name'		=> $process_data['wf_procname'],
					'filter_process_version'	=> $process_data['version']
				));
				$this->t->parse('select_process', 'block_select_process', true);
			}
		}

		function show_select_activity($all_activitys_data, $filter_activity)
		{
			$this->t->set_block('user_instances', 'block_select_activity', 'select_activity');
			$this->t->set_var('selected_filter_activity_all', (!($filter_activity))? 'selected="selected"' : '');

			foreach ($all_activitys_data as $activity_data)
			{
				$this->t->set_var(array(
					'selected_filter_activity'	=> ($filter_activity == $activity_data['wf_name'])? 'selected="selected"' : '',
					'filter_activity_name'		=> $activity_data['wf_name']
				));
				$this->t->parse('select_activity', 'block_select_activity', true);
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
				'filter_user_name'	=> $fname . ' ' . $lname
			));
		}

		function show_select_act_status($filter_act_status)
		{
			$this->t->set_var(array(
				'filter_act_status_all'	=> ($filter_act_status == '')? 'selected="selected"' : '',
				'filter_act_status_running'	=> ($filter_act_status == 'running')? 'selected="selected"' : '',
				'filter_act_status_completed'	=> ($filter_act_status == 'completed')? 'selected="selected"' : '',
				'filter_act_status_empty'	=> ($filter_act_status == 'empty')? 'selected="selected"' : '',
			));
		}
	}
?>
