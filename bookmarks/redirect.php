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
  
  // NOTE: I will also make a preference for the frames part of things
  //       Also, this file needs templates

  $phpgw_info["flags"]= array("currentapp" => "bookmarks", "nonavbar" => True, "noheader" => True);
  include("../header.inc.php");

  $phpgw->db->query("select bm_info from phpgw_bookmarks where bm_id='$bm_id'",__LINE__,__FILE__);
  $phpgw->db->next_record();

  $ts = explode(",",$phpgw->db->f("bm_info"));
  $newtimestamp = sprintf("%s,%s,%s",$ts[0],time(),$ts[2]);

  $phpgw->db->query("update phpgw_bookmarks set bm_info='$newtimestamp', bm_visits=bm_visits+1 "
                  . "where bm_id='$bm_id'");

  if (isset($showheader)) {
     ?>
      <body bgcolor="FFFFFF">
      <table border="0" width="100%" cellpadding="0" cellspacing="0">
      <tr>
       <td>
        <img src="<?php echo $phpgw->common->get_image_path("phpgwapi"); ?>/logo.gif">
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
     $phpgw->common->phpgw_exit();
  }
?>
  <frameset rows="65,*" marginheight="0" marginwidth="0">
   <frame src="<?php echo $phpgw->link("redirect.php","showheader=True"); ?>" marginheight="0" marginwidth="0">
   <frame src="<?php echo $url; ?>" marginheight="0" marginwidth="0">
  </frameset>
<?php $phpgw->common->phpgw_footer(); ?>
