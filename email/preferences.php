<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("currentapp" => "email","noheader" => True, "nonavbar" => True,
																"enable_nextmatchs_class" => True);

  include("../header.inc.php");

  if ($submit) {
     $phpgw->preferences->read_repository();
     
     $phpgw->preferences->delete("email","mainscreen_showmail");
     if ($mainscreen_showmail) {
        $phpgw->preferences->add("email","mainscreen_showmail");
     }
     
     $phpgw->preferences->delete("email","use_trash_folder");
     if ($use_trash_folder) {
        $phpgw->preferences->add("email","use_trash_folder");
     }
     $phpgw->preferences->add("email","default_sorting");
     $phpgw->preferences->add("email","email_sig"); 

     $phpgw->preferences->delete("email","use_custom_settings");
     $phpgw->preferences->delete("email","userid");
     $phpgw->preferences->delete("email","passwd");
     $phpgw->preferences->delete("email","address");
     $phpgw->preferences->delete("email","mail_server");
     $phpgw->preferences->delete("email","mail_folder");
     $phpgw->preferences->delete("email","mail_server_type");
     $phpgw->preferences->delete("email","imap_server_type");
     
     if ($use_custom_settings) {
       $phpgw->preferences->add("email","use_custom_settings");
       if ($userid) {$phpgw->preferences->add("email","userid");}
       if ($passwd) {
          $encrypted_passwd = $phpgw->common->encrypt($passwd);
          $phpgw->preferences->add("email","passwd",$encrypted_passwd);
       }
       if ($address) {$phpgw->preferences->add("email","address");}
       if ($mail_server) {$phpgw->preferences->add("email","mail_server");}
       if ($mail_folder) {$phpgw->preferences->add("email","mail_folder");}
       if ($mail_server_type) {$phpgw->preferences->add("email","mail_server_type");}
       if ($imap_server_type) {$phpgw->preferences->add("email","imap_server_type");}
     }
     $phpgw->preferences->save_repository();

     Header("Location: " . $phpgw->link("/preferences/index.php"));
  }

  $phpgw->common->phpgw_header();
  echo parse_navbar();

  if ($totalerrors) {  
     echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";
  }

  echo "<p><b>" . lang("E-Mail preferences") . ":" . "</b><hr><p>";
?>
  <form method="POST" action="<?php echo $phpgw->link(); ?>">
   <table border="0" align="center" cellspacing="1" cellpadding="1" width="60%">
    <tr bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
     <td colspan="2">&nbsp;</td>
    </tr>

    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("email signature"); ?></td>
     <td align="center">
      <textarea name="email_sig" rows="3" cols="30"><?php echo $phpgw_info["user"]["preferences"]["email"]["email_sig"]; ?></textarea>
     </td>
    </tr>

    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Default sorting order"); ?></td>
     <td align="center"><?php
           $default_order_display[$phpgw_info["user"]["preferences"]["email"]["default_sorting"]] = " selected"; ?>
       <select name="default_sorting">
 	   <option value="old_new"<?php echo $default_order_display["old_new"]; ?>>oldest -> newest</option>   
 	   <option value="new_old"<?php echo $default_order_display["new_old"]; ?>>newest -> oldest</option>
       </select>
     </td>
    </tr>

    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("show new messages on main screen"); ?></td>
     <td align="center"><input type="checkbox" name="mainscreen_showmail" value="True"<?php if ($phpgw_info["user"]["preferences"]["email"]["mainscreen_showmail"]) echo " checked"; ?>></td>
    </tr>
    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Send deleted messages to the trash"); ?></td>
     <td align="center"><input type="checkbox" name="use_trash_folder" value="True"<?php if ($phpgw_info["user"]["preferences"]["email"]["use_trash_folder"]) echo " checked"; ?>></td>
    </tr>

    <tr><td colspan="2">&nbsp;</td></tr>

    <tr bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
     <td colspan="2">Custom Email settings</td>
    </tr>
    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Use custom settings"); ?> - (<?php echo lang("Non-Standard"); ?>)</td>
     <td align="center"><input type="checkbox" name="use_custom_settings" value="True"<?php if ($phpgw_info["user"]["preferences"]["email"]["use_custom_settings"]) echo " checked"; ?>></td>
    </tr>

    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Email Account Name"); ?></td>
     <td align="center">
      <input type="text" name="userid" value="<?php echo $phpgw_info["user"]["preferences"]["email"]["userid"];?>">
     </td>
    </tr>
    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Email Password"); ?></td>
     <td align="center">
      <input type="password" name="passwd" value="<?php echo $phpgw->common->decrypt($phpgw_info["user"]["preferences"]["email"]["passwd"]); ?>">
     </td>
    </tr>
    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Email address"); ?></td>
     <td align="center">
      <input type="text" name="address" value="<?php echo $phpgw_info["user"]["preferences"]["email"]["address"]; ?>">
     </td>
    </tr>
    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Mail Server"); ?></td>
     <td align="center">
      <input type="text" name="mail_server" value="<?php echo $phpgw_info["user"]["preferences"]["email"]["mail_server"]; ?>">
     </td>
    </tr>
    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Mail Server type"); ?></td>
     <td align="center">
      <select name="mail_server_type">
       <?php $selected[$phpgw_info["user"]["preferences"]["email"]["mail_server_type"]] = " selected"; ?>
       <option value="imap"<?php echo $selected["imap"]; ?>>IMAP</option>
       <option value="pop3"<?php echo $selected["pop3"]; ?>>POP-3</option>
       <?php $selected = array(); ?>
      </select>
     </td>
    </tr>
    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("IMAP Server Type"); ?> - (<?php echo lang("If Applicable"); ?>)</td>
     <td align="center">
     <select name="imap_server_type">
      <?php $selected[$phpgw_info["user"]["preferences"]["email"]["imap_server_type"]] = " selected"; ?>
      <option value="Cyrus"<?php echo $selected["Cyrus"]; ?>>Cyrus</option>
      <option value="UWash"<?php echo $selected["UWash"]; ?>>UWash</option>
      <option value="UW-Maildir"<?php echo $selected["UW-Maildir"]; ?>>UW-Maildir</option>
      <?php $selected = array(); ?>
     </select>
     </td>
    </tr>
    <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
    <tr bgcolor="<?php echo $tr_color; ?>">
     <td align="left"><?php echo lang("Mail Folder(UW-Maildir)"); ?></td>
     <td align="center">
      <input type="text" name="mail_folder" value="<?php echo $phpgw_info["user"]["preferences"]["email"]["mail_folder"]; ?>">
     </td>
    </tr>
    <tr>
     <td colspan="3" align="center">
      <input type="submit" name="submit" value="<?php echo lang("submit"); ?>">
     </td>
    </tr>
   </table>
  </form>
<?php $phpgw->common->phpgw_footer(); ?>
