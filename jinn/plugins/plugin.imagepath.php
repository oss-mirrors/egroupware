<?
/*************************************************************************\
* phpGroupWare - HTMLAREA-form-plugin for phpGW-jinn            *
* The original script is written by interactivetools.com, inc.            *
* Ported to phpGW by Pim Snel info@lingewoud.nl                           *
* --------------------------------------------                            *
* http://www.phpgroupware.org                                             *
* http://www.lingewoud.nl                                                 *
* --------------------------------------------                            *
* The original script HTMLAREA is distributed under a Open Source-licence *
* See the readme.txt in the htmlarea-directory for the complete licence   *
* text.                                                                   *
* phpGroupWare and the jinn are free software; you can          *
* redistribute it and/or modify it under the terms of the GNU General     *
* Public License as published by the Free Software Foundation; either     *
* version 2 of the License, or (at your option) any later version.        *
\*************************************************************************/

/*********************************************************************\
* $setup_info['jinn'] tells the site object administrator   *
* for which databasefieldtypes the plugin can be used for and more    *
\*********************************************************************/

$this->plugins['imagepath']['name']				= 'imagepath';
$this->plugins['imagepath']['title']			= 'ImagePath plugin';
$this->plugins['imagepath']['version']			= '0.1';
$this->plugins['imagepath']['enable']			= 1;
$this->plugins['imagepath']['db_field_hooks']	= array
(
	'text',
	'varchar',
	'blob'
);

/* ATTENTION: spaces and special character are not allowed in config array 
   use underscores for spaces */
$this->plugins['imagepath']['config']		= array
(
	'Max_files'=>'3',
	'Max_Image_width'=>'',
	'Max_Image_height'=>'',
	'Image_filetype'=>'png',
	'Generate_thumbnail'=>'False',
	'Store_thumbnail_in_thumb_pathfield_for_backwards_compatibility'=>'True',
	'Max_thumbnail_width'=>'',
	'Show_image_in_form'=>'False'
);

function plg_fi_imagepath($field_name,$value,$config,$bo_vars)
{	
	/* replace FLD for SEP to let the processor know the field is seperated by semicolons */
	
	$field_name=substr($field_name,3);	
	if($config) $config = explode(';',$config);
	foreach($config as $entry)
	{
		list($key,$val)=explode('=',$entry);	
		$conf[$key]=$val;		
	}

	
	/* show existing images */	
	if($value)
	{
		$input='<input type="hidden" name="IMG_ORG'.$field_name.'" value="'.$value.'">';
	
		$value=explode(';',$value);

		/* there are more images */
		if (is_array($value))
		{
			$i=0;
			foreach($value as $img_path)
			{
				$i++;

				$input.=$i.'. <b><a href="'.$bo_vars->site_object[upload_url].'/'.$img_path.'" target="_blank">'.$img_path.'</a></b> <input type="checkbox" value="'.$img_path.'" name="IMG_DEL'.$i.'_'.$field_name.'"> '.lang('remove').'<br>';
				
			}
		}
		/* there's just one image */
		else
		{
			$input=$img_path.'<input type="checkbox" value="'.$img_path.'" name="IMG_DEL'.$fieldname.'"> '.lang('remove').'<br>';
		}
	}
	
	/* get max images, set max 5 filefields */
	if (is_numeric($conf[Max_files])) 
	{
		if ($conf[Max_files]>30) $num_input=30;
		else $num_input =$conf[Max_files];
	}
	else 
	{
		die(is_numeric($conf[Max_files]));
		$num_input=1;
	}
	
	for($i=1;$i<=$num_input;$i++)
	{
		if (!$input) $input .=lang('add image'); 
		else $input.='<br>';
		
		$input.=lang('add %1st image', $i).' <input type="file" name="IMG_SRC'.$i.'_'.$field_name.'">';
	}

	$input.='<input type="hidden" name="FLD'.$field_name.'" value="$value">';

	

	return $input;
}







