<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
//!! Start
//! Start class
/*!
This class handles activities of type 'start'
*/
class Start extends BaseActivity {
	function Start($db)
	{
	  $this->setDb($db);
	}
}
?>
