<?php
/*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   phpGroupWare - http://www.phpgroupware.org
   
   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; version 2 of the License 

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
*/

	$setup_info['jinn']['name']		= 'jinn';
	$setup_info['jinn']['title']	= 'JiNN Data Manager';
	$setup_info['jinn']['version']	= '0.6.004';
	$setup_info['jinn']['app_order']= 15;
	$setup_info['jinn']['author'] = 'Pim Snel';
	$setup_info['jinn']['license']  = 'GPL';
	$setup_info['jinn']['note'] =
		'<p>JiNN is currently only tested with MySQL but because it only uses the phpGW-API database calls. JiNN is known not to work with PostgreSQL at the moment. We\'ll try to fix this as soon as possible.</p>';
	$setup_info['jinn']['description'] =
		'<p>JiNN is a multi-site, multi-database, multi-user/-group, database driven contentmanager written in and for the eGroupWare Framework. JiNN makes it possible to have a lot of site-databases moderated by a lot of people all in one system. Access Rights are assigned at table-level and every site can have one or more site-administrators.</p>
		<p>JiNN is a very useful tool for webdevelopers who need to setup a contentmanager for their frondend product. You\'re able to setup a nice idiot-proof interface for your complex database design within minutes. Even one with many, and many with many relations are a peace of cake.</p>';

	/* retrieve all plugin information here */

	$setup_info['jinn']['maintainer'] = array(
		'name'  => 'Pim Snel',
		'email' => 'pim@lingewoud.nl');
	$setup_info['jinn']['tables']	= array
	(
		'phpgw_jinn_acl',
		'phpgw_jinn_sites',
		'phpgw_jinn_site_objects'
	);

	$setup_info['jinn']['enable']		= 1;

	/* The hooks this app includes, needed for hooks registration */
	$setup_info['jinn']['hooks']		= array
	(
		'admin',
		'sidebox_menu',
		'preferences',
		'settings'
	);

	/* Dependencies for this app to work */
	$setup_info['jinn']['depends'][]	= array
	(
		'appname'  => 'phpgwapi',
		'versions' => Array('0.9.14','0.9.15','1.0.0')
	);


