<?php
  /**************************************************************************\
  * phpGroupWare - administration                                            *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  if ($confirm) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "admin";
  $phpgw_info["flags"]["parent_page"] = "admin.php";
  include("../header.inc.php");

  function remove_account_data($query,$t)
  {
    global $phpgw;
    $phpgw->db->query("delete from $t where $query");
  }
  
  if (($con) && (! $confirm)) {
?>
     <center>
      <table border=0 with=65%>
       <tr colspan=2>
        <td align=center>
         <?php echo lang("Are you sure you want to delete this news site ?"); ?>
        <td>
       </tr>
       <tr>
         <td>
           <a href="<?php echo $phpgw->link("admin.php") . "\">" . lang("No"); ?></a>
         </td>
         <td>
           <a href="<?php echo $phpgw->link("deleteheadline.php","con=$con&confirm=true") . "\">" . lang("Yes"); ?></a>
         </td>
       </tr>
      </table>
     </center>
<?php
    $phpgw->common->phpgw_footer();
  } else {
    $table_locks = array('news_site','news_headlines');
    $phpgw->db->lock($table_locks);

    remove_account_data("con=$con","news_site");
    remove_account_data("site=$con","news_headlines");
    $phpgw->db->unlock();

    $phpgw->db->query("SELECT * FROM preferences");
    while($phpgw->db->next_record()) {
      if($phpgw->db->f("preference_owner") == $phpgw_info["user"]["account_id"]) {
	if($phpgw_info["user"]["preferences"]["headlines"]["$con"]) {
	  $phpgw->preferences->delete("headlines",$con);
	  $phpgw->preferences->commit();
	}
      } else {
	$phpgw_newuser["user"]["preferences"] = $phpgw->db->f("preference_value");
	if($phpgw_newuser["user"]["preferences"]["headlines"]["$con"]) {
	  $phpgw->preferences->delete_newuser("headlines",$con);
	  $phpgw->preferences->commit_user($phpgw->db->f("preference_owner"));	
	}
      }
    }

    Header("Location: " . $phpgw->link("admin.php","cd=16"));
  }
?>
