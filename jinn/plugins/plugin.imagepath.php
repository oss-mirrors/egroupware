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
	$this->plugins['imagepath']['version']			= '0.9.1';
	$this->plugins['imagepath']['enable']			= 1;

	$this->plugins['imagepath']['description']		= '
	plugin for uploading/resizing images and storing their imagepaths in
	to database, using default uploadpath for site or object';

	$this->plugins['imagepath']['changelog']		= 
	'
	0.9.1
	- Add argument attr_arr to plg_fi_imagepath
	0.9.0
	- implement resizing with GDLib (look in general JiNN-configuration)
	0.8.8
	- fixed limited images handeling
	- fixed resizing
	- enabled user preferences
	- enabled preview in form
	- cleaned up de form display a bit
	0.8.7
	- added changelog
	- changed varchar to string so it actually binds to varchars ;)
	';

	$this->plugins['imagepath']['db_field_hooks']	= array
	(
		'text',
		'varchar',
		'string',
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
		'Max_thumbnail_width' => array('100','text','maxlength=3 size=3'),
		'Max_thumbnail_height'=> array('100','text','maxlength=3 size=3'),
		'Allow_other_images_sizes'=> array( array('False','True') /* 1st is default the rest are all possibilities */ ,'select',''),
	);

	function plg_fi_imagepath($field_name,$value,$config,$attr_arr)
	{	
		global $local_bo;

		$field_name=substr($field_name,3);	

		if($local_bo->common->so->config[server_type]=='dev')
		{
			$field_prefix='dev_';
		}

		if($local_bo->site_object[$field_prefix.'upload_url'])
		{
			$upload_url=$local_bo->site_object[$field_prefix.'upload_url'].'/';
			$upload_path=$local_bo->site_object[$field_prefix.'upload_path'];
		}
		elseif($local_bo->site[$field_prefix.'upload_url'])
		{
			$upload_url=$local_bo->site[$field_prefix.'upload_url'].'/';
			$upload_path=$local_bo->site[$field_prefix.'upload_path'];
		}
		else
		{
			$upload_url=false;
			$upload_path=false;
		}

		$input.='<table style="" cellpadding="3" width="100%">';
		/****************************************
		* if value is set, show existing images *
		****************************************/	
		if(trim($value))// FIXME or rather TESTME
		{
			$input.='<input type="hidden" name="IMG_ORG'.$field_name.'" value="'.$value.'">';

			$value=explode(';',$value);

			if (is_array($value) && count($value)>0)
			{
				$i=0;

				$max_prev=$local_bo->read_preferences('max_prev');
				
				foreach($value as $img_path)
				{
					$i++;

					$input.='<tr><td style="border-width:1px;border-style:solid;border-color:grey" valign="top">'.$i.'.</td><td style="border-width:1px;border-style:solid;border-color:grey" >';

					// if URL exists show link or if set show image in form
					if($local_bo->read_preferences('prev_img')!='no' &&  ($max_prev>=$i || $max_prev==-1) && $upload_url && is_file($upload_path . SEP . $img_path)) 
					{	
						if($local_bo->read_preferences('prev_img')=='yes')
						{
							if(is_file($upload_path . SEP . str_replace('normal_size','thumb',$img_path)))
							{
								$input.='<a href="'.$upload_url.$img_path.'" target="_blank"><img src="'.$upload_url.str_replace('normal_size','thumb',$img_path).'" alt="preview" style="border-style:solid;border-width:1px;border-color:#000000" /></a>';
							}
							else
							{
								$input.='<img src="'.$upload_url.$img_path.'" alt="preview" style="border-style:solid;border-width:1px;border-color:#000000" />';
							}
						}
						elseif($local_bo->read_preferences('prev_img')=='only_tn' && is_file($upload_path . SEP . str_replace('normal_size','thumb',$img_path)))
						{
								$input.='<a href="'.$upload_url.$img_path.'" target="_blank"><img src="'.$upload_url.str_replace('normal_size','thumb',$img_path).'" alt="preview" style="border-style:solid;border-width:1px;border-color:#000000" /></a>';
						}
						else
						{
							$input.='<b><a href="'.$upload_url.$img_path.'" target="_blank">'.$img_path.'</a></b>';
						}
					}
					else // just show a link 
					{
						if($upload_url && is_file($upload_path . SEP . $img_path))
						{
							$input.='<b><a href="'.$upload_url.$img_path.'" target="_blank">'.$img_path.'</a></b>';
						}
						else
						{
							$input.='<b>'.$img_path.'</b>';
						}
					}


					$input.='</td><td style="border-width:1px;border-style:solid;border-color:grey" valign="top"><input type="checkbox" value="'.$img_path.'" name="IMG_DEL'.$field_name.$i.'"> '.lang('remove').'</td></tr>';
				}
			}
		}
		
		/***************************************
		* get max images, set max 5 filefields *
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

		for($i=1;$i<=$num_input;$i++)
		{
			$input.='<tr><td colspan="3" style="border-width:1px;border-style:solid;border-color:grey">';
			if($num_input==1) 
			{
				$input .=lang('add image').'<input type="file" name="IMG_SRC'.$field_name.$i.'">';
			}
			else
			{
				$input.=lang('add image %1', $i).' <input type="file" name="IMG_SRC'.$field_name.$i.'">';
			}

			/* when user is allowed to give own image sizes */
			if($config['Allow_other_images_sizes']=='True')
			{
				$input.= '<table>';
				$input.='<tr><td>'.lang('Optional max. height').'('.lang('default').':'.$config['Max_image_height'].')</td><td><input type="text" size="3" name="IMG_HEI'.$field_name.$i.'"></td></tr>';
				$input.='<tr><td>'.lang('Optional max. width').'('.lang('default').':'.$config['Max_image_width'].')</td><td><input type="text" size="3" name="IMG_WID'.$field_name.$i.'"></td></tr>';
				$input.='</table>';
			}


			$input.='</td></tr>';			

		}
		$input.='</table>';
		$input.='<input type="hidden" name="FLD'.$field_name.'" value="">';
		return $input;
	}

	function plg_sf_imagepath($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
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

		if($local_bo->common->so->config[server_type]=='dev')
		{
			$field_prefix='dev_';
		}

		if($local_bo->site_object[$field_prefix.'upload_url'])
		{
			$upload_url=$local_bo->site_object[$field_prefix.'upload_url'].'/';
			$upload_path=$local_bo->site_object[$field_prefix.'upload_path'];
		}
		elseif($local_bo->site[$field_prefix.'upload_url'])
		{
			$upload_url=$local_bo->site[$field_prefix.'upload_url'].'/';
			$upload_path=$local_bo->site[$field_prefix.'upload_path'];
		}
		else
		{
			$upload_url=false;
			$upload_path=false;
		}

		$images_to_delete=$local_bo->common->filter_array_with_prefix($HTTP_POST_VARS,'IMG_DEL');

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
		$images_to_add=$local_bo->common->filter_array_with_prefix($HTTP_POST_FILES,'IMG_SRC'.substr($field_name,3));


		$images_to_add_hei=$local_bo->common->filter_array_with_prefix($HTTP_POST_VARS,'IMG_HEI'.substr($field_name,3));
		$images_to_add_wid=$local_bo->common->filter_array_with_prefix($HTTP_POST_VARS,'IMG_WID'.substr($field_name,3));

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

			//FIXME messages to standard msgbox
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

					if($images_to_add_hei[$img_position] || $images_to_add_wid[$img_position])
					{
						/* set user size */
						$img_size = GetImageSize($add_image['tmp_name']);
						if ($images_to_add_wid[$img_position] && $img_size[0] > $images_to_add_wid[$img_position])
						{
							$new_img_width=$images_to_add_wid[$img_position];
						}

						if ($images_to_add_hei[$img_position] && $img_size[1] > $images_to_add_hei[$img_position])
						{
							$new_img_height=$images_to_add_hei[$img_position];
						}
			
					}
					else
					{
						/* default set size */
						$img_size = GetImageSize($add_image['tmp_name']);
						if ($config['Max_image_width'] && $img_size[0] > $config['Max_image_width'])
						{
							$new_img_width=$config['Max_image_width'];
						}

						if ($config['Max_image_height'] && $img_size[1] > $config['Max_image_height'])
						{
							$new_img_height=$config['Max_image_height'];
						}
					}

					/* get original type */
					$filetype=$graphic->Get_Imagetype($add_image['tmp_name']);	
					if(!$filetype)
					{
						die(lang("The file you uploaded named %1 is not an imagefile, is corrupt, or the filetype is not supported by JiNN. If this error repeates, please check your ImageMagick installation.  Older version of ImageMagick are known not work properly with JiNN. Be sure to install at least Version 5.4.9 or higher",$add_image['name']));
					}
					elseif($filetype!='JPEG' && $filetype!='GIF' && $filetype!='PNG')
					{
						$filetype='PNG';
						$new_temp_file=$graphic->Resize($new_img_width,$new_img_height,$add_image['tmp_name'],$filetype);
						if(!$new_temp_file) die(lang('the resize process failed, please contact the administrator'));

					}
					elseif($new_img_width || $new_img_height)
					{
						$target_image_name.='.'.$filetype;
						$new_temp_file=$graphic->Resize($new_img_width,$new_img_height,$add_image['tmp_name'],$filetype);
						if(!$new_temp_file) die(lang('the resize process failed, please contact the administrator'));
					}
					else
					{
						$new_temp_file=$add_image['tmp_name']; // just copy
					}


					/* if thumb */
					if($config['Generate_thumbnail']=='True')
					{
						//generate thumb
						$new_temp_thumb=$graphic->Resize($config['Max_thumbnail_width'],
						$config['Max_thumbnail_height'],$add_image['tmp_name'],$filetype);
					}
					
					$target_image_name = time() . ereg_replace("[^a-zA-Z0-9_.]", '_', $add_image['name']);

					if(substr(substr($target_image_name,-4),0,1) =='.') 
					{
						$target_image_name = substr($target_image_name,0,(strlen($target_image_name)-3)).$filetype;	
					}
					else $target_image_name .='.'.$filetype;
					
					if(is_file($upload_path . SEP . 'normal_size' . SEP .$target_image_name))
					{
						$target_image_name='another_'.$target_image_name;
					}

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
						die ("failed copying $new_temp_file to $upload_path/normal_size/$target_image_name...<br>\n");
					}
				}
				
				$img_position++;
			}
		}

		if(is_array($images_array))
		{
			//check max images
			if( count($images_array) > $config[Max_files] )
			{
				$images_array=array_slice($images_array, 0, $config[Max_files]);
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

		return '-1'; /* return -1 when there no value to give but the function finished succesfully */
	}

	function plg_bv_imagepath($value,$config)
	{

		global $local_bo;
		$field_name=substr($field_name,3);	

		if($local_bo->common->so->config[server_type]=='dev')
		{
			$field_prefix='dev_';
		}

		if($local_bo->site_object[$field_prefix.'upload_url'])
		{
			$upload_url=$local_bo->site_object[$field_prefix.'upload_url'].'/';
			$upload_path=$local_bo->site_object[$field_prefix.'upload_path'];
		}
		elseif($local_bo->site[$field_prefix.'upload_url'])
		{
			$upload_url=$local_bo->site[$field_prefix.'upload_url'].'/';
			$upload_path=$local_bo->site[$field_prefix.'upload_path'];
		}
		else
		{
			$upload_url=false;
			$upload_path=false;
		}

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

					if($upload_url) $display.='<a href="'.$upload_url.$img_path.'" target="_blank">'.$i.'</a>';
					else $display.=' '.$i;
					$display.=' ';

				}
			}
		}

		return $display;


	}

	?>
