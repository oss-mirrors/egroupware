<?php
   /**
    **  addrbook_popup.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Frameset for the JavaScript version of the address book.
    **
    **  $Id$
    **/

   session_start();

   if (!isset($i18n_php))
      include('../functions/i18n.php');
   if (!isset($config_php))
      include('../config/config.php');
   if (!isset($page_header_php))
      include('../functions/page_header.php');
   if (!isset($auth_php))
      include('../functions/auth.php');
   if (!isset($addressbook_php))
      include('../functions/addressbook.php');

   is_logged_in();

   include('../src/load_prefs.php');
   
   set_up_language(getPref($data_dir, $username, 'language'));
   
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">

<HTML>
<HEAD>
<TITLE><?php 
   printf("%s: %s", $org_title, lang("Address Book")); 
?></TITLE>
</HEAD>

<FRAMESET ROWS="60,*" BORDER=0>
 <FRAME NAME="abookmain" MARGINWIDTH=0 SCROLLING=NO
        SRC="addrbook_search.php?show=form" BORDER=0>
 <FRAME NAME="abookres" MARGINWIDTH=0 SRC="addrbook_search.php?show=blank"
        BORDER=0>
</FRAMESET>

</HTML>
