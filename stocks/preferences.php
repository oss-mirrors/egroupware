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

  $phpgw_info["flags"] = array("noheader" => True, 
                               "nonavbar" => True,
                               "enable_nextmatchs_class" => True);

  $phpgw_info["flags"]["currentapp"] = "stocks";
  include("../header.inc.php");

  if ($action == "add") {
     $phpgw->preferences->change("stocks",urlencode($symbol),urlencode($name));
     $phpgw->preferences->commit(True);
     // For some odd reason, if I forward it back to stocks/preferences.php after an add
     // I get no data errors, so for now forward it to the main preferences section.
     Header("Location: " . $phpgw->link('/preferences/index.php'));
     $phpgw->common->phpgw_exit();
  } else if ($action == "delete") {
     // This needs to be fixed
     $phpgw->preferences->delete("stocks",$value);
     $phpgw->preferences->commit(True);
     Header("Location: " . $phpgw->link('/stocks/preferences.php'));
     $phpgw->common->phpgw_exit();
  }

    if ($mainscreen) {
	if ($mainscreen == "enable") {
        $phpgw->preferences->delete("stocks","disabled");
        $phpgw->preferences->change("stocks","enabled","True");
    }

    if ($mainscreen == "disable") {
	$phpgw->preferences->delete("stocks","enabled");
	$phpgw->preferences->change("stocks","disabled","True");
    }
    $phpgw->preferences->commit(True);
    Header("Location: " . $phpgw->link('/stocks/preferences.php'));
    $phpgw->common->phpgw_exit();
    }

    $phpgw->common->phpgw_header();
    echo parse_navbar();

  // If they don't have any stocks in there, give them something to look at
    if (count($phpgw_info["user"]["preferences"]["stocks"]) == 1) {
	$phpgw->preferences->change("stocks","LNUX","VA%20Linux");
	$phpgw->preferences->change("stocks","RHAT","RedHat");
	$phpgw->preferences->commit(True);
	$phpgw_info["user"]["preferences"]["stocks"]["LNUX"] = "VA%20Linux";
	$phpgw_info["user"]["preferences"]["stocks"]["RHAT"] = "RedHat";
    }


    echo "<p><b>" . lang("Stock Quote preferences") . ":" . "</b><hr><p>";
?>
   <table border="0" align="center" cellspacing="1" cellpadding="1" width="60%">
    <?php
      echo '<tr bgcolor="' . $phpgw_info["theme"]["th_bg"] . '">'
         . '<td>' . lang("Symbol") . '</td>'
         . '<td>' . lang("Company name") . '</td>'
// For right now, editing is disabled, feel free to add it :)         
// added it...
         . '<td width="5%" align="center">' . lang("Edit") . '</td>'
         . '<td width="5%" align="center">' . lang("Delete") . '</td>'
         . '</tr>';
      echo "\n";
         
      while ($stock = each($phpgw_info["user"]["preferences"]["stocks"])) {
         if (($stock[0] != "enabled") && ($stock[0] != "disabled")) {
            $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
            echo '<tr bgcolor="' . $tr_color . '">';

            echo '<td>' . rawurldecode($stock[0]) . '</td>';
            echo '<td>' . rawurldecode($stock[1]) . '</td>';
            echo '<td width="5%" align="center"><a href="'                                                                                                                                     
               . $phpgw->link("/stocks/preferences_edit.php","sym=" . $stock[0]) . '">Edit</a></td>';
            echo '<td width="5%" align="center"><a href="'
               . $phpgw->link("/stocks/preferences.php","action=delete&value=" . $stock[0]) . '">Delete</a></td>';

            echo "</tr>\n";
         }
     }

     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
     
     echo '<tr bgcolor="' . $tr_color . '"><td colspan="4">&nbsp;</td></tr>';
     
     $tr_color = $phpgw->nextmatchs->alternate_row_color($tr_color);
     echo '<tr bgcolor="' . $tr_color . '"><td colspan="3">';
     if ($phpgw_info["user"]["preferences"]["stocks"]["enabled"]) {
        echo lang("Display stocks on main screen is enabled");
        $newstatus = "disable";
     } else {
        echo lang("Display stocks on main screen is disabled");
        $newstatus = "enable";
     }
     echo '</td><td><a href="' . $phpgw->link("/stocks/preferences.php","mainscreen=$newstatus") . '">' . $newstatus . '</a>'
        . '</td><tr>';
     ?>

    <tr>
     <td colspan="4">&nbsp;</td>
    </tr>

    <tr>
     <td colspan="4">

      <form method="POST" action="<?php echo $phpgw->link("/stocks/preferences.php"); ?>">
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
$phpgw->common->phpgw_footer();  
?>
