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

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "headlines", "enable_network_class" => True, "noheader" => True, "nonavbar" => True );
  include("../header.inc.php");

  if(!count($phpgw_info["user"]["preferences"]["headlines"])) {
    Header("Location: ".$phpgw->link("/headlines/preferences.php"));
  } else {
    $phpgw->common->phpgw_header();
    echo parse_navbar();
  }

  $i = 0;
  while ($preference = each($phpgw_info["user"]["preferences"]["headlines"])) {
     $sites[$i++] = $preference[0];
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
<?php $phpgw->common->phpgw_footer(); ?>
