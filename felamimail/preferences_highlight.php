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

// store the value of $mailbox, because it will overwriten
        $MAILBOX = $mailbox;
        
	$phpgw_info["flags"] = array("currentapp" => "felamimail","noheader" => True, "nonavbar" => True,
		"enable_nextmatchs_class" => True, "enable_network_class" => True);
		
	include("../header.inc.php");
	
	$mailbox = $MAILBOX;
	

	$phpgw->common->phpgw_header();
	echo parse_navbar();

	if ($totalerrors) 
	{
		echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";
	}

	include "src/load_prefs.php";
	
	if (! isset($action))
		$action = '';
		
	if (! isset($message_highlight_list))
		$message_highlight_list = array();
		
	if ($action == "delete" && isset($theid)) 
	{
		removePref($data_dir, $username, "highlight$theid");
		$phpgw->preferences->delete("felamimail", "highlight$theid");
		$phpgw->preferences->save_repository();
	} 
	else if ($action == "save") 
	{
		if (!$theid) $theid = 0;
		$identname = ereg_replace(",", " ", $identname);
		$identname = str_replace("\\\\", "\\", $identname);
		$identname = str_replace("\\\"", "\"", $identname);
		$identname = str_replace("\"", "&quot;", $identname);
		if ($color_type == 1) 
			$newcolor = $newcolor_choose;
		else 
			$newcolor = $newcolor_input;
			
		$newcolor = ereg_replace(",", "", $newcolor);
		$newcolor = ereg_replace("#", "", $newcolor);
		$newcolor = "$newcolor";
		$value = ereg_replace(",", " ", $value);
		$value = str_replace("\\\\", "\\", $value);
		$value = str_replace("\\\"", "\"", $value);
		$value = str_replace("\"", "&quot;", $value);
		
		#setPref($data_dir, $username, "highlight$theid", $identname.",".$newcolor.",".$value.",".$match_type);
		$phpgw->preferences->add("felamimail","highlight$theid", $identname.",".$newcolor.",".$value.",".$match_type);
		$phpgw->preferences->save_repository();
		$message_highlight_list[$theid]["name"] = $identname;
		$message_highlight_list[$theid]["color"] = $newcolor;
		$message_highlight_list[$theid]["value"] = $value;
		$message_highlight_list[$theid]["match_type"] = $match_type;
	} 

	include "src/load_prefs.php";

#	$tmpl = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	#$tmpl->set_unknowns('remove');

#	$tmpl->set_file(array('body' => 'preferences_index_order.tpl'));
	
	$var = Array
	(
		'th_bg'			=> $phpgw_info["theme"]["th_bg"],
		'tr_color1'		=> $phpgw_info['theme']['row_on'],
		'tr_color2'		=> $phpgw_info['theme']['row_off'],
		'link'			=> $phpgw->link('/felamimail/preferences_index_order.php'),
		'link_back'		=> $phpgw->link('/preferences/index.php'),
		'tablerows'		=> $tableRows,
		'options'		=> $options
	);
	
#	$tmpl->set_var($var);
	
	$translations = Array
	(
		'lang_return'		=> lang('Return to options page'),
		'lang_description'	=> lang("The index order is the order that the columns are arranged in the message index.  You can add, remove, and move columns around to customize them to fit your needs.")
	);
#	$tmpl->set_var($translations);

#	$tmpl->pparse('out','body');
?>
   <br>

<p><b><?php echo lang("Message Highlighting"); ?></b><hr><p>


