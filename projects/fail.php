<?php
  /**************************************************************************\
  * phpGroupWare - Projects                                                  *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */
  
  $phpgw_info["flags"] = array("currentapp" => projects,
                               "noheader" => True,
                               "nonavbar"   => True);

  include("../header.inc.php");

echo "<p><center>" . lang("You have to CREATE a delivery or invoice first !");
echo "</center>";
 ?>
