<?php
   /**
    **  folders_rename_do.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Does the actual renaming of files on the IMAP server. 
    **  Called from the folders.php
    **
    **  $Id$
    **/

	// store the value of $mailbox, because it will overwriten
	$MAILBOX = $mailbox;
	$phpgw_info["flags"] = array(
		'noheader'    => 'True',
		'nonavbar'    => 'True',
		"currentapp" => "felamimail"
	);
	include("../header.inc.php");
	$mailbox = $MAILBOX;

	$phpgw->session->restore();

   if (!isset($strings_php))
      include(PHPGW_APP_ROOT . '/inc/strings.php');
   if (!isset($config_php))
      include(PHPGW_APP_ROOT . "/config/config.php");
   if (!isset($page_header_php))
      include(PHPGW_APP_ROOT . '/inc/page_header.php');
   if (!isset($imap_php))
      include(PHPGW_APP_ROOT . '/inc/imap.php');

   include(PHPGW_APP_ROOT . "/src/load_prefs.php");


   if($old_name == $new_name) {
      $location = get_location();
      header ("Location: $location/folders.php");
      exit;
   }

   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   $dm = sqimap_get_delimiter($imapConnection);

   if (strpos($orig, $dm))
      $old_dir = substr($orig, 0, strrpos($orig, $dm));
   else
      $old_dir = "";

   if ($old_dir != "")
      $newone = "$old_dir$dm$new_name";
   else
      $newone = "$new_name";

   $orig = sqStripSlashes($orig);
   $newone = sqStripSlashes($newone);

   fputs ($imapConnection, ". RENAME \"$orig\" \"$newone\"\r\n");
   $data = sqimap_read_data($imapConnection, ".", true, $a, $b);

   // Renaming a folder doesn't renames the folder but leaves you unsubscribed
   //    at least on Cyrus IMAP servers.
   if (isset($isfolder)) {
      $newone = $newone.$dm;
      $orig = $orig.$dm;
   }   
   sqimap_unsubscribe($imapConnection, $orig);
   sqimap_subscribe($imapConnection, $newone);

	fputs ($imapConnection, "a001 LIST \"\" \"$newone*\"\r\n");
   $data = sqimap_read_data($imapConnection, "a001", true, $a, $b);
   for ($i=0; $i < count($data); $i++)
   {
      $name = find_mailbox_name($data[$i]);

      if ($name != $newone) // don't try to resubscribe when renaming ab to abc
      {
        sqimap_unsubscribe($imapConnection, $name);
        $name = substr($name, strlen($orig));
        $name = $newone . $name;
        sqimap_subscribe($imapConnection, $name);
      }
   }

   /** Log out this session **/
   sqimap_logout($imapConnection);
   header ("Location: ".$phpgw->link('/felamimail/folders.php','success=rename'));

?>
