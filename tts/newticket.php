<?php
  /**************************************************************************\
  * phpGroupWare - Trouble Ticket System                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */
  
  if ($submit) {
     $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);
  }

  $phpgw_info["flags"]["currentapp"] = "tts";
  $phpgw_info["flags"]["enable_send_class"]       = True;
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
           <b><?php echo lang("Add new ticket"); ?></b>
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
            <td width="15%" valign="middle" align="right"><b><?php echo lang("Group"); ?>:</b> </td>
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
            <td width="15%" valign="middle" align="right"><b><?php echo lang("assign to"); ?>:</b> </td>
            <td width="75%" valign="middle">
              <select size="1" name="assignto">
<?php
  $phpgw->db->query("select account_lid from accounts");
	echo "<option value=none SELECTED>none</option>\n";
  while ($phpgw->db->next_record()) {
    echo "<option value= " . $phpgw->db->f(0) . ">" . $phpgw->db->f(0) . "</option>\n";
  }
?>
              </select>
            </td>
           </tr>
           <tr>
             <td width="15%" valign="middle" align="right"><b><?php echo lang("Subject"); ?>:</b></td>
             <td width="75%" valign="middle">
               <input type=text size=50 maxlength=80 name="subject"
                value="<?php echo lang("No subject"); ?>">
             </td>
           </tr>
           <tr>
            <td width="15%" valign="top" align="right"><b><?php echo lang("Detail"); ?>:</b> </td>
            <td width="75%"><textarea rows="10" name="txtAdditional" cols="65" wrap="virtual"></textarea></td>
           </tr>
           <tr>
            <td width="15%" valign="middle" align="right"><b><?php echo lang("Priority"); ?>:</b> </td>
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
        <p align="center"><center><input type="submit" value="<?php echo lang("Add Ticket"); ?>" name="submit">
         <input type="reset" value="<?php echo lang("Clear Form"); ?>"></center></td>
       </tr>
      </table>
     </center>
   </div>
  </form>
<?php
$phpgw->common->phpgw_footer();
  } else {
     //$current_date = date("ymdHi");		//set timestamp

    $txtDetail .= $phpgw_info["user"]["userid"] . " - " . $phpgw->common->show_date($phpgw->db->f(6)) . "<BR>\n";
    $txtDetail .= $txtAdditional . "<br><hr>";
     $txtDetail = addslashes($txtDetail);

     $phpgw->db->query("INSERT INTO ticket (t_category,t_detail,t_priority,t_user,t_assignedto, "
		           . " t_timestamp_opened,t_timestamp_closed,t_subject) VALUES ('$lstCategory','$txtDetail',"
 		           . "'$optPriority','" . $phpgw_info["user"]["userid"] . "','$assignto','"
		           . time() . "',0,'$subject');");
     $phpgw->db->query("SELECT t_id FROM ticket WHERE t_subject='$subject' AND t_user='".$phpgw_info["user"]["userid"]."'");
     $phpgw->db->next_record();
     mail_ticket($phpgw->db->f("t_id"));

     Header("Location: " . $phpgw->link("index.php"));
  }
?>
