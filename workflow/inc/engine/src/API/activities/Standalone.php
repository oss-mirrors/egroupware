<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
//!! Standalone
//! Standalone class
/*!
This class handles activities of type 'standalone'
*/
class Standalone extends BaseActivity {
	function Standalone(&$db)
	{
	 	parent::Base($db);
		$this->child_name = 'Standalone';
	}
}
?>
