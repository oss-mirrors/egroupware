<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'Process.php');

	class workflow_process extends Process
	{
		function workflow_process()
		{
			parent::Process($GLOBALS['egw']->db->link_id());
		}
	}
?>
