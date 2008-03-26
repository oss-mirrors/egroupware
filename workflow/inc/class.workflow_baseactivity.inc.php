<?php
	// include galaxia's configuration tailored to egroupware
	require_once('engine/config.egw.inc.php');

	require_once(GALAXIA_LIBRARY . SEP . 'src' . SEP . 'API' . SEP . 'BaseActivity.php');

	class workflow_baseactivity extends BaseActivity
	{
		function workflow_baseactivity()
		{
			parent::BaseActivity($GLOBALS['egw']->db->link_id());
		}
	}
?>
