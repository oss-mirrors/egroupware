<?php
  /**************************************************************************\
  * phpGroupWare API - NNTP                                                  *
  * This file written by Mark Peters <skeeter@phpgroupware.org>              *
  * and Angelo Tony Puglisi (Angles) <angles@phpgroupware.org>              *
  * Handles specific operations in dealing with NNTP                         *
  * Copyright (C) 2001 Mark Peters                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

	//$debug_dcom = True;
	$debug_dcom = False;
	
// -----  is IMAP compiled into PHP
	//if (extension_loaded("imap"))
	//if (defined("TYPEVIDEO"))
	if (extension_loaded('imap') || function_exists('imap_open'))
	{
		$imap_builtin = True;
		$sock_fname = '';
		if ($debug_dcom) { echo 'imap builtin extension is available<br>'; }
	}
	else
	{
		$imap_builtin = False;
		$sock_fname = '_sock';
		if ($debug_dcom) { echo 'imap builtin extension NOT available, using socket class<br>'; }
	}

	// debug
	if ($debug_dcom)
	{
		$imap_builtin = False;
		$sock_fname = '_sock';
		if ($debug_dcom) { echo 'FORCE: imap builtin extension NOT available, using socket class<br>'; }
	}
	
	// SILENT DEBUG
	//$imap_builtin = False;
	//$sock_fname = '_sock';

// -----  include SOCKET or PHP-BUILTIN classes as nevessary
	if ($imap_builtin == False)
	{
		CreateObject('phpgwapi.network');
		if ($debug_dcom) { echo 'created phpgwapi network class used with sockets<br>'; }
	}

	//CreateObject('email.mail_dcom_base'.$sock_fname);
	include(PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_base'.$sock_fname.'.inc.php');
	if ($debug_dcom) { echo 'including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_base'.$sock_fname.'.inc.php<br>'; }

	if (($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] == 'imap')
	|| ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] == 'imaps'))
        {
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_imap'.$sock_fname.'.inc.php');
		if ($debug_dcom) { echo 'including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_imap'.$sock_fname.'.inc.php<br>'; }
	}
	elseif (($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] == 'pop3')
	|| ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] == 'pop3s'))
	{
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_pop3'.$sock_fname.'.inc.php');
		if ($debug_dcom) { echo 'including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_pop3'.$sock_fname.'.inc.php<br>'; }
	}
	elseif ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] == 'nntp')
	{
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_nntp'.$sock_fname.'.inc.php');
		if ($debug_dcom) { echo 'including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_nntp'.$sock_fname.'.inc.php<br>'; }
	}
	elseif ((isset($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type']))
	&& ($GLOBALS['phpgw_info']['user']['preferences']['email']['mail_server_type'] != ''))
	{
		// educated guess based on info being available:
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_'.$phpgw_info['user']['preferences']['email']['mail_server_type'].$sock_fname.'.inc.php');
		if ($debug_dcom) { echo 'Educated Guess: including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_'.$phpgw_info['user']['preferences']['email']['mail_server_type'].$sock_fname.'.inc.php<br>'; }
  	}
	else
	{
		// DEFAULT FALL BACK:
		include(PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_imap.inc.php');
		if ($debug_dcom) { echo 'NO INFO DEFAULT: including :'.PHPGW_INCLUDE_ROOT.'/email/inc/class.mail_dcom_imap.inc.php<br>'; }
	}
?>
