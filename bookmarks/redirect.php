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
  
  // NOTE: This will handle updating the database with the last vist in the future.
  //       I will also make a preference for the frames part of things
  $phpgw_info["flags"]= array("currentapp" => "bookmarks", "nonavbar" => True, "noheader" => True);
  include("../header.inc.php");
  
  if (isset($showheader)) {
     ?>
      <body bgcolor="FFFFFF">
      <table border="0" width="100%" cellpadding="0" cellspacing="0">
      <tr>
       <td>
        <img src="<?php echo $phpgw_info["server"]["webserver_url"]; ?>/phpGroupWare.jpg">
       </td>
       <td>
        <?php 
          echo lang("You are viewing this site outside of phpGroupWare") . '<br>'
             . lang("close this window to return");
        ?>
       </td>
      </tr>
     </table>
     <?php
     exit;
  }
?>
  <frameset rows="65,*" marginheight="0" marginwidth="0">
   <frame src="<?php echo $phpgw->link("redirect.php","showheader=True"); ?>" marginheight="0" marginwidth="0">
   <frame src="<?php echo $url; ?>" marginheight="0" marginwidth="0">
  </frameset>
