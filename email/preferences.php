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

  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True, "currentapp" => "email");
  include("../header.inc.php");

  if ($submit) {
     $phpgw->common->preferences_delete("byapp",$phpgw_info["user"]["userid"]);
  
     if ($mainscreen_showmail) {
        $phpgw->common->preferences_add($phpgw_info["user"]["userid"],"mainscreen_showmail","email");
     }
     $phpgw->common->preferences_add($phpgw_info["user"]["userid"],"default_sorting","email");
     $phpgw->common->preferences_add($phpgw_info["user"]["userid"],"email_sig","email"); 

     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/preferences/index.php"));
  }

  $phpgw->common->phpgw_header();
  $phpgw->common->navbar();

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
    <tr>
     <td colspan="3" align="center">
      <input type="submit" name="submit" value="<?php echo lang("submit"); ?>">
     </td>
    </tr>
   </table>
  </form>
<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>