<?php
{
  $img = "/" . $appname . "/images/" . $appname .".gif";
  if (file_exists($phpgw_info["server"]["server_root"].$img)) {
    $img = $phpgw_info["server"]["webserver_url"].$img;
  } else {
    $img = "/" . $appname . "/images/navbar.gif";
    if (file_exists($phpgw_info["server"]["server_root"].$img)) {
      $img=$phpgw_info["server"]["webserver_url"].$img;
    } else {
    $img = "";
    }
  }
  section_start("Headlines",$img);

  echo '<a href="' . $phpgw->link('/headlines/admin.php') . '">' . lang('Edit headline sites') . '</a><br>';
  echo '<a href="' . $phpgw->link('/headlines/preferences.php','editDefault=1') . '">' . lang('Edit headlines shown by default') . '</a>';

  section_end(); 
}
?>
