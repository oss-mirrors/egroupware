<?php
  /**************************************************************************\
  * phpGroupWare - news headlines                                            *
  * http://www.phpgroupware.org                                              *
  * Written by Mark Peters <mpeters@satx.rr.com>                             *
  * Based on pheadlines 0.1 19991104 by Dan Steinman <dan@dansteinman.com>   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  $phpgw_info["flags"]["currentapp"] = "headlines";
  $phpgw_info["flags"]["disable_network_class"] = True;
  $phpgw_info["flags"]["disable_vfs_class"] = True;
  $phpgw_info["flags"]["disable_msg_class"] = True;
  include("../header.inc.php");
  
  $sql = "SELECT site FROM users_headlines "
       . "WHERE owner='" . $phpgw_info["user"]["userid"] . "'";
  
  $phpgw->db->query($sql);
  while ($phpgw->db->next_record()) {
    $sites[]=$phpgw->db->f(0);
  }

  $headlines = new headlines;
?>
<table width="99%" border="0" bordercolor="#ffffff" cellspacing="2" cellpadding="2">
<tr>
<?php
  for ($i=0;$i<sizeof($sites);$i++) {
    if ($i % 3 == 0) {
?>
</tr>
<tr>
<?php    
    }
    $headlines->readtable($sites[$i]);
?>
<td valign="top" width="33%" bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
<table width="99%" border="0" bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" cellpadding="0" cellspacing="0">
<tr>
<td valign="top">
<font color="<?php echo $phpgw_info["theme"]["th_text"]; ?>" size="+1">
<center><a href="<?php echo $headlines->base_url ?>" target="_new"><?php echo $headlines->display ?></a></center>
</font>
<ul>
<?php
    $links = $headlines->getLinks($sites[$i]);
    while (list($title,$link) = each($links)) {
?>
          <li><a href="<?php echo stripslashes($link) ?>" target="_new"><?php echo stripslashes($title) ?></a>
<?php
    }
?>
</ul>
</td>
</tr>
</table>
<?php
  }
?>
</tr>
</table>
<p>
<p>
<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
