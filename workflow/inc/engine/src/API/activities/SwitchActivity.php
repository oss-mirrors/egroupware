<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
//!! SwitchActivity
//! SwitchActivity class
/*!
This class handles activities of type 'switch'
*/
class SwitchActivity extends BaseActivity {
	function SwitchActivity(&$db)
	{
	  $this->setDb($db);
	}
}
?>
