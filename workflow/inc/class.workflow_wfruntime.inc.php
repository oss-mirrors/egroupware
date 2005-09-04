<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'common' . SEP . 'WfRuntime.php');

	class workflow_wfruntime extends WfRuntime
	{
		function workflow_wfruntime()
		{
			parent::WfRuntime($GLOBALS['phpgw']->ADOdb);
		}
	}
?>
