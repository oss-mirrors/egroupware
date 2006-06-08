<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Copyright (C)2005 Rob van Kraanen <rob@lingewoud.nl>

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

   class db_fields_plugin_visual_selection
   {
	  /**
	  * Constructor
	  */
	  function db_fields_plugin_visual_selection()
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
		 #_debug_array($field_name);
		 #_debug_array($value);
		 #_debug_array($config);
		 #die();
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
		 if(is_array($config))
		 {
			foreach($config[multi1] as $single)
			{
			   foreach($single as $key=>$val)
			   {
				  #if($val == $value)
				  #{
					 #			 _debug_array($val);
					 #$id = substr($key,strlen($key)-1);
					 #$img = $config["imgfile$"];
					 #}
			   }
			}
		 }
		 #die("jammerrrr.......");
		 $site_fs= createobject('jinn.site_fs');

		 $siterootdir=$site_fs->get_jinn_sitefile_url($this->local_bo->site[site_id]);
		 
		 $this->tplsav2->assign('field_name',$field_name);
		 $this->tplsav2->assign('value',$value);
		 $this->tplsav2->assign('config',$config[multi1]);
		 $this->tplsav2->assign('upload_url',$siterootdir);
		 $this->tplsav2->assign('img',$img);

		 $input=$this->tplsav2->fetch('visual_selection.formview_edit.tpl.php');
		 return $input;
	  }

	  /**
	  * filter or manipulate the post value of a field before it is stored in 
	  * the database
	  *
	  * This one generates the images using the user submitted text and the 
	  * fonts and other data from the plugin config array
	  *
	  * @param string $key which referers to the fielddata in the _POST array
	  * @param array $HTTP_POST_VARS the _POST array as if the form contains one single record
	  * @param array $HTTP_POST_FILES the _FILES array as if the form contains one single record
	  * $param array $config the plugin config array of this field
	  * @return string returns a serialized array with all img paths and the first element contains the text as string
	  * @todo implement the last two images
	  * @todo tweak the parameters
	  * @todo prevent auto remove the old images garbage 
	  * @cleanup form lay-out
	  */
	  function on_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  {
		 return $HTTP_POST_VARS[$key];
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
	  function listview_read($value,$config,$where_val_enc)
	  {
		 $upload_url=$this->local_bo->cur_upload_url();
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
		 if($value != '')
		 {
			foreach($config[multi1] as $key=>$val)
			{
			   if($val[option_value] == $value)
			   {
				  $img = $val[imgfile];
				  $site_fs= createobject('jinn.site_fs');

				  $siterootdir=$site_fs->get_jinn_sitefile_url($this->local_bo->site[site_id]);

				  $this->tplsav2->assign('upload_url',$siterootdir);
				  $this->tplsav2->assign('img',$img);
			   }
			}
			$input=$this->tplsav2->fetch('visual_selection.formview_read.tpl.php');
		 }
		 else
		 {
			$input = 'leeg';
		 }
		 return $input;
	  }

	  /**
	  * formview_read 
	  * 
	  * @param mixed $value 
	  * @param mixed $config 
	  * @access public
	  * @return void
	  */
	  function formview_read($value,$config)
	  {
		 $upload_url=$this->local_bo->cur_upload_url();
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
		 if($value != '')
		 {
			foreach($config[multi1] as $key=>$val)
			{
			   if($val[option_value] == $value)
			   {
				  $img = $val[imgfile];
				  $site_fs= createobject('jinn.site_fs');

				  $siterootdir=$site_fs->get_jinn_sitefile_url($this->local_bo->site[site_id]);

				  $this->tplsav2->assign('upload_url',$siterootdir);
				  $this->tplsav2->assign('img',$img);
			   }
			}
			$input=$this->tplsav2->fetch('visual_selection.formview_read.tpl.php');
		 }
		 else
		 {
			$input = 'empty';
		 }
		 return $input;
	  }


   }	
?>
