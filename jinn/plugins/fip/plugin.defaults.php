<?
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



/*********************************************************************\
* $this->plugins['fip']['pluginname'] tells gives information about  *
* these vars are not optional                                         *
* the databasefieldtypes in db_field_hooks tells for which fields the *
* plugin can be used for                                              *
\*********************************************************************/

$this->plugins['fip']['default_area']['name'] 			= 'def_text';
$this->plugins['fip']['default_area']['title']			= 'default area plugin';
$this->plugins['fip']['default_area']['version']		= '0.9';
$this->plugins['fip']['default_area']['enable']		= 1;
$this->plugins['fip']['default_area']['db_field_hooks']= array
(
	'text',
);

function plugin_def_text($field_name,$value)
{

	$input='
	<textarea name="'.$field_name.'" style="width:100%; height:200">'.$value.'</textarea>
	';

	return $input;

}	

$this->plugins['fip']['default_area']['name'] 			= 'def_varchar';
$this->plugins['fip']['default_area']['title']			= 'default area plugin';
$this->plugins['fip']['default_area']['version']		= '0.9';
$this->plugins['fip']['default_area']['enable']		= 1;
$this->plugins['fip']['default_area']['db_field_hooks']= array
(
	'varchar',
);

function plugin_def_varchar($field_name,$value)
{

	$input='
	<textarea name="'.$field_name.'" style="width:100%; height:200">'.strip_tags($value).'</textarea>
	';

	return $input;

}	


?>
