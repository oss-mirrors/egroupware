<?php
  /**************************************************************************\
  * phpGroupWare - User manual                                               *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "manual", "enable_utilities_class" => True);
  include("../header.inc.php");
?>
<table cellpadding="10" width=20%>
<td>
<font face="Arial, Helvetica, san-serif" size="2">
Note: Some of this information is out of date<br>
<?php
  $phpgw->utilities->menutree->showtree("mymenu.txt");
//  $treefile = "mymenu.txt";
//  require "./menutree.inc";
?>
</td>
</table>
<?php include($phpgw_info["server"]["api_dir"] . "/footer.inc.php"); ?>