<?php
   /**
    **  folders.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Handles all interaction between the user and the other folder
    **  scripts which do most of the work. Also handles the Special
    **  Folders.
    **
    **  $Id$
    **/

   $enablePHPGW = 1;

   	// store the value of $mailbox, because it will overwriten
   	$MAILBOX = $mailbox;
   	$phpgw_info["flags"] = array("currentapp" => "felamimail", "enable_network_class" => True, 
   			"enable_nextmatchs_class" => True);
   	include("../header.inc.php");
   	$mailbox = $MAILBOX;

	$phpgw->session->restore();

   if (!isset($strings_php))
      include(PHPGW_APP_ROOT . "/inc/strings.php");
   if (!isset($config_php))
      include(PHPGW_APP_ROOT . "/config/config.php");
   if (!isset($page_header_php))
      include(PHPGW_APP_ROOT . "/inc/page_header.php");
   if (!isset($imap_php))
      include(PHPGW_APP_ROOT . "/inc/imap.php");
   if (!isset($array_php))
      include(PHPGW_APP_ROOT . "/inc/array.php");
   if (!isset($plugin_php))
      include(PHPGW_APP_ROOT . "/inc/plugin.php");

   include(PHPGW_APP_ROOT . "/src/load_prefs.php");

#   displayPageHeader($imapConnection, $color, "None");

   echo "<br>";
   echo "<TABLE WIDTH=95% COLS=1 ALIGN=CENTER>\n";
   echo "   <TR><TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><b>\n";
   echo lang("Folders");
   echo "   </b></TD></TR>\n";
   echo "</TABLE>\n";

   if ((isset($success) && $success) || 
       (isset($sent_create) && $sent_create == "true") || 
       (isset($trash_create) && $trash_create == "true")) {
      echo "<table width=100% align=center cellpadding=3 cellspacing=0 border=0>\n";
      echo "   <tr><td><center>\n";
      if ($success == "subscribe") {
         echo "<b>" . lang("Subscribed successfully!") . "</b><br>";
      } else if ($success == "unsubscribe") {
         echo "<b>" . lang("Unsubscribed successfully!") . "</b><br>";
      } else if ($success == "delete") {
         echo "<b>" . lang("Deleted folder successfully!") . "</b><br>";
      } else if ($success == "create") {
         echo "<b>" . lang("Created folder successfully!") . "</b><br>";
      } else if ($success == "rename") {
         echo "<b>" . lang("Renamed successfully!") . "</b><br>";
      } else if (($sent_create == "true") || ($trash_create == "true")) {
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         if ($sent_create == "true") {
            sqimap_mailbox_create ($imapConnection, $sent_folder, "");  
         }
         if ($trash_create == "true") {
            sqimap_mailbox_create ($imapConnection, $trash_folder, "");
         }
         sqimap_logout($imapConnection);
         echo lang("Folders created successfully!");
      }

      echo "   </center></td></tr>\n";
      echo "</table><br>\n";
   }
   $imapConnection = sqimap_login ($username, $key, $imapServerAddress, $imapPort, 0);
   $boxes = sqimap_mailbox_list($imapConnection);

   //display form option for creating Sent and Trash folder
   if ($imap_server_type == "cyrus" && ($sent_folder != "none" || $trash_folder != "none")) {
      if ((!sqimap_mailbox_exists ($imapConnection, $sent_folder)) || 
	  (!sqimap_mailbox_exists ($imapConnection, $trash_folder))) {
         echo "<TABLE WIDTH=70% COLS=1 ALIGN=CENTER cellpadding=2 cellspacing=0 border=0>\n";
         echo "<TR><TD BGCOLOR=\"$color[9]\" ALIGN=CENTER><B>";
         echo lang("Special Folder Options");
         echo "</B></TD></TR>";
         echo "<TR><TD BGCOLOR=\"$color[0]\" ALIGN=CENTER>";
         echo lang("In order for SquirrelMail to provide the full set of options you need to create the special folders listed below.  Just click the check box and hit the create button.");
         echo "<FORM ACTION=\"".$phpgw->link('/felamimail/folders.php')."\" METHOD=\"POST\">\n";
         if (!sqimap_mailbox_exists ($imapConnection, $sent_folder) && $sent_folder != "none") {
            echo lang("Create Sent") . "<INPUT TYPE=checkbox NAME=sent_create value=true><br>\n";
         }
         if (!sqimap_mailbox_exists ($imapConnection, $trash_folder) && $trash_folder != "none"){
            echo lang("Create Trash") . "<INPUT TYPE=checkbox NAME=trash_create value=true><br>\n";
         }
         echo "<INPUT TYPE=submit VALUE=".lang("Create").">";
         echo "</FORM></TD></TR></TABLE><br>";
      }
   }

   /** DELETING FOLDERS **/
   echo "<TABLE WIDTH=70% COLS=1 ALIGN=CENTER cellpadding=2 cellspacing=0 border=0>\n";
   echo "<TR><TD BGCOLOR=\"$color[9]\" ALIGN=CENTER><B>";
   echo lang("Delete Folder");
   echo "</B></TD></TR>";
   echo "<TR><TD BGCOLOR=\"$color[0]\" ALIGN=CENTER>";

   $count_special_folders = 0;
       $num_max = 1;
       if (strtolower($imap_server_type) == "courier" || $move_to_trash)
               $num_max++;
       if ($move_to_sent)
               $num_max++;

   for ($p = 0; $p < count($boxes) && $count_special_folders < $num_max; $p++) {                                                                                 
      if (strtolower($boxes[$p]["unformatted"]) == "inbox")
         $count_special_folders++;
      else if (strtolower($imap_server_type) == "courier" &&
               strtolower($boxes[$p]["unformatted"]) == "inbox.trash")
         $count_special_folders++;
      else if ($boxes[$p]["unformatted"] == $trash_folder && $trash_folder)
         $count_special_folders++;
      else if ($boxes[$p]["unformatted"] == $sent_folder && $sent_folder)
         $count_special_folders++;
   }   

   if ($count_special_folders < count($boxes)) {
      echo "<FORM ACTION=\"".$phpgw->link('/felamimail/folders_delete.php')."\" METHOD=\"POST\">\n";
      echo "<TT><SELECT NAME=mailbox>\n";
      for ($i = 0; $i < count($boxes); $i++) {
         $use_folder = true;
	 if ((strtolower($boxes[$i]["unformatted"]) != "inbox") &&
	     ($boxes[$i]["unformatted"] != $trash_folder) && 
	     ($boxes[$i]["unformatted"] != $sent_folder) &&
	     (strtolower($imap_server_type) != "courier" ||
	      strtolower($boxes[$i]["unformatted"]) != "inbox.trash"))
	    {
	       $box = $boxes[$i]["unformatted-dm"];
	       $box2 = replace_spaces($boxes[$i]["unformatted-disp"]);
	       echo "         <OPTION VALUE=\"$box\">$box2\n";
	    }
      }
      echo "</SELECT></TT>\n";
      echo "<INPUT TYPE=SUBMIT VALUE=\"";
      echo lang("Delete");
      echo "\">\n";
      echo "</FORM></TD></TR>\n";
   } else {
      echo lang("No folders found") . "<br><br></td><tr>";
   }

   echo "<tr><td bgcolor=\"$color[4]\">&nbsp;</td></tr>\n";

   /** CREATING FOLDERS **/
   echo "<TR><TD BGCOLOR=\"$color[9]\" ALIGN=CENTER><B>";
   echo lang("Create Folder");
   echo "</B></TD></TR>";
   echo "<TR><TD BGCOLOR=\"$color[0]\" ALIGN=CENTER>";
   echo "<FORM NAME=cf ACTION=\"".$phpgw->link('/felamimail/folders_create.php')."\" METHOD=\"POST\">\n";
   echo "<INPUT TYPE=TEXT SIZE=25 NAME=folder_name><BR>\n";
   echo lang("as a subfolder of");
   echo "<BR>";
   echo "<TT><SELECT NAME=subfolder>\n";
   if (strtolower($imap_server_type) != "courier"){
     if ($default_sub_of_inbox == false)
       echo "<OPTION SELECTED>[ None ]\n";
     else
       echo "<OPTION>[ None ]\n";
   }

   for ($i = 0; $i < count($boxes); $i++) {
      if (count($boxes[$i]["flags"]) > 0) {
         $noinf = false;
         for ($j = 0; $j < count($boxes[$i]["flags"]); $j++) {
            if ($boxes[$i]["flags"][$j] == "noinferiors") {
               $noinf = true;
               continue;
            }
         }    
         if ($noinf == false) {
            if ((strtolower($boxes[$i]["unformatted"]) == "inbox") && ($default_sub_of_inbox == true)) {
               $box = $boxes[$i]["unformatted"];
               $box2 = replace_spaces($boxes[$i]["unformatted-disp"]);
               echo "<OPTION SELECTED VALUE=\"$box\">$box2\n";
            } else {
               $box = $boxes[$i]["unformatted"];
               $box2 = replace_spaces($boxes[$i]["unformatted-disp"]);
               if (strtolower($imap_server_type) != "courier" ||
                  strtolower($box) != "inbox.trash")
                echo "<OPTION VALUE=\"$box\">$box2\n";
            }
         }
      } else {
         if ((strtolower($boxes[$i]["unformatted"]) == "inbox") && ($default_sub_of_inbox == true)) {
            $box = $boxes[$i]["unformatted"];
            $box2 = replace_spaces($boxes[$i]["unformatted-disp"]);
            echo "<OPTION SELECTED VALUE=\"$box\">$box2\n";
         } else {
            $box = $boxes[$i]["unformatted"];
            $box2 = replace_spaces($boxes[$i]["unformatted-disp"]);
           if (strtolower($imap_server_type) != "courier" ||
               strtolower($box) != "inbox.trash")
             echo "<OPTION VALUE=\"$box\">$box2\n";
         }
      }
   }
   echo "</SELECT></TT><BR>\n";
   if ($show_contain_subfolders_option) {
      echo "<INPUT TYPE=CHECKBOX NAME=\"contain_subs\"> &nbsp;";
      echo lang("Let this folder contain subfolders");
      echo "<BR>";
   }   
   echo "<INPUT TYPE=SUBMIT VALUE=\"".lang("Create")."\">\n";
   echo "</FORM></TD></TR>\n";

   echo "<tr><td bgcolor=\"$color[4]\">&nbsp;</td></tr>\n";

   /** RENAMING FOLDERS **/
   echo "<TR><TD BGCOLOR=\"$color[9]\" ALIGN=CENTER><B>";
   echo lang("Rename a Folder");
   echo "</B></TD></TR>";
   echo "<TR><TD BGCOLOR=\"$color[0]\" ALIGN=CENTER>";
   if ($count_special_folders < count($boxes)) {
      echo "<FORM ACTION=\"".$phpgw->link('/felamimail/folders_rename_getname.php')."\" METHOD=\"POST\">\n";
      echo "<TT><SELECT NAME=old>\n";
      for ($i = 0; $i < count($boxes); $i++) {
         $use_folder = true;

	 if ((strtolower($boxes[$i]["unformatted"]) != "inbox") && 
	     ($boxes[$i]["unformatted"] != $trash_folder)  &&
	     ($boxes[$i]["unformatted"] != $sent_folder)) 
	    {	
	       $box = $boxes[$i]["unformatted-dm"];
	       $box2 = replace_spaces($boxes[$i]["unformatted-disp"]);
	       if (strtolower($imap_server_type) != "courier" || strtolower($box) != "inbox.trash")
		  echo "<OPTION VALUE=\"$box\">$box2\n";
	    }
      }
      echo "</SELECT></TT>\n";
      echo "<INPUT TYPE=SUBMIT VALUE=\"";
      echo lang("Rename");
      echo "\">\n";
      echo "</FORM></TD></TR>\n";
   } else {
      echo lang("No folders found") . "<br><br></td></tr>";
   }
   $boxes_sub = $boxes;

   echo "<tr><td bgcolor=\"$color[4]\">&nbsp;</td></tr></table>\n";
   
   /** UNSUBSCRIBE FOLDERS **/
   echo "<TABLE WIDTH=70% COLS=1 ALIGN=CENTER cellpadding=2 cellspacing=0 border=0>\n";
   echo "<TR><TD BGCOLOR=\"$color[9]\" ALIGN=CENTER colspan=3><B>";
   echo lang("Unsubscribe") . "/" . lang("Subscribe");
   echo "</B></TD></TR>";
   echo "<TR><TD BGCOLOR=\"$color[0]\" width=49% ALIGN=CENTER>";
   if ($count_special_folders < count($boxes)) {
      echo "<FORM ACTION=\"".$phpgw->link('/felamimail/folders_subscribe.php','method=unsub')."\" METHOD=\"POST\">\n";
      echo "<TT><SELECT NAME=mailbox[] multiple size=8>\n";
      for ($i = 0; $i < count($boxes); $i++) {
         $use_folder = true;
	 if ((strtolower($boxes[$i]["unformatted"]) != "inbox") &&
	     ($boxes[$i]["unformatted"] != $trash_folder) &&
	     ($boxes[$i]["unformatted"] != $sent_folder)) 
	    {	
	       $box = $boxes[$i]["unformatted-dm"];
	       $box2 = replace_spaces($boxes[$i]["unformatted-disp"]);
	       echo "         <OPTION VALUE=\"$box\">$box2\n";
	    }
      }
      echo "</SELECT></TT><br>\n";
      echo "<INPUT TYPE=SUBMIT VALUE=\"";
      echo lang("Unsubscribe");
      echo "\">\n";
      echo "</FORM></TD>\n";
   } else {
      echo lang("No folders were found to unsubscribe from!") . "</td>";
   }
   $boxes_sub = $boxes;

   echo "<td bgcolor=\"$color[9]\" width=2%>&nbsp;</td>";
   
   /** SUBSCRIBE TO FOLDERS **/
   echo "<TD BGCOLOR=\"$color[0]\" widtn=49% ALIGN=CENTER>";
   $imap_stream = sqimap_login ($username, $key, $imapServerAddress, $imapPort, 1);
   $boxes_all = sqimap_mailbox_list_all ($imap_stream);

   $box = "";
   $box2 = "";
   for ($i = 0, $q = 0; $i < count($boxes_all); $i++) {
      $use_folder = true;
      for ($p = 0; $p < count ($boxes); $p++) {
	 if ($boxes_all[$i]["unformatted"] == $boxes[$p]["unformatted"]) {
	    $use_folder = false;
	    continue;
	 } else if ($boxes_all[$i]["unformatted-dm"] == $folder_prefix) {
	    $use_folder = false;
	 }
      }
      if ($use_folder == true) {	
	 $box[$q] = $boxes_all[$i]["unformatted-dm"];
	 $box2[$q] = $boxes_all[$i]["unformatted-disp"];
	 $q++;
      }
   }
   sqimap_logout($imap_stream);

   if ($box && $box2) {
      echo "<FORM ACTION=\"".$phpgw->link('/felamimail/folders_subscribe.php','method=sub')."\" METHOD=\"POST\">\n";
      echo "<tt><select name=mailbox[] multiple size=8>";

      for ($q = 0; $q < count($box); $q++) {      
         echo "         <OPTION VALUE=\"$box[$q]\">".$box2[$q]."\n";
      }      
      echo "</select></tt><br>";
      echo "<INPUT TYPE=SUBMIT VALUE=\"". lang("Subscribe") . "\">\n";
      echo "</FORM></TD></TR></TABLE><BR>\n";
   } else {
      echo lang("No folders were found to subscribe to!") . "</td></tr></table>";
   }

   do_hook("folders_bottom");
   sqimap_logout($imapConnection);

   if ($enablePHPGW)
   {
   	$phpgw->session->save();
	$phpgw->common->phpgw_footer();
   }
   else
   {
   	print "</BODY></HTML>";
   }

?>
