<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
	Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

	eGroupWare - http://www.egroupware.org

	This file is part of JiNN

	JiNN is free software; you can redistribute it and/or modify it under
	the terms of the GNU General Public License as published by the Free
	Software Foundation; either version 2 of the License, or (at your 
	option) any later version.

	JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
	WARRANTY; without even the implied warranty of MERCHANTABILITY or 
	FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License 
	along with JiNN; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
	*/

	$phpgw_flags = Array(
		'currentapp'	=>	'jinn',
		'noheader'	=>	True,
		'nonavbar'	=>	True,
		'noappheader'	=>	True,
		'noappfooter'	=>	True,
		'nofooter'	=>	True
	);

	$GLOBALS['phpgw_info']['flags'] = $phpgw_flags;

	include('../header.inc.php');

	/*	old call*/
	//	Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.plug_config&plug_orig='.$_GET[plug_orig].'&plug_name='.$_GET[plug_name].'&hidden_name='.$_GET[hidden_name].'&hidden_val='.$_GET[hidden_val]));

	Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.plug_config&plug_orig='.$_GET[plug_orig].'&plug_name='.$_GET[plug_name].'&hidden_name='.$_GET[hidden_name].'&&field_name='.$_GET[field_name].'&object_id='.$_GET[object_id].'&hidden_val='.$_GET[hidden_val]));
	
	$GLOBALS['phpgw']->common->phpgw_exit();

?>
