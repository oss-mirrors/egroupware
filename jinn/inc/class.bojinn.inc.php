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

class bojinn
{
	var $public_functions = array(
			'save_site_data' => True
			);

	var $so;
	var $session = True;
	var $current_config;
	var $uid;
	var $action;

	var $message;


	// It will be a hell to change everything but all these vars beneath must reside in one array $site
	var $site_object_id; // can we loose this one allready?
	var $site_object; // array
	var $site_id; // can we lose this one allready?
	var $site; //array
	var $site_db; // REMOVEarray

	var $plug;


	function bojinn()
	{
		$this->so = CreateObject('jinn.sojinn');
		$this->get_config();

		$this->uid=$GLOBALS['phpgw_info']['user']['account_id'];

		$this->read_sessiondata();
		$this->use_session = True;

		$_form = $GLOBALS['HTTP_POST_VARS']['form'] ? $GLOBALS['HTTP_POST_VARS']['form']   : $GLOBALS['HTTP_GET_VARS']['form'];
		$_action = $GLOBALS['HTTP_POST_VARS']['action'] ? $GLOBALS['HTTP_POST_VARS']['action']   : $GLOBALS['HTTP_GET_VARS']['action'];
		$_site_id = $GLOBALS['HTTP_POST_VARS']['site_id'] ? $GLOBALS['HTTP_POST_VARS']['site_id']   : $GLOBALS['HTTP_GET_VARS']['site_id'];
		$_site_object_id = $GLOBALS['HTTP_POST_VARS']['site_object_id'] ? $GLOBALS['HTTP_POST_VARS']['site_object_id']    : $GLOBALS['HTTP_GET_VARS']['site_object_id'];

		if((!empty($_action) && empty($this->action)) || !empty($_action))
		{
			$this->action  = $_action;
		}
		if (($_form=='main_menu')|| (!empty($site_id)))
		{
			$this->site_id  = $_site_id;
		}

		if (($_form=='main_menu')|| (!empty($site_object_id)))
		{
			$this->site_object_id  = $_site_object_id;
		}

		// get array of site and object
		$this->site = $this->so->get_site_values($this->site_id);

		if ($this->site_object_id)
		{
			$this->site_object = $this->so->get_object_values($this->site_object_id);
		}

		$this->plug = CreateObject('jinn.boplugins.inc.php', $this->site, $this->site_object);
				

	}



	function save_sessiondata($data)
	{
		if ($this->use_session)
		{
			$GLOBALS['phpgw']->session->appsession('session_data','jinn',$data);
		}
	}

	function read_sessiondata()
	{
		if ($GLOBALS['HTTP_POST_VARS']['form']!='main_menu')
		{
			$data = $GLOBALS['phpgw']->session->appsession('session_data','jinn');
			$this->message 		= $data['message'];
			$this->site_id 		= $data['site_id'];
			$this->site_object_id	= $data['site_object_id'];
		}
	}




	function get_config()
	{
		$c = CreateObject('phpgwapi.config',$config_appname);

		$c->read_repository();

		if ($c->config_data)
		{
			$this->current_config = $c->config_data;
		}
	}

	/****************************************************************************\
	 * adminfunction for saving sitedata                                          *
	 \****************************************************************************/
/*
	function save_site_data()
	{
		$data = Array(
				'name'         => $GLOBALS['HTTP_POST_VARS']['new_site']['name'],
				'title'        => $GLOBALS['HTTP_POST_VARS']['new_site']['title'],
				'description'  => $GLOBALS['HTTP_POST_VARS']['new_site']['description'],
				'db_host'      => $GLOBALS['HTTP_POST_VARS']['new_site']['db_host'],
				'db_name'      => $GLOBALS['HTTP_POST_VARS']['new_site']['db_name'],
				'db_user'      => $GLOBALS['HTTP_POST_VARS']['new_site']['db_user'],
				'db_password'  => $GLOBALS['HTTP_POST_VARS']['new_site']['db_password']
			     );

		$this->so->insert_site_data($data);

		$GLOBALS['phpgw']->redirect($GLOBALS['phpgw']->link('/admin/index.php',''));
		$GLOBALS['phpgw']->common->phpgw_exit();
	}
*/
	/****************************************************************************\
	 * format timestamp date to europian format                                   *
	 \****************************************************************************/
	// gebruik zo snel mogelijk phpgwapi functie
	function format_date($input)
	{
		// Deze functie converteert bv. 200124061216  naar:  24-06-2001 12:16
		$jaar = substr($input,0,4);
		$maand = substr($input,4,2);
		$dag = substr($input,6,2);
		$uren = substr($input,8,2);
		$minuten = substr($input,10,2);
		return("$dag-$maand-$jaar $uren:$minuten");
	}


