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
		* $this->plugins['fip']['pluginname'] tells gives information about   *
		* these vars are not optional                                         *
		* the databasefieldtypes in db_field_hooks tells for which fields the *
		* plugin can be used for                                              *
		\*********************************************************************/

//global $local_bo;
//var_dump($local_bo);

/* DEFAULT/FALLBACK BLOB/TEXT/TEXTAREA PLUGIN */
$this->plugins['default_area']['name'] 			= 'def_text';
$this->plugins['default_area']['title']			= 'default area plugin';
$this->plugins['default_area']['version']		= '1.0';
$this->plugins['default_area']['enable']		= 1;
$this->plugins['default_area']['db_field_hooks']	= array
(
	'text',
);

function plg_fi_def_text($field_name,$value, $config, $local_bo)
{
	$input='<textarea name="'.$field_name.'" style="width:100%; height:200">'.$value.'</textarea>';

	return $input;
}	

/* DEFAULT/FALLBACK VARCHAR PLUGIN */
$this->plugins['default_varchar']['name'] 			= 'def_varchar';
$this->plugins['default_varchar']['title']			= 'default varchar plugin';
$this->plugins['default_varchar']['version']		= '1.0';
$this->plugins['default_varchar']['enable']			= 1;
$this->plugins['default_varchar']['db_field_hooks']	= array
(
	'string',
);

function plg_fi_def_string($field_name, $value, $config, $local_bo)
{
	$input='<input type="text" name="'.$field_name.'" input_max_length" value="'.$value.'">';

	return $input;

}	

/* DEFAULT/FALLBACK INTEGER PLUGIN */
$this->plugins['default_int']['name'] 			= 'def_int';
$this->plugins['default_int']['title']			= 'default int plugin';
$this->plugins['default_int']['version']		= '1.0';
$this->plugins['default_int']['enable']			= 1;
$this->plugins['default_int']['db_field_hooks']	= array
(
	'int',	
);

function plg_fi_def_int($field_name,$value, $config, $local_bo)
{
	$input='<input type="text" name="'.$field_name.'" size="10" value="'.$value.'">';

	return $input;

}
						
/* DEFAULT/FALLBACK TIMESPAMP/DATE PLUGIN */
$this->plugins['default_int']['name'] 			= 'def_timestamp';
$this->plugins['default_int']['title']			= 'default timestamp plugin';
$this->plugins['default_int']['version']		= '1.0';
$this->plugins['default_int']['enable']			= 1;
$this->plugins['default_int']['db_field_hooks']	= array
(
	'timestamp',	
);

function plg_fi_def_timestamp($field_name,$value, $config, $xxx)
{

	global $local_bo;
	$input=$local_bo->format_date($value);
	
	return $input;
}


?>
