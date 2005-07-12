<?php
   /*******************************************************************\
   * eGroupWare - JiNN                                                 *
   * http://www.egroupware.org                                         *
   * ----------------------------------------------------------------- *
   * Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare.    *
   * Copyright (C)2002-2004, 2003 Pim Snel <pim@lingewoud.nl>          *
   * ----------------------------------------------------------------- *
   * Select-box Plugin                                                 *
   * This file is part of JiNN                                         *
   * ----------------------------------------------------------------- *
   * This library is free software; you can redistribute it and/or     *
   * modify it under the terms of the GNU General Public License as    *
   * published by the Free Software Foundation; Version 2 of the       *
   * License                                                           *
   *                                                                   *
   * This program is distributed in the hope that it will be useful,   *
   * but WITHOUT ANY WARRANTY; without even the implied warranty of    *
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  *
   * General Public License for more details.                          *
   *                                                                   *
   * You should have received a copy of the GNU General Public License *
   * along with this program; if not, write to the Free Software       *
   * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.         *
   \*******************************************************************/

	class db_fields_plugin_selectbox
	{
	
		function db_fields_plugin_selectbox()
		{
		}
		

	   function formview_edit($field_name,$value, $config,$attr_arr)
	   {
		  $value_in_list = false;
		  $pos_values=explode(',',$config['Value_seperated_by_commas']);
		  if(is_array($pos_values))
		  {
	
			 if($config['Keys_seperated_by_commas'])
			 {
				$pos_keys=explode(',',$config['Keys_seperated_by_commas']);
				if(is_array($pos_keys) && count($pos_keys)==count($pos_values)) 
				{
				   $keys=$pos_keys;
				}
			 }
	
			 if(!$keys)	
			 {
				$keys=$pos_values;
			 }
	

			 if($config[Show_fields])
			 {
				$onchange.='jinnShowHideFields('.$field_name.',\'Show\','.$config[Show_fields].');';	

			 }
			 if($config[Hide_fields])
			 {
				$onchange.='jinnShowHideFields('.$field_name.',\'Hide\','.$config[Hide_fields].');';	
			 }

			 
				$input='<select onChange="'.$onchange.'" id="'.$field_name.'" name="'.$field_name.'">';
				if($config['Empty_option_available']=='yes') $input.='<option>';
				$i=0;
				foreach($pos_values as $pos_val) 
				{
					
				   $pos_val=trim($pos_val);
				   $value=trim($value);
				   
					// quick fix for handling with 0's 
					// todo: this is not a solution!!!!
				   if(strval($pos_val)=='0')
				   {
					  $pos_val='null'; 
				   }
	
				   if(strval($value)=='0')
				   {
					  $value='null'; 
				   }
	
				   if(strval($config['Default_value'])=='0')
				   {
					  $config['Default_value']='null'; 
				   }
					// end quick fix for handling with 0's 
	
				   unset($selected);
	
				   if(strval(empty($value)) && strval($pos_val)==strval($config['Default_value'])) 
				   {
					  $selected='selected="selected"';	
					  $value_in_list = true;
				   }
				   elseif(strval($value)==strval($pos_val))
				   {
					  $selected='selected';	
					  $value_in_list = true;
				   }
	
				   $input.='<option '.$selected.' value="'.trim($pos_val).'">'.trim($keys[$i]).'</option>';
				   $i++;
				}
				if(!$value_in_list) $input.='<option selected="selected" value="'.$value.'">?'.$value.'?</option>';
				$input.='</select>';
		  }	
		  else
		  {
			 $input= '<input name="'.$field_name.'" type=text value="'.$value.'">';
		  }
	
		  return $input;
	   }
	
	   function formview_read($value, $config)
	   {
		  return $this->listview_read($value, $config,'');
	   }
	
	   function listview_read($value, $config,$where_val_enc)
	   {
		  $value_in_list = false;
		  $pos_values=explode(',',$config['Value_seperated_by_commas']);
		  if(is_array($pos_values))
		  {
	
			 if($config['Keys_seperated_by_commas'])
			 {
				$pos_keys=explode(',',$config['Keys_seperated_by_commas']);
				if(is_array($pos_keys) && count($pos_keys)==count($pos_values)) 
				{
				   $keys=$pos_keys;
				}
			 }
	
			 if(!$keys)	
			 {
				$keys=$pos_values;
			 }
	
			 $i=0;
			 foreach($pos_values as $pos_val) 
			 {
				if($value==$pos_val) 
				{
				  $display = trim($keys[$i]);	
				  $value_in_list = true;
				}
				$i++;
			 }
		  }
		  if(!$value_in_list) $display = '?'.$value.'?';
		  return $display;
	   }
	}
?>
