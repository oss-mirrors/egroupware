<!-- BEGIN tts/setup/config.inc.php -->
   <tr bgcolor="FFFFFF">
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr bgcolor="486591">
    <td colspan="2"><font color="fefefe">&nbsp;<b>Trouble Ticket System settings</b></font></td>
   </tr>
   <tr bgcolor="FFFFFF">
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr bgcolor="e6e6e6">
    <td>Do you want to enable the automatic mailing of tickets?:</td>
    <td><input type="checkbox" name="newsettings[tts_mailticket]" value="True"<?php echo ($current_config["tts_mailticket"]?" checked":""); ?>></td>
   </tr>
