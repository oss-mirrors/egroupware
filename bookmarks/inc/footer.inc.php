<?php
  /**************************************************************************\
  * phpGroupWare - Bookmarks                                                 *
  * http://www.phpgroupware.org                                              *
  * Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
  *                     http://www.renaghan.com/bookmarker                   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  include($phpgw_info["server"]["server_root"] . "/bookmarks/inc/messages.inc.php");
  if (isset ($bk_output_html)) {
     $phpgw->template->set_var(MESSAGES, $bk_output_html);
  }

  $phpgw->template->parse("BODY", array("body", "standard"));
  $phpgw->template->p("BODY");
?>
