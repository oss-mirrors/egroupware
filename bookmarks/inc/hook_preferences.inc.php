<?php
  /**************************************************************************\
  * phpGroupWare - Bookmarks                                                 *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
{
  section_start($appname,$phpgw_info["server"]["webserver_url"] . "/bookmarks/templates/"
              . $phpgw_info["server"]["template_set"] . "/images/navbar.gif");

  echo "<a href=" . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/bookmarks/preferences.php")
     . ">" . lang("Bookmark preferences") . "</a>";

  echo "<br><a href=" . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/bookmarks/categories.php","type=category")
     . ">" . lang("Bookmark categorys") . "</a>";

  echo "<br><a href=" . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/bookmarks/categories.php","type=subcategory")
     . ">" . lang("Bookmark sub-categorys") . "</a>";


  section_end(); 
}
?>
