<?php

// -----  is IMAP compiled into PHP
	//if (extension_loaded("imap"))
	if (defined("TYPEVIDEO"))
	{
		$imap_builtin = True;
		$sock_fname = '';
	}
	else
	{
		$imap_builtin = False;
		$sock_fname = '_sock';
	}
	
	/*
	// for now the SOCKET classes are INCOMPLETE
	$imap_builtin = True;
	$sock_fname = '';
	*/

// -----  this gets included no matter what
	include(PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_common.inc.php');

// -----  include SOCKET or PHP-BUILTIN classes as nevessary
	if ($imap_builtin == False)
	{
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_common'.$sock_fname.'.inc.php');
		//echo '<br>including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_common'.$sock_fname.'.inc.php';
	}


	if (($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'imap')
	|| ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'imaps'))
        {
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_imap'.$sock_fname.'.inc.php');
		//echo '<br>including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_imap'.$sock_fname.'.inc.php';
	}
	elseif (($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'pop3')
	|| ($phpgw_info['user']['preferences']['email']['mail_server_type'] == 'pop3s'))
	{
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_pop3'.$sock_fname.'.inc.php');
		//echo '<br>including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_pop3'.$sock_fname.'.inc.php';
	}
        else
        {
		// guess:
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_'.$sock_fname['user']['preferences']['email']['mail_server_type'].$sock_str.'.inc.php');
		//echo '<br>Guess: including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.msg_'.$sock_fname['user']['preferences']['email']['mail_server_type'].$sock_str.'.inc.php';
  	}

?>
