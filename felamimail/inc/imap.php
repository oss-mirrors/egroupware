<?php
   /**  This just includes the different sections of the imap functions.
    **  They have been organized into these sections for simplicity sake.
    **
    **  $Id$
    **/

   $imap_php = true;
   $imap_backend = 'imap';
   
   include(PHPGW_APP_ROOT . '/inc/' . $imap_backend . '_mailbox.php');
   include(PHPGW_APP_ROOT . '/inc/' . $imap_backend . '_messages.php');
   include(PHPGW_APP_ROOT . '/inc/' . $imap_backend . '_general.php');
   include(PHPGW_APP_ROOT . '/inc/' . $imap_backend . '_search.php');
?>
