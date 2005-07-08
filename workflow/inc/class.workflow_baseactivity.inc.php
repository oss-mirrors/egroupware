<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'BaseActivity.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'activities' . SEP . 'Activity.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'activities' . SEP . 'End.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'activities' . SEP . 'Join.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'activities' . SEP . 'Split.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'activities' . SEP . 'Standalone.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'activities' . SEP . 'Start.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'activities' . SEP . 'SwitchActivity.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'common' . SEP . 'WfSecurity.php');

	class workflow_baseactivity extends BaseActivity
	{
		function workflow_baseactivity()
		{
			parent::BaseActivity($GLOBALS['phpgw']->ADOdb);
		}
	}
?>
