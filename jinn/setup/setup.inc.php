<?php
/*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   phpGroupWare - http://www.phpgroupware.org
   
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

	$setup_info['jinn']['name']		= 'jinn';
	$setup_info['jinn']['title']		= 'jinn';
	$setup_info['jinn']['version']	= '0.4.4';
	$setup_info['jinn']['app_order']	= 100;
	$setup_info['jinn']['tables']		= array
	(
		'phpgw_jinn_acl',
		'phpgw_jinn_sites',
        'phpgw_jinn_site_objects'
	);

	$setup_info['jinn']['enable']		= 1;

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['jinn']['hooks']		= array
	(
		'admin'
	);

	/* Dependacies for this app to work */
	$setup_info['jinn']['depends'][]	= array
	(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.13','0.9.14','0.9.15')
	);
?>
