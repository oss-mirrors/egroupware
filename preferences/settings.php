<?php
  /**************************************************************************\
  * phpGroupWare - preferences                                               *
  * http://www.phpgroupware.org                                              *
  * This file written by Joseph Engo <jengo@phpgroupware.org>                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  if ($submit) {
     $phpgw_info["flags"] = array("nonavbar" => True, "noheader" => True);  
  }
  $phpgw_info["flags"]["currentapp"] = "preferences";

  include("../header.inc.php");

  if (! $submit) {
     if ($phpgw_info["server"]["useframes"] != "never") {
        $target = ' target="_top"';
     }
     ?>
      <form method="POST" action="<?php echo $phpgw->link("settings.php"); ?>"<?php echo $target; ?>>
       <table border=0>
       <tr>
        <td><?php echo lang("max matchs per page"); ?>: </td>
        <td>
         <input name="settings[maxmatchs]" value="<?php
           echo $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]; ?>" size="2">
        </td>
       </tr>

       <?php if ($phpgw_info["server"]["usrtplchoice"] == "user_choice"){ ?>
       <tr>
         <?php $selected[$phpgw_info["user"]["preferences"]["common"]["template_set"]] = " selected"; ?>
        <td>Interface/Template Selection:<br></td>
        <td>
         <select name="settings[template_set]">
        <?php
          $templates = $phpgw->common->list_templates();
          while (list ($key, $value) = each ($templates)){
            echo '<option value="'.$key.'" '.$selected[$key].'>'.$templates[$key]["title"].'</option>';
          }
        ?>
         </select>
        </td>
       </tr>
      <?php } ?>
       <tr>
        <td><?php echo lang("Show navigation bar as"); ?>: </td>
        <td>
         <?php $selected[$phpgw_info["user"]["preferences"]["common"]["navbar_format"]] = " selected"; ?>
         <select name="settings[navbar_format]">
          <option value="icons"<?php echo $selected["icons"] . ">" . lang("icons only"); ?></option>
          <option value="icons_and_text"<?php echo $selected["icons_and_text"] . ">" . lang("icons and text"); ?></option>
          <option value="text"<?php echo $selected["text"] . ">" . lang("text only"); ?></option>
         </select>
        </td>
       </tr>

       <?php
         if ($phpgw_info["server"]["useframes"] == "allowed") {
       ?>
       <tr>
        <td><?php echo lang("Show navigation bar in a frame"); ?>: </td>
        <td>
         <input type="checkbox" name="settings[useframes]" value="True"<?php echo ($phpgw_info["user"]["preferences"]["common"]["useframes"]?" checked":""); ?>>
        </td>
       </tr>
       <?php
         }
         if ($phpgw_info["server"]["useframes"] != "never") {
       ?>
       <tr>
        <td><?php echo lang("Navigation bar frame location"); ?>: </td>
        <td>
         <?php $selected[$phpgw_info["user"]["preferences"]["common"]["frame_navbar_location"]] = " selected"; ?>
         <select name="settings[frame_navbar_location]">
          <option value="top"<?php echo $selected["top"] . ">" . lang("Top"); ?></option>
          <option value="bottom"<?php echo $selected["bottom"] . ">" . lang("bottom"); ?></option>
         </select>
        </td>
       </tr>
       <?php        
         }
       ?>

       <tr>
        <td><?php echo lang("time zone offset"); ?>: </td>
        <td>
         <select name="settings[tz_offset]"><?php
           for ($i = -23; $i<24; $i++) {
               echo "<option value=\"$i\"";
               if ($i == $phpgw_info["user"]["preferences"]["common"]["tz_offset"])
                  echo " selected";
               if ($i < 1)
                  echo ">$i</option>\n";
               else
                  echo ">+$i</option>\n";
           }
         ?></select>
         <?php echo lang("This server is located in the x timezone",strftime("%Z")); ?>
        </td>
       </tr>

       <tr>
        <td><?php echo lang("date format"); ?>:</td>
        <td>
         <?php $df[$phpgw_info["user"]["preferences"]["common"]["dateformat"]] = " selected"; ?>
         <select name="settings[dateformat]">
          <option value="m/d/Y"<?php echo $df["m/d/Y"]; ?>>m/d/y</option>
          <option value="m-d-Y"<?php echo $df["m-d-Y"]; ?>>m-d-y</option>
          <option value="m.d.Y"<?php echo $df["m.d.Y"]; ?>>m.d.y</option>

          <option value="Y/d/m"<?php echo $df["Y/d/m"]; ?>>y/d/m</option>
          <option value="Y-d-m"<?php echo $df["Y-d-m"]; ?>>y-d-m</option>
          <option value="Y.d.m"<?php echo $df["Y.d.m"]; ?>>y.d.m</option>

          <option value="Y/m/d"<?php echo $df["Y/m/d"]; ?>>y/m/d</option>
          <option value="Y-m-d"<?php echo $df["Y-m-d"]; ?>>y-m-d</option>
          <option value="Y.m.d"<?php echo $df["Y.m.d"]; ?>>y.m.d</option>

          <option value="d/m/Y"<?php echo $df["d/m/Y"]; ?>>d/m/y</option>
    	  <option value="d-m-Y"<?php echo $df["d-m-Y"]; ?>>d-m-y</option>
    	  <option value="d.m.Y"<?php echo $df["d.m.Y"]; ?>>d.m.y</option>
         </select>
        </td>
       </tr>
       <tr>
        <td><?php echo lang("time format"); ?>:</td>
        <td><?php
            $timeformat_select[$phpgw_info["user"]["preferences"]["common"]["timeformat"]] = " selected";
            echo "<select name=\"settings[timeformat]\">"
               . "<option value=\"12\"$timeformat_select[12]>12 Hour</option>"
               . "<option value=\"24\"$timeformat_select[24]>24 Hour</option>"
	       . "</select>\n";
          ?>
        </td>
       </tr>
       <tr>
         <td><?php echo lang("language"); ?></td>
         <td>
          <select name="settings[lang]">
          <?php

            $lang_select[$phpgw_info["user"]["preferences"]["common"]["lang"]] = " selected"; 
            $phpgw->db->query("SELECT lang_id, lang_name FROM languages WHERE available = 'Yes'",__LINE__,__FILE__);
            while ($phpgw->db->next_record()) {
                echo "<option value=\"" . $phpgw->db->f("lang_id") . "\"";
                if ($phpgw_info["user"]["preferences"]["common"]["lang"]) {
                   if ($phpgw->db->f("lang_id") == $phpgw_info["user"]["preferences"]["common"]["lang"]) {
                      echo " selected";
                   }
                } elseif ($phpgw->db->f("lang_id") == "en") {
                   echo " selected";
                }
                echo ">" . $phpgw->db->f("lang_name") . "</option>";
            }
            ?>
          </select>
         </td>
       </tr>
       <?php
         if ($phpgw_info["user"]["apps"]["admin"]) {
            echo '<tr><td>' . lang("show current users on navigation bar") . '</td><td>'
               . '<input type="checkbox" name="show_currentusers" value="True"';
            if ($phpgw_info["user"]["preferences"]["common"]["show_currentusers"]) {
               echo " checked";
            }
            echo "></td></tr>";
         }
?>        
       <tr>
        <td><?php echo lang("Default application"); ?></td>
        <td>
         <select name="settings[default_app]">
          <option value="">&nbsp;</option>
           <?php
 			$db_perms = $phpgw->accounts->read_apps($phpgw_info["user"]["userid"]);
             while ($permission = each($db_perms)) {
               if ($phpgw_info["apps"][$permission[0]]["enabled"]) {
				  echo "<option value=\"" . $permission[0] . "\"";
				  if ($phpgw_info["user"]["preferences"]["common"]["default_app"] == $permission[0]) {
					 echo " selected";
                  }
				  echo ">" . lang($phpgw_info["apps"][$permission[0]]["title"])
					 . "</option>";
               }
             }
          ?></select>
        </td>
       </tr>

       <tr>
        <td><?php echo lang("Currency"); ?></td>
        <td>
         <?php
           if (! isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {
              $phpgw_info["user"]["preferences"]["common"]["currency"] = '$';
           }
         ?>
         <input name="settings[currency]" value="<?php echo $phpgw_info["user"]["preferences"]["common"]["currency"]; ?>">
        </td>
       </tr>

       <tr>
        <td colspan="2" align="center">
         <input type="submit" name="submit" value="<?php echo lang("submit"); ?>">
        </td>
       </tr>
      </table>
     </form>

 <?php
     $phpgw->common->phpgw_footer();
  } else {

     while ($setting = each($settings)) {
        $phpgw->preferences->change("common",$setting[0],$setting[1]);
     }

     // This one is specialized, so we do it manually
     if ($phpgw_info["user"]["apps"]["admin"]) {
        if ($show_currentusers) {
           $phpgw->preferences->change("common","show_currentusers");
        }
     }

     $phpgw->preferences->commit();

     if ($phpgw_info["server"]["useframes"] != "never") {
        Header("Location: " . $phpgw->link($phpgw_info["server"]["webserver_url"] . "/preferences/index.php"));
        $phpgw->common->phpgw_exit();
     }

     Header("Location: " . $phpgw->link("index.php"));
  }
?>
