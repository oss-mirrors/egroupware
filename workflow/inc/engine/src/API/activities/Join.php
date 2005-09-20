<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
//!! Join
//! Join class
/*!
This class handles activities of type 'join'
*/
class Join extends BaseActivity {
	function Join(&$db)
	{
	 	parent::Base($db);
		$this->child_name = 'Join';
	}
}
?>