	/****************************************************************************\
	 * get fieldnames for phpgwtable                                              *
	 \****************************************************************************/

	function get_phpgw_fieldnames($table)
	{
		$fieldnames = $this->so->get_phpgw_fieldnames($table);

		return $fieldnames;
	}

	/****************************************************************************\
	 * get all sites the user has access to                                       *
	 \****************************************************************************/

	function get_sites($uid)
	{


		$groups=$GLOBALS['phpgw']->accounts->membership();

		if (count ($groups)>0)
		{
			foreach ( $groups as $groupfields )
			{
				$group[]=$groupfields[account_id];
			}
		}

		$user_sites=$this->so->get_sites_for_user($uid,$group);
		return $user_sites;
	}


	/****************************************************************************\
	 * create html-option list with sites for rendering                           *
	 \****************************************************************************/

	function make_site_options($sites,$site_id)
	{
		$options.="<option value=\"\">------------------</option>\n";
		foreach ( $sites as $site ) {
			$display=$this->so->get_site_name($site);
			unset($SELECTED);

			if ($site==$site_id)
			{
				$SELECTED='SELECTED';
			}
			$options.="<option value=\"$site\" $SELECTED>$display</option>\n";
		}
		return $options;
	}


	/****************************************************************************\
	 * get all tables for sitename(database_name)                                 *
	 \****************************************************************************/

	function get_site_tables($site_id)
	{

		//get databasename
		//$site_db_name=$this->get_phpgw_records('phpgw_jinn_sites',"site_id='$site_id'",'','','name');
		//die (var_dump($site_db_name).$site_db_name[0]['site_db_name']);
		//get tables for database
		$tables=$this->so->get_table_names($site_id);
		return $tables;
	}


	/****************************************************************************\
	 * create html-option list with sitestables for rendering                     *
	 \****************************************************************************/

	function make_table_options($tables,$selected_table)
	{
		//die(var_dump($tables));
		$options.="<option value=\"\">------------------</option>\n";
		foreach ( $tables as $table ) {
			unset($SELECTED);

			if ($table['table_name']==$selected_table)
			{
				$SELECTED='SELECTED';
			}
			$options.="<option value=\"".$table['table_name']."\" $SELECTED>".$table['table_name']."</option>\n";
		}
		return $options;
	}



	/****************************************************************************\
	 * create html-option list with sites for rendering                           *
	 \****************************************************************************/

	function site_options($sites)
	{
		$options.="<option value=\"\">------------------</option>\n";
		if (is_array($sites))
		{
			foreach ( $sites as $site ) {
				$display=$this->so->get_site_name($site);
				unset($SELECTED);
				if ($site==$this->site_id)
				{
					$SELECTED='SELECTED';
				}
				$options.="<option value=\"$site\" $SELECTED>$display</option>\n";
			}
		}
		return $options;
	}


	/****************************************************************************\
	 * create html-option list (thisone is going to be standard)                  *
	 \****************************************************************************/

	function make_options($list_array,$selected)
	{

		$options.="<option value=\"\">------------------</option>\n";
		//sort($list_array);
		if(is_array($list_array))
		{
			foreach ( $list_array as $array ) {

				unset($SELECTED);
				if ($array[value]==$selected)
				{
					$SELECTED='SELECTED';
				}
				$options.="<option value=\"".$array[value]."\" $SELECTED>".$array[name]."</option>\n";
			}

		}
		return $options;
	}

	function make_options_non_empty($list_array,$selected)
	{

		//		$options.="<option value=\"\">------------------</option>\n";
		//sort($list_array);
		if(is_array($list_array))
		{
			foreach ( $list_array as $array ) {

				unset($SELECTED);
				if ($array[value]==$selected)
				{
					$SELECTED='SELECTED';
				}
				$options.="<option value=\"".$array[value]."\" $SELECTED>".$array[name]."</option>\n";
			}

		}
		return $options;
	}


