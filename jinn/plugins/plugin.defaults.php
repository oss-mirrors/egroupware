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


	/**
	* DEFAULT/FALLBACK BLOB/TEXT/TEXTAREA PLUGIN 
	* @abstract default field plugin for text/blob fields
	*/
	$this->plugins['def_blob']['name'] 				= 'def_blob';
	$this->plugins['def_blob']['title']				= 'default textarea';
	$this->plugins['def_blob']['version']			= '1.2';
	$this->plugins['def_blob']['author']			= 'Pim Snel';
	$this->plugins['def_blob']['enable']			= 1;
	$this->plugins['def_blob']['default']			= 1;
	$this->plugins['def_blob']['db_field_hooks']	= array
	(
		'text',
		'blob',
	 );

	 $this->plugins['def_blob']['config']		= array
	 (
		'New_height_in_pixels' => array('100','text','maxlength=3 size=3'), 
	 );
	   

	function plg_fi_def_blob($field_name,$value, $config,$attr_arr)
	{
	   if($config['New_height_in_pixels'] && is_numeric(intval($config['New_height_in_pixels']))) $height=intval($config['New_height_in_pixels']);
	   else $height = '100';
	   
	   $input='<textarea name="'.$field_name.'" style="padding:1px;border:solid 1px #cccccc;width:460px; height:'.$height.'px">'.$value.'</textarea>';
		return $input;
	}

	function plg_bv_def_blob($value, $config,$attr_arr)
	{
	   if(strlen($value)>20)
	   {
		  $value = strip_tags($value);

		  $value = '<span title="'.substr($value,0,200).'">' . substr($value,0,20). ' ...' . '</span>';
	   }
	   return $value;   		
	}
	
	/**
	* DEFAULT/FALLBACK AUTO INCREMENTING PLUGIN 
	*/
	$this->plugins['def_auto']['name'] 				= 'def_auto';
	$this->plugins['def_auto']['title']				= 'default auto incr.';
	$this->plugins['def_auto']['author']			= 'Pim Snel';
	$this->plugins['def_auto']['version']			= '1.0';
	$this->plugins['def_auto']['enable']			= 1;
	$this->plugins['def_auto']['default']			= 1;
	$this->plugins['def_auto']['db_field_hooks']	= array
	(
	   'int'
	);

	function plg_fi_def_auto($field_name, $value, $config,$attr_arr)
	{

	   if(!$value) $display_value=lang('automaticly incrementing');
	   $input='<b>'.$value.'</b><input type="hidden" name="'.$field_name.'" value="'.$value.'">'.$display_value;

	   return $input;
	}

	/**
	* DEFAULT/FALLBACK BINARY PLUGIN
	*/
	$this->plugins['def_binary']['name'] 			= 'def_binary';
	$this->plugins['def_binary']['title']			= 'default binary';
	$this->plugins['def_binary']['author']			= 'Pim Snel';
	$this->plugins['def_binary']['version']			= '1.0';
	$this->plugins['def_binary']['enable']			= 1;
	$this->plugins['def_binary']['default']			= 1;
	$this->plugins['def_binary']['db_field_hooks']	= array
	(
	   'blob',
	   'text',
	);
	
	function plg_fi_def_binary($field_name,$value, $config,$attr_arr)
	{
	   if($value) $text=lang('binary, contains data');
	   else $text =lang('binary, empty');
	   
	   return '<span style="font-style:italic;">'.$text.'</span>';
	}
	
	function plg_bv_def_binary($value, $config,$attr_arr)
	{
	   if($value) $text=lang('binary, contains data');
	   else $text =lang('binary, empty');
	   
	   return '<span style="font-style:italic;">'.$text.'</span>';
	}


	/**
	* DEFAULT/FALLBACK VARCHAR PLUGIN 
	*/
	$this->plugins['def_string']['name'] 			= 'def_string';
	$this->plugins['def_string']['title']			= 'default varchar';
	$this->plugins['def_string']['author']			= 'Pim Snel';
	$this->plugins['def_string']['version']			= '1.1';
	$this->plugins['def_string']['enable']			= 1;
	$this->plugins['def_string']['default']			= 1;
	$this->plugins['def_string']['db_field_hooks']	= array
	(
		'string'
	);

	function plg_fi_def_string($field_name, $value, $config,$attr_arr)
	{
	   if($attr_arr['max_size'])
	   {
		  if($attr_arr['max_size']>40) $size=40;
		  else $size=$attr_arr['max_size'];

		  $max='size="'.$size.'" maxlength="'.$attr_arr['max_size'].'"';	
	   }

	   $input='<input type="text" name="'.$field_name.'" '.$max.' value="'.strip_tags($value).'">';

		return $input;
	}	

	function plg_bv_def_string($value, $config,$attr_arr)
	{
	   if(strlen($value)>20)
	   {
		  $value = strip_tags($value);

		  $value = '<span title="'.substr($value,0,200).'">' . substr($value,0,20). ' ...' . '</span>';
	   }
	   return $value;   		
	}
	
	/**
	* DEFAULT/FALLBACK INTEGER PLUGIN 
	*/
	$this->plugins['def_int']['name'] 			= 'def_int';
	$this->plugins['def_int']['title']			= 'default int plugin';
	$this->plugins['def_int']['version']		= '1.0';
	$this->plugins['def_int']['author']			= 'Pim Snel';
	$this->plugins['def_int']['enable']			= 1;
	$this->plugins['def_int']['default']		= 1;
	$this->plugins['def_int']['db_field_hooks']	= array
	(
	   'int'
	);

	function plg_fi_def_int($field_name,$value, $config,$attr_arr)
	{
		$input='<input type="text" name="'.$field_name.'" size="10" value="'.$value.'">';

		return $input;
	}

	function plg_bv_def_int($value, $config,$attr_arr)
	{
	   return $value;   		
	}
	
	/**
	* DEFAULT/FALLBACK TIMESPAMP/DATE PLUGIN 
	*/
	$this->plugins['def_timestamp']['name'] 			= 'def_timestamp';
	$this->plugins['def_timestamp']['title']			= 'default timestamp';
	$this->plugins['def_timestamp']['version']		= '1.1';
	$this->plugins['def_timestamp']['author']		= 'Pim Snel';
	$this->plugins['def_timestamp']['enable']			= 1;
	$this->plugins['def_timestamp']['db_field_hooks']	= array
	(
		'timestamp',	
	);

	function plg_fi_def_timestamp($field_name,$value, $config,$attr_arr)
	{
		global $local_bo;
		if ($value)
		{
		   $input=$local_bo->common->format_date($value);
		}
		else
		{
		   $input = lang('automatic');
		}

		return $input;
	}

	function plg_bv_def_timestamp($value, $config,$attr_arr)
	{
	   return $value;   		
	}

?>
