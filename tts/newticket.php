<?php
  if ($submit) {
     $phpgw_flags = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_flags["currentapp"] = "tts";
  include("../header.inc.php");
  if (! $submit) {
     ?>
      <form method=POST action="<?php echo $phpgw->link("newticket.php"); ?>">

       <div align="center">
       <center>
       <table bgcolor="<?php echo $theme["th_bg"]; ?>" cellpadding="3" border="1" width="600">
        <tr>
         <td width="100%" valign="center" align="center">
          <font color="<?php echo $theme["th_text"]; ?>">
           <b><?php echo lang_tts("Add new ticket"); ?></b>
          </font>
         </td>
       </tr>
       <tr>
        <td width="100%" align="left">
        <table cellspacing="0" cellpadding="5" border="0" width="100%">
         <tr>
<!--          <td width="15%" valign=middle align=right><font size=3>ID:</font></td> -->
          <td width="75%" valign="middle"></td>
           </tr> 

           <tr>
            <td width="15%" valign="middle" align="right"><b><?php echo lang_tts("Group"); ?>:</b> </td>
            <td width="75%" valign="middle">
              <select size="1" name="lstCategory">
<?php
  $phpgw->db->query("select group_name from groups");
  while ($phpgw->db->next_record()) {
    echo "<option value= " . $phpgw->db->f(0) . ">" . $phpgw->db->f(0) . "</option>\n";
  }
?>
              </select>
            </td>
           </tr>
<tr>
            <td width="15%" valign="middle" align="right"><b><?php echo lang_tts("assign to"); ?>:</b> </td>
            <td width="75%" valign="middle">
              <select size="1" name="assignto">
<?php
  $phpgw->db->query("select loginid from accounts");
	echo "<option value=none SELECTED>none</option>\n";
  while ($phpgw->db->next_record()) {
    echo "<option value= " . $phpgw->db->f(0) . ">" . $phpgw->db->f(0) . "</option>\n";
  }
?>
              </select>
            </td>
           </tr>
           <tr>
             <td width="15%" valign="middle" align="right"><b><?php echo lang_tts("Subject"); ?>:</b></td>
             <td width="75%" valign="middle">
               <input type=text size=50 maxlength=80 name="subject"
                value="<?php echo lang_tts("No subject"); ?>">
             </td>
           </tr>
           <tr>
            <td width="15%" valign="top" align="right"><b><?php echo lang_tts("Detail"); ?>:</b> </td>
            <td width="75%"><textarea rows="10" name="txtAdditional" cols="65" wrap="virtual"></textarea></td>
           </tr>
           <tr>
            <td width="15%" valign="middle" align="right"><b><?php echo lang_tts("Priority"); ?>:</b> </td>
            <td width="75%" valign="middle">
             <table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
               <td width="25%">
                <select name="optPriority">
                 <option value="1">1 - Lowest</option>
                 <option value="2">2</option>
                 <option value="3">3</option>
                 <option value="4">4</option>
                 <option value="5" selected>5 - Medium</option>
                 <option value="6">6</option>
                 <option value="7">7</option>
                 <option value="8">8</option>
                 <option value="9">9</option>
                 <option value="10">10 - Highest</option>
                </select>
               </td>
              </tr>
             </table>
            </td>
           </tr>
         </table>
        <p align="center"><center><input type="submit" value="<?php echo lang_tts("Add Ticket"); ?>" name="submit">
         <input type="reset" value="<?php echo lang_tts("Clear Form"); ?>"></center></td>
       </tr>
      </table>
     </center>
   </div>
  </form>
<?php
include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
  } else {
     //$current_date = date("ymdHi");		//set timestamp

    $txtDetail .= $phpgw_info["user"]["userid"] . " - " . $phpgw->common->show_date($phpgw->db->f(6)) . "<BR>\n";
    $txtDetail .= $txtAdditional;
     $txtDetail = addslashes($txtDetail);

     $phpgw->db->query("INSERT INTO ticket (t_category,t_detail,t_priority,t_user,t_assignedto, "
		 . " t_timestamp_opened,t_subject) VALUES ('$lstCategory','$txtDetail',"
		 . "'$optPriority','" . $phpgw_info["user"]["userid"] . "','$assignto','"
		 . time() . "','$subject');");

     Header("Location: " . $phpgw_info["server"]["webserver_url"] . "/tts/?sessionid=" . $sessionid);
  }
?>
