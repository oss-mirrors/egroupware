<?php
   /**
    **  options_personal.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Displays all options relating to personal information
    **
    **  $Id$
    **/

	// store the value of $mailbox, because it will overwriten
	$MAILBOX = $mailbox;
	$phpgw_info["flags"] = array("currentapp" => "felamimail", "enable_network_class" => True, "enable_nextmatchs_class" => True);
	include("../../header.inc.php");
	$mailbox = $MAILBOX;

	$phpgw->session->restore();

   if (!isset($strings_php))
      include("../inc/strings.php");
   if (!isset($config_php))
      include("../config/config.php");

	$key      = $phpgw_info['user']['preferences']['email']['passwd'];
	$username = $phpgw_info['user']['preferences']['email']['userid'];

   if (!isset($page_header_php))
      include("../inc/page_header.php");
   if (!isset($display_messages_php))
      include("../inc/display_messages.php");
   if (!isset($imap_php))
      include("../inc/imap.php");
   if (!isset($array_php))
      include("../inc/array.php");
   if (!isset($i18n_php))
      include("../inc/i18n.php");
   if (!isset($plugin_php))
      include("../inc/plugin.php");

   include("../src/load_prefs.php");
   displayPageHeader($color, "None");

   $fullname = getPref($data_dir, $username, "full_name");
   $replyto = getPref($data_dir, $username, "reply_to");
   $email_address  = getPref($data_dir, $username, "email_address"); 

?>
   <br>
   <table width=95% align=center border=0 cellpadding=2 cellspacing=0><tr><td bgcolor="<?php echo $color[0] ?>">
      <center><b><?php echo lang("Options") . " - " . lang("Personal Information"); ?></b></center>
   </td></tr></table>

   <form name=f action="options.php" method=post>
      <table width=100% cellpadding=0 cellspacing=2 border=0>
         <tr>
            <td align=right nowrap><?php echo lang("Full Name"); ?>:
            </td><td>
               <input size=50 type=text value="<?php echo $fullname ?>" name=full_name> 
            </td>
         </tr>
         <tr>
            <td align=right nowrap><?php echo lang("E-Mail Address"); ?>:
            </td><td>
               <input size=50 type=text value="<?php echo $email_address ?>" name=email_address> 
            </td>
         </tr>
         <tr>
            <td align=right nowrap><?php echo lang("Reply To"); ?>:
            </td><td>
               <input size=50 type=text value="<?php echo $replyto ?>" name=reply_to> 
            </td>
         </tr>
         <tr>
            <td align=right nowrap valign=top><br><?php echo lang("Signature"); ?>:
            </td><td>
<?php
   if ($use_signature == true)
      echo "<input type=checkbox value=\"1\" name=usesignature checked>&nbsp;&nbsp;" . lang("Use a signature") . "?<BR>";
   else {
      echo "<input type=checkbox value=\"1\" name=usesignature>&nbsp;&nbsp;";
      echo lang("Use a signature?");
      echo "<BR>";
   } 
   echo "\n<textarea name=signature_edit rows=5 cols=50>$signature_abs</textarea><br>";
?>
            </td>
         </tr>
         <?php do_hook("options_personal_inside"); ?>
         <tr>
            <td>&nbsp;
            </td><td>
               <input type="submit" value="<?php echo lang("Submit"); ?>" name="submit_personal">
            </td>
         </tr>
      </table>   
   </form>
   <?php do_hook("options_personal_bottom"); ?>
</body></html>
