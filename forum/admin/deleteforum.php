<?php
  /**************************************************************************\
 
  \**************************************************************************/

  $phpgw_info["flags"] = array("currentapp" => "forum", "enable_nextmatchs_class" => True);
  if($confirm || ! $for_id) {
    $phpgw_info["flags"]["noheader"] = True;
    $phpgw_info["flags"]["nonavbar"] = True;
  }
  include("../../header.inc.php");

$phpgw->template->set_file('DELETEFORUM','admin.deleteforum.tpl');


  if (($for_id) && (! $confirm)) {
    $phpgw->template->set_var(array(
  		'ARE_U_SURE' 	=> lang("Are you sure you want to delete this forum?"),
  		'ALL_DEL'	=> lang("All user posts and topics will be lost!"),
  		'NO'		=> lang("No"),
  		'YES'		=> lang("Yes"),
  		'NO_LINK'	=> $phpgw->link("/forum/admin/index.php"),
  		'YES_LINK'	=> $phpgw->link("/forum/admin/deleteforum.php","for_id=$for_id&confirm=true")
  		));

    $phpgw->template->pfp('Out','DELETEFORUM');	
    $phpgw->common->phpgw_footer();
  }

  if (($for_id) && ($confirm)) {
    $phpgw->db->query("delete from f_threads where for_id=$for_id");
    $phpgw->db->query("delete from f_body where for_id=$for_id");
    $phpgw->db->query("delete from f_forums where id=$for_id");

    Header("Location: " . $phpgw->link("/forum/admin/index.php"));
  }
?>
