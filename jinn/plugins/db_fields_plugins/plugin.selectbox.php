<?php
   /*******************************************************************\
   * eGroupWare - JiNN                                                 *
   * http://www.egroupware.org                                         *
   * ----------------------------------------------------------------- *
   * Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare.    *
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

   $this->plugins['selectbox']['name'] 				= 'selectbox';
   $this->plugins['selectbox']['title']				= 'Select Box';
   $this->plugins['selectbox']['author']			= 'Pim Snel';
   $this->plugins['selectbox']['version']			= '0.7';
   $this->plugins['selectbox']['enable']			= 1;
   $this->plugins['selectbox']['description']		= 'List a couple of values in a listbox....';
   $this->plugins['selectbox']['db_field_hooks']	= array
   (
	  'string',
	  'int',
	  'blob'
   );
   $this->plugins['selectbox']['help']			=  'If the hidden fields arent available they won\'t work';
   $this->plugins['selectbox']['config']		= array
   (
	  'Keys_seperated_by_commas'=>array('one,two,three','area',''),
	  'Value_seperated_by_commas'=>array('one,two,three','area',''),
	  'Activate_hidden_fields'=>array('two:emailaddress,three:phonenumber','area',''),
	  'Default_value'=>array('one','text',''),
	  'Empty_option_available'=> array(array('yes','no'),'select','')
   );

   $this->plugins['selectbox']['config_help']		= array
   (
	  'Keys_seperated_by_commas'=>'These keys are displayed to the user',
	  'Value_seperated_by_commas'=>'These values are stored in the database'
   );

   function plg_fi_selectbox($field_name,$value, $config,$attr_arr)
   {
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

/*		 if($config[Activate_hidden_fields])
		 $script="<script type=\"text/javascript\">
			function fillrouter(obj) {
			      if(obj.options[obj.selectedIndex].value == 'DSL') 
				        document.getElementById(\"dslarea\").style.display='';
						   else 
						         document.getElementById(\"dslarea\").style.display='none';  
							  }"
							  */

		 
		 $input='<select name="'.$field_name.'">';
			if($config['Empty_option_available']=='yes') $input.='<option>';
			$i=0;
			foreach($pos_values as $pos_val) 
			{
				
			   $pos_val=trim($pos_val);
			   $value=trim($value);
			   
				// quick fix for handling with 0's 
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
				  $selected='SELECTED';	
			   }
			   elseif(strval($value)==strval($pos_val))
			   {
	//				  die($value .' '. $pos_val);
				  $selected='SELECTED';	
			   }

			   $input.='<option '.$selected.' value="'.trim($pos_val).'">'.trim($keys[$i]).'</option>';
			   $i++;
			}
			$input.='</select>';
	  }	
	  else
	  {
		 $input= '<input name="'.$field_name.'" type=text value="'.$value.'">';
	  }

	  return $input;
   }

   function plg_ro_selectbox($value, $config)
   {
	  return plg_bv_selectbox($value, $config,'');
   }

   function plg_bv_selectbox($value, $config,$where_val_enc)
   {

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
			}
			$i++;
		 }
	  }	
	  return $display;
   }
?>
