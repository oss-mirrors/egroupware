<?php
   /*******************************************************************\
   * eGroupWare - JiNN                                                 *
   * http://www.egroupware.org                                         *
   * ----------------------------------------------------------------- *
   * Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare.    *
   * Copyright (C)2002-2004, 2003 Pim Snel <pim@lingewoud.nl>          *
   * ----------------------------------------------------------------- *
   * Switchboard Plugin                                                *
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


	class db_fields_plugin_switchboard
	{
	
		function db_fields_plugin_switchboard()
		{
		}
	
	   function formview_edit($field_name, $value, $config, $attr_arr)
	   {
		  if($value)
		  {
			 $val_arr=unserialize($value);
		  }
	
		  $switches_arr=explode(';',$config[Switchboard_data]);
	
		  if(is_array($switches_arr))
		  {
			 $input.= '<table><input type="hidden" name="'.$field_name.'" value="TRUE" />';
				foreach($switches_arr as $switch)
				{
				   $switch_tmp_arr=explode(':',$switch);
				   list($option_name,$options_tmp) = $switch_tmp_arr;
				   $option_arr=explode('/',$options_tmp);
					
				   $option_name=trim($option_name);
	
				   if($option_name)
				   {
					  $input.= '<tr><td>'.$option_name.':<input type="hidden" name="SWINAM'.$field_name.$option_name.'" value="'.$option_name.'"></td><td>';
	
							if(is_array($option_arr))
							foreach($option_arr as $option)
							{ 
							   unset($checked);
							   if($val_arr[$option_name]==$option)
							   {
								  $checked='checked="checked"'; 
							   }
							   
							   $input.='<input type="radio" '.$checked.' name="SWIOPT'.$field_name.$option_name.'" value="'.$option.'" />'.$option.'';
							}
							$input.= '</td></tr>';
				   }
	
				}
				$input.= '</table>';
		  }
	
		  return $input;
	   }
	
	   function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	   {
		  global $local_bo;
	
		  $names=$local_bo->common->filter_array_with_prefix($HTTP_POST_VARS,'SWINAM'.$field_name);
	
		  foreach($names as $name) 
		  {
			 $ret_arr[$name]=$HTTP_POST_VARS['SWIOPT'.$field_name.$name];
		  }
	
		  if(is_array($ret_arr))
		  {
			 return serialize($ret_arr);
		  }
	
		  return '-1'; /* return -1 when there no value to give but the function finished succesfully */
	   }
	
	
	   function formview_read($value, $config)
	   {
		  return $this->listview_read($value, $config,'');
	   }
	
	   function listview_read($value, $config,$where_val_enc)
	   {
		  return lang('Switchboard');
	   }
	}	
?>
