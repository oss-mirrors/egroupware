<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Copyright (C)2005 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN
   ---------------------------------------------------------------------
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
   */
   
   /* $Id$ */

   /**
   * Generate Image Menu Items
   *
   * @package jinn_plugins
   * @author pim-AT-lingewoud-DOT-nl
   * @copyright (c) 2005 by Pim Snel
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class db_fields_plugin_colorpicker
   {
	  /**
	  * Constructor
	  */
	  function db_fields_plugin_colorpicker()
	  {
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
	  }

	  /**
	  * Form edit plugin which return html to use in the form
	  *
	  * This one askes for text input to generate the menu's from, 
	  * it also shows the available generated images
	  *
	  * @param string $field_name name of the calling field
	  * @param string $value value of the calling field
	  * @param array $config contains the stored configuration data of this field concerning this plugin
	  * @param array $attr_arr this can contain dynamicly added attributes when the field metadata is read which can change the behaviour of the plugin.
	  * @return string/array normally return the generated html to create the input
	  * @todo implement more than one image
	  */
	  function formview_edit($field_name, $value, $config,$attr_arr)
	  {
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');

		 if(!$this->javascript_inserted)
		 {
			$this->tplsav2->javascript = $this->tplsav2->fetch('colorpicker.javascriptfunctions.tpl.php');
		 }

		 $stripped_name=substr($field_name,6);	//the real field name
		 $prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
		 $helper_id = $this->local_bo->plug->registry->plugins['colorpicker']['helper_fields_substring'];
		 $prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)

		 //if($value) $img_arr=unserialize(base64_decode($value));

		 $this->tplsav2->assign('prefix',$prefix);
		 $this->tplsav2->assign('stripped_name',$stripped_name);
		 $this->tplsav2->assign('fieldname',$field_name);
		 $this->tplsav2->assign('value',$value);
		 $this->tplsav2->assign('text',$img_arr[0]);
		 $this->tplsav2->assign('input_text_name',$prefix.'_TXT_'.$stripped_name);
		 $input=$this->tplsav2->fetch('colorpicker.formview_edit.tpl.php');
		 return $input;
	  }

	  /**
	  * filter or manipulate the post value of a field before it is stored in 
	  * the database
	  *
	  * @param string $key which referers to the fielddata in the _POST array
	  * @param array $HTTP_POST_VARS the _POST array as if the form contains one single record
	  * @param array $HTTP_POST_FILES the _FILES array as if the form contains one single record
	  * $param array $config the plugin config array of this field
	  * @return string returns a serialized array with all img paths and the first element contains the text as string
	  * @todo check for valid color code
	  */
	  function on_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  {
		 return $HTTP_POST_VARS[$key];
	  }

	  function formview_read($value, $config)
	  {
		 return $this->listview_read($value, $config,'');
	  }


	  /**
	  * listview_read: return data to be displayed in the list view screen
	  *
	  * @param string $value value from the database
	  * @param array $config array with plugin config array for this field
	  * @param string $where_val_enc ????????
	  * @return text which is stored in the first element
	  * @todo complete phpdoc 
	  */
	  function listview_read($value, $config,$attr_arr)
	  {
		 return '<div style="margin:2px;width:34px;height:14px;background-color:'.$value.'"></div>';	
	  }
	
	  /***
	  * gex2RGB: converts a hex col to rgb
	  *
	  * If color can't be converted it returns black
	  *
	  * @param string $hexcolor the hex color string
	  * @return array with three elements containing the rgb values; 
	  * @todo this function has to move to a general place in the egroupware API
	  */
	  function hex2RGB($hexcolor) 
	  { 
		 if(!eregi("^#", $hexcolor)) 
		 { 
			$hexcolor = "#".$hexcolor; 
		 } 
		 if(eregi("^#[a-f0-9]{6}$", $hexcolor))
		 { 
			$color[0] = hexdec(substr($hexcolor,1,2)); 
			$color[1] = hexdec(substr($hexcolor,3,2)); 
			$color[2] = hexdec(substr($hexcolor,5,2)); 
			return $color; 
		 } 
		 else 
		 { 
			return array(0, 0, 0); 
		 } 
	  } 

	  }	
   ?>
