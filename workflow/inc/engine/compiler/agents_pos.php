<?php
//Code to be executed after an activity by agents
if (!($GLOBALS['__activity_completed']))
{
  foreach ($this->agents as $agent_type => $ui_agent)
  {
    if(!($ui_agent->run_activity_pos()))
    {
      galaxia_show_error($ui_agent->get_error(), false);
    }

  }
}
else
{
  foreach ($this->agents as $agent_type => $ui_agent)
  {
    if (!($ui_agent->run_activity_completed_pos()))
    {
      galaxia_show_error($ui_agent->get_error(), false);
    }
  }
}
?>
