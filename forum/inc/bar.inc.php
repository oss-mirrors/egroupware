<?php
  /**************************************************************************\
  * phpGroupWare - Forums                                                    *
  * http://www.phpgroupware.org                                              *
  * Written by Jani Hirvinen <jpkh@shadownet.com>                            *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

 // Forums bar is created here

 echo "<font size=-1>";
 echo "[ <a href=" . $phpgw->link("post.php","$catfor&type=new&col=$col") . ">" . lang_forums("New Topic") . "</a> | ";
 if(!$col) echo "<a href=" . $phpgw->link("threads.php","$catfor&col=1") . ">" . lang_forums("View Threads") . "</a> | ";
 if($col) echo "<a href=" . $phpgw->link("threads.php","$catfor&col=0") . ">" . lang_forums("Collapse Threads") . "</a> | ";
 echo "<a href=" . $phpgw->link("search.php","$catfor") . ">" . lang_forums("Search") . "</a> ";

 if($phpgw_info["user"]["app_perms"][1]) 
  echo "| <a href=" . $phpgw->link("admin/") . ">" . lang_forums("Admin") . "</a>"; 
 
 echo "]</font><br><br>";


?>

