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
$this->plugins['imagepath']['title']				= 'ImagePath plugin';
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


//var_dump($local_bo);
function plugin_imagepath($field_name,$value,$config,$bo_vars)
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
		$input='<input type="hidden" name="image_path_org" value="'.$value.'">';
		
		$value=explode(';',$value);

		/* there are more images */
		if (is_array($value))
		{
			$i=0;
			foreach($value as $img_path)
			{
				$i++;

				/* preview thumb in form */
				/* at the moment disabled */
				/*
				if($config['Show_image_in_form'] && $config['Generate_thumbnail'])
				{
					$input.=$i.'.<b>'.$img_path.'</b><input type="checkbox" value="'.$img_path
					.'" name="IMGDEL'.$i.'"> '.lang('remove').'<br><img src="'
					.$this->bo->site_object[image_dir_url].'/'.$img_path.'" border="1">&nbsp;&nbsp;<br><br>';
				}
				else
				{*/
					
				$input.=$i.'. <b><a href="'.$bo_vars->site_object[image_dir_url].'/'.$img_path.'" target="_blank">'.$img_path.'</a></b> <input type="checkbox" value="'.$img_path.'" name="IMGDEL'.$i.'"> '.lang('remove').'<br>';
				
				
				//}

			}
		}
		/* there's just one image */
		else
		{
			$input=$img_path.'<input type="checkbox" value="'.$img_path.'" name="IMGDEL'.$img_path.'"> '.lang('remove').'<br>';
		}
	}
	
	/* get max images, set max 5 filefields */
	if (is_numeric($conf[Max_files])) 
	{
		if ($conf[Max_files]>5) $num_input=5;
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
		
		$input.=lang('add %1st image', $i).' <input type="file" name="SEP'.$i.'_'.$field_name.'">';
	}


	

	return $input;
}

?>
