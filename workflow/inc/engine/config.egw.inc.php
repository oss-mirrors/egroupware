<?php

/**
 * Configuration of the Galaxia Workflow Engine for E-Groupware
 */

// Common prefix used for all database table names, e.g. galaxia_
if (!defined('GALAXIA_TABLE_PREFIX')) {
		define('GALAXIA_TABLE_PREFIX', 'egw_wf_');
}

// Directory containing the Galaxia library, e.g. this directory
if (!defined('GALAXIA_LIBRARY')) {
		define('GALAXIA_LIBRARY', dirname(__FILE__));
}

define('SEP', '/');

// Specify how error messages should be shown
if (!function_exists('galaxia_show_error')) {
	/**
	* Show error messages from the engine
	* @param $msg is the message to show
	* @param $dying is true by default and will launch a terminal die command
	* @param $log is trru by default and will emit a PHP WARNING if set to true
	* if not set to false.
	*/
	function galaxia_show_error($msg, $dying = true, $log=true)
	{
		$final_message = htmlspecialchars("Galaxia Workflow Error: $msg");
		if ($log)
		{
			trigger_error($final_message, E_USER_WARNING);
		}
		if ($dying)
		{
			die($final_message);
		}
		else
		{
			echo("<br>$final_message");
		}
	}
}

$workflow_dir = $GLOBALS['egw_info']['server']['files_dir'].SEP.'workflow';

if (!file_exists($workflow_dir) && !mkdir($workflow_dir,0700,true) || !is_dir($workflow_dir) || !is_writable($workflow_dir))
{
	galaxia_show_error(lang('Base directory does not exist, please ask adminstrator to check the global configuration'),false, true);
}

// Directory where the Galaxia processes will be stored, e.g. /workflow in eGW's files dir
if (!defined('GALAXIA_PROCESSES'))
{
		// Note: this directory must be writeable by the webserver !
		define('GALAXIA_PROCESSES', $workflow_dir);
}

// Directory where a *copy* of the Galaxia activity templates will be stored, e.g. templates
// Define as '' if you don't want to copy templates elsewhere
if (!defined('GALAXIA_TEMPLATES')) {
		// Note: this directory must be writeable by the webserver !
		define('GALAXIA_TEMPLATES', '');
}

// Default header to be added to new activity templates
if (!defined('GALAXIA_TEMPLATE_HEADER')) {
		define('GALAXIA_TEMPLATE_HEADER', '');
}

// File where the ProcessManager logs for Galaxia will be saved, e.g. lib/Galaxia/log/pm.log
// Define as '' if you don't want to use logging
if (!defined('GALAXIA_LOGFILE')) {
		// Note: this file must be writeable by the webserver !
		//define('GALAXIA_LOGFILE', GALAXIA_LIBRARY . '/log/pm.log');
		define('GALAXIA_LOGFILE',  $workflow_dir.SEP.'galaxia.log');
}

// Directory containing the GraphViz 'dot' and 'neato' programs, in case
// your webserver can't find them via its PATH environment variable
if (!defined('GRAPHVIZ_BIN_DIR')) {
		define('GRAPHVIZ_BIN_DIR', '');
		//define('GRAPHVIZ_BIN_DIR', 'd:/wintools/ATT/GraphViz/bin');
}

// language function
function tra($msg, $m1='', $m2='', $m3='', $m4='')
{
	return lang($msg, $m1, $m2, $m3, $m4);
}


//define the list of agents avaible with your Galaxia installation
if (!function_exists('galaxia_get_agents_list'))
{
	/**
	 * * This function list the agents avaible with your galaxia installation. The name of an agent
	 * * is his unique identifier, the priority is an execution order priority
	*  * @return an associative array of agents description, each row is an agent description
	*  * containing a 'wf_agent_type' key and a 'wf_agent_priority' key
	 */
	function galaxia_get_agents_list()
	{
		$res = array(
			array(
				'wf_agent_type' => 'mail_smtp',
				'wf_agent_priority' => 1,
			)
		);
		$hooked_agents = $GLOBALS['egw']->hooks->process('workflow_agent');
		foreach($hooked_agents as $appname => $agent_list) {
			if(!is_array($agent_list)) {
				continue;
			}
			foreach($agent_list as $agent_array) {
				if($agent_array['wf_agent_type']) {
					$res[] = $agent_array;
				}
			}
		}
		return  $res;
	}
}



