<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'ProcessMonitor' . SEP . 'ProcessMonitor.php');

	class workflow_processmonitor extends ProcessMonitor
	{
		function workflow_processmonitor()
		{
			parent::ProcessMonitor($GLOBALS['egw']->db->link_id());
		}
	}
?>
