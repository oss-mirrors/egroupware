<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'common' . SEP . 'WfSecurity.php');

	class workflow_wfsecurity extends WfSecurity
	{
		function workflow_wfsecurity()
		{
			parent::WfSecurity($GLOBALS['phpgw']->ADOdb);
		}
	}
?>
