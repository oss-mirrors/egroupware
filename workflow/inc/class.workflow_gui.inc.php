<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'GUI' . SEP . 'GUI.php');

	class workflow_gui extends GUI
	{
		function workflow_gui()
		{
			parent::GUI($GLOBALS['phpgw']->ADOdb);
		}
	}
?>
