
   <tr bgcolor="FFFFFF">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr bgcolor="486591">
    <td colspan="2"><font color="fefefe">&nbsp;<b>Mail settings</b></font></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter your POP/IMAP mail server hostname or IP address:</td>
    <td><input name="newsettings[mail_server]" value="<?php echo $current_config["mail_server"]; ?>"></td>
   </tr>

   <?php $selected[$current_config["mail_server_type"]] = " selected"; ?>
   <tr bgcolor="e6e6e6">
    <td>Select your mail server type:</td>
    <td>
     <select name="newsettings[mail_server_type]">
      <option value="imap"<?php echo $selected["imap"]; ?>>IMAP</option>
      <option value="pop3"<?php echo $selected["pop3"]; ?>>POP-3</option>
<?php /* HvG20010502, Added IMAPS and POP3S as supported mail server types: */ ?>
      <option value="imaps"<?php echo $selected["imaps"]; ?>>IMAPS</option>
      <option value="pop3s"<?php echo $selected["pop3s"]; ?>>POP-3S"</option>
     </select>
    </td>
   </tr>
   <?php $selected = array(); ?>

   <?php $selected[$current_config["imap_server_type"]] = " selected"; ?>
   <tr bgcolor="e6e6e6">
    <td>IMAP server type:</td>
    <td>
     <select name="newsettings[imap_server_type]">
      <option value="Cyrus"<?php echo $selected["Cyrus"]; ?>>Cyrus or Courier</option>
      <option value="UWash"<?php echo $selected["UWash"]; ?>>UWash</option>
      <option value="UW-Maildir"<?php echo $selected["UW-Maildir"]; ?>>UW-Maildir</option>
     </select>
    </td>
   </tr>
   <?php $selected = array(); ?>

   <tr bgcolor="e6e6e6">
    <td>Enter your default mail domain ( From: user@domain ):</td>
    <td><input name="newsettings[mail_suffix]" value="<?php echo $current_config["mail_suffix"]; ?>"></td>
   </tr>

   <?php $selected[$current_config["mail_login_type"]] = " selected"; ?>
   <tr bgcolor="e6e6e6">
    <td>Mail server login type:</td>
    <td>
     <select name="newsettings[mail_login_type]">
      <option value="standard"<?php echo $selected["standard"]; ?>>standard</option>
      <option value="vmailmgr"<?php echo $selected["vmailmgr"]; ?>>vmailmgr</option>
     </select>
    </td>
   </tr>
   <?php $selected = array(); ?>

   <tr bgcolor="e6e6e6">
    <td>Enter your SMTP server hostname or IP address:</td>
    <td><input name="newsettings[smtp_server]" value="<?php echo $current_config["smtp_server"]; ?>"></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter your SMTP server port:</td>
    <td><input name="newsettings[smtp_port]" value="<?php echo $current_config["smtp_port"]; ?>"></td>
   </tr>

   <tr bgcolor="FFFFFF">
    <td colspan="2">&nbsp;</td>
   </tr>

   <tr bgcolor="486591">
    <td colspan="2"><font color="fefefe">&nbsp;<b>NNTP settings</b></font></td>
   </tr>
   
   <tr bgcolor="e6e6e6">
    <td>Enter your NNTP server hostname:</td>
    <td><input name="newsettings[nntp_server]" value="<?php echo $current_config["nntp_server"]; ?>"></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter your NNTP server port:</td>
    <td><input name="newsettings[nntp_port]" value="<?php echo $current_config["nntp_port"]; ?>"></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter your NNTP sender:</td>
    <td><input name="newsettings[nntp_sender]" value="<?php echo $current_config["nntp_sender"]; ?>"></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter your NNTP organization:</td>
    <td><input name="newsettings[nntp_organization]" value="<?php echo $current_config["nntp_organization"]; ?>"></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter your NNTP admins email address:</td>
    <td><input name="newsettings[nntp_admin]" value="<?php echo $current_config["nntp_admin"]; ?>"></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter your NNTP login:</td>
    <td><input name="newsettings[nntp_login_username]" value="<?php echo $current_config["nntp_login_username"]; ?>"></td>
   </tr>

   <tr bgcolor="e6e6e6">
    <td>Enter your NNTP password:</td>
    <td><input name="newsettings[nntp_login_password]" value="<?php echo $current_config["nntp_login_password"]; ?>"></td>
   </tr>
