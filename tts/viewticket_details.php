<?php

  if ($submit) {
     $phpgw_flags = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_flags["currentapp"] = "tts";
  include("../header.inc.php");
  function group_list($db,$currgroup)
  {
    $db->query("select group_name from groups");
    while ($db->next_record()) {
      $tag="";
      if ($db->f(0) == "$currgroup") { $tag = "selected"; }
      echo "<option value=\"" . $db->f(0) . "\" $tag>" . $db->f(0) . "</option>\n";
    }
  }

  function groupusers_list($db,$curruser)
  {
    $db->query("select loginid from accounts");
    $db->next_record();
	echo "<option value=none ";
	if($db->f(0) == "none")
		echo "SELECTED ";
	echo ">none</option>";
    $db->query("select loginid from accounts"); // no db::rewind function?
    while ($db->next_record()) {
      $tag="";
      if ($db->f(0) == "$curruser") { $tag = "selected"; }
      echo "<option value=\"" . $db->f(0) . "\" $tag>" . $db->f(0) . "</option>\n";
    }
  }

  if (! $submit) {
// select the ticket that you selected
$phpgw->db->query("select t_id,t_category,t_detail,t_priority,t_user,t_assignedto,"
	    . "t_timestamp_opened, t_timestamp_closed, t_subject from ticket where t_id='$ticketid'");
$phpgw->db->next_record();

$lstAssignedto=$phpgw->db->f(5);
$lstCategory=$phpgw->db->f(1);

// Print the table
?>
<form method="POST" action="viewticket_details.php">
 <?php echo $phpgw->session->hidden_var(); ?>
 <input type=hidden value="<?php echo $phpgw->db->f(0); ?>" name="t_id">
 <input type=hidden value="<?php echo $phpgw->db->f(4); ?>" name="lstAssignedfrom">
  <div align=center>
   <center>
    <table border=0 width="80%" bgcolor="<?php echo $phpgw_info["theme"][th_bg]; ?>" cellspacing=0>
     <tr>
       <td width=33%>&nbsp;</td>
       <td width=33%>&nbsp;</td>
       <td width=33%>&nbsp;</td>
     </tr>
     <tr>
       <td colspan=3 align=center><font size=+2><?php echo lang_tts("View Job Detail"); ?></font></td>
     </tr>
     <tr>
       <td colspan=3 align=center><hr noshade></td>
     </tr>
     <tr>
       <td align=center>
         <font size=3>ID: <b><?php echo $phpgw->db->f(0); ?></b>
       </td>
       <td align=center>
         <?php echo lang_tts("Assigned from"); ?>: <br><b><?php echo $phpgw->db->f(4);?></b>
       </td>
       <td align=center>
         <?php echo lang_tts("Open Date"); ?>: <br><b><?php echo $phpgw->common->show_date($phpgw->db->f(6)); ?></b>
         <br>
         <?php echo lang_tts("Close Date"); ?>: <br><b><?php
                        if ($phpgw->db->f(7) > 0) {
                          echo $phpgw->common->show_date($phpgw->db->f(7));
                        } else {
                          echo lang_tts("in progress");
                        }
                      ?></b>
       </td>

     </tr>
     <tr>
       <td colspan=3 align=center><hr noshade></td>
     </tr>
     <tr>
       <td align=center>
         <?php // Choose the correct priority to display
           $prority_selected[$phpgw->db->f("t_priority")] = " selected";
         ?>
         <b><?php echo lang_tts("Priority"); ?>:</b>
         <select name="optPriority">
           <option value="1"<?php echo $prority_selected[1]; ?>>1 - Lowest</option>
           <option value="2"<?php echo $prority_selected[2]; ?>>2</option>
           <option value="3"<?php echo $prority_selected[3]; ?>>3</option>
           <option value="4"<?php echo $prority_selected[4]; ?>>4</option>
           <option value="5"<?php echo $prority_selected[5]; ?>>5 - Medium</option>
           <option value="6"<?php echo $prority_selected[6]; ?>>6</option>
           <option value="7"<?php echo $prority_selected[7]; ?>>7</option>
           <option value="8"<?php echo $prority_selected[8]; ?>>8</option>
           <option value="9"<?php echo $prority_selected[9]; ?>>9</option>
           <option value="10"<?php echo $prority_selected[10]; ?>>10 - Highest</option>
         </select>

       </td>
       <td align=center>
         <b><?php echo lang_tts("Group"); ?>:</b>
         <select size="1" name="lstCategory">
           <?php group_list($phpgw->db,$lstCategory); ?>
         </select>
       </td>
       <td align=center>
         <b><?php echo lang_tts("Assigned to"); ?>:</b>
         <select size="1" name="lstAssignedto">
           <?php groupusers_list($phpgw->db,$lstAssignedto); ?>
         </select>
       </td>
     </tr>
<?php
  $details_string = $phpgw->db->f(2);
  if (empty($details_string)) {
    echo "   <input type=hidden value=\"$details_string\" name=\"prevtxtdetail\">";
    echo "     <tr>\n";
    echo "         <td colspan=3 align=center>\n";
    echo "         <textarea rows=\"12\" name=\"txtDetail\" cols=\"70\" wrap=physical></textarea>\n";
    echo "       </td>\n";
  } else {  
    echo "   <input type=hidden value=\"$details_string\" name=\"prevtxtdetail\">";
    echo "    <tr><td colspan=3 align=left><br><b>".lang_tts("Subject").":</b> " . $phpgw->db->f(8) . "<br><br>";
    echo "    <tr><td colspan=3 align=left><B>".lang_tts("Details").":</B><BR> $details_string </td></tr>\n";
    echo "    <tr><td colspan=3 align=left><BR><BR>".lang_tts("Additional notes").":<BR></td></tr>\n";
    echo "     <tr>\n";
    echo "         <td colspan=3 align=center>\n";
    echo "         <textarea rows=\"12\" name=\"txtAdditional\" cols=\"70\" wrap=physical></textarea>\n";
    echo "       </td>\n";
  }
?>
     <tr>
       <td colspan=3 align=center><hr noshade></td>
     </tr>
     <tr>
       <td align=center>
<?php
  # change buttons from update/close to close/reopen if ticket is already closed
  if ($phpgw->db->f(7) > 0) {
    echo "<input type=radio value='letclosed' name='optUpdateclose' checked>".lang_tts("Closed")."
       </td>
       <td align=center>
         <input type=submit value='".lang_tts("OK")."' name='submit'>
       </td>
       <td align=center>
         <input type=radio value='reopen' name='optUpdateclose'>".lang_tts("ReOpen")."
       </td>
    ";
  } else {
    echo "<input type=radio value='update' name='optUpdateclose' checked>".lang_tts("Update")."
       </td>
       <td align=center>
         <input type=submit value='".lang_tts("OK")."' name='submit'>
       </td>
       <td align=center>
         <input type=radio value='close' name='optUpdateclose'>".lang_tts("Close")."
       </td>
    ";
  }
?>
       </td>
     </tr>
     <tr>
       <td colspan=3>&nbsp;</td>
     </tr>
   </table>

</form>
<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
  } else {
    $txtDetail = $prevtxtdetail;

    if (! empty($txtAdditional)) {
      $txtDetail .= "<BR><i>\n" . $phpgw->session->loginid . " - " . $phpgw->common->show_date(time()) . "</i><BR>\n";
    }

    if ($optUpdateclose == "letclosed" ) {
      # let ticket be closed
      # don't do any changes, ppl will have to reopen tickets to
      # submit additional infos
    } else {
      if ($optUpdateclose == "reopen") {
        # reopen the ticket
        $phpgw->db->query("UPDATE ticket set t_timestamp_closed=NULL WHERE t_id=$t_id");
        $txtDetail .= "<b>".lang_tts("Ticket reopened")."</b><br>\n";
      }

      if (! empty($txtAdditional)) { $txtDetail .= $txtAdditional; }

      if ( $optUpdateclose == "close" ) {
        $txtDetail .= "<br><b>".lang_tts("Ticket closed")."</b><br>\n";
      }
     
      if (! empty($txtAdditional)) {
        $txtDetail .= "<hr>";
        $txtDetail = addslashes($txtDetail);
      }

      # update the database if ticket content changed
      $phpgw->db->query("UPDATE ticket set t_category='$lstCategory',t_detail='$txtDetail',t_priority='$optPriority',t_user='$lstAssignedfrom',t_assignedto='$lstAssignedto' WHERE t_id=$t_id");

      if ( $optUpdateclose == "close" ) {
        $phpgw->db->query("UPDATE ticket set t_timestamp_closed='" . time() . "' WHERE t_id=$t_id");
      }

    }
    Header("Location: " . $phpgw_info["server"]["webserver_url"] . "/tts/?sessionid=" . $sessionid);
  }
?>
