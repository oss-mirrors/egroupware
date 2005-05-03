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

	class db_fields_plugin_boolean
	{
	
		function db_fields_plugin_boolean()
		{
		}
		
		function formview_edit($field_name,$value, $config,$attr_arr)
		{
			if($config['OFF_output_value_If_not_the_same_as_input_value'] == '')
			{
				$val_off=$config['OFF_input_display_value'];
			}
			else $val_off=$config['OFF_output_value_If_not_the_same_as_input_value'];
			
			if($config['ON_output_value_If_not_the_same_as_input_value'] == '')
			{
				$val_on=$config['ON_input_display_value'];
			}
			else $val_on=$config['ON_output_value_If_not_the_same_as_input_value'];
			
			if($value==$val_on) $on_select='SELECTED';
			elseif($value==$val_off) $off_select='SELECTED';
			elseif($value != '') $unknownvalue = true;
			elseif($config['Default_value']=='ON') $on_select='SELECTED'; 
			elseif($config['Default_value']=='OFF') $off_select='SELECTED'; 
	
	
			$input='<select name="'.$field_name.'">';
			if($unknownvalue) $input.='<option value="'.$value.'">?'.$value.'?</option>';
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
			if($config['OFF_output_value_If_not_the_same_as_input_value'] == '')
			{
				$val_off=$config['OFF_input_display_value'];
			}
			else $val_off=$config['OFF_output_value_If_not_the_same_as_input_value'];
			
			if($config['ON_output_value_If_not_the_same_as_input_value'] == '')
			{
				$val_on=$config['ON_input_display_value'];
			}
			else $val_on=$config['ON_output_value_If_not_the_same_as_input_value'];
			
		    if($value == $val_on)
			{
				$display = $config['ON_input_display_value'];
			}
		    elseif($value == $val_off)
			{
			$display = $config['OFF_input_display_value'];
			}
			else $display = '?'.$value.'?'; //this should not happen, except after changing the plugin maybe .. in that case, this value is a good visual error indicator
			return $display;
		}
	}
 ?>