	/****************************************************************************\
	 * get all sites the user has access to                                       *
	 \****************************************************************************/

	function get_objects($site_id,$uid)
	{
		$groups=$GLOBALS['phpgw']->accounts->membership();

		if (count ($groups)>0)
		{
			foreach ( $groups as $groupfields )
			{
				$group[]=$groupfields[account_id];
			}
		}

		$objects=$this->so->get_objects($site_id,$uid,$group);
		return $objects;
	}


	/****************************************************************************\
	 * create html-option list with site-objects for rendering                    *
	 \****************************************************************************/

	function object_options($objects)
	{

		$options.="<option value=\"\">----------$site_id--------</option>\n";

		if (is_array($objects))
		{
			foreach ( $objects as $object ) {
				$display=$this->so->get_object_name($object);
				unset($SELECTED);
				if ($object==$this->site_object_id)
				{
					$SELECTED='SELECTED';
				}
				$options.="<option value=\"$object\" $SELECTED>$display</option>\n";
			}
		}


		return $options;
	}

	/****************************************************************************\
	 * set startlimits or new limits for for browsing through table records       *
	 \****************************************************************************/

	function set_limits($limit_start,$limit_stop,$direction,$num_rows)
	{

		if ($limit_start>$limit_stop) unset($limit_stop);
		if ($direction==">")
		{
			$limit_start=$limit_stop;
			$limit_stop=($limit_stop+30);
			$limit="LIMIT $limit_start,30";
		}
		elseif ($direction=="<")
		{
			$limit_start=($limit_start-30);
			$limit_stop=($limit_start+30);
			$limit="LIMIT $limit_start,30";
		}
		elseif ($direction=="<<")
		{
			$limit_start=0;
			$limit_stop=30;
			$limit="LIMIT 0,30";
		}
		elseif ($direction==">>")
		{
			$limit_start=$num_rows-30;
			$limit_stop=$num_rows;
			$limit="LIMIT $limit_start,30";
		}
		elseif (($limit_start) && ($limit_stop)) $limit="LIMIT $limit_start,".($limit_stop-$limit_start);
		elseif (($limit_start) && (!$limit_stop)) $limit="LIMIT $limit_start,30";
		elseif ((!$limit_start) && ($limit_stop)) $limit="LIMIT 0,$limit_stop";
		else {
			$limit="LIMIT 0,30";
			$limit_start=0;
			$limit_stop=30;
		}

		$returnlimit = array
			(
			 'start'=>$limit_start,
			 'stop'=>$limit_stop,
			 'SQL'=>$limit
			);

		return $returnlimit;

	}




	function get_phpgw_records($table,$where_condition,$offset,$limit,$value_reference)
	{
		if (!$value_reference)
		{
			$value_reference='num';
		}

		$records = $this->so->get_phpgw_record_values($table,$where_condition,$offset,$limit,$value_reference);

		return $records;
	}




	function get_records($table,$where_condition,$offset,$limit,$value_reference)
	{
		if (!$value_reference)
		{
			$value_reference='num';
		}

		$records = $this->so->get_record_values($this->site_id,$table,$where_condition,$offset,$limit,$value_reference);

		return $records;
	}

	function get_records_2($table,$where_condition,$offset,$limit,$value_reference,$order_by)
	{
		if (!$value_reference)
		{
			$value_reference='num';
		}

		$records = $this->so->get_record_values_2($this->site_id,$table,$where_condition,$offset,$limit,$value_reference,$order_by);

		return $records;
	}


	/****************************************************************************\
	 * delete record from external table                                          *
	 \****************************************************************************/

	function delete_phpgw_data($table,$where_condition)
	{
		$status=$this->so->delete_phpgw_data($this->site_id,$table,$where_condition);

		return $status;
	}

	/****************************************************************************\
	 * insert data into phpgw table                                               *
	 \****************************************************************************/

	function insert_phpgw_data($table,$HTTP_POST_VARS,$HTTP_POST_FILES)
	{
		$data=$this->make_http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES);
		$status=$this->so->insert_phpgw_data($table,$data);

