<?php
   /**
    **  folders_rename_getname.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Gets folder names and enables renaming
    **  Called from folders.php
    **
    **  $Id$
    **/

	// store the value of $mailbox, because it will overwriten
	$MAILBOX = $mailbox;
	$phpgw_info["flags"] = array("currentapp" => "felamimail", "enable_network_class" => True, "enable_nextmatchs_class" => True);
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

   include(PHPGW_APP_ROOT . "/src/load_prefs.php");

   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

   $dm = sqimap_get_delimiter($imapConnection);
   if (substr($old, strlen($old) - strlen($dm)) == $dm) {
      $isfolder = true;
      $old = substr($old, 0, strlen($old) - 1);
   }
   
   if (strpos($old, $dm)) {
      $old_name = substr($old, strrpos($old, $dm)+1, strlen($old));
      $old_parent = substr($old, 0, strrpos($old, $dm));
   } else {
      $old_name = $old;
      $old_parent = "";
   }

   $old_name = sqStripSlashes($old_name);

   displayPageHeader($imapConnection, $color, "None");
   echo "<br><TABLE align=center border=0 WIDTH=95% COLS=1>";
   echo "<TR><TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><B>";
   echo lang("Rename a folder");
   echo "</B></TD></TR>";
   echo "<TR><TD BGCOLOR=\"$color[4]\" ALIGN=CENTER>";
   echo "<FORM ACTION=\"".$phpgw->link('/felamimail/folders_rename_do.php')."\" METHOD=\"POST\">\n";
   echo lang("New name:");
   echo "<br><B>$old_parent . </B><INPUT TYPE=TEXT SIZE=25 NAME=new_name VALUE=\"$old_name\"><BR>\n";
   if (isset($isfolder))
      echo "<INPUT TYPE=HIDDEN NAME=isfolder VALUE=\"true\">";
   printf("<INPUT TYPE=HIDDEN NAME=orig VALUE=\"%s\">\n", $old);
   printf("<INPUT TYPE=HIDDEN NAME=old_name VALUE=\"%s\">\n", $old_name);
   echo "<INPUT TYPE=SUBMIT VALUE=\"".lang("Submit")."\">\n";
   echo "</FORM><BR></TD></TR>";
   echo "</TABLE>";

   /** Log out this session **/
   sqimap_logout($imapConnection);

	$phpgw->common->phpgw_footer();

?>


