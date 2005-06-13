<?php
//Code to be executed before a switch activity
// If we didn't retrieve the instance before
if(empty($instance->instanceId)) {
  // This activity needs an instance to be passed to 
  // be started, so get the instance into $instance.
  if(isset($_REQUEST['iid'])) {
    $instance->getInstance($_REQUEST['iid']);
  } else {
    // defined in lib/Galaxia/config.php
    galaxia_show_error(lang("No instance indicated"));
    die;  
  }
}
if (!($GLOBALS['workflow']['__leave_activity']))
{
  // Set the current user for this activity
  if(isset($GLOBALS['user']) && !empty($instance->instanceId) && !empty($activity_id)) 
  {
    if ($activity->isInteractive())
    {// activity is interactive and we want the form, we'll try to grab the ticket on this instance-activity
      if (!$instance->setActivityUser($activity_id,$GLOBALS['user']))
      {
         galaxia_show_error(lang("You do not have the right to run this activity anymore, maybe a concurrent access problem, refresh your datas."));
         die;
      }
    }// if activity is not interactive there's no need to grab the token
  }
  else
  {
    galaxia_show_error(lang("We cannot run this activity, maybe this instance or this activity do not exists anymore."));
    die;
  }
}

?>
