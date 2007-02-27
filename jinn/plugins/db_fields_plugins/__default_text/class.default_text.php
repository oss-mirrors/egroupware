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

	/* $Id$

	/**
	* DEFAULT/FALLBACK text/TEXT/TEXTAREA PLUGIN 
	@abstract default field plugin for text/text fields
	*/
	class db_fields_plugin_default_text
	{
		function formview_edit($field_name,$value, $config,$attr_arr)
		{
		   if($config['New_height_in_pixels'] && is_numeric(intval($config['New_height_in_pixels']))) $height=intval($config['New_height_in_pixels']);
		   else $height = '100';
		   
		   $input='<textarea name="'.$field_name.'" style="padding:1px;border:solid 1px #cccccc;width:460px; height:'.$height.'px">'.htmlentities($value).'</textarea>';
			return $input;
		}
	
		function listview_read($value, $config,$attr_arr)
		{
		   $value = strip_tags($value);
		   $value = htmlentities($value);
		   if(strlen($value)>20)
		   {

			  $_val=explode(' ',$value);

			  $value = implode(' ',array_slice($_val,0,5)); 

			  $value = '<span title="'.$title.'">' . $value. ' ...' . '</span>';
		   }
		   return $value;   		
		}
	}
?>
