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

	class db_fields_plugin_timestamp2integer
	{
		function db_fields_plugin_timestamp2integer() {}
		
		function formview_edit($field_name,$value,$config,$attr_arr)
		{	
			if($value)
			{
			   $input.='<strong>'.$value.'</strong><br/>';
			   $input.=lang('Give new timestamp').' <input type="checkbox" value="1" name="ST2IN2'.$field_name.'">';
			   $input.='<input type="hidden" value="1" name="'.$field_name.'">';
			   $input.='<input type="hidden" value="'.$value.'" name="ST2IN1'.$field_name.'">';
			}
			else
			{
			   $input.='<input type="hidden" value="1" name="'.$field_name.'">';
			   $input.=lang('automatic');
			}
			
			return $input;
		}
	
		function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
		{
			$field_1=$_POST['ST2IN1'.$field_name];//real value
			$field_2=$_POST['ST2IN2'.$field_name];// boolian for new timestamp
			
			if( !$field_1 || $field_2)
			{
			   $time=time();
			   return $time;
			}
			else
			{
				return $field_1;
			}
		}
	}	
?>
