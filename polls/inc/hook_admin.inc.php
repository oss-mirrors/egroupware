<?php
{
  section_start($appname,$phpgw_info['server']['webserver_url'] . '/polls/templates/'
              . $phpgw_info['server']['template_set'] . '/images/navbar.gif');

  echo '<a href="' . $phpgw->link('/polls/admin.php') . '">' . lang('Voting booth admin') . '</a>';

  section_end(); 
}
?>