		return $status;
	}

	/****************************************************************************\
	 * update data in phpgw table                                                 *
	 \****************************************************************************/

	function update_phpgw_data($table,$HTTP_POST_VARS,$HTTP_POST_FILES,$where_condition)
	{

		/*************************************
		 * start relation section             *
		 *************************************/

		if ($HTTP_POST_VARS[FLDrelations])
		{
			// check if there are relations to delete
			$relations_to_delete=$this->filter_array_with_prefix($HTTP_POST_VARS,'DEL');
			if (count($relations_to_delete)>0){

				$relations_org=explode('|',$HTTP_POST_VARS[FLDrelations]);
				foreach($relations_org as $relation_org)
				{
					if (!in_array($relation_org,$relations_to_delete))
					{
						if ($new_org_relation) $new_org_relation.='|';
						$new_org_relation.=$relation_org;
					}
				}
				$HTTP_POST_VARS[FLDrelations]=$new_org_relation;
			}
		}

		// check if new ONE WITH MANY relation parts are complete else drop them
		if($HTTP_POST_VARS['1_relation_org_field'] && $HTTP_POST_VARS['1_relation_table_field'] 
				&& $HTTP_POST_VARS['1_display_field'])
		{
			$new_relation='1:'.$HTTP_POST_VARS['1_relation_org_field'].':null:'.$HTTP_POST_VARS['1_relation_table_field']
				.':'.$HTTP_POST_VARS['1_display_field'];

			if ($HTTP_POST_VARS['FLDrelations']) $HTTP_POST_VARS['FLDrelations'].='|';
			$HTTP_POST_VARS['FLDrelations'].=$new_relation;
		}

		// check if new MANY WITH MANY relation parts are complete else drop them
		if($HTTP_POST_VARS['2_relation_via_primary_key'] && $HTTP_POST_VARS['2_relation_foreign_key'] 
				&& $HTTP_POST_VARS['2_relation-via-foreign-key'] && $HTTP_POST_VARS['2_display_field'])
		{
			$new_relation='2:'.$HTTP_POST_VARS['2_relation_via_primary_key'].':'.$HTTP_POST_VARS['2_relation-via-foreign-key'].':'
				.$HTTP_POST_VARS['2_relation_foreign_key'].':'.$HTTP_POST_VARS['2_display_field'];

			if ($HTTP_POST_VARS['FLDrelations']) $HTTP_POST_VARS['FLDrelations'].='|';

			$HTTP_POST_VARS['FLDrelations'].=$new_relation;
		}

		// check all pluginfield for values
		// put values in http_post_var

		if ($HTTP_POST_VARS[FLDplugins])
		{
			$HTTP_POST_VARS['FLDplugins']=$this->make_http_vars_pairs_plugins($HTTP_POST_VARS);
		}


		$data=$this->make_http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES);
	//	die(var_dump($data));	
		$status=$this->so->update_phpgw_data($table,$data, $where_condition);

		return $status;


	}

	/********************data********************************************************\
	 * delete record from external table                                          *
	 \****************************************************************************/

	function delete_object_data($table,$where_condition)
	{
		$status=$this->so->delete_object_data($this->site_id,$table,$where_condition);

		return $status;
	}

	/****************************************************************************\
	 * copy record from external table                                          *
	 \****************************************************************************/

	function copy_object_data($table,$where_condition)
	{
		$status=$this->so->copy_object_data($this->site_id,$table,$where_condition);

		return $status;
	}


	/****************************************************************************\
	 * insert data into external table                                            *
	 \****************************************************************************/

	function insert_object_data($table,$HTTP_POST_VARS,$HTTP_POST_FILES)
	{

		//		$many_data=$this->make_http_vars_pairs_many($HTTP_POST_VARS,$HTTP_POST_FILES);

		//		$status=$this->so->update_object_many_data($this->site_id,$many_data);

		$data=$this->make_http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES);
		$image_data=$this->add_image_data($HTTP_POST_VARS,$HTTP_POST_FILES);
		$attachment_data=$this->add_attachment_data($HTTP_POST_VARS,$HTTP_POST_FILES);

		if (is_array($data) && is_array($image_data))
		{
			$data=array_merge($data,$image_data);
		}

		if (is_array($data) && is_array($attachment_data))
		{
			$data=array_merge($data,$attachment_data);
		}

		$status=$this->so->insert_object_data($this->site_id,$table,$data);



		return $status;
	}

	/****************************************************************************\
	 * update data in external table                                              *
	 \****************************************************************************/
	function update_object_data($table,$HTTP_POST_VARS,$HTTP_POST_FILES,$where_condition)
	{
		$unlink_error=0;

		$many_data=$this->make_http_vars_pairs_many($HTTP_POST_VARS,$HTTP_POST_FILES);

		$status=$this->so->update_object_many_data($this->site_id,$many_data);


		$data=$this->make_http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES);
		$attachment_data=$this->add_attachment_data($HTTP_POST_VARS,$HTTP_POST_FILES);
		$image_data=$this->add_image_data($HTTP_POST_VARS,$HTTP_POST_FILES);

		if (is_array($data) && is_array($attachment_data))
		{
			$data=array_merge($data,$attachment_data);
		}

		if (is_array($data) && is_array($image_data))
		{
			$data=array_merge($data,$image_data);
		}

		$status=$this->so->update_object_data($this->site_id,$table,$data, $where_condition);

		return $status;
	}


	/****************************************************************************\
	 * main image data function                                                   *
	 \****************************************************************************/
	function add_image_data($HTTP_POST_VARS,$HTTP_POST_FILES)
	{


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
		$images_to_delete=$this->filter_array_with_prefix($HTTP_POST_VARS,'IMGDEL');
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

		/// deleting thumbs if neccesary thirst
		$thumbs_to_delete=$this->filter_array_with_prefix($HTTP_POST_VARS,'TMBDEL');
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

			$tmppath=$this->convertImage ($image_input_handle, $width,$image_type);



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

				$tmppath=$this->convertImage ($image_input_handle,$thumb_width,$image_type);
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


	/****************************************************************************\
	 * main image data function                                                   *
	 \****************************************************************************/
	function add_attachment_data($HTTP_POST_VARS,$HTTP_POST_FILES)
	{
		$upload_path= trim($this->site_object['upload_path']);



		$attachment_input_handle=$GLOBALS['HTTP_POST_FILES']['attachment_path'];
		//die(var_dump($GLOBALS['HTTP_POST_FILES']));
		$attachment_path_field_name='attachment_path';

		/// deleting images if neccesary thirst
		$attachments_to_delete=$this->filter_array_with_prefix($HTTP_POST_VARS,'ATTDEL');

		if (count($attachments_to_delete)>0){
			//			unset($attachment_path_new);
			$attachment_path_changed=True;

			// delete from harddisk
			foreach($attachments_to_delete as $attachment_to_delete)
			{
				if (!@unlink($upload_path.'/'.$attachment_to_delete)) $unlink_error++;
			}

			$attachments_org=explode(';',$HTTP_POST_VARS[attachment_path_org]);


			foreach($attachments_org as $attachment_org)
			{
				//die (var_dump($attachment_org).'<P>'.var_dump($attachments_to_delete));
				//die ($attachment_path_new);
				if (!in_array($attachment_org,$attachments_to_delete))
				{
					//die('hallo');
					if ($attachment_path_new) $attachment_path_new.=';';
					$attachment_path_new.=$attachment_org;
				}
			}
		}
		else
		{
			$attachment_path_new.=$HTTP_POST_VARS['attachment_path_org'];
		}

		// finally adding new attachment
		if($GLOBALS['HTTP_POST_FILES']['attachment_path']['name'])
		{
			$image_type=substr($attachment_path_field_name,(strlen($attachment_path_field_name)-3));

			if(!is_dir($upload_path.'/attachments') && !mkdir($upload_path.'/attachments', 0755))
			{
				die (lang("attachment directory does not exist or is not correct, nor can it be created...<br>please check object's upload dir: ").$upload_path);
			}


			$attachment_file_name='att_'.time().'_'.$GLOBALS['HTTP_POST_FILES']['attachment_path']['name'];

			$tmppath=$attachment_input_handle[tmp_name];
			if (copy($tmppath, $upload_path."/attachments/".$attachment_file_name))
			{

				if($attachment_path_new) $attachment_path_new .= ';';
				$attachment_path_new.="attachments/".$attachment_file_name;

			}
			else
			{
				die ("failed to copy $file...<br>\n");
			}
			@unlink($tmppath);

		}


		//// make return array for storage
		//$attachments_path_changed
		if($attachment_path_new || $attachment_path_changed)
		{
			$data[] = array
				(
				 'name' => $attachment_path_field_name,
				 'value' => $attachment_path_new
				);
		}

		return $data;
	}

	function update_access_rights_object($HTTP_POST_VARS)
	{
		reset ($HTTP_POST_VARS);

		while (list ($key, $val) = each ($HTTP_POST_VARS)) {

			if (substr($key,0,6)=='editor'){
				$editors[]=$val;

			}

		}

		if (is_array($editors)) $editors=array_unique($editors);

		$status=$this->so->update_object_access_rights($editors,$HTTP_POST_VARS['object_id']);

		return $status;

	}





	function update_access_rights_site($HTTP_POST_VARS)
	{
		reset ($HTTP_POST_VARS);

		while (list ($key, $val) = each ($HTTP_POST_VARS)) 
		{

			if (substr($key,0,6)=='editor')
			{
				$editors[]=$val;

			}

		}

		if (is_array($editors)) $editors=array_unique($editors);

		$status=$this->so->update_site_access_rights($editors,$HTTP_POST_VARS['site_id']);

		return $status;

	}

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

	/****************************************************************************\
	 * make array with pairs of keys and values from http_post_vars               *
	 \****************************************************************************/
	// get all of this sort plugins and out in it into one
	function make_http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES) 
	{

		/* for standard make_http_var:

		   1 check is SEP is used and if
		   2 get original
		   3 delete the deleted ones from string
		   4 add the ones to the string


		/* for filter:
		1 check for filter
		2 delete files from disk
		3 upload new ones
		4 convert images
		5 return mergeable data


		 */

		while(list($key, $val) = each($HTTP_POST_VARS)) 
		{

			/*
			   if (substr($key,0,3)=='SEP')
			   {
			   $new_var = substr($key,strpos($key, "_"));
			   if($last_var==$new_var)
			   {
			// go on with array
			$data[count($data)][$last_var]='$val'; 

			}
			else
			{
			// start new array

			}

			$last_var = $new_var;

			}

			 */
			if(substr($key,0,3)=='FLD')
			{
				// get_fsp_plugin met key en val en kom terug met array
				//$data=$this->plugins->get_fsp_plugin(substr($key,3),$val);			

				//if(!$data)
				//{
					$data[] = array
						(
						 'name' => substr($key,3),
						 'value' => addslashes($val) 
						);
				//}
			}
		}

		return $data;
	}

	/****************************************************************************
	* make array with pairs of keys and values from http_post_vars              *
	****************************************************************************/
