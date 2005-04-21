<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
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
	* LAST RESORT FALLBACK PLUGIN 
	@abstract default field plugin for all field types
	*/
		 

	class db_fields_plugin_default_
	{
	
		function db_fields_plugin_default_()
		{
		}
		
		function listview_read($value, $config,$attr_arr)
		{
		   if(strlen($value)>20)
		   {
			  $value = strip_tags(htmlentities($value));
			  $title = substr($value,0,200);
			  $value = '<span title="'.$title.'" style="border-color: #ff0000;">' . substr($value,0,20). ' ...' . '</span>';
		   }
		   return '<font color="#ff0000">'.$value.'</font>';
		}
		
		function formview_edit($field_name,$value, $config,$attr_arr)
		{
		   if($config['New_height_in_pixels'] && is_numeric(intval($config['New_height_in_pixels']))) $height=intval($config['New_height_in_pixels']);
		   else $height = '100';
		   
		   $input='<textarea name="'.$field_name.'" style="padding:1px;border:solid 1px #ff0000;width:460px; height:'.$height.'px">'.$value.'</textarea>';
			return $input;
		}

		function listview_edit($field_name, $value, $config, $attr_arr)
		{
			return $this->formview_edit($field_name, $value, $config, $attr_arr);
		}

		function formview_read($value, $config)
		{
			return $this->listview_read($value, $config, '');
		}
		
	}
?>