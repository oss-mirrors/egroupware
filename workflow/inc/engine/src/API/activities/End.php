<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
//!! End
//! End class
/*!
This class handles activities of type 'end'
*/
class End extends BaseActivity {
	function End(&$db)
	{
		$this->child_name = 'End';
	 	parent::Base($db);
	}
}
?>