if (!function_exists('galaxia_user_can_admin_process'))
{
	//! Specify if the user has special admin rights on processes
	/**
	*  * @return true if the actual user has access to the processes administration.
	*  * ie. he can edit/activate/deactivate/create/destroy processes and activities
	*  * warning: dangerous rights, this user can do whatever PHP can do...
	 */
	function galaxia_user_can_admin_process()
	{
			return  (($GLOBALS['egw']->acl->check('run',1,'admin')) ||  ($GLOBALS['egw']->acl->check('admin_workflow',1,'workflow')));
	}
}

if (!function_exists('galaxia_user_can_admin_instance'))
{
	//! Specify if the user has special admin rights on instances
	/**
	*  * @return true if the actual user has access to the instance administration
	*  * ie. he can edit and modify all properties, members, assigned users of an instance whatever the state of the instance is
	*  * warning: this is clearly an administrator right
	 */
	function galaxia_user_can_admin_instance()
	{
		return  (($GLOBALS['egw']->acl->check('run',1,'admin')) ||  ($GLOBALS['egw']->acl->check('admin_instance_workflow',1,'workflow')));
	}
}


if (!function_exists('galaxia_user_can_clean_instances'))
{
	//! Specify if the user has special cleanup rights on ALL instances
	/**
	*  * @return true if the actual user is granted access to the 'clean instances' and 'clean all instances for a process' functions
	*  * warning: theses are dangerous functions!
	 */
	function galaxia_user_can_clean_instances()
	{
		return  (($GLOBALS['egw']->acl->check('run',1,'admin')) ||  ($GLOBALS['egw']->acl->check('cleanup_workflow',1,'workflow')));
	}
}

if (!function_exists('galaxia_user_can_clean_aborted_instances'))
{
	//! Specify if the actual user has special cleanup rights on aborted instances
	/**
	*  * @return true if the user is granted access to the 'clean aborted instances' functions
	 */
	function galaxia_user_can_clean_aborted_instances()
	{
		return  ((!$GLOBALS['egw']->acl->check('run',1,'admin')) ||  (!$GLOBALS['egw']->acl->check('cleanup_aborted_workflow',1,'workflow')));
	}
}