function plg_sf_imagepath($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config,$bo_vars)
/****************************************************************************\
* main image data function                                                   *
\****************************************************************************/
{

	$magick=CreateObject('jinn.boimagemagick');
	
	
	//die (var_dump($HTTP_POST_FILES));
	$upload_path= trim($this->site_object['upload_path']);


   //// workaround for use of img_path and image_path as names. 'image_path' will be official
   if ($GLOBALS['HTTP_POST_FILES']['img_path'])
   {
	   $image_input_handle=$GLOBALS['HTTP_POST_FILES']['img_path'];
	   $image_path_field_name='img_path';
   }
   else
   {
	   $image_input_handle=$GLOBALS['HTTP_POST_FILES']['image_path'];
	   $image_path_field_name='image_path';
   }


   /// deleting images if neccesary thirst
   
   
   $images_to_delete=filter_array_with_prefix($HTTP_POST_VARS,'IMG_DEL');
   if (count($images_to_delete)>0){

	   $image_path_changed=True;

	   // delete from harddisk
	   foreach($images_to_delete as $image_to_delete)
	   {
		   if (!@unlink($upload_path.'/'.$image_to_delete)) $unlink_error++;
	   }

	   $images_org=explode(';',$HTTP_POST_VARS[image_path_org]);
	   foreach($images_org as $image_org)
	   {
		   if (!in_array($image_org,$images_to_delete))
		   {
			   if ($image_path_new) $image_path_new.=';';
			   $image_path_new.=$image_org;
		   }
	   }
   }
   else
   {
	   $image_path_new.=$HTTP_POST_VARS['image_path_org'];
   }
//die (var_dump($this));
   /// deleting thumbs if neccesary thirst
   $thumbs_to_delete=filter_array_with_prefix($HTTP_POST_VARS,'TMBDEL');
   if (count($thumbs_to_delete)>0){

	   $thumb_path_changed=True;

	   // delete from harddisk
	   foreach($thumbs_to_delete as $thumb_to_delete)
	   {
		   if (!@unlink($upload_path.'/'.$thumb_to_delete)) $unlink_error++;
	   }

	   // delete from table
	   $thumbs_org=explode(';',$HTTP_POST_VARS['thumb_path_org']);
	   //die ($thumb_org);
	   foreach($thumbs_org as $thumb_org)
	   {
		   if (!in_array($thumb_org,$thumbs_to_delete))
		   {
			   if ($thumb_path_new) $thumb_path_new.=';';
			   $thumb_path_new.=$thumb_org;
		   }
	   }
   }
   else
   {
	   $thumb_path_new.=$HTTP_POST_VARS[thumb_path_org];
   }


   // finally adding new image and if neccesary a new thumb
   if($GLOBALS['HTTP_POST_FILES']['image_path']['name'] || $GLOBALS['HTTP_POST_FILES']['img_path']['name'])
   {

	   // new better error_messages

	   if(!is_dir($upload_path))
	   {
		   die (lang("<i>image upload root-directory</i> does not exist or is not correct ...<br>
					   please contact Administrator with this message"));
	   }

	   if(!is_dir($upload_path.'/normal_size') && !mkdir($upload_path.'/normal_size', 0755))
	   {
		   die (lang("<i>image normal_size-directory</i> does not exist and cannot be created ...<br>
					   please contact Administrator with this message"));
	   }

	   if(!is_dir($upload_path.'/normal_size') && !mkdir($upload_path.'/normal_size', 0755))
	   {
		   die (lang("<i>image normal_size-directory</i> does not exist and cannot be created ...<br>
					   please contact Administrator with this message"));
	   }

	   if($temporary_file = tempnam ($upload_path.'/normal_size', "test_")) // make temporary file name...
	   {
		   unlink($temporary_file);
	   } 
	   else
	   {
		   die (lang("<i>image normal_size-directory</i> is not writable ...<br>
					   please contact Administrator with this message"));
	   }

	   // get image configuration for this object and else use defaults and else use defaults defaults

	   /*************************
		* set image width
		*************************/

	   if($this->site_object['image_width']) 
	   {
		   $image_width=$this->site_object['image_width'];
	   }

	   elseif($this->current_config['default_image_width'])
	   {
		   $image_width=$this->current_config['default_image_width'];
	   }
	   else
	   {
		   die(lang('maximum image width isn\'t set in object-configuration and not in the defaults-settings ...<br>
					   please contact Administrator with this message"'));
	   }

	   /*****************
		* set image type *
		*****************/

	   if($this->site_object['image_type'])
	   {
		   $image_type=$this->site_object['image_type'];
	   }
	   elseif($this->current_config['default_image_type'])
	   {
		   $image_type=$this->current_config['default_image_type'];
	   }
	   else
	   {
		   $image_type='png';
	   }


	   /*************************************
		* make unique name base on date/time *
		*************************************/

	   $img_file_name='img-'.time().'.'.$image_type;

	   $imgsize = GetImageSize($image_input_handle['tmp_name']);
	   if ($imgsize[0] > $image_width)
	   {
		   $width=$image_width;
	   }
	   else
	   {
		   $width=$imgsize[0];
	   }

	   //if(!$width) die('Maximum image width is not set, please set this globaly or for this object');

	   $tmppath=$this->bo->convertImage ($image_input_handle, $width,$image_type);



	   if (copy($tmppath, $upload_path."/normal_size/".$img_file_name))
	   {

		   if($image_path_new) $image_path_new .= ';';
		   $image_path_new.="normal_size/".$img_file_name;

	   }
	   else
	   {
		   die ("failed to copy $file...<br>\n");
	   }
	   @unlink($tmppath);

	   // if thumb_path exists in site-table
	   if($GLOBALS['HTTP_POST_VARS']['thumb_path'])
	   {
		   if($this->site_object['thumb_width']) $thumb_width=$this->site_object['thumb_width'];
		   else $thumb_width=$this->current_config['default_thumb_width'];

		   if(!is_dir($upload_path.'/thumb') && !mkdir($upload_path.'/thumb', 0755))
		   {
			   die (lang("thumb directory does not exist or is not correct ...<br>please check object's upload dir"));
		   }

		   $tmppath=$this->bo->convertImage ($image_input_handle,$thumb_width,$image_type);
		   if (copy($tmppath, $upload_path."/thumb/".$img_file_name))
		   {

			   if($thumb_path_new) $thumb_path_new .= ';';
			   $thumb_path_new.="thumb/".$img_file_name;
		   }
		   else
		   {
			   die ("failed to copy $file...<br>\n");
		   }
		   @unlink($tmppath);

	   }

   }


   //// make return array for storage
   if($image_path_new || $image_path_changed)
   {
	   $data[] = array
		   (
			'name' => $image_path_field_name,
			'value' => $image_path_new
		   );
   }

   if($thumb_path_new || $thumb_path_changed)
   {
	   $data[] = array
		   (
			'name' => 'thumb_path',
			'value' => $thumb_path_new
		   );
   }

   return $data;
}




/* extra support functions fro this plugin , better move this to a seperated class */ 


function filter_array_with_prefix($array,$prefix)
{

	while (list ($key, $val) = each ($array)) 
	{

		if (substr($key,0,strlen($prefix))==$prefix)
		{
			$return_array[]=$val;
		}
	}

	return $return_array;

}


?>
