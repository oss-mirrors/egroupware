<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessManager' . SEP . 'InstanceManager.php');

	class workflow_instancemanager extends InstanceManager
	{
		function workflow_instancemanager()
		{
			parent::InstanceManager($GLOBALS['egw']->ADOdb);
		}
	}
?>