/*
	function make_http_vars_pairs_plugins($HTTP_POST_VARS) 
	{

		$data=$this->make_http_vars_pairs_fip($HTTP_POST_VARS);

		if($data) $data .='|';

		$data.=$this->make_http_vars_pairs_sfp($HTTP_POST_VARS);
		//			die($data);
		return $data;
	}
*/
	function make_http_vars_pairs_plugins($HTTP_POST_VARS) 
	{
		//var_dump($HTTP_POST_VARS);

		while(list($key, $val) = each($HTTP_POST_VARS)) {

			if(substr($key,0,7)=='CFG_PLG' && $val)
			{
				$cfg[substr($key,7)]=$val;
			}
		}
		//var_dump($cfg);
		reset($HTTP_POST_VARS);	
		while(list($key, $val) = each($HTTP_POST_VARS)) 
		{

			if(substr($key,0,3)=='PLG' && $val)
			{
				if($data) $data .='|';
				$data .= substr($key,3).':'.$val.':xx:'.$cfg[substr($key,3)];
			}
		}

		//var_dump($data);
		return $data;
	}
/*
	function make_http_vars_pairs_sfp($HTTP_POST_VARS) 
	{
		while(list($key, $val) = each($HTTP_POST_VARS)) 
		{

			if(substr($key,0,7)=='CFG_SFP' && $val)
			{
				$cfg[substr($key,7)]=$val;
			}
		}
		reset($HTTP_POST_VARS);	
		while(list($key, $val) = each($HTTP_POST_VARS)) {

			if(substr($key,0,3)=='SFP' && $val)
			{
				if($data) $data .='|';
				$data .= substr($key,3).':'.$val.':sf:'.$cfg[substr($key,3)];
			}
		}

		return $data;
	}

*/
	/****************************************************************************
	 * make array with pairs of keys and values from http_post_vars              *
	 ****************************************************************************/

	function make_http_vars_pairs_many($HTTP_POST_VARS) {

		while(list($key, $val) = each($HTTP_POST_VARS)) {


			if(substr($key,0,3)=='MAN'||substr($key,0,5)=='FLDid')
			{

				$data = array_merge($data,array
						(
						 $key=> $val
						));
			}
		}
		//		die(var_dump($HTTP_POST_VARS));
		return $data;
	}

	/****************************************************************************\
	 * make array with pairs of keys and values from http_post_vars               *
	 \****************************************************************************/

	function make_http_vars_pairs_img($HTTP_POST_VARS,$HTTP_POST_FILES) {

		while(list($key, $val) = each($HTTP_POST_VARS)) {

			if(substr($key,0,3)=='IMG')
			{

				$data[] = array
					(
					 'name' => substr($key,3),
					 'value' => $val
					);
			}
		}

		return $data;
	}

	function convertImage ($image, $width,$image_type)
	{
		$convert_exec=$this->current_config[convert_exec];

		if (!file_exists ($convert_exec))
		{
			die(lang('The path to <i>convert</i> is not correct. Either <i>convert (ImageMagick)</i>
						is not installed or the path is not correct.<br>Please contact your adminisrator 
						with this message<P>The configured path is: ').$convert_exec);
		}

		if(!exec($convert_exec))
		{
			if (ini_get('safe_mode'))
			{
				$savemode='On';
			}
			else
			{
				$savemode='Off';
			}

			die(lang('<i>Convert</i> can\'t be executed! If this PHP-application-server runs in <i>Save Mode</i> make sure <i>convert</i> is located in the phpGroupWare-root-directory. This is the only location we\'re allowed to execute it, Also make sure it\'s executable for the owner of this script.<br>Please contact your adminisrator with this message.<P>The configured path is: ').$convert_exec.'<br>'.lang('Save Mode is: ').' '.$savemode);


		}

		if (!file_exists ($convert_exec))
		{
			die(lang('The path to <i>convert</i> is not correct. Either <i>convert (ImageMagick)</i>
						is not installed or the path is not correct.<P>Please contact your adminisrator 
						with this message').'<P>'.$convert_exec);
		}

		// get temporary file name...
		$temporary_file = tempnam ("jinn/temp", "tn");
		unlink($temporary_file);

		// convert to temporary file name
		$execute_command = exec ("$convert_exec -geometry $width $image[tmp_name] $temporary_file");


		if (!file_exists ($temporary_file))
			die(lang("Though we checked almost everything, something went wrong :-(<br>Here is some debugging information:<P>").
					'Full execution command: '."$convert_exec -geometry $width $image[tmp_name] $temporary_file".'<br>'.
					'Input filename : '.$image[tmp_name].'<br>'.
					'Output filename : '. $temporary_file.'<br>');



		return $temporary_file;
	}


	function get_object_column_names($site_id,$table)
	{
		$fields_props=$this->so->get_site_fieldproperties($site_id,$table);

		foreach ($fields_props as $field_props)
		{
			$column_names[]=$field_props[name];
		}

		return $column_names;
	}

	function get_site_fieldproperties($site_id,$table)
	{

		$fieldproperties=$this->so->get_site_fieldproperties($site_id,$table);

		return $fieldproperties;
	}

	function get_phpgw_fieldproperties($table)
	{
		$fieldproperties=$this->so->get_phpgw_fieldproperties($table);

		return $fieldproperties;
	}
	function read_preferences($key)
	{
		$GLOBALS['phpgw']->preferences->read_repository();

		$prefs = array();

		if ($GLOBALS['phpgw_info']['user']['preferences']['jinn'])
		{
			$prefs = $GLOBALS['phpgw_info']['user']['preferences']['jinn'][$key];
		}
		return $prefs;
	}

	function save_preferences($key,$prefs)
	{
		$GLOBALS['phpgw']->preferences->read_repository();

		if ($prefs)
		{
			$GLOBALS['phpgw']->preferences->change('jinn','$key',$prefs);
			$GLOBALS['phpgw']->preferences->save_repository(True);
		}
	}


}
?>
