<?php
require_once(GALAXIA_LIBRARY.SEP.'src'.SEP.'API'.SEP.'BaseActivity.php');
//!! View
//! View class
/*!
This class handles activities of type 'view'
*/
class View extends BaseActivity 
{
	function View(&$db)
	{
		$this->child_name = 'View';
	 	parent::Base($db);
	}
	
}
?>
