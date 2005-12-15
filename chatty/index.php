<?php
	/**************************************************************************\
	* eGroupWare - Chatty                                                      *
	* http://www.egroupware.org                                                *
	* Copyright (C) 2005  TITECA-BEAUPORT Olivier     oliviert@maphilo.com     *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

$GLOBALS['egw_info'] = array('flags' => array(
		'currentapp'	=>	'chatty',
		'noheader'	=>	true,
		'nonavbar'	=>	true,
		'noappheader'	=>	true,
		'noappfooter'	=>	true,
		'nofooter'	=>	true,
		'nocachecontrol' => true,
		'noapi'=>false,
		'disable_Template_class' => true
	));


include('../header.inc.php');
include('./inc/IXR_Lib.php');
include('./inc/class.xmlrpcserver.inc.php');

$xmlrpcserver = new xmlrpc_server(array(										
'chatty.sync'       	=>	'chatty.chatty.syncData',
'chatty.restoreWindows' => 	'chatty.chatty.restoreWindows',
'chatty.sendmsg' 		=>	'chatty.chatty.sendMsg'

));
		

	

		
?>
