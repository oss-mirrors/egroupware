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

  $pg = $phpgw->link($phpgw_info["server"]["webserver_url"]."/headlines/admin.php");
  echo "<A href=".$pg.">".lang("Edit headline sites")."</A><br>";
  $pg = $phpgw->link($phpgw_info["server"]["webserver_url"]."/headlines/preferences.php","editDefault=1");
  echo "<A href=".$pg.">".lang("Edit headlines shown by default")."</A>";

  section_end(); 
}
?>
