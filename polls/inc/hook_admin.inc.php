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
  section_start("$appname",$img);

  $pg = $phpgw->link($phpgw_info["server"]["webserver_url"]."/".$appname."/admin.php");
  echo "<A href=".$pg.">".lang("Votting booth admin")."</A>";

  section_end(); 
}
?>
