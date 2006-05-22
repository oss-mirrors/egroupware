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


	class db_fields_plugin_priority
	{
	
		function db_fields_plugin_priority()
		{
		}
		
		 
		function formview_edit($field_name,$value,$config,$attr_arr)
		{	
			$color_arr[1]='#ffffff';
			$color_arr[2]='#ffebeb';
			$color_arr[3]='#ffd2d2';
			$color_arr[4]='#ffb4b4';
			$color_arr[5]='#ff9696';
			$color_arr[6]='#ff7878';
			$color_arr[7]='#ff5a5a';
			$color_arr[8]='#ff3c3c';
			$color_arr[9]='#ff2323';
			$color_arr[10]='#ff0000';
			
			if(!$value) $value=5;
	
			$input='<div style="background-color:'.$color_arr[$value].'"><sel'.'ect name="'.$field_name.'">';
			for($i=1;$i<=10;$i++)
			{
			   unset($selected);
			   if($i==$value)
			   {
				  $selected='selected="selected"';
			   }
			   $input.='<option style="color:black;background-color:'.$color_arr[$i].'" value="'.$i.'" '.$selected.'>'.$i.'</option>';
			};
			$input.='</s'.'elect></div>';
			
			return $input;
		 }
	
		 function formview_read($value,$config)
		 {
			return $this->listview_read($value,$config,'');	
		 }
	
		 function listview_read($value,$config,$where_val_enc)
		 {
			$color_arr[1]='#ffffff';
			$color_arr[2]='#ffebeb';
			$color_arr[3]='#ffd2d2';
			$color_arr[4]='#ffb4b4';
			$color_arr[5]='#ff9696';
			$color_arr[6]='#ff7878';
			$color_arr[7]='#ff5a5a';
			$color_arr[8]='#ff3c3c';
			$color_arr[9]='#ff2323';
			$color_arr[10]='#ff0000';
				if($value) return '<div style="background-color:'.$color_arr[$value].'">'.$value.'</div>';
		 }
	}
?>
