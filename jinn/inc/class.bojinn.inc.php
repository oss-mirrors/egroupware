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
		var $magick;

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
			$this->magick = CreateObject('jinn.boimagemagick.inc.php');	

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
			$tables=$this->so->get_table_names($site_id);
			return $tables;
		}

		/****************************************************************************\
		* create html-option list with sitestables for rendering                     *
		\****************************************************************************/

		function make_table_options($tables,$selected_table)
		{
			$options.="<option value=\"\">------------------</option>\n";
			foreach ( $tables as $table ) {
				unset($SELECTED);

				if ($table['table_name']==$selected_table)
				{
					$SELECTED='SELECTED';
				}
				$options.="<option value=\"".$table['table_name']."\" $SELECTED>".stripslashes($table['table_name'])."</option>\n";
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
					$options.="<option value=\"$site\" $SELECTED>".stripslashes($display)."</option>\n";
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
			if(is_array($list_array))
			{
				foreach ( $list_array as $array ) {

					unset($SELECTED);
					if ($array[value]==$selected)
					{
						$SELECTED='SELECTED';
					}
					$options.="<option value=\"".$array[value]."\" $SELECTED>".stripslashes($array[name])."</option>\n";
				}

			}
			return $options;
		}

		function make_options_non_empty($list_array,$selected)
		{

			if(is_array($list_array))
			{
				foreach ( $list_array as $array ) {

					unset($SELECTED);
					if ($array[value]==$selected)
					{
						$SELECTED='SELECTED';
					}
					$options.="<option value=\"".$array[value]."\" $SELECTED>".stripslashes($array[name])."</option>\n";
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

			$data=$this->make_http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES);
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
			$status=$this->so->update_object_data($this->site_id,$table,$data, $where_condition);

			return $status;
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
		function make_http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES) 
		{

			while(list($key, $val) = each($HTTP_POST_VARS)) 
			{
				if(substr($key,0,3)=='FLD')
				{

					/* Check for plugin need and plugin availability */
					if ($filtered_data=$this->plug->get_plugin_sf($key,$HTTP_POST_VARS,$HTTP_POST_FILES))
					{
						if ($filtered_data==-1) $filtered_data='';
						$data[] = array
						(
							'name' => substr($key,3),
							'value' =>  $filtered_data  //addslashes($val)
						);
					}
					else // if there's no plugin, just save the vals
					{
						$data[] = array
						(
							'name' => substr($key,3),
							'value' => addslashes($val) 
						);
					}
				}
			}
			return $data;
		}

		/****************************************************************************
		* make array with pairs of keys and values from http_post_vars              *
		****************************************************************************/

		function make_http_vars_pairs_plugins($HTTP_POST_VARS) 
		{
			//var_dump($HTTP_POST_VARS);

			while(list($key, $val) = each($HTTP_POST_VARS)) {

				if(substr($key,0,7)=='CFG_PLG' && $val)
				{
					$cfg[substr($key,7)]=$val;
				}
			}
			reset($HTTP_POST_VARS);	
			while(list($key, $val) = each($HTTP_POST_VARS)) 
			{

				if(substr($key,0,3)=='PLG' && $val)
				{
					if($data) $data .='|';
					$data .= substr($key,3).':'.$val.':xx:'.$cfg[substr($key,3)];
				}
			}

			return $data;
		}

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
			return $data;
		}

		function check_safe_mode()
		{
			if (ini_get('safe_mode'))
			{
				$safe_mode='On';
			}
			else
			{
				$safe_mode='Off';
			}
			return $safe_mode;
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

		/****************************************************************************\
		* make array of one with many relational information                         *
		\****************************************************************************/

		function get_fields_with_1_relation($relations)
		{
			$relations_array = explode('|',$relations);

			foreach($relations_array as $relation)
			{
				$relation_part=explode(':',$relation);
				if ($relation_part[0]=='1')
				{
					$return_relation[$relation_part[1]] = array
					(
						'type'=>$relation_part[0],
						'field_org'=>$relation_part[1],
						'related_with'=>$relation_part[3],
						'display_field'=>$relation_part[4]
					);
				}

			}
			return $return_relation;
		}

		/****************************************************************************\
		* make array of many with many relational information                        *
		\****************************************************************************/

		function get_fields_with_2_relation($relations)
		{
			$relations_array = explode('|',$relations);

			foreach($relations_array as $relation)
			{
				$relation_part=explode(':',$relation);
				if ($relation_part[0]=='2')
				{
					$tmp=explode('.',$relation_part[1]);
					$via_table=$tmp[0];
					$tmp=explode('.',$relation_part[4]);
					$display_table=$tmp[0];

					$return_array[] = array
					(
						'type'=>$relation_part[0],
						'via_primary_key'=>$relation_part[1],
						'via_foreign_key'=>$relation_part[2],
						'via_table'=>$via_table,
						'foreign_key'=>$relation_part[3],
						'display_field'=>$relation_part[4],
						'display_table'=>$display_table
					);
				}
			}
			return $return_array;
		}

		function get_related_field($relation_array)
		{
			$table_info=explode('.',$relation_array[related_with]);
			$table=$table_info[0];
			$related_field=$table_info[1];

			$table_info2=explode('.',$relation_array[display_field]);
			$table_display=$table_info2[0];
			$display_field=$table_info2[1];

			$allrecords=$this->get_records_2($table,'','','','name',$display_field);

			foreach ($allrecords as $record)
			{
				$related_fields[]=array
				(
					'value'=>$record[$related_field],
					'name'=>$record[$display_field]
				);
			}
			return $related_fields;
		}

		function get_m2m_options($relation2,$all_or_stored,$object_id)
		{
			return $this->so->get_m2m_record_values($this->site_id,$object_id,$relation2,$all_or_stored);
		}
	}


?>
