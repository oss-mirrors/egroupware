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
  section_start("Polls",$img);

  $pg = $phpgw->link($phpgw_info["server"]["webserver_url"]."/polls/admin.php");
  echo "<A href=".$pg.">".lang("Polls")."</A>";

  section_end(); 
}
?>
