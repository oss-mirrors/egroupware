<?php
	/*
	JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
	Authors:	Pim Snel, Lex Vogelaar for Lingewoud
	Copyright (C)2005 Pim Snel <pim@lingewoud.nl>

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

	---------------------------------------------------------------------

   	/*-------------------------------------------------------------------
	Boolean PLUGIN
	-------------------------------------------------------------------*/
//_debug_array('including boolean plugin class file');

		//when creating new plugins, make sure to add entries for it in the configurations class
	class db_fields_plugin_boolean
	{
	
		function db_fields_plugin_boolean()
		{
//_debug_array('db_fields_plugin_boolean constructor called');
		}
		
		function formview_edit($field_name,$value, $config,$attr_arr)
		{
			if(!is_null($config['ON_output_value_If_not_the_same_as_input_value'])) $val_on=$config['ON_output_value_If_not_the_same_as_input_value'];
			else $val_on=$config['ON_input_display_value'];
	
			// FIXME
			if($config['OFF_output_value_If_not_the_same_as_input_value']=='0') $val_off=$config['OFF_output_value_If_not_the_same_as_input_value'];
			elseif($config['OFF_output_value_If_not_the_same_as_input_value']) $val_off=$config['OFF_output_value_If_not_the_same_as_input_value'];
			else $val_off=$config['OFF_input_display_value'];
	
			if($value==$val_on) $on_select='SELECTED';
			elseif($value==$val_off) $off_select='SELECTED';
			elseif($value || $config['Default_value']=='NOTHING') $empty_option='<option value=""></option>';
			elseif(!$value && $config['Default_value']=='ON') $on_select='SELECTED'; 
			elseif(!$value && $config['Default_value']=='OFF') $off_select='SELECTED'; 
	
	
			$input='<select name="'.$field_name.'">';
			$input.=$empty_option;
			$input.='<option '.$on_select.' value="'.$val_on.'">'.$config['ON_input_display_value'].'</option>';
			$input.='<option '.$off_select.' value="'.$val_off.'">'.$config['OFF_input_display_value'].'</option>';
			$input.='</select>';
	
			return $input;
		}
	
		function formview_read($value,$config)
		{
		   return $this->listview_read($value,$config,'');	
		}
		
		function listview_read($value,$config,$where_val_enc)
		{
	
			if(!is_null($config['ON_output_value_If_not_the_same_as_input_value'])) $val_on=$config['ON_output_value_If_not_the_same_as_input_value'];
			else $val_on=$config['ON_input_display_value'];
	
			// FIXME
			if($config['OFF_output_value_If_not_the_same_as_input_value']=='0') $val_off=$config['OFF_output_value_If_not_the_same_as_input_value'];
			elseif($config['OFF_output_value_If_not_the_same_as_input_value']) $val_off=$config['OFF_output_value_If_not_the_same_as_input_value'];
			else $val_off=$config['OFF_input_display_value'];
	
			if($value)
			{
			   if($value==$val_on) $display=$config['ON_input_display_value'];
			   elseif($value==$val_off) $display=$config['OFF_input_display_value'];
			}
			else $display=$config['OFF_input_display_value'];
			return $display;
		}
	}
 ?>
