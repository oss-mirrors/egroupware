<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'Instance.php');

	class workflow_instance extends Instance
	{
		function workflow_Instance()
		{
			parent::Instance($GLOBALS['phpgw']->ADOdb);
		}
	}
?>