<?php
   echo "<br><center>[<a href=\"". $phpgw->link('/felamimail/preferences_highlight.php','action=add')."\">" . lang("New") . "</a>]";
   echo " - [<a href=\"". $phpgw->link('/preferences/index.php')."\">".lang("Done")."</a>]</center><br>\n";
   if (count($message_highlight_list) >= 1) {
      echo "<table border=0 cellpadding=3 cellspacing=0 align=center width=80%>\n";
      for ($i=0; $i < count($message_highlight_list); $i++) {
         echo "<tr>\n";
         echo "   <td width=1% bgcolor=" . $color[4] . ">\n";
         echo "<nobr><small>[<a href=\"".$phpgw->link('/felamimail/preferences_highlight.php',"action=edit&theid=$i")."\">" . lang("Edit") . "</a>]&nbsp;[<a href=\"".$phpgw->link('/felamimail/preferences_highlight.php',"action=delete&theid=$i")."\">".lang("Delete")."</a>]</small></nobr>\n";
         echo "   </td>";
         echo "   <td bgcolor=" . $message_highlight_list[$i]["color"] . ">\n";
         echo "      " . $message_highlight_list[$i]["name"];
         echo "   </td>\n";
         echo "   <td bgcolor=" . $message_highlight_list[$i]["color"] . ">\n";
         echo "      ".$message_highlight_list[$i]["match_type"]." = " . $message_highlight_list[$i]["value"];
         echo "   </td>\n";
         echo "</tr>\n";
      }
      echo "</table>\n";
      echo "<br>\n";
   } else {
      echo "<center>" . lang("No highlighting is defined") . "</center><br>\n";
      echo "<br>\n";
   }
   if ($action == "edit" || $action == "add") {
      if (!isset($theid)) $theid = count($message_highlight_list);
          $message_highlight_list[$theid] = array();
 
      $color_list[0] = "4444aa";
      $color_list[1] = "44aa44";
      $color_list[2] = "aaaa44";
      $color_list[3] = "44aaaa";
      $color_list[4] = "aa44aa";
      $color_list[5] = "aaaaff";
      $color_list[6] = "aaffaa";
      $color_list[7] = "ffffaa";
      $color_list[8] = "aaffff";
      $color_list[9] = "ffaaff";
      $color_list[10] = "aaaaaa";
      $color_list[11] = "bfbfbf";
      $color_list[12] = "dfdfdf";
      $color_list[13] = "ffffff";               
      
      $selected_input = "";
      
      for ($i=0; $i < 14; $i++) {
         ${"selected".$i} = "";
      }
      if (isset($message_highlight_list[$theid]["color"])) {
         for ($i=0; $i < 14; $i++) {
            if ($color_list[$i] == $message_highlight_list[$theid]["color"]) {
               $selected_choose = " checked";
               ${"selected".$i} = " selected";
               continue;
            }
	     }
      }
      if (!isset($message_highlight_list[$theid]["color"]))
         $selected_choose = " checked";
      else if (!isset($selected_choose))
         $selected_input = " checked";
 
      echo "<form name=f method=post action=\"". $phpgw->link('/felamimail/preferences_highlight.php')."\">\n";
      echo "<input type=\"hidden\" value=\"save\" name=\"action\">\n";
      echo "<input type=\"hidden\" value=\"$theid\" name=\"theid\">\n";
      echo "<table width=80% align=center cellpadding=3 cellspacing=0 border=0>\n";
      echo "   <tr bgcolor=\"$color[0]\">\n";
      echo "      <td align=right width=25%><b>\n";
      echo lang("Identifying name") . ":";
      echo "      </b></td>\n";
      echo "      <td width=75%>\n";
      if (isset($message_highlight_list[$theid]["name"]))
          $disp = $message_highlight_list[$theid]["name"];
      else
          $disp = "";
      $disp = str_replace("\\\\", "\\", $disp);
      $disp = str_replace("\\\"", "\"", $disp);
      $disp = str_replace("\"", "&quot;", $disp);
      echo "         <input type=\"text\" value=\"".$disp."\" name=\"identname\">";
      echo "      </td>\n";
      echo "   </tr>\n";
      echo "   <tr><td><small><small>&nbsp;</small></small></td></tr>\n";
      echo "   <tr bgcolor=\"$color[0]\">\n";
      echo "      <td align=right width=25%><b>\n";
      echo lang("Color") . ":";
      echo "      </b></td>\n";
      echo "      <td width=75%>\n";
      echo "         <input type=\"radio\" name=color_type value=1$selected_choose> &nbsp;<select name=newcolor_choose>\n";
      echo "            <option value=\"$color_list[0]\"$selected0>" . lang("Dark Blue") . "\n";
      echo "            <option value=\"$color_list[1]\"$selected1>" . lang("Dark Green") . "\n";
      echo "            <option value=\"$color_list[2]\"$selected2>" . lang("Dark Yellow") . "\n";
      echo "            <option value=\"$color_list[3]\"$selected3>" . lang("Dark Cyan") . "\n";
      echo "            <option value=\"$color_list[4]\"$selected4>" . lang("Dark Magenta") . "\n";
      echo "            <option value=\"$color_list[5]\"$selected5>" . lang("Light Blue") . "\n";
      echo "            <option value=\"$color_list[6]\"$selected6>" . lang("Light Green") . "\n";
      echo "            <option value=\"$color_list[7]\"$selected7>" . lang("Light Yellow") . "\n";
      echo "            <option value=\"$color_list[8]\"$selected8>" . lang("Light Cyan") . "\n";
      echo "            <option value=\"$color_list[9]\"$selected9>" . lang("Light Magenta") . "\n";
      echo "            <option value=\"$color_list[10]\"$selected10>" . lang("Dark Gray") . "\n";
      echo "            <option value=\"$color_list[11]\"$selected11>" . lang("Medium Gray") . "\n";
      echo "            <option value=\"$color_list[12]\"$selected12>" . lang("Light Gray") . "\n";
      echo "            <option value=\"$color_list[13]\"$selected13>" . lang("White") . "\n";
      echo "         </select><br>\n";
      echo "         <input type=\"radio\" name=color_type value=2$selected_input> &nbsp;". lang("Other:") ."<input type=\"text\" value=\"";
      if ($selected_input) echo $message_highlight_list[$theid]["color"];
      echo "\" name=\"newcolor_input\" size=7> ".lang("Ex: 63aa7f")."<br>\n";
      echo "      </td>\n";
      echo "   </tr>\n";
      echo "   <tr><td><small><small>&nbsp;</small></small></td></tr>\n";
      echo "   <tr bgcolor=\"$color[0]\">\n";
      echo "      <td align=right width=25%><b>\n";
      echo lang("Match") . ":";
      echo "      </b></td>\n";
      echo "      <td width=75%>\n";
      echo "         <select name=match_type>\n";
      if (isset($message_highlight_list[$theid]["match_type"]) && $message_highlight_list[$theid]["match_type"] == "from")    echo "            <option value=\"from\" selected>From\n";
      else                                                         echo "            <option value=\"from\">From\n";
      if (isset($message_highlight_list[$theid]["match_type"]) && $message_highlight_list[$theid]["match_type"] == "to")      echo "            <option value=\"to\" selected>To\n";
      else                                                         echo "            <option value=\"to\">To\n";
      if (isset($message_highlight_list[$theid]["match_type"]) && $message_highlight_list[$theid]["match_type"] == "cc")      echo "            <option value=\"cc\" selected>Cc\n";
      else                                                         echo "            <option value=\"cc\">Cc\n";
      if (isset($message_highlight_list[$theid]["match_type"]) && $message_highlight_list[$theid]["match_type"] == "to_cc")   echo "            <option value=\"to_cc\" selected>To or Cc\n";
      else                                                         echo "            <option value=\"to_cc\">To or Cc\n";
      if (isset($message_highlight_list[$theid]["match_type"]) && $message_highlight_list[$theid]["match_type"] == "subject") echo "            <option value=\"subject\" selected>Subject\n";
      else                                                         echo "            <option value=\"subject\">Subject\n";
      echo "         </select>\n";
      if (isset($message_highlight_list[$theid]["value"]))
          $disp = $message_highlight_list[$theid]["value"];
      else
          $disp = '';
      $disp = str_replace("\\\\", "\\", $disp);
      $disp = str_replace("\\\"", "\"", $disp);
      $disp = str_replace("\"", "&quot;", $disp);
      echo "         <nobr><input type=\"text\" value=\"".$disp."\" name=\"value\">";
      echo "        <nobr></td>\n";
      echo "   </tr>\n";
      echo "</table>\n";
      echo "<center><input type=\"submit\" value=\"" . lang("Submit") . "\"></center>\n";
      echo "</form>\n";
      do_hook("options_highlight_bottom");
   } 
?>
<?php	
	$phpgw->common->phpgw_footer(); 
?>
