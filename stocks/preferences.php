<?php
  /**************************************************************************\
  * phpGroupWare - Stock Quotes                                              *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $phpgw_info["flags"] = array("noheader" => True, "nonavbar" => True);

  $phpgw_info["flags"]["currentapp"] = "stocks";
  include("../header.inc.php");

  if ($action == "add") {
     $phpgw->common->preferences_add($phpgw_info["user"]["account_id"],urlencode($symbol),"stocks",urlencode($name));
     // For some odd reason, if I forward it back to stocks/preferences.php after an add
     // I get no data errors, so for now forward it to the main preferences section.
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/preferences/index.php"));
     exit;
  } else if ($action == "delete") {
     $phpgw->common->preferences_delete("byappvar_single",$phpgw_info["user"]["account_id"],"stocks",$value);
     Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/stocks/preferences.php"));
     exit;  

  }

  $phpgw->common->phpgw_header();
  $phpgw->common->navbar();

  echo "<p><b>" . lang("Stock Quote preferences") . ":" . "</b><hr><p>";
?>
   <table border="0" align="center" cellspacing="1" cellpadding="1" width="60%">
    <?php
      echo '<tr bgcolor="' . $phpgw_info["theme"]["th_bg"] . '">'
         . '<td>' . lang("Symbol") . '</td>'
         . '<td>' . lang("Company Name") . '</td>'
         . '<td width="5%" align="center">' . lang("Edit") . '</td>'
         . '<td width="5%" align="center">' . lang("Delete") . '</td>'
         . '</tr>';
      echo "\n";
         
      while ($stock = each($phpgw_info["user"]["preferences"]["stocks"])) {
         if ($stock[0] != "enabled") {
            $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
            echo '<tr bgcolor="' . $tr_color . '">';

            echo '<td>' . rawurldecode($stock[0]) . '</td>';
            echo '<td>' . rawurldecode($stock[1]) . '</td>';
            echo '<td width="5%" align="center">Edit</td>';
            echo '<td width="5%" align="center"><a href="'
               . $phpgw->link("preferences.php","action=delete&value=" . $stock[0]) . '">Delete</a></td>';

            echo "</tr>\n";
         }
     }

     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
     ?>

    <tr>
     <td colspan="4">&nbsp;</td>
    </tr>

    <tr>
     <td colspan="4">

      <form method="POST" action="<?php echo $phpgw->link(); ?>">
      <input type="hidden" name="action" value="add">
      <table border="0" align="center">
       <tr bgcolor="<?php echo $phpgw_info["theme"]["th_bg"]; ?>">
        <td colspan="2" align="center"><?php echo lang("Add new stock"); ?></td>
       </tr>
       <?php
         // Reset $tr_color
         $tr_color = "";
         $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
       ?>
       <tr bgcolor="<?php echo $tr_color; ?>">
        <td align="right"><?php echo lang("Symbol"); ?>:&nbsp;</td>
        <td><input name="symbol"></td>
       </tr>
       <?php $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color); ?>
       <tr bgcolor="<?php echo $tr_color; ?>">
        <td align="right"><?php echo lang("Company name"); ?>:&nbsp;</td>
        <td><input name="name"></td>
       </tr>
       <tr>
        <td colspan="2" align="center">
         <input type="submit" name="submit" value="<?php echo lang("add"); ?>">
        </td>
       </tr>
      </table>
      </form>

     </td>
    </tr>
   </table>
<?php
  include($phpgw_info["server"]["api_dir"] . "/footer.inc.php");
?>