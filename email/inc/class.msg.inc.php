<?php
	include(PHPGW_INCLUDE_ROOT."/email/inc/class.msg_common.inc.php");
	/* HvG20010502: The following goes wrong when "imaps" or "pop3s" is selected,
	   in that case it should include class.msg_imap.inc.php in stead
   	   of class.msg_imaps.inc.php: */
	if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"]=='imaps' ||
    	    $phpgw_info["user"]["preferences"]["email"]["mail_server_type"]=='pop3s')
        {
		/* HvG20010502: Because PHP's imap_open has imaps and pop3s support 
		   built-in, we use the class.msg_imap.inc.php for both these mail-
		   server types: */
	   include(PHPGW_INCLUDE_ROOT."/email/inc/class.msg_imap.inc.php");
	}
        else
        {
   	   /* Do what it normally does: */	
	   include(PHPGW_INCLUDE_ROOT."/email/inc/class.msg_".$phpgw_info["user"]["preferences"]["email"]["mail_server_type"].".inc.php");
  	}
?>
