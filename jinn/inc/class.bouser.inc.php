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

	class bouser 
	{
		var $public_functions = Array
		(
			'object_update'		=> True,
			'object_insert'		=> True,
			'del_object'			=> True,
			'save_object_config'	=> True,
			'copy_object'			=> True
		);

		var $so;
		var $session;

		var $message;

		var $site_object_id; 
		var $site_object; 
		var $site_id; 
		var $site; 
		var $local_bo;
		var $magick;

		var $current_config;
		var $action;
		var $common;
		var $browse_settings;

		function bouser()
		{
			$this->common = CreateObject('jinn.bocommon');
			$this->current_config=$this->common->get_config();		

			$this->so = CreateObject('jinn.sojinn');

			$this->include_plugins();
			$this->magick = CreateObject('jinn.boimagemagick.inc.php');	

			$this->read_sessiondata();

			$_form = $GLOBALS['HTTP_POST_VARS']['form'];
			$_site_id = $GLOBALS['HTTP_POST_VARS']['site_id'];
			$_site_object_id = $GLOBALS['HTTP_POST_VARS']['site_object_id'];
			
			if (($_form=='main_menu')|| !empty($site_id)) $this->site_id  = $_site_id;
			if (($_form=='main_menu') || !empty($site_object_id)) $this->site_object_id  = $_site_object_id;

			if ($this->site_id) $this->site = $this->so->get_site_values($this->site_id);
			if ($this->site_object_id) $this->site_object = $this->so->get_object_values($this->site_object_id);
		}

		function save_sessiondata()
		{
			$data = array(
				'message' => $this->message, 
				'site_id' => $this->site_id,
				'site_object_id' => $this->site_object_id,
				'browse_settings'=>	$this->browse_settings
			);

			$GLOBALS['phpgw']->session->appsession('session_data','jinn',$data);
		}

		function read_sessiondata()
		{
			if ($GLOBALS['HTTP_POST_VARS']['form']!='main_menu')
			{
				$data = $GLOBALS['phpgw']->session->appsession('session_data','jinn');
				$this->message 		= $data['message'];
				$this->site_id 		= $data['site_id'];
				$this->site_object_id	= $data['site_object_id'];
				$this->browse_settings	= $data['browse_settings'];
			}
		}

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



		//remove this one
		function get_records($table,$where_condition,$offset,$limit,$value_reference)
		{
			if (!$value_reference)
			{
				$value_reference='num';
			}

			$records = $this->so->get_record_values($this->site_id,$table,$where_condition,$offset,$limit,$value_reference);

			return $records;
		}

		// remove this one
		function get_records_2($table,$where_condition,$offset,$limit,$value_reference,$order_by)
		{
			if (!$value_reference)
			{
				$value_reference='num';
			}

			$records = $this->so->get_record_values_2($this->site_id,$table,$where_condition,$offset,$limit,$value_reference,$order_by);

			return $records;
		}



		function object_insert()
		{
			$data=$this->http_vars_pairs($GLOBALS[HTTP_POST_VARS],$GLOBALS[HTTP_POST_FILES]);
			$status=$this->so->insert_object_data($this->site_id,$this->site_object[table_name],$data);

			if ($status==1)	$this->message['info']='Record met succes toegevoegd';
			else $this->message[error]=lang('Record NOT succesfully deleted. Unknown error');

			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		}

		function object_update()
		{
			/* exit and go to del function */
			if($GLOBALS[HTTP_POST_VARS][delete])
			{
				$this->del_object();
			}

			$where_condition = $GLOBALS[where_condition];
			$table=$this->site_object[table_name];

			$many_data=$this->http_vars_pairs_many($GLOBALS[HTTP_POST_VARS], $GLOBALS[HTTP_POST_FILES]);

			$status=$this->so->update_object_many_data($this->site_id, $many_data);

			$data=$this->http_vars_pairs($GLOBALS[HTTP_POST_VARS], $GLOBALS[HTTP_POST_FILES]);
			$status=$this->so->update_object_data($this->site_id, $table, $data, $where_condition);

			if ($status==1)	$this->message[info]='Record succesfully saved';
			else $this->message[error]='Record NOT succesfully saved';

			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		}

		function del_object()
		{
			$table=$this->site_object[table_name];
			$where_condition=stripslashes($GLOBALS[where_condition]);

			$status=$this->so->delete_object_data($this->site_id, $table, $where_condition);

			if ($status==1)	$this->message[info]=lang('Record succesfully deleted');
			else $this->message[error]=lang('Record NOT succesfully deleted. Unknown error');

			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		}

		function copy_object()
		{
			$table=$this->site_object[table_name];
			$where_condition=$GLOBALS[where_condition];

			$status=$this->so->copy_object_data($this->site_id,$table,$where_condition);
			if ($status==1)	$this->message[info]=lang('Record succesfully copied');
			else $this->message[error]=lang('Record NOT succesfully copied. Unknown error');

			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		}


		function extract_1w1_relations($string)
		{
			$relations_array = explode('|',$string);

			foreach($relations_array as $relation)
			{
				$relation_part=explode(':',$relation);
				if ($relation_part[0]=='1')
				{
					$relation_arr[$relation_part[1]] = array
					(
						'type'=>$relation_part[0],
						'field_org'=>$relation_part[1],
						'related_with'=>$relation_part[3],
						'display_field'=>$relation_part[4]
					);
				}

			}
			return $relation_arr;
		}


		function extract_1wX_relations($string)
		{
			$relations_array = explode('|',$string);

			foreach($relations_array as $relation)
			{
				$relation_part=explode(':',$relation);
				if ($relation_part[0]=='2')
				{
					$tmp=explode('.',$relation_part[1]);
					$via_table=$tmp[0];
					$tmp=explode('.',$relation_part[4]);
					$display_table=$tmp[0];

					$relation_arr[] = array
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
			return $relation_arr;
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


			if(is_array($allrecords))
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

		function http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES) 
		{

			////////////////die('halo');
			while(list($key, $val) = each($HTTP_POST_VARS)) 
			{
				if(substr($key,0,3)=='FLD')
				{
					/* Check for plugin need and plugin availability */
					if ($filtered_data=$this->get_plugin_sf($key,$HTTP_POST_VARS,$HTTP_POST_FILES))				
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


		function http_vars_pairs_many($HTTP_POST_VARS) {

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

		/**
		* get input function from plugin 
		*/
		function get_plugin_fi($input_name,$value,$type)
		{
			global $local_bo;
			$local_bo=$this;
			$plugins=explode('|',$this->site_object['plugins']);
			foreach($plugins as $plugin)
			{	
				$sets=explode(':',$plugin);

				/* make plug config array for this field */
				if($sets[3]) $conf_str = explode(';',$sets[3]);
				if(is_array($conf_str))
				{
					foreach($conf_str as $conf_entry)
					{
						list($key,$val)=explode('=',$conf_entry);	
						$conf_arr[$key]=$val;		
					}
				}

				if (substr($input_name,3)==$sets[0])
				{
					if(!$input=@call_user_func('plg_fi_'.$sets[1],$input_name,$value,$conf_arr)) 
					{
						}
					}
				}
				if (!$input) $input=call_user_func('plg_fi_def_'.$type,$input_name,$value,'');

				return $input;

			}

			/**
			* include ALL plugins
			*/
			function include_plugins()
			{
				global $local_bo;
				$local_bo=$this;
				if ($handle = opendir(PHPGW_SERVER_ROOT.'/jinn/plugins')) {

					/* This is the correct way to loop over the directory. */

					while (false !== ($file = readdir($handle))) 
					{ 
						if (substr($file,0,7)=='plugin.')
						{

							include(PHPGW_SERVER_ROOT.'/jinn/plugins/'.$file);
						}
					}
					closedir($handle); 
				}
			}

			/**
			* get storage filter from plugin 
			*/
			function get_plugin_sf($key,$HTTP_POST_VARS,$HTTP_POST_FILES)
			{
				global $local_bo;
				$local_bo=$this;
				//			die(var_dump($this));
				$plugins=explode('|',$this->site_object['plugins']);

				foreach($plugins as $plugin)
				{
					$sets=explode(':',$plugin);

					/* make plug config array for this field */
					if($sets[3]) $conf_str = explode(';',$sets[3]);

					if(is_array($conf_str))
					{
						foreach($conf_str as $conf_entry)
						{
							list($conf_key,$val)=explode('=',$conf_entry);	
							$conf_arr[$conf_key]=$val;		
						}
					}

					//					echo $key."<P>";


					if (substr($key,3)==$sets[0])
					{
						if(!$data=@call_user_func('plg_sf_'.$sets[1],$key,$HTTP_POST_VARS,$HTTP_POST_FILES,$conf_arr)) return;
					}
				}
				return $data;
			}
		}
		?>
