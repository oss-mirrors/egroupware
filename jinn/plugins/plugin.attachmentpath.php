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
	plugin.attachmentpath.php contains the standard attachment-upload plugin for 
	JiNN number off standardly available 
	plugins for JiNN. 
	*/

	$this->plugins['attachpath']['name']			= 'attachpath';
	$this->plugins['attachpath']['title']			= 'AttachmentPath plugin';
	$this->plugins['attachpath']['version']			= '0.8.0';
	$this->plugins['attachpath']['enable']			= 1;
	$this->plugins['attachpath']['db_field_hooks']	= array
	(
		'text',
		'string',
		'blob'
	);

	/* ATTENTION: spaces and special character are not allowed in config array 
	use underscores for spaces */
	$this->plugins['attachpath']['config']		= array
	(
		'Max_files' => array('3','text','maxlength=2 size=2'), 
		'Max_attachment_size_in_MB_eg_1_Leave_empty_for_no_limit' => array('','text','maxlength=4 size=4')
	);

	function plg_fi_attachpath($field_name,$value,$config)
	{	

		global $local_bo;
		$field_name=substr($field_name,3);	

		if ($local_bo->site_object['upload_url']) $upload_url=$local_bo->site_object['upload_url'].'/';
		elseif($local_bo->site['upload_url']) $upload_url=$local_bo->site['upload_url'].'/';
		else $upload_url=false;
		
		/* if value is set, show existing images */	
		if($value)
		{
			$input='<input type="hidden" name="ATT_ORG'.$field_name.'" value="'.$value.'">';

			$value=explode(';',$value);

			/* there are more images */
			if (is_array($value))
			{
				$i=0;
				foreach($value as $att_path)
				{
					$i++;

					$input.=$i.'. ';
					if($upload_url) $input.='<b><a href="'.$upload_url.$att_path.'" target="_blank">'.$att_path.'</a></b>';
					else $input.='<b>'.$att_path.'</b>';
					$input.=' <input type="checkbox" value="'.$att_path.'" name="ATT_DEL'.$field_name.$i.'"> '.lang('remove').'<br>';
				}
			}
			/* there's just one image */
			else
			{
				$input=$att_path.'<input type="checkbox" value="'.$att_path.'" name="ATT_DEL'.$fieldname.'"> '.lang('remove').'<br>';
			}
		}

		/* get max attachments, set max 5 filefields */
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
			if($num_input==1) $input .=lang('add attachment');
			else
			{
				$input.='<br>';
				$input.=lang('add attachment %1', $i).
				' <input type="file" name="ATT_SRC'.$field_name.$i.'">';
			}
		}

		$input.='<input type="hidden" name="FLD'.$field_name.'" value="TRUE">';

		return $input;
	}



	function plg_sf_attachpath($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	/****************************************************************************\
	* main image data function                                                   *
	\****************************************************************************/
	{
		global $local_bo;

		if ($local_bo->site_object['upload_path']) $upload_path=$local_bo->site_object['upload_path'].'/';
		elseif($local_bo->site['upload_path']) $upload_path=$local_bo->site['upload_path'].'/';
		else $upload_path=false;

		$atts_to_delete=$local_bo->common->filter_array_with_prefix($HTTP_POST_VARS,'ATT_DEL');

		if (count($atts_to_delete)>0){

			$atts_path_changed=True;
			// delete from harddisk
			foreach($atts_to_delete as $att_to_delete)
			{
				if (!@unlink($upload_path.'/'.$att_to_delete)) $unlink_error++;
			}

			$atts_org=explode(';',$HTTP_POST_VARS['ATT_ORG'.substr($field_name,3)]);

			foreach($atts_org as $att_org)
			{
				if (!in_array($att_org,$atts_to_delete))
				{
					if ($atts_path_new) $atts_path_new.=';';
					$atts_path_new.=$att_org;
				}
			}
		}
		else
		{
			$atts_path_new.=$HTTP_POST_VARS['ATT_ORG'.substr($field_name,3)];
		}

		/* make array again of the original attachment */
		$atts_array=explode(';',$atts_path_new);
		unset($atts_path_new);

		/* finally adding new attachments */
		$atts_to_add=$local_bo->common->filter_array_with_prefix($HTTP_POST_FILES,'ATT_SRC');

		// quick check for new attchments
		if(is_array($atts_to_add))
		foreach($atts_to_add as $attscheck)
		{
			if($attscheck['name']) $num_atts_to_add++;
		}

		if ($num_atts_to_add)
		{
			/* check for minimal criteria */
			/* new better error_messages */

			if(!is_dir($upload_path))
			{
				die (lang("<i>attachments upload root-directory</i> does not exist or is not correct ...<br>
				please contact Administrator with this message") .lang('check: '). $upload_path);
			}

			if(!is_dir($upload_path.'/attachments') && !mkdir($upload_path.'/attachments', 0755))
			{
				die (lang("<i>attachments normal_size-directory</i> does not exist and cannot be created ...<br>
				please contact Administrator with this message"));
			}

			if(!is_dir($upload_path.'/attachments') && !mkdir($upload_path.'/attachments', 0755))
			{
				die (lang("<i>attachments-directory</i> does not exist and cannot be created ...<br>
				please contact Administrator with this message"));
			}

			if($temporary_file = tempnam ($upload_path.'/attachments', "test_")) // make temporary file name...
			{
				unlink($temporary_file);
			} 
			else
			{
				die (lang("<i>/attachments-directory</i> is not writable ...<br>
				please contact Administrator with this message"));
			}

			$att_position=0;
			foreach($atts_to_add as $add_att)
			{
				if($add_att['name'])
				{
					$new_temp_file=$add_att['tmp_name']; // just copy

					$target_att_name = time().ereg_replace("[^a-zA-Z0-9_.]", '_', $add_att['name']);

					if (copy($new_temp_file, $upload_path."/attachments/".$target_att_name))
					{
						$atts_array[$att_position]='attachments/'.$target_att_name;
					}
					else
					{
						die ("failed to copy $target_att_name...<br>\n");
					}
				}

				$att_position++;

			}
		}

		if(is_array($atts_array))
		{
			foreach ($atts_array as $atts_string)
			{

				if($atts_path_new) $atts_path_new .= ';';
				$atts_path_new.=$atts_string;
			}						
		}

		//// make return array for storage
		if($atts_path_new || $atts_path_changed)
		{
			return $atts_path_new;
		}

		return '-1'; /* return -1 when there no value to give but the function finished succesfully */
	}

?>
