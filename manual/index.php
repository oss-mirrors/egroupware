<?php
  /**************************************************************************\
  * phpGroupWare module (File Manager)                                       *
  * http://www.phpgroupware.org                                              *
  * Written by Dan Kuykendall <dan@kuykendall.org>                           *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  $phpgw_info["flags"] = array("currentapp" => "manual");
  include("../header.inc.php");
?>
<table cellpadding="10" width=20%>
<td>
<font face="Arial, Helvetica, san-serif" size="2">
<?php
  $treefile = "mymenu.txt";

  require "./menutree.inc";
?>
</td>
</table>
<?php include($phpgw_info["server"]["api_dir"] . "/footer.inc.php"); ?>