<?php
  $
  $img = $phpgw_info["server"]["webserver_url"] . $appname . "/images/navbar.gif";
  section_start("Forum",$img);
  echo "<a HREF=" . $phpgw->link($phpgw_info["server"]["webserver_url"] ."/" . $appname ."/" . "admin/index.php") . ">";
  echo lang("Change Forum settings") . "</a>";
  section_end();
?>
