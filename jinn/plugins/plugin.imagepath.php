<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
	Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

	phpGroupWare - http://www.phpgroupware.org

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
	plugin.imagepath.php contains the standard image-upload plugin for 
	JiNN number off standardly available 
	plugins for JiNN. 
	*/

	$this->plugins['imagepath']['name']				= 'imagepath';
	$this->plugins['imagepath']['title']			= 'ImagePath plugin';
	$this->plugins['imagepath']['version']			= '0.8.3';
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
		/* array('default value','input field type', 'extra html properties')*/
		'Max_files' => array('3','text','maxlength=2 size=2'), 
		'Max_image_width' => array('','text','maxlength=4 size=4'),
		'Max_image_height' => array('','text','maxlength=4 size=4'),
		'Image_filetype' => array(array('png','gif','jpg'),'select','maxlength=3 size=3'),
		'Generate_thumbnail' => array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
		'Store_thumbnail_in_thumb_pathfield_for_backwards_compatibility' => array( array('False', 'True') ,'select',''),
		'Max_thumbnail_width' => array('100','text','maxlength=3 size=3'),
		'Max_thumbnail_height'=> array('100','text','maxlength=3 size=3'),
		'Show_image_in_form' => array( array('False','True') ,'select','')
	);

	function plg_fi_imagepath($field_name,$value,$config)
	{	

		global $local_bo;
		$field_name=substr($field_name,3);	

		if ($local_bo->bo->site_object['upload_url']) $upload_url=$local_bo->bo->site_object['upload_url'].'/';
		elseif($local_bo->bo->site['upload_url']) $upload_url=$local_bo->bo->site['upload_url'].'/';
		else $upload_url=false;

		/* if value is set, show existing images */	
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

					$input.=$i.'. ';
					if($upload_url) $input.='<b><a href="'.$upload_url.$img_path.'" target="_blank">'.$img_path.'</a></b>';
					else $input.='<b>'.$img_path.'</b>';
					$input.=' <input type="checkbox" value="'.$img_path.'" name="IMG_DEL'.$field_name.$i.'"> '.lang('remove').'<br>';
				}
			}
			/* there's just one image */
			else
			{
				$input=$img_path.'<input type="checkbox" value="'.$img_path.'" name="IMG_DEL'.$fieldname.'"> '.lang('remove').'<br>';
			}
		}

		/* get max images, set max 5 filefields */
		if (is_numeric($config[Max_files])) 
		{
			if ($config[Max_files]>30) $num_input=30;
			else $num_input =$config[Max_files];
		}
		else 
		{
			$num_input=100;
		}

		for($i=1;$i<=$num_input;$i++)
		{
			if($num_input==1) $input .=lang('add image'); 
			else
			{
				$input.='<br>';
				$input.=lang('add image %1', $i).
				' <input type="file" name="IMG_SRC'.$field_name.$i.'">';
			}
		}

		$input.='<input type="hidden" name="FLD'.$field_name.'" value="TRUE">';

		return $input;
	}



	function plg_sf_imagepath($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	/****************************************************************************\
	* main image data function                                                   *
	\****************************************************************************/
	{
		global $local_bo;
		$magick=CreateObject('jinn.boimagemagick');

		if ($local_bo->bo->site_object['upload_path']) $upload_path=$local_bo->bo->site_object['upload_path'].'/';
		elseif($local_bo->bo->site['upload_path']) $upload_path=$local_bo->bo->site['upload_path'].'/';
		else $upload_path=false;


		$images_to_delete=$local_bo->filter_array_with_prefix($HTTP_POST_VARS,'IMG_DEL');

		if (count($images_to_delete)>0){

			$image_path_changed=True;
			// delete from harddisk
			foreach($images_to_delete as $image_to_delete)
			{
				if (!@unlink($upload_path.'/'.$image_to_delete)) $unlink_error++;
				// if generate thumb
				if (!@unlink($upload_path.'/'.$thumb_to_delete)) $unlink_error++;
			}

			$images_org=explode(';',$HTTP_POST_VARS['IMG_ORG'.substr($field_name,3)]);

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
			$image_path_new.=$HTTP_POST_VARS['IMG_ORG'.substr($field_name,3)];
		}

		/* make array again of the original images*/
		$images_array=explode(';',$image_path_new);
		unset($image_path_new);

		/* finally adding new image and if neccesary a new thumb */
		$images_to_add=$local_bo->filter_array_with_prefix($HTTP_POST_FILES,'IMG_SRC');

		// quick check for new images
		if(is_array($images_to_add))
		foreach($images_to_add as $imagecheck)
		{
			if($imagecheck['name']) $num_img_to_add++;
		}

		if ($num_img_to_add)
		{
			/* check for minimal criteria */
			/* new better error_messages */

			if(!is_dir($upload_path))
			{
				die (lang("<i>image upload root-directory</i> does not exist or is not correct ...<br>
				please contact Administrator with this message") .lang('check: '). $upload_path);
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

			if($config['Generate_thumbnail']=='True')
			{
				if(!is_dir($upload_path.'/thumb') && !mkdir($upload_path.'/thumb', 0755))
				{
					die (lang("thumb directory does not exist or is not correct ...<br>please check object's upload dir"));
				}

			}

			if($config['Generate_thumbnail'])
			{
				if(!$config['Max_thumbnail_width'] && $config['Max_thumbnail_height'])
				{
					$config['Max_thumbnail_width']='100';
				}

			}

			$img_position=0;
			foreach($images_to_add as $add_image)
			{
				if($add_image['name'])
				{
					/* set size */
					$img_size = GetImageSize($add_image['tmp_name']);
					if ($config['Max_image_width'] && $img_size[0] > $config['Max_image_width'])
					{
						$new_img_width=$config['Max_image_width'];
					}

					if ($config['Max_image_height'] && $img_size[1] > $config['Max_image_height'])
					{
						$new_img_height=$config['Max_image_height'];
					}

					/* get original type */
					$filetype=$magick->Get_Imagetype($add_image['tmp_name']);	
					if(!$filetype)
					{
						die(lang("The file you uploaded named %1 is not an imagefile, is corrupt, or the filetype is not supported by JiNN. If this error repeates, please check your ImageMagick installation.  Older version of ImageMagick are known not work properly with JiNN. Be sure to install at least Version 5.4.9 or higher",$add_image['name']));
					}
					elseif($filetype!='JPEG' && $filetype!='GIF' && $filetype!='PNG')
					{
						$filetype='png';
						$new_temp_file=$magick->Resize($new_img_width,$new_img_height,$add_image['tmp_name'],$filetype,'');

						/* if thumb */
						if($config['Generate_thumbnail']) 
						$new_temp_thumb=$magick->Resize($config['Max_thumbnail_width'],$config['Max_thumbnail_height'],$add_image['tmp_name'],$filetype,'');

					}
					elseif($new_img_width || $new_img_height)
					{
						$target_image_name.='.'.$filetype;
						$new_temp_file=$magick->Resize($new_img_width,$new_img_height,$add_image['tmp_name'],$filetype,'');

						/* if thumb */
						if($config['Generate_thumbnail']) 
						$new_temp_thumb=$magick->Resize($config['Max_thumbnail_width'],$config['Max_thumbnail_height'],$add_image['tmp_name'],$new_filetype,'');
					}
					else
					{
						$new_temp_file=$add_image['tmp_name']; // just copy

						/* if thumb */
						if($config['Generate_thumbnail']) 
						$new_temp_thumb=$magick->Resize($config['Max_thumbnail_width'],$config['Max_thumbnail_height'],$add_image['tmp_name'],$filetype,'');

					}

					$target_image_name = time().ereg_replace("[^a-zA-Z0-9_.]", '_', $add_image['name']);

					if(substr(substr($target_image_name,-4),0,1) =='.') 
					{
						$target_image_name = substr($target_image_name,0,(strlen($target_image_name)-3)).$filetype;					
					}
					else $target_image_name .='.'.$filetype;	

					if (copy($new_temp_file, $upload_path."/normal_size/".$target_image_name))
					{
						$images_array[$img_position]='normal_size/'.$target_image_name;
						if($config['Generate_thumbnail'])
						{
							copy($new_temp_thumb, $upload_path."/thumb/".$target_image_name);
						}
					}
					else
					{
						die ("failed to copy $target_image_name...<br>\n");
					}
				}

				$img_position++;

			}
		}

		if(is_array($images_array))
		{
			foreach ($images_array as $image_string)
			{

				if($image_path_new) $image_path_new .= ';';
				$image_path_new.=$image_string;
			}						


		}

		//die(var_dump($image_path_new));		

		//// make return array for storage
		if($image_path_new || $image_path_changed)
		{
			return $image_path_new;
		}

		return '-1'; /* return -1 when there no value to give but the function finished succesfully */
	}

?>
