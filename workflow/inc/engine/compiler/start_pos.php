<?php
//Code to be executed after a start activity
if(isset($_REQUEST['wf_name']))
  $instance->setName($_REQUEST['wf_name']);
?>
