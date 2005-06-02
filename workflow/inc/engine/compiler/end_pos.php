<?php
//Code to be executed after the end activity
// we save name and activity only when the instance
// has been completed
if ($GLOBALS['__activity_completed'])
{
  if(isset($_REQUEST['wf_name'])){
    $instance->setName($_REQUEST['wf_name']);
  }
  if(isset($_REQUEST['wf_priority'])){
    $instance->setPriority((int)$_REQUEST['wf_priority']);
  }
}

?>