if (!function_exists('galaxia_user_can_monitor'))
{
	//! Specify if the user has special monitors rights
	/**
	*  * @return true if the actual user has access to the monitor screens (this is not sufficient for cleanup access)
	 */
	function galaxia_user_can_monitor()
	{
		return  (($GLOBALS['egw']->acl->check('run',1,'admin')) ||  ($GLOBALS['egw']->acl->check('monitor_workflow',1,'workflow')));
	}
}

	if (!function_exists('galaxia_retrieve_user_groups'))
	{
		/*!
		* Specify how to retrieve an array containing all groups id for a given user
		* if the user is in no group this function should return false
		* @param $user is the current user id
		* @return an arry of integers, the groups ids the user is member of, or false if the user is not
		* the member of any group
		*/
		function galaxia_retrieve_user_groups($user=0)
		{
			$user_groups=Array();
			if (!($user == $GLOBALS['egw_info']['user']['account_id']))
			{
				//we are asking groups membership for another user than the actually loaded in memory.
				$other_account =& CreateObject('phpgwapi.accounts',$user,'u');
				$memberships = $other_account->membership($user);
				unset($other_account);
			}
			else
			{
				// we are asking groups membership for the actual user
				// in egroupware we retrieve the already loaded in memory group list.
				$memberships = $GLOBALS['egw']->accounts->membership();
			}
			foreach((array)$memberships as $key => $value)
			{
				if (!( (is_null($value)) || ($value=='') )) $user_groups[]=($value['account_id']);
			}
			if (empty($user_groups)) return false;
			return $user_groups;
		}
	}


	if (!function_exists('galaxia_retrieve_group_users'))
	{
		//! Specify how to retrieve an array containing all users id for a given group id
		/**
		*  * @param $group the group id
		*  * @param $add_names false by default, if true we add user names in the result
		*  * return an array with all users id or an associative array with names associated with ids if $add_names is true
		 */
		function galaxia_retrieve_group_users($group, $add_names=false)
		{
			$members = $GLOBALS['egw']->accounts->member($group);
			foreach((array)$members as $key => $value)
			{
				if ($add_names)
				{
					$group_users[$value['account_id']] = $value['account_name'];
				}
				else
				{
					$group_users[]=($value['account_id']);
				}
			}

			return $group_users;
		}
	}

	if (!function_exists('galaxia_retrieve_running_user'))
{
	//! returns the actual user running this PHP code
	/**
	*  * @return the user id of the actual running user.
	 */
	function galaxia_retrieve_running_user()
	{
			return ($GLOBALS['egw_info']['user']['account_id']);
	}
}


	if (!function_exists('galaxia_retrieve_name'))
	{
		//! Specify how to retrieve the name of an user with is Id
		/**
		*  * @param $user the user or group id
		*  * return the name of the user
		 */
		function galaxia_retrieve_name($user)
		{
			$username = $GLOBALS['egw']->accounts->id2name($user);
			return $username;
		}
	}

// Specify how to execute a non-interactive activity (for use in src/API/Instance.php)
if (!function_exists('galaxia_execute_activity')) {
		/**
		 * * This function will execute automatic (non-interactive) activities for the engine
		*  * @param $activityId (default 0) is the activity_id it should run
		*  * @param $iid (default 0) is the instance id it should run for
		*  * @param $auto is true by default
		*  * @return AN ARRAY, or at least true or false. This array can contain :
		*	* a key 'failure' with an error string the engine will retrieve in instance error messages in case of
		*	failure (this will mark your execution as Bad),
		*	* a key 'debug' with a debug string the engine will retrieve in instance error messages,
		 */
		function galaxia_execute_activity($activityId = 0, $iid = 0, $auto = 1)
		{
			// This way we create a new run_activity instance for the next activity
			$run_activity =& CreateObject('workflow.run_activity.go');
			$data = $run_activity->go($activityId, $iid, $auto);
			return $data;
		}
}

/*
	Specify how to obtain stored config values
	Parameter: an array containing pairs of (variables_names => default values)
	For an unknown variable name it will return default_value and this
	default value will be the NEW STORED value. If no default value is
	given we assume it's a false.
	WARNING: you should cast your result if you bet its' an integer
	as it is maybe stored as a string. But 1 and 0 special values are
	handled correctly as ints (bools).
*/
if (!function_exists('galaxia_get_config_values'))
{
	function galaxia_get_config_values($parameters=array())
	{
			$config = config::read('workflow');

			$result_array = array();
			foreach ($parameters as $config_var => $default_value)
			{
				$config_value = $config[$config_var];
				if(isset($config_value))
				{ //we add something in the config store, we take it
					if ($config_value=='False')
					{
						$result_array[$config_var]=0;
					}
					elseif ($config_value=='True')
					{
						$result_array[$config_var]=1;
					}
					else
					{
						$result_array[$config_var] = $config_value;
					}
				}
				else
				{
					//we had no value stored yet, so we store it now
		//boolean warning: egw'config class is not storing false values if it is 0
					//we have to map theses int...
					$stored_value= (string)$default_value;
					if ($stored_value=='1')
					{
						$stored_value='True';
					}
					elseif ($stored_value=='0')
					{
						$stored_value='False';
					}

					config::save_value($config_var, $stored_value, 'workflow');
					// take the not casted variable
					$result_array[$config_var] = $default_value;
				}
			}
			unset($config);
			return $result_array;
	}
}

?>
