<?php
   /*******************************************************************\
   * eGroupWare - JiNN                                                 *
   * http://www.egroupware.org                                         *
   * ----------------------------------------------------------------- *
   * Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare.    *
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

   $this->plugins['switchboard']['name'] 			= 'switchboard';
   $this->plugins['switchboard']['title']			= 'Switchboard';
   $this->plugins['switchboard']['author']			= 'Pim Snel';
   $this->plugins['switchboard']['version']			= '0.1';
   $this->plugins['switchboard']['enable']			= 1;
   $this->plugins['switchboard']['screenshot']		= 'switchboard.png'; 
   $this->plugins['switchboard']['description']		= 'Switchboard with radio buttons.';
   $this->plugins['switchboard']['help']			= '
   <p>Create as many switches with as many options as you like in the following notation:<br/>
   NameSwitchOne:option 1/option 2/option 3;</br>
   NameSwitchTwo:yes/no;
   NameSwitchThree:0/1/2/3/4/5;</p>
   <p>The above example produces the switchboard:</p>
   ';
   $this->plugins['switchboard']['db_field_hooks']	= array
   (
	  'string',
	  'varchar',
	  'longtext',
	  'text',
	  'blob'
   );
   $this->plugins['switchboard']['config']		= array
   (
	  'Switchboard_data'=>array("NameSwitchOne:option 1/option 2/option 3;\nNameSwitchTwo:yes/no;\nNameSwitchThree:0/1/2/3/4/5;",'area',''),
	  'Store_as'=>array(array('serialized array','string with seperation characters'),'select',''),
	  'Store_as'=>array(array('serialized array'),'select',''),
	 // 	  'Seperation_character_when_storing_as_string'=>array(array(';',',','|','/','[space]'),'select',''),
   );


   function plg_fi_switchboard($field_name, $value, $config, $attr_arr)
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

			   if(trim($option_name))
			   {
				  $input.= '<tr><td>'.$option_name.':<input type="hidden" name="SWINAM'.$field_name.$option_name.'" value="'.$option_name.'"></td><td>';

						if(is_array($option_arr))
						foreach($option_arr as $option)
						{ 
						   unset($checked);
						   if($val_arr[trim($option_name)]==$option)
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

   function plg_sf_switchboard($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
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


   function plg_ro_switchboard($value, $config)
   {
	  return plg_bv_switchboard($value, $config,'');
   }

   function plg_bv_switchboard($value, $config,$where_val_enc)
   {
	  return lang('Switchboard');
   }

?>
