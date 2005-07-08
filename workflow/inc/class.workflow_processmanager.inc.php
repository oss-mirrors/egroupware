<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'ProcessManager.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'RoleManager.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'ActivityManager.php');
	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'Process.php');

	class workflow_processmanager extends ProcessManager
	{
		function workflow_processmanager()
		{
			parent::ProcessManager($GLOBALS['phpgw']->ADOdb);
		}
	}
?>
