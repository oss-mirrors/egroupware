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

   /* 
   plugin.filemanager.php contains the standard image-upload plugin for 
   JiNN number off standardly available 
   plugins for JiNN. 
   */

   $this->plugins['filemanager']['name']			= 'filemanager';
   $this->plugins['filemanager']['title']			= 'filemanager plugin';
   $this->plugins['filemanager']['author']			= 'Pim Snel/Lex Vogelaar';
   $this->plugins['filemanager']['version']			= '0.1';
   $this->plugins['filemanager']['enable']			= 1;

   $this->plugins['filemanager']['description']		= '
   plugin for uploading/editing files and storing their paths in
   to database, using default uploadpath for site or object';

   $this->plugins['filemanager']['db_field_hooks']	= array
   (
	  'string',
	  'blob'
   );

   /* ATTENTION: spaces and special character are not allowed in config array 
   use underscores for spaces */
   $this->plugins['filemanager']['config']		= array
   (
	  /* array('default value','input field type', 'extra html properties')*/
	  'Max_files' => array('3','text','maxlength=2 size=2'),  
	  'Allow_more_then_max_files'=> array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
	  'Zip_file_box'=> array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
	  'Max_image_width' => array('','text','maxlength=4 size=4'),
	  'Max_image_height' => array('','text','maxlength=4 size=4'),
	  'Image_filetype' => array(array('png','gif','jpg'),'select','maxlength=3 size=3'),
	  'Generate_thumbnail' => array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
	  'Max_thumbnail_width' => array('100','text','maxlength=3 size=3'),
	  'Max_thumbnail_height'=> array('100','text','maxlength=3 size=3'),
	  'Allow_other_images_sizes'=> array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
   );

   function plg_fi_filemanager($field_name,$value,$config,$attr_arr)
   {	
	  global $local_bo;
	  
	  $stripped_name=substr($field_name,6);	

	  $upload_path=$local_bo->cur_upload_path();
	  
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
/*	  
	  elseif(!is_dir($upload_path.SEP.'/normal_size') && !mkdir($upload_path.SEP.'/normal_size', 0755))
	  {
		 $input=lang('The image normal_size-directory subdirectory does not exist and cannot be created ...');
		 $input.=lang('Please contact Administrator with this message.');
		 return $input;
	  }
	  elseif(!touch($upload_path.SEP.'normal_size'.SEP.'JiNN_write_test'))
	  {
		 $input=lang('The image_path normal_size subdirectory is not writable by the webserver ...');
		 $input.=lang('please contact Administrator with this message');
		 return $input;
	  }
*/

/*	  
	  // everything ok, remove temporary file
	  unlink($upload_path.SEP.'normal_size'.SEP.'JiNN_write_test');

	  if($config['Generate_thumbnail']=='True')
	  {
		 if(!is_dir($upload_path.SEP.'thumb') && !mkdir($upload_path.SEP.'thumb', 0755))
		 {
			$input= lang("thumb directory does not exist or is not correct ...");
			$input.=lang('please contact Administrator with this message');
			return $input;
		 }
		 elseif(!touch($upload_path.SEP.'thumb'.SEP.'JiNN_write_test'))
		 {
			$input=lang('The image_path thumb subdirectory is not writable by the webserver ...');
			$input.=lang('please contact Administrator with this message');
			return $input;
		 }

		 // everything ok, remove temporary file
		 unlink($upload_path.SEP.'thumb'.SEP.'JiNN_write_test');
	  }
*/
	  $table_style='';
	  $cell_style='style="border-width:1px;border-style:solid;border-color:grey"';
	  $img_style='style="border-style:solid;border-width:1px;border-color:#000000"';
	  $spacer = "jinn/plugins/db_fields_plugins/plugin_images/spacer.png";
	  $spacer_style='';

	  	 /**************************************************
		 *  javascript and hidden param for server browser *
		 **************************************************/
		$input.='<input type="hidden" value="" name="CURRENT_FIELD">';
		$input.='<input type="hidden" value="" name="CURRENT_SLOT">';
		$true_field_name = substr($field_name, 6); //fixme: this is ugly...
		$input.='	<script language="JavaScript">
		<!--  
		function getLabel(type)
		{
			if(type=="add") return "'.lang('add').'";
			if(type=="replace") return "'.lang('replace').'";
		}
		
		function onBrowseServer(field, slot) 
		{
			//the popup will be aware of this window by the opener property
			//when a server image is chosen, the popup will call the onSave function, passing the chosen image path
			childWindow=open("jinn/plugins/db_fields_plugins/UploadImage/popups/insert_image.php?field='.$true_field_name.'","console","resizable=no,width=580,height=440");
			if (childWindow.opener == null)	childWindow.opener = self;
			document.frm.CURRENT_FIELD.value=field;
			document.frm.CURRENT_SLOT.value=slot;
		}
		
		function setSlot(field, slot, val1, val2, val3)
		{
			//set the img src property for preview purposes
			//fill a hidden form input to enable processing and saving of the chosen image path on submitting the form
			
			//todo: set img style?
			//todo: remove width/height text?
			//todo: remove delete checkbox?
			
			var cmd;
			
			cmd = "document.frm.IMG_EDIT_" + field + slot + ".value = \"" + val1 + "\";";
			eval(cmd);
			
				//we need to put a dot in front of the filename to display the thumbnail
				//fixme: can this be done easier with RegEx?
			var val2_arr = val2.split("/");
			var idx = val2_arr.length - 1;
			val2_arr[idx] = "." + val2_arr[idx];
			var thumb = val2_arr.join("/");

			cmd = "document.IMG_" + field + slot + ".src = \"" + thumb + "\";";
			eval(cmd);

			cmd = "document.frm.IMG_EDIT_BUTTON_" + field + slot + ".value = \"" + val3 + "\";";
			eval(cmd);

			cmd = "document.getElementById(\"PATH_" + field + slot + "\").style.display = \"none\";";
			eval(cmd);
		}
		
		function onSave(val)
		{
			//access the CURRENT_... hidden fields to find out which image slot to use
			setSlot(document.frm.CURRENT_FIELD.value, document.frm.CURRENT_SLOT.value, val, val, getLabel("replace"));
		}
		
		function onDelete(field, slot)
		{
			setSlot(field, slot, "delete", "'.$spacer.'", getLabel("add"));
		}
		-->
		</script>';

	  $input.='<table '.$table_style.' cellpadding="3" width="100%">';

		 /****************************************
		 * if value is set, show existing images *
		 ****************************************/	
		 if(trim($value))
		 {
			$input.='<input type="hidden" name="IMG_ORG'.$field_name.'" value="'.$value.'">';
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

			$field_string = "'".$field_name."'";

			if (is_array($value) && count($value)>0)
			{
			   $i=0;

			   $max_prev=$local_bo->read_preferences('max_prev');
			   if($max_prev == '') $max_prev = -1; //default we want to see all preview images

			   foreach($value as $img_path)
			   {
				  $i++;

				  unset($imglink); 
				  unset($thumblink); 
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
//_debug_array($imglink);			   
				  }

//_debug_array($upload_path . SEP . '.thumb_01_'.$img_path);
				  $path_array = explode('/', $img_path);
//_debug_array($path_array);
				  $path_array[count($path_array)-1] = '.'.$path_array[count($path_array)-1];
//_debug_array($path_array);
				  $thumb_path = implode('/', $path_array);
//_debug_array($thumb_path);
				  
				  /* check for thumb and create previewlink */
				  if(is_file($upload_path . SEP . $thumb_path))
				  {
					 $thumblink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path . SEP . $thumb_path);
//_debug_array($thumblink);			   
				  }

				  $input.='<tr><td '.$cell_style.' valign="top">'.$i.'.</td><td '.$cell_style.'>';

						// if URL exists show link or if set show image in form
						if($local_bo->read_preferences('prev_img')!='no' &&  ($max_prev>=$i || $max_prev==-1) && $imglink) 
						{	
						   if($local_bo->read_preferences('prev_img')=='yes')
						   {
							  if($thumblink)
							  {
									//show thumbnail if normal image is not available
								 $input.='<a href="javascript:'.$popup.'"><img name="IMG_'.$field_name.$i.'" src="'.$thumblink.'" alt="preview" '.$img_style.' /></a>';
								 $input.='<span id="PATH_'.$field_name.$i.'"></span>';
							  }
							  else
							  {
									//show image
								 $input.='<img name="IMG_'.$field_name.$i.'" src="'.$imglink.'" alt="preview" '.$img_style.' />';
								 $input.='<span id="PATH_'.$field_name.$i.'"></span>';
							  }
						   }
						   elseif($local_bo->read_preferences('prev_img')=='only_tn' && $thumblink)
						   {
									//show thumbnail
							  $input.='<a href="javascript:'.$popup.'"><img name="IMG_'.$field_name.$i.'" src="'.$thumblink.'" alt="preview" '.$img_style.' /></a>';
							  $input.='<span id="PATH_'.$field_name.$i.'"></span>';
						   }
						   else
						   {
									//show path with link to image
		   					  $input.='<img name="IMG_'.$field_name.$i.'" src="'.$spacer.'" '.$spacer_style.' />';
							  $input.='<span id="PATH_'.$field_name.$i.'"><b><a href="javascript:'.$popup.'">'.$img_path.'</a></b></span>';
						   }
						}
						else  
						{
						   if($imglink)
						   {
									//show path with link to image
		   					  $input.='<img name="IMG_'.$field_name.$i.'" src="'.$spacer.'" '.$spacer_style.' />';
							  $input.='<span id="PATH_'.$field_name.$i.'"><b><a href="javascript:'.$popup.'">'.$img_path.'</a></b></span>';
						   }
						   else
						   {
									//show path only
		   					  $input.='<img name="IMG_'.$field_name.$i.'" src="'.$spacer.'" '.$spacer_style.' />';
							  $input.='<span id="PATH_'.$field_name.$i.'"><b>'.$img_path.'</b></span>';
						   }
						}

						$input.='</td><td '.$cell_style.' valign="top"><img onClick="onDelete('.$field_string.', '.$i.');" src="jinn/plugins/db_fields_plugins/UploadImage/popups/ImageManager/edit_trash.gif">';
						$input.='<input onClick="onBrowseServer('.$field_string.', '.$i.');" type="button" value="'.lang('replace').'" name="IMG_EDIT_BUTTON_'.$field_name.$i.'">';
						$input.='<input type="hidden" value="" name="IMG_EDIT_'.$field_name.$i.'">';
						$input.='</td></tr>';
			   }
			}
			
			//generate empty slots for the browse server plugin to activate
			
			if(count($value) < $num_input)
			{
				for($i = count($value); $i < $num_input; $i++)
				{
					$input.='<tr><td '.$cell_style.' valign="top">'.($i+1).'.</td><td '.$cell_style.'>';
					$input.='<img name="IMG_'.$field_name.($i+1).'" src="'.$spacer.'" '.$spacer_style.' />';
 				    $input.='<span id="PATH_'.$field_name.($i+1).'"></span>';
					$input.='</td><td '.$cell_style.' valign="top"><img onClick="onDelete('.$field_string.', '.($i+1).');" src="jinn/plugins/db_fields_plugins/UploadImage/popups/ImageManager/edit_trash.gif">';
					$input.='<input onClick="onBrowseServer('.$field_string.', '.($i+1).');" type="button" value="'.lang('add').'" name="IMG_EDIT_BUTTON_'.$field_name.($i+1).'">';
					$input.='<input type="hidden" value="" name="IMG_EDIT_'.$field_name.($i+1).'">';
					$input.='</td></tr>';
				}
			}

			//generate 'add more images' slot if appropriate
			if($config['Allow_more_then_max_files']=='True')
			{
				if(count($value) > $num_input) $num_input = count($value);
				$js="document.getElementById('extra').style.display='table-row'; document.getElementById('add').style.display='none'";
				$input.='<tr id="add"><td colspan="3" '.$cell_style.' valign="top"><input onClick="'.$js.'" type="button" name="IMG_ADD_SLOT_'.$field_name.'" value="'.lang('add slot').'"></td></tr>';

				//add a hidden slot which can be unhidden by clicking on the "add slot" button
				$input.='<tr id="extra" style="display:none;"><td '.$cell_style.' valign="top">'.($num_input+1).'.</td><td '.$cell_style.'>';
				$input.='<img name="IMG_'.$field_name.($num_input+1).'" src="'.$spacer.'" '.$spacer_style.' />';
				$input.='<span id="PATH_'.$field_name.($num_input+1).'"></span>';
				$input.='</td><td '.$cell_style.' valign="top">';
				$input.='<input onClick="onBrowseServer('.$field_string.', '.($num_input+1).');" type="button" value="'.lang('add').'" name="IMG_EDIT_BUTTON_'.$field_name.($num_input+1).'">';
				$input.='<input type="hidden" value="" name="IMG_EDIT_'.$field_name.($num_input+1).'">';
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

   function plg_sf_filemanager($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
   /****************************************************************************\
   * main image data function                                                   *
   \****************************************************************************/
   {
	  global $local_bo;

	  /* choose image library to use */
	  if($local_bo->common->so->config[use_magick]=='MAGICK')
	  {
		 $graphic=CreateObject('jinn.boimagemagick');
	  }
	  else
	  {
		 $graphic=CreateObject('jinn.bogdlib');
	  }

	  $upload_path=$local_bo->cur_upload_path();
	  $upload_url =$local_bo->cur_upload_url ();

	  $images_array=explode(';',$HTTP_POST_VARS['IMG_ORG'.$field_name]);

	  // process edited slots
  	  $images_edited=$local_bo->common->filter_array_with_prefix($HTTP_POST_VARS,'IMG_EDIT_'.$field_name);

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


   function plg_ro_filemanager($value,$config)
   {

	  global $local_bo;
	  $stripped_name=substr($field_name,6);	

	  
 	  $upload_path=$local_bo->cur_upload_path();

	  $table_style='';
	  $cell_style='style="border-width:1px;border-style:solid;border-color:grey"';
	  $img_style='style="border-style:solid;border-width:1px;border-color:#000000"';

	  $input.='<table '.$table_style.' cellpadding="3" width="100%">';
	  if(trim($value))// FIXME or rather TESTME
	  {
		 $input.='<input type="hidden" name="IMG_ORG'.$field_name.'" value="'.$value.'">';

		 $value=explode(';',$value);

		 if (is_array($value) && count($value)>0)
		 {
			$i=0;

			$max_prev=$local_bo->read_preferences('max_prev');
			if($max_prev == '') $max_prev = -1; //default we want to see all preview images

			foreach($value as $img_path)
			{
			   $i++;

			   unset($imglink); 
			   unset($thumblink); 
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

			   /* check for thumb and create previewlink */
			   if(is_file($upload_path . SEP . str_replace('normal_size','thumb',$img_path)))
			   {
				  $tmpthumbpath=$upload_path.SEP.str_replace('normal_size','thumb',$img_path);
				  $thumblink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$tmpthumbpath);
			   }

			   $input.='<tr><td '.$cell_style.' valign="top">'.$i.'.</td><td '.$cell_style.'>';

					 // if URL exists show link or if set show image in form
					 if($local_bo->read_preferences('prev_img')!='no' &&  ($max_prev>=$i || $max_prev==-1) && $imglink) 
					 {	
						if($local_bo->read_preferences('prev_img')=='yes')
						{
						   if($thumblink)
						   {
							  $input.='<a href="javascript:'.$popup.'"><img src="'.$thumblink.'" alt="preview" '.$img_style.' /></a>';
						   }
						   else
						   {
							  $input.='<img src="'.$imglink.'" alt="preview" '.$img_style.' />';
						   }
						}
						elseif($local_bo->read_preferences('prev_img')=='only_tn' && $thumblink)
						{
						   $input.='<a href="javascript:'.$popup.'"><img src="'.$thumblink.'" alt="preview" '.$img_style.' /></a>';
						}
						else
						{
						   $input.='<b><a href="javascript:'.$popup.'">'.$img_path.'</a></b>';
						}
					 }
					 else  
					 {
						if($imglink)
						{
						   $input.='<b><a href="javascript:'.$popup.'">'.$img_path.'</a></b>';
						}
						else
						{
						   $input.='<b>'.$img_path.'</b>';
						}
					 }

					 $input.='</td></tr>';
			}
		 }
	  }

		 $input.='</table>';

	return $input;


   }

   
   function plg_bv_filemanager($value,$config,$where_val_enc)
   {

	  global $local_bo;
	  $stripped_name=substr($field_name,6);	


	  $upload_path=$local_bo->cur_upload_path();

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
			   
			   if($imglink) $display.='<a href="javascript:'.$popup.'">'.$i.'</a>';
			   else $display.=' '.$i;
			   $display.=' ';

			}
		 }
	  }

	  return $display;


   }

?>
