<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'ActivityManager.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'GraphViz.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'ProcessManager.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'common' . SEP . 'WfSecurity.php');

	class workflow_activitymanager extends ActivityManager
	{
		function workflow_activitymanager()
		{
			parent::ActivityManager($GLOBALS['phpgw']->ADOdb);
		}
	}
?>
