<?php
	/*
	JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
	Copyright (C)2002, 2004 Pim Snel <pim@lingewoud.nl>

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

	/* $Id: class.passwd_converter.php,v 1.1 2005/04/21 12:39:23 lex_vogelaar Exp $

	/**
	* DEFAULT/FALLBACK VARCHAR PLUGIN 
	*/

	class db_fields_plugin_passwd_converter
	{
	
		function db_fields_plugin_passwd_converter()
		{
		}
		
		function formview_edit($field_name, $value, $config,$attr_arr)
		{
		   if($attr_arr['max_size'])
		   {
			  if($attr_arr['max_size']>40) 
			  {
				 $size=40;
			  }
			  else
			  {
				 $size=$attr_arr['max_size'];
			  }
	
			  $max='size="'.$size.'" maxlength="'.$attr_arr['max_size'].'"';	
		   }

		   $value=strip_tags($value);
		   $value=str_replace('"','&quot;',$value);

		   $input='<input type="password" name="'.$field_name.'" '.$max.' value="'.$value.'"><input type="checkbox" value="yes" name="ALTER_'.$field_name.'">'.lang('Alter');
	
			return $input;
		 }	

		 function listview_read($value, $config,$attr_arr)
		 {
			$value="*********";
			return $value;   		
		 }
		 function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
		 {
			if($HTTP_POST_VARS['ALTER_'.$field_name] == 'yes')
			{
			   $value = md5($HTTP_POST_VARS[$field_name]);
			}
			else
			{
			   $value = $HTTP_POST_VARS[$field_name];
			}
			return $value;
		 }
		 

	}
?>
