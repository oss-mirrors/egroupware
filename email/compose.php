<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  if ($newsmode == "on"){$phpgw_info["flags"]["newsmode"] = True;}

  $phpgw_info["flags"] = array("currentapp" => "email", "enable_message_class" => True);
  include("../header.inc.php");

  if ($msgnum) {
    $msg = $phpgw->msg->header($mailbox, $msgnum);
    $struct = $phpgw->msg->fetchstructure($mailbox, $msgnum);
    if ($action == "reply") {
	    $from = $msg->from[0];
	    $to = $from->mailbox."@".$from->host;
	    $subject = !$msg->Subject ? lang("no subject") : decode_header_string($msg->Subject);
	    $begin = strtoupper(substr($subject, 0, 3)) != "RE:" ? "Re: " : "";
	    $subject = $begin . $subject;
    }
    if ($action == "replyall") {
	    if ($msg->to) {
	      for ($i = 0; $i < count($msg->to); $i++) {
	        $topeople = $msg->to[$i];
	        $tolist[$i] = "$topeople->mailbox@$topeople->host";
	      }
	    $from = $msg->from[0];
	    $to = "$from->mailbox@$from->host, " . implode(", ", $tolist);
    }

    if ($msg->cc) {
	    for ($i = 0; $i < count($msg->cc); $i++)	{
        $ccpeople = $msg->cc[$i];
        $cclist[$i] = "$ccpeople->mailbox@$ccpeople->host";	
      }
	    $cc = implode(", ", $cclist);
    }

    $subject = !$msg->Subject ? lang("no subject") : decode_header_string($msg->Subject);
    $begin = strtoupper(substr($subject, 0, 3)) != "RE:" ? "Re: " : "";
    $subject = $begin . $subject;
  }


  if ($action == "forward") {
    $subject = !$msg->Subject ? lang("no subject") : decode_header_string($msg->Subject);
    $begin = strtoupper(substr($subject, 0, 3)) != "FW:" ? "Fw: " : "";
    $subject = $begin . $subject;
  }

// This may be needed for multi-language support
//  $body = "\n\n\n$L_ORIG_MSG\n&gt\n";
  $body = "\n\n\n$to wrote:\n&gt\n";
  $numparts = !$struct->parts ? "1" : count($struct->parts);
  for ($i = 0; $i < $numparts; $i++) {
    $part = !$struct->parts[$i] ? $part = $struct : $part = $struct->parts[$i];
    if (get_att_name($part) == "Unknown") {
	    if (strtoupper($part->subtype) == "PLAIN") {
	      $bodystring = $phpgw->msg->fetchbody($mailbox, $msgnum, $i+1);
        $body_array = array();
	      $body_array = explode("\n", $bodystring);
        $bodycount = count ($body_array);
        for ($bodyidx = 0; $bodyidx < ($bodycount -1); ++$bodyidx) {
          if ($body_array[$bodyidx] != "\r") {
            $body .= "&gt;" . $body_array[$bodyidx];
            $body = chop ($body);
            $body .= "\n";
          }
        }    
        trim ($body);
      }
    }
  }
}
?>

 <script>
    self.name="first_Window";
    function addressbook()
    {
<!--   Window1=window.open('<?php echo $phpgw->link("addressbook.php","query="); ?>+document.doit.to.value',"Search","width=800,height=600","toolbar=yes,resizable=yes");  -->
       Window1=window.open('<?php echo $phpgw->link("addressbook.php"); ?>&query=',"Search","width=800,height=600,toolbar=yes,scrollbars=yes,resizable=yes");
    }

    function attach_window(url)
    {
       awin = window.open(url,"attach","width=500,height=400,toolbar=no,resizable=yes");
    }
  </script>

<table border=0 cellpadding="1" cellspacing="1" width="95%" align="center">

<form enctype="multipart/form-data" name="doit" action="<?php echo $phpgw->link("send_message.php")?>" method=POST>
  <input type="hidden" name="return" value="<?php echo $folder ?>">

  <tr>
   <td colspan=2 bgcolor="<?php echo $phpgw_info["theme"]["em_folder"]; ?>">

     <table border=0 cellpadding=4 cellspacing=1 width=100%>
      <tr>
       <td align="left" bgcolor="<?php echo $phpgw_info["theme"]["em_folder"] ?>">
	<input type="button" value="<?php echo lang("addressbook"); ?>" onclick="addressbook();">
       </td>
       <td align="right" bgcolor="<?php echo $phpgw_info["theme"]["em_folder"] ?>">
	<input type="submit" value="<?php echo lang("send"); ?>">
       </td>
      </tr>
     </table>
   </td>

   <tr><td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>"><b>&nbsp;<?php echo lang("to"); ?>:</b></td>
     <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" width="570">
      <input type=text name=to size=80 value="<?php

       if ($mailto) {
         echo substr($mailto, 7, strlen($mailto));
       } else {
         echo $to;
       }

?>"></td></tr>
  <tr>
   <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
    <font size="2" face="<?php echo $phpgw_info["theme"]["font"] ?>">
     <b>&nbsp;<?php echo lang("cc"); ?>:</b>
    </font>
   </td>
   <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" width="570">
    <input type="text" name="cc" size="80" value="<?php echo $cc ?>">
   </td>
  </tr>

  <tr>
   <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
    <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
     <b>&nbsp;<?php echo lang("subject"); ?>:&nbsp;</b>
    </font>
   </td>
   <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" width="570">
    <input type="text" name="subject" size="80" value="<?php echo $subject; ?>">
   </td>
  </tr>
  <?php
    if ($phpgw_info["user"]["preferences"]["email"]["email_sig"]) {
?>
  <tr>
   <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" colspan="2">
    <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
      Attach signature
      <input type="checkbox" name="attach_sig" value="true" checked>
    </font>
   </td>
  </tr>
<?php
    }
?>
  <tr>
   <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>" colspan="2">
    <font size="2" face="<?php echo $phpgw_info["theme"]["font"]; ?>">
      <a href="javascript:attach_window('<?php echo $phpgw->link("attach_file.php"); ?>')">Attach file</a>
    </font>
   </td>
  </tr>
 </table>

 <table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
  <tr align="center">
   <td>
    <textarea name="body" cols="84" rows="15" wrap="hard"><?php echo $body; ?></textarea>
   </td>
  </tr>
 </table>
</form>
<script>

document.doit.body.focus();
if(document.doit.subject.value == "") document.doit.subject.focus();
if(document.doit.to.value == "") document.doit.to.focus();

</script>

<?php 
$phpgw->common->phpgw_footer();
?>
