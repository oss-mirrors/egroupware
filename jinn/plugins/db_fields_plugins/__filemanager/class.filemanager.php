<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Authors: Pim Snel, Lex Vogelaar for Lingewoud
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

   	/*-------------------------------------------------------------------
	Filemanager PLUGIN
	-------------------------------------------------------------------*/

	class db_fields_plugin_filemanager
	{
		var $javascript_inserted = false;
		var $spacer = 'jinn/plugins/db_fields_plugins/plugin_images/spacer.png';
		var $spacer_style = '';
		var $local_bo;	//this is a reference to a bo class and needs to be set by the instanciating factory class
		
	   function formview_edit($field_name,$value,$config,$attr_arr)
	   {	
		  $upload_path = $this->local_bo->cur_upload_path();
		  $helper_id   = $this->local_bo->plug->registry->plugins['filemanager']['helper_fields_substring'];
		  
			// Check if everything is set to upload files
		  if(!$upload_path)
		  {
			 $input=lang('The path to upload images is not set, please contact your JiNN administrator.');
			 return $input;
		  }
		  elseif(!file_exists($upload_path))
		  {
			 $input=lang('The path to upload images is not correct, please contact your JiNN administrator.');
			 return $input;
		  }
	
			$stripped_name=substr($field_name,6);	//the real field name
			$prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
			$prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)
			
			if(!$this->javascript_inserted)
			{
				$input .= $this->add_javascript($stripped_name);
			}
	
		    $table_style='';
			$input.='<table '.$table_style.' cellpadding="3" width="100%">';

			/****************************************
			 * if value is set, show existing images *
			 ****************************************/	
			 if(trim($value))
			 {
				$input.='<input type="hidden" name="'.$prefix.'_IMG_ORG_'.$stripped_name.'" value="'.$value.'">';
				$value=explode(';',$value);
			 }
			 else
			 {
				$value = array();
			 }
	
			/***************************************
			 * get max images, set max 10 filefields *
			 ***************************************/
			 if (is_numeric($config[Max_files])) 
			 {
				if ($config[Max_files]>30) $num_input=30;
				else $num_input =$config[Max_files];
			 }
			 else 
			 {
				$num_input=10;
			 }
	
	
				$cell_style='style="border-width:1px;border-style:solid;border-color:grey"';
				$field_string  = "'".$stripped_name."'";
				$prefix_string = "'".$prefix."'";
				
				if (is_array($value) && count($value)>0)
				{
				   $i=0;
				   foreach($value as $img_path)
				   {
					  $i++;
						//the slot number
					  $input.='<tr><td '.$cell_style.' valign="top">'.$i.'.</td><td '.$cell_style.'>';
						//the file
					  $input .= $this->show_file($img_path, true, $field_name, $i);
					  	//the delete/replace/add buttons
					  $input.='</td><td '.$cell_style.' valign="top"><img onClick="onDelete('.$prefix_string.', '.$field_string.', '.$i.');" src="jinn/plugins/db_fields_plugins/__filemanager/popups/ImageManager/edit_trash.gif">';
					  $input.='<input onClick="onBrowseServer('.$prefix_string.', '.$field_string.', '.$i.');" type="button" value="'.lang('replace').'" name="'.$prefix.'_IMG_EDIT_BUTTON_'.$stripped_name.$i.'">';
					  $input.='<input type="hidden" value="" name="'.$prefix.'_IMG_EDIT_'.$stripped_name.$i.'">';
					  $input.='</td></tr>';
				   }
				}
					//empty slots for the plugin to put filepaths in
				if(count($value) < $num_input)
				{
					for($i = count($value); $i < $num_input; $i++)
					{
						//the slot number
						$input.='<tr><td '.$cell_style.' valign="top">'.($i+1).'.</td><td '.$cell_style.'>';
						//the file (empty)
						$name = $prefix.'_IMG_'.$stripped_name.($i+1);
						$span_id = $prefix.'_PATH_'.$stripped_name.($i+1);
						$input .= $this->show_slot(true, 'path', $name, $this->spacer, $this->spacer_style, $span_id, '');
					  	 //the delete/replace/add buttons
						$input.='</td><td '.$cell_style.' valign="top"><img onClick="onDelete('.$prefix_string.', '.$field_string.', '.($i+1).');" src="jinn/plugins/db_fields_plugins/__filemanager/popups/ImageManager/edit_trash.gif">';
						$input.='<input onClick="onBrowseServer('.$prefix_string.', '.$field_string.', '.($i+1).');" type="button" value="'.lang('add').'" name="'.$prefix.'_IMG_EDIT_BUTTON_'.$stripped_name.($i+1).'">';
						$input.='<input type="hidden" value="" name="'.$prefix.'_IMG_EDIT_'.$stripped_name.($i+1).'">';
						$input.='</td></tr>';
					}
				}
	
					//'add more files' slot if appropriate
				if($config['Allow_more_then_max_files']=='True')
				{
						//add a hidden slot which can be unhidden by clicking on the "add slot" button
					if(count($value) > $num_input) $num_input = count($value);
					$js="document.getElementById('".$prefix.$stripped_name."extra').style.display='table-row'; document.getElementById('".$prefix.$stripped_name."add').style.display='none'";
					$input.='<tr id="'.$prefix.$stripped_name.'add"><td colspan="3" '.$cell_style.' valign="top"><input onClick="'.$js.'" type="button" name="'.$prefix.'_IMG_ADD_SLOT_'.$stripped_name.'" value="'.lang('add slot').'"></td></tr>';
	
						//the slot number
					$input.='<tr id="'.$prefix.$stripped_name.'extra" style="display:none;"><td '.$cell_style.' valign="top">'.($num_input+1).'.</td><td '.$cell_style.'>';
						//the file (empty)
					$name = $prefix.'_IMG_'.$stripped_name.($num_input+1);
					$span_id = $prefix.'_PATH_'.$stripped_name.($num_input+1);
					$input .= $this->show_slot(true, 'path', $name, $this->spacer, $this->spacer_style, $span_id, '');
					  	 //the delete/replace/add buttons
					$input.='</td><td '.$cell_style.' valign="top">';
					$input.='<input onClick="onBrowseServer('.$prefix_string.', '.$field_string.', '.($num_input+1).');" type="button" value="'.lang('add').'" name="'.$prefix.'_IMG_EDIT_BUTTON_'.$stripped_name.($num_input+1).'">';
					$input.='<input type="hidden" value="" name="'.$prefix.'_IMG_EDIT_'.$stripped_name.($num_input+1).'">';
					$input.='</td></tr>';
				}
	
			 $input.='</table>';
	
		  /* add extra images file container here */
	
		  $input.='<input type="hidden" name="'.$field_name.'" value="">';
	
		  if($config['Zip_file_box']=='True')
		  {
			 $input.= '<table>';
				$input.='<tr><td>'.lang('Add your ZIP-file with images here').'<input type="file" name="IMG_ZIP'.$field_name.'" value=""></td></tr>';
				$input.='</table>';
		  }
		  
		  return $input;
	   }
	
	   function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	   /****************************************************************************\
	   * main image data function                                                   *
	   \****************************************************************************/
	   {
		  $stripped_name=substr($field_name,6);	//the real field name
		  $prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
		  $helper_id = $this->local_bo->plug->registry->plugins['filemanager']['helper_fields_substring'];
		  $prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)
	
		  $upload_path=$this->local_bo->cur_upload_path();
		  $upload_url =$this->local_bo->cur_upload_url ();
	
		  $images_array=explode(';',$HTTP_POST_VARS[$prefix.'_IMG_ORG_'.$stripped_name]);
		  $images_edited=$this->local_bo->common->filter_array_with_prefix($HTTP_POST_VARS, $prefix.'_IMG_EDIT_'.$stripped_name);
	
		  if(is_array($images_edited))
		  {
			  foreach($images_edited as $key => $value)
			  {
				if($value == 'delete')
				{
					unset ($images_array[$key]);
				}
				else if($value != '') //add new image
				{
					$value = str_replace($upload_url, '', $value);
					$images_array[$key] = $value;
				}
			  }
		  }
		  
		  if(is_array($images_array))
		  {
		  
				//check max images
			 if($config['Allow_more_then_max_files']=='False')
			 {
				 if( count($images_array) > $config[Max_files] )
				 {
					$images_array=array_slice($images_array, 0, $config[Max_files]);
				 }	
			 }
			 foreach ($images_array as $image_string)
			 {
				$image_string = str_replace('//','/',$image_string); //fixme: this fixes a bug in the popup. Better solve that..
				if($image_path_new) $image_path_new .= ';';
				$image_path_new.=$image_string;
			 }						
		  }
			// make return array for storage
		  if($image_path_new)
		  {
			 return $image_path_new;
		  }
		  elseif($image_path_changed)
		  {
			 return null;
		  }
	
		  return '-1'; // return -1 when there no value to give but the function finished succesfully
	   }
	
	
	   function formview_read($value,$config)
	   {
		  $table_style='';
		  $cell_style='style="border-width:1px;border-style:solid;border-color:grey"';

		  $input.='<table '.$table_style.' cellpadding="3" width="100%">';
		  if(trim($value))// FIXME or rather TESTME
		  {
			 //$input.='<input type="hidden" name="IMG_ORG'.$field_name.'" value="'.$value.'">';
			 $value=explode(';',$value);
			 if (is_array($value) && count($value)>0)
			 {
				$i=0;
				foreach($value as $img_path)
				{
					$i++;
					$input .= '<tr><td '.$cell_style.' valign="top">'.$i.'.</td><td '.$cell_style.'>';
					$input .= $this->show_file($img_path);
					$input .= '</td></tr>';							  
				}
			 }
		  }
		  $input.='</table>';

		  return $input;
	   }
	
	   
	   function listview_read($value,$config,$where_val_enc)
	   {
		  $stripped_name=substr($field_name,6);	
	
		  $upload_path=$this->local_bo->cur_upload_path();
		  $upload_url =$this->local_bo->cur_upload_url ();
	
		  /* if value is set, show existing images */	
		  if($value)
		  {
			 $value=explode(';',$value);
	
			 /* there are more images */
			 if (is_array($value))
			 {
				$i=0;
				foreach($value as $img_path)
				{
				   $i++;
	
				   unset($imglink); 
				   unset($popup); 
	
				   /* check for image and create previewlink */
				   if(is_file($upload_path . SEP . $img_path))
				   {
					  
					  $imglink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path.SEP.$img_path);
	
					  // FIXME move code to class
					  $image_size=getimagesize($upload_path . SEP. $img_path);
					  $pop_width = ($image_size[0]+50);
					  $pop_height = ($image_size[1]+50);
	
					  $popup = "img_popup('".base64_encode($imglink)."','$pop_width','$pop_height');";
				   }
				   
					  unset($thumblink); 
	
					  $path_array = explode('/', $img_path);
					  $path_array[count($path_array)-1] = '..'.$path_array[count($path_array)-1];
					  $thumb_path = implode('/', $path_array);
					  
						/* check for thumb and create previewlink */
					  if(is_file($upload_path . SEP . $thumb_path))
					  {
						 $thumblink='<img src="'.$upload_url . SEP . $thumb_path.'" alt="'.$i.'">';
					  }
					  else
					  {
						 $thumblink=$i;
					  }
				   
				   if($imglink) $display.='<a href="javascript:'.$popup.'">'.$thumblink.'</a>';
				   else $display.=' '.$thumblink;
				   $display.=' ';
	
				}
			 }
		  }
	
		  return $display;
	   }
	   
		function show_file($img_path, $edit=false, $field_name='', $i='')
		{
			$upload_path	= $this->local_bo->cur_upload_path();
			$max_prev		= $this->local_bo->read_preferences('max_prev');
			$helper_id		= $this->local_bo->plug->registry->plugins['filemanager']['helper_fields_substring'];

			if($max_prev == '') $max_prev = -1; //default we want to see all preview images
			$stripped_name = substr($field_name,6);	//the real field name
			$prefix = substr($field_name,0,6); 	//the prefix used to identify records in a multi record view
			$prefix .= $helper_id;				//the helper id will help identifying which post vars to ignore when saving the record(s)
			//$spacer = "jinn/plugins/db_fields_plugins/plugin_images/spacer.png";
			//$spacer_style = '';
			$unknown = "jinn/plugins/db_fields_plugins/__filemanager/popups/ImageManager/unknown.gif";
			$unknown_style = '';
			$img_style = 'style="border-style:solid;border-width:1px;border-color:#000000"';
		    $name    = $prefix.'_IMG_'.$stripped_name.$i;
		    $span_id = $prefix.'_PATH_'.$stripped_name.$i;
			
			$input='';
					//check if file exists
					if(is_file($upload_path . SEP . $img_path))
					{
						$image_info = getimagesize($upload_path . SEP. $img_path);
						$text = '<b>'.$img_path.'</b>';
						if(is_array($image_info))
						{
							//process as image

								// create previewlink
							  $imglink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path.SEP.$img_path);
								// FIXME move code to class
							  $image_size=getimagesize($upload_path . SEP. $img_path);
							  $pop_width = ($image_size[0]+50);
							  $pop_height = ($image_size[1]+50);
							  $popup   = "img_popup('".base64_encode($imglink)."','$pop_width','$pop_height');";

	
							  $path_array = explode('/', $img_path);
							  $path_array[count($path_array)-1] = '.'.$path_array[count($path_array)-1];
							  $thumb_path = implode('/', $path_array);
							  
								/* check for thumb and create previewlink */
							  if(is_file($upload_path . SEP . $thumb_path))
							  {
								 $thumblink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path . SEP . $thumb_path);
							  }
			
								 // if URL exists show link or if set show image in form
								 if($this->local_bo->read_preferences('prev_img')!='no' &&  ($max_prev>=$i || $max_prev==-1) && $imglink) 
								 {	
									if($this->local_bo->read_preferences('prev_img')=='yes')
									{
									   if($thumblink)
									   {
											$input .= $this->show_slot($edit, 'thumblink', $name, $thumblink, $img_style, $span_id, $text, $popup);
									   }
									   else
									   {
											$input .= $this->show_slot($edit, 'image', $name, $imglink, $img_style, $span_id, $text, $popup);
									   }
									}
									elseif($this->local_bo->read_preferences('prev_img')=='only_tn' && $thumblink)
									{
										$input .= $this->show_slot($edit, 'thumblink', $name, $thumblink, $img_style, $span_id, $text, $popup);
									}
									else
									{
										$input .= $this->show_slot($edit, 'pathlink', $name, $this->spacer, $this->spacer_style, $span_id, $text, $popup);
									}
								 }
								 else  
								 {
									if($imglink)
									{
										$input .= $this->show_slot($edit, 'pathlink', $name, $this->spacer, $this->spacer_style, $span_id, $text, $popup);
									}
									else
									{
										$input .= $this->show_slot($edit, 'path', $name, $this->spacer, $this->spacer_style, $span_id, $text);
									}
								 }
						}
						else
						{
							//process as unknown filetype
							$input .= $this->show_slot($edit, 'unknown', $name, $unknown, $unknown_style, $span_id, $text);
						}
					}
					else
					{
						$text = '<b>error: file does not exist on server ('.$img_path.')</b>';
						$input .= $this->show_slot($edit, 'path', $name, $this->spacer, $this->spacer_style, $span_id, $text);
					}
			return $input;
	    }
		
		function show_slot($edit, $type, $name, $src, $style, $span_id, $text, $link='')
		{
			$input = '';
			if($edit)
			{
				switch($type)
				{
					case 'path':
						$input .= '<img name="'.$name.'" src="'.$src.'" '.$style.' />';
						$input .= '<span id="'.$span_id.'">'.$text.'</span>';
						break;
					case 'pathlink':
						$input .= '<img name="'.$name.'" src="'.$src.'" '.$style.' />';
						$input .= '<span id="'.$span_id.'"><a href="javascript:'.$link.'">'.$text.'</a></span>';
						break;
					case 'thumblink':
						$input .= '<a href="javascript:'.$link.'"><img name="'.$name.'" src="'.$src.'" alt="preview" '.$style.' /></a>';
						$input .= '<span id="'.$span_id.'"></span>';
						break;
					case 'image':
						$input .= '<img name="'.$name.'" src="'.$src.'" alt="preview" '.$style.' />';
						$input .= '<span id="'.$span_id.'"></span>';
						break;
					case 'unknown':
						$input .= '<img name="'.$name.'" src="'.$src.'" alt="file of unknown type" '.$style.' />';
						$input .= '<span id="'.$span_id.'">'.$text.'</span>';
						break;
				}
			}
			else
			{
				switch($type)
				{
					case 'path':
						$input .= $text;
						break;
					case 'pathlink':
						$input .= '<a href="javascript:'.$link.'">'.$text.'</a>';
						break;
					case 'thumblink':
						$input .= '<a href="javascript:'.$link.'"><img src="'.$src.'" alt="preview" '.$style.' /></a>';
						break;
					case 'image':
						$input .= '<img src="'.$src.'" alt="preview" '.$style.' />';
						break;
					case 'unknown':
						$input .= '<img src="'.$src.'" alt="file of unknown type" '.$style.' />'.$text;
						break;
				}
			}
			return $input;
		}
		
		function add_javascript($stripped_name)
		{
			$this->javascript_inserted = true;
			$input = '';
			
			 /**************************************************
			 *  javascript and hidden param for server browser *
			 **************************************************/
			$input.='<input type="hidden" value="" name="CURRENT_RECORD">';
			$input.='<input type="hidden" value="" name="CURRENT_FIELD">';
			$input.='<input type="hidden" value="" name="CURRENT_SLOT">';
			$input.='	<script language="JavaScript">
			<!--  
			function getLabel(type)
			{
				if(type=="add") return "'.lang('add').'";
				if(type=="replace") return "'.lang('replace').'";
			}
			
			function onBrowseServer(record, field, slot) 
			{
				//the popup will be aware of this window by the opener property
				//when a server image is chosen, the popup will call the onSave function, passing the chosen image path
				childWindow=open("jinn/plugins/db_fields_plugins/__filemanager/popups/insert_image.php?field='.$stripped_name.'","console","resizable=no,width=580,height=440");
				if (childWindow.opener == null)	childWindow.opener = self;
				document.frm.CURRENT_RECORD.value=record;
				document.frm.CURRENT_FIELD.value=field;
				document.frm.CURRENT_SLOT.value=slot;
			}
			
			function setSlot(record, field, slot, val1, val2, val3)
			{
				//alert("set Slot: " + record + ", "+ field + ", "+ slot);
				
				//set the img src property for preview purposes
				//fill a hidden form input to enable processing and saving of the chosen image path on submitting the form
				
				//todo: set img style?
				//todo: remove width/height text?
				//todo: remove delete checkbox?
				
				var cmd;
				
				cmd = "document.frm." + record + "_IMG_EDIT_" + field + slot + ".value = \"" + val1 + "\";";
				eval(cmd);
				
				if(val1 == "delete")
				{
					cmd = "document." + record + "_IMG_" + field + slot + ".src = \"" + val2 + "\";";
				}
				else
				{
						//we need to put a dot in front of the filename to display the thumbnail
						//fixme: can this be done easier with RegEx?
					var val2_arr = val2.split("/");
					var idx = val2_arr.length - 1;
					val2_arr[idx] = "." + val2_arr[idx];
					var thumb = val2_arr.join("/");
		
					cmd = "document." + record + "_IMG_" + field + slot + ".src = \"" + thumb + "\";";
				}
				eval(cmd);
	
				cmd = "document.frm." + record + "_IMG_EDIT_BUTTON_" + field + slot + ".value = \"" + val3 + "\";";
				eval(cmd);
	
				cmd = "document.getElementById(\"" + record + "_PATH_" + field + slot + "\").style.display = \"none\";";
				eval(cmd);
			}
			
			function onSave(val)
			{
				//access the CURRENT_... hidden fields to find out which image slot to use
				setSlot(document.frm.CURRENT_RECORD.value, document.frm.CURRENT_FIELD.value, document.frm.CURRENT_SLOT.value, val, val, getLabel("replace"));
			}
			
			function onDelete(record, field, slot)
			{
				setSlot(record, field, slot, "delete", "'.$this->spacer.'", getLabel("add"));
			}
			-->
			</script>';

			return $input;
		}
	}
?>