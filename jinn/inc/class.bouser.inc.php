<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; Version 2 of the License.

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
		 'record_update'			=> True,
		 'record_insert'			=> True,
		 'multiple_records_insert'	=> True,
		 'multiple_records_update'	=> True,
		 'del_record'				=> True,
		 'save_object_config'		=> True,
		 'multiple_actions'			=> True,
		 'get_plugin_afa'			=> True,
		 'mult_change_num_records'	=> True,
		 'submit_to_plugin_afa'		=> True,
		 'copy_record'				=> True
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

	  var $plug;

	  var $current_config;
	  var $action;
	  var $common;
	  var $browse_settings;

	  var $repeat_input;
	  var $where_key;
	  var $where_value;
	  var $where_string;
	  var $last_where_string;

	  var $mult_where_array;
	  var $mult_records_amount;

	  function bouser()
	  {
		 $this->common = CreateObject('jinn.bocommon');
		 $this->current_config=$this->common->get_config();		

		 $this->so = CreateObject('jinn.sojinn');

		 $this->magick = CreateObject('jinn.boimagemagick.inc.php');	

		 $this->read_sessiondata();

		 $_form = $_POST['form'];
		 $_site_id = $_POST['site_id'];
		 $_site_object_id = $_POST['site_object_id'];

		 list($_where_string,$_where_key,$_where_value,$_repeat_input)=$this->common->get_global_vars(array('where_string','where_key','where_value','repeat_input'));

		 if(!empty($_repeat_input)) $this->repeat_input  = $_repeat_input;

		 if(!empty($_where_key))	$this->where_key  = $_where_key;

		 if(!empty($_where_value)) $this->where_value  = $_where_value;

		 if(!empty($_where_string)) 
		 {
			$this->where_string  = base64_decode($_where_string);
			$this->where_string_encoded  = $_where_string;
			$this->last_where_string = $this->where_string_encoded;
		 }

		 if (($_form=='main_menu')) 
		 {
			$this->site_id  = $_site_id;
		 }
		 
		 if (($_form=='main_menu') || !empty($site_object_id)) $this->site_object_id  = $_site_object_id;

		 if ($this->site_id) $this->site = $this->so->get_site_values($this->site_id);
		 if ($this->site_object_id) $this->site_object = $this->so->get_object_values($this->site_object_id);
		 $this->plug = CreateObject('jinn.plugins');
		 $this->plug->local_bo = $this;

		 /* this is for the sidebox */
		 global $local_bo;
		 $local_bo=$this;
	  }

	  
	  function save_sessiondata()
	  {
		 $data = array(
			'message' => $this->message, 
			'site_id' => $this->site_id,
			'site_object_id' => $this->site_object_id,
			'browse_settings'=>	$this->browse_settings,
			'mult_where_array'=> $this->mult_where_array,
			'mult_records_amount'=>$this->mult_records_amount,
			'last_where_string'=>$this->last_where_string
		 );

		 $GLOBALS['phpgw']->session->appsession('session_data','jinn',$data);
	  }

	  /* 
	  @function read_sessiondata
	  @abstract read sessiondata from and fill class vars
	  @note test menu
	  */
	  function read_sessiondata()
	  {
		 $data = $GLOBALS['phpgw']->session->appsession('session_data','jinn');
		 if ($GLOBALS['HTTP_POST_VARS']['form']!='main_menu')
		 {
			$this->message 		= $data['message'];
			$this->site_id 		= $data['site_id'];
			$this->site_object_id	= $data['site_object_id'];
			$this->browse_settings	= $data['browse_settings'];
			$this->mult_where_array	= $data['mult_where_array'];
			$this->mult_records_amount = $data['mult_records_amount'];
			$this->last_where_string = $data['last_where_string'];
		 }
		 if($GLOBALS['HTTP_POST_VARS']['form']=='main_menu')
		 {
			if($data['site_id'] && $_POST['site_id']!=$data['site_id'])
			{
			   unset($_POST[site_object_id]);
			   unset($data[site_object_id]);
			   unset($this->site_object_id);
			}
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
	  
	  function mult_change_num_records()
	  {
		 if(is_numeric)	$this->mult_records_amount=intval($_POST['num_records']);

		 $this->save_sessiondata();

		 $this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries&insert=yes');
	 }

	  //remove this one
	  function get_records($table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by='',$field_list='*',$where_condition='')
	  {
		 if (!$value_reference)
		 {
			$value_reference='num';
		 }

		 $records = $this->so->get_record_values($this->site_id,$table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by,$field_list,$where_condition);


		 return $records;
	  }

	  function record_insert()
	  {
		 $data=$this->http_vars_pairs($_POST,$_FILES);
		 $status=$this->so->insert_object_data($this->site_id,$this->site_object[table_name],$data);

		 $m2m_data=$this->http_vars_pairs_m2m($_POST);
		 $m2m_data['FLDXXX'.$status['idfield']]=$status['id'];
		 $status_relations=$this->so->update_object_many_data($this->site_id, $m2m_data);

		 if ($status[status]==1)	$this->message['info']='Record successfully added';
		 else $this->message[error]=lang('Record NOT succesfully deleted. Unknown error');

		 $this->save_sessiondata();

		 if($_POST['continue'] && $status[where_string])
		 {
			$this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form&where_string='.base64_encode($status[where_string]));
		 }
		 else
		 {
			if($_POST[repeat_input]=='true')
			{
			   $this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form&repeat_input=true');
			}
			else
			{
			   $this->common->exit_and_open_screen('jinn.uiuser.index');
			}
		 }
	  }


	  function mult_to_fld($i,$type='_POST')
	  {
		 if($type=='_POST')
		 {
			reset($_POST);
			while (list($key, $val) = each($_POST))
			{
				// normal fields
			   if (substr($key,0,4)=='MLTX' && intval(substr($key,4,2)) == $i) 
			   {
				  $post_arr['FLDXXX'.substr($key,6)]=$val;
			   }
			   // special plugin fields
			   elseif (substr($key,7,4)=='MLTX' && intval(substr($key,11,2)) == $i) 
			   {
				  $post_arr[substr($key,0,7).'FLDXXX'.substr($key,13)]=$val;
			   }
			   // m2m relation fields
			   elseif(substr($key,0,3)=='M2M' && intval(substr($key,4,2)) == $i)
			   {
				  $post_arr['M2M'.substr($key,3,1).'XX'.substr($key,6)]=$val;
			   }
			
			}
		 }
		 else
		 {
			reset($_FILES);
			while (list($key, $val) = each($_FILES))
			{
			   if (substr($key,0,4)=='MLTX' && intval(substr($key,4,2)) == $i) 
			   {
				  $post_arr['FLDXXX'.substr($key,6)]=$val;
			   }
			   elseif (substr($key,7,4)=='MLTX' && intval(substr($key,11,2)) == $i) 
			   {
				  $post_arr[substr($key,0,7).'FLDXXX'.substr($key,13)]=$val;
			   }
			}

		 }
		 return $post_arr;
	  }

	  function multiple_actions()
	  {
		 switch($_POST['action'])
		 {
			case 'del':
			   $where_arr=$this->set_multiple_where();
			   $this->multiple_records_delete($where_arr);
			   break;

			case 'edit':
			   $this->mult_where_array=$this->set_multiple_where();
			   $this->save_sessiondata();
			   $this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries');

			   break;

			default:
			   $this->message[error]=lang('Operation on multiple records failed. (error code 100)');
			   $this->save_sessiondata();
			   $this->common->exit_and_open_screen('jinn.uiuser.index');
			}
		 }

		 function set_multiple_where()
		 {
			reset($_POST);
			while (list($key, $val) = each($_POST))
			{
			   if(substr($key,0,3)=='SEL')
			   {
				  $where_arr[]=base64_decode($val);
			   }
			}
			return $where_arr;
		 }

		 function multiple_records_insert()
		 {
			unset($this->mult_where_array);
			if(is_numeric($_POST[MLTNUM]) and intval($_POST[MLTNUM])>0)
			{
			   for($i=0;$i<$_POST[MLTNUM];$i++)
			   {
				  $post_arr=$this->mult_to_fld($i,'_POST');
				  $files_arr=$this->mult_to_fld($i,'_FILES');

				  $data=$this->http_vars_pairs($post_arr,$files_arr);
				  $status=$this->so->insert_object_data($this->site_id,$this->site_object[table_name],$data);
				  
			//	  _debug_array($status);
				  $this->mult_where_array[]=$status[where_string];
				  $m2m_data=$this->http_vars_pairs_m2m($post_arr);
				  $m2m_data['FLDXXX'.$status['idfield']]=$status['id'];
				  $status_relations=$this->so->update_object_many_data($this->site_id, $m2m_data);
			   }
			}
			
//die();
			if ($status[status]==1)	$this->message['info']='Records successfully added';
			else $this->message[error]=lang('One or more records NOT succesfully added. (error code 107)');

			$this->save_sessiondata();

			if($_POST['continue'] && is_array($this->mult_where_array) )
			{

			   $this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries'); //mult_where_string
			}
			/*	 if($_POST['continue'] && $status[where_string])
			{
			   $this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form&where_string='.base64_encode($status[where_string]));//mult_where_string
			}
			*/
			else
			{
			   $this->common->exit_and_open_screen('jinn.uiuser.index');
			}
		 }


		 function multiple_records_update()
		 {
			/* exit and go to del function */
			if($_POST['delete'])
			{
			   $this->multiple_records_delete($this->mult_where_array);
			}
			unset($this->mult_where_array);

			if(is_numeric($_POST[MLTNUM]) and intval($_POST[MLTNUM])>0)
			{
			   for($i=0;$i<$_POST[MLTNUM];$i++)
			   {
				  $post_arr=$this->mult_to_fld($i,'_POST');
				  $files_arr=$this->mult_to_fld($i,'_FILES');

				  $data=$this->http_vars_pairs($post_arr,$files_arr);

				  $where_string=base64_decode($_POST['MLTWHR'.sprintf("%02d",$i)]);
				  $this->mult_where_array[]=$where_string;

				  $table=$this->site_object[table_name];

				  $m2m_data=$this->http_vars_pairs_m2m($post_arr);

				  $status=$this->so->update_object_many_data($this->site_id, $m2m_data);

				  $data=$this->http_vars_pairs($post_arr, $files_arr);
					
				 // _debug_array($data);
				// die();
				  $status=$this->so->update_object_data($this->site_id, $table, $data, $where_key,$where_value,$where_string);
			   }
			}

			if ($status[status]==1)	$this->message['info']='Records successfully saved';
			else $this->message[error]=lang('One or more records NOT succesfully saved. (error code 106)');

			$this->save_sessiondata();

			if($_POST['continue'] && is_array($this->mult_where_array) )
			{

			   $this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries'); //mult_where_string
			}
			else
			{
			   $this->common->exit_and_open_screen('jinn.uiuser.index');
			}
		 }


		 function multiple_records_delete($where_arr)
		 {
			$status=1;
			foreach ($where_arr as $where_string)
			{
			   $where_string =stripslashes($where_string);

			   $stat=$this->so->delete_object_data($this->site_id, $this->site_object['table_name'], false, false,$where_string);
			   if($stat!=1) $status=0;
			}

			if ($status==1) $this->message[info]=lang('Records succesfully deleted');
			else $this->message[error]=lang('Records NOT succesfully deleted. (error code 101)');

			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		 }


		 function record_update()
		 {
			/* exit and go to del function */
			if($_POST['delete'])
			{
			   $this->del_record();
			}

			$where_key = $this->where_key;
			$where_value = $this->where_value;
			$where_string=$this->where_string;
			$table=$this->site_object[table_name];

			$m2m_data=$this->http_vars_pairs_m2m($_POST);
//			_debug_array($m2m_data);
			$status[o2o]=$this->o2o_update();

			$status=$this->so->update_object_many_data($this->site_id, $m2m_data);
			$data=$this->http_vars_pairs($_POST, $_FILES);

			$status=$this->so->update_object_data($this->site_id, $table, $data, $where_key,$where_value,$where_string);

			if ($status[status]==1)	$this->message[info]='Record succesfully saved';
			else $this->message[error]='Record NOT succesfully saved (error code 104)';

			$this->save_sessiondata();

			if($_POST['continue'])
			{
			   $this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form&where_string='.base64_encode($status[where_string]));
			}
			else
			{
			   $this->common->exit_and_open_screen('jinn.uiuser.index');
			}
		 }
		
		 function o2o_update()
		 {		 
			$o2o_data=$this->http_vars_pairs_o2o($_POST, $_FILES);

			if(is_array($o2o_data))
			{
			   // FIXME implement m2m relations for o2o related objects
			   foreach($o2o_data as $o2o_entry)
			   {
				  if($o2o_entry[meta][O2OW])
				  {

					 //_debug_array($o2o_data);
					 //die();
					 // update
					 $status=$this->so->update_object_data($this->site_id, $o2o_entry[meta][O2OT], $o2o_entry[data], '','',$this->so->strip_magic_quotes_gpc($o2o_entry[meta][O2OW]));	   
				  }
				  else
				  {
					 // insert
					 $status=$this->so->insert_object_data($this->site_id,$o2o_entry[meta][O2OT],$o2o_entry[data]);
				  }			   
			   }
			}
			return $status;
		 }



		 function del_record() 
		 {
			$table=$this->site_object[table_name];
			$where_key=stripslashes($this->where_key);
			$where_value=stripslashes($this->where_value);
			$where_string=stripslashes($this->where_string);

			$status=$this->so->delete_object_data($this->site_id, $table, $where_key,$where_value,$where_string);

			if ($status==1)	$this->message[info]=lang('Record succesfully deleted');
			else $this->message[error]=lang('Record NOT succesfully deleted. (error code 105)');

			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		 }


		 function copy_record()
		 {
			// check if id is autoincrementing
			$autokey= $this->so->check_auto_incr($this->site_id,$this->site_object['table_name']);
			if($autokey)
			{
			   $status=$this->so->copy_record($this->site_id,$this->site_object[table_name],$this->where_string,$autokey);
			   if ($status[status]==1)	$this->message[info]=lang('Record succesfully copied');
			   else $this->message[error]=lang('Record NOT succesfully copied. (error code 102)');

			   if($status[where_string])
			   {
				  $this->save_sessiondata();
				  $this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form&where_string='.base64_encode($status[where_string]));
			   }
			}
			else
			{
			   // disable copy icon when its not possible
			   $this->message[error]=lang('Cannot copy a record from this table. (error code 103)');
			}

			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		 }


		 // one-to-one relations
		 function extract_O2O_relations($string)
		 {
			$relations_array = explode('|',$string);

			foreach($relations_array as $relation)
			{
			   $relation_part=explode(':',$relation);
			   if ($relation_part[0]=='3')
			   {
				  $relation_arr[$relation_part[1]] = array
				  (
					 'type'=>$relation_part[0],
					 'field_org'=>$relation_part[1],
					 'related_with'=>$relation_part[3],
					 'object_conf'=>$relation_part[4]
				  );
			   }

			}
			return $relation_arr;
		 }


		 // one-to-many relations
		 function extract_O2M_relations($string)
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

		 // many-to-many relations
		 function extract_M2M_relations($string)
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

			$allrecords=$this->get_records($table,'','','','','name',$display_field);

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

		 function get_related_value($relation_array,$value)
		 {
			$table_info=explode('.',$relation_array[related_with]);
			$table=$table_info[0];
			$related_field=$table_info[1];

			$table_info2=explode('.',$relation_array[display_field]);
			$table_display=$table_info2[0];
			$display_field=$table_info2[1];

			$allrecords=$this->get_records($table,'','','','','name',$display_field);


			if(is_array($allrecords))
			foreach ($allrecords as $record)
			{
			   if($record[$related_field]==$value) return $record[$display_field];
			}
		 }

		 function http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES) 
		 {
			while(list($key, $val) = each($HTTP_POST_VARS)) 
			{
			   if(substr($key,0,6)=='FLDXXX')
			   {
				  // being backwards compatible, check for old method 
				  if($this->site_object['plugins'])
				  {
					 $filtered_data=$this->get_plugin_sf($key,$HTTP_POST_VARS,$HTTP_POST_FILES,$this->site_object['plugins']);
				  }
				  else
				  {
					 $field_values=$this->so->get_field_values($this->site_object[object_id],substr($key,6));
					 $filtered_data=$this->plug->call_plugin_sf($key,$field_values,$HTTP_POST_VARS,$HTTP_POST_FILES);
				  }
				  if ($filtered_data)				
				  {
					 if ($filtered_data==-1) $filtered_data='';
					 $data[] = array
					 (
						'name' => substr($key,6),
						'value' =>  $filtered_data  //addslashes($val)
					 );
				  }
				  else // if there's no plugin, just save the vals
				  {
					 $data[] = array
					 (
						'name' => substr($key,6),
						'value' => addslashes($val) 
					 );
				  }


			   }
			}


			return $data;

		 }

		 function http_vars_pairs_o2o($HTTP_POST_VARS,$HTTP_POST_FILES) 
		 {

			while(list($key, $val) = each($HTTP_POST_VARS)) 
			{
			   if(substr($key,0,4)=='O2OO')
			   {
				  $curr_object_arr=$this->so->get_object_values($val);
			   }
			   
			   if(substr($key,0,4)=='O2OW' || substr($key,0,4)=='O2OT' || substr($key,0,4)=='O2OO')
			   {
				  $idx=intval(substr($key,4,2));
				  $o2o_data_arr[$idx]['meta'][substr($key,0,4)]=$val;
			   }
			   elseif(substr($key,0,3)=='O2O')
			   {

				  // being backwards compatible, check for old method 
				  if($curr_object_arr[plugins])
				  {
					 $filtered_data=$this->get_plugin_sf($key,$HTTP_POST_VARS,$HTTP_POST_FILES,$curr_object_arr[plugins]);
					 // $filtered_data=$this->get_plugin_sf($key,$HTTP_POST_VARS,$HTTP_POST_FILES,$this->site_object['plugins']);
				  }
				  else
				  {
//					 echo ($curr_object_arr[plugins].substr($key,6));
					 $field_values=$this->so->get_field_values($curr_object_arr[object_id],substr($key,6));
					 $filtered_data=$this->plug->call_plugin_sf($key,$field_values,$HTTP_POST_VARS,$HTTP_POST_FILES);

				  }
				  
				  /* Check for plugin need and plugin availability */
				  if($filtered_data)				
				  {
					 if ($filtered_data==-1) $filtered_data='';
					 $data = array
					 (
						'name' => substr($key,6),
						'value' =>  $filtered_data  //addslashes($val)
					 );
				  }
				  else // if there's no plugin, just save the vals
				  {
					 $data = array
					 (
						'name' => substr($key,6),
						'value' => addslashes($val) 
					 );
				  }
				  $idx=intval(substr($key,4,2));
				  $o2o_data_arr[$idx]['data'][]=$data;

			   }

			}

			return $o2o_data_arr;
		 }



		 
		 function http_vars_pairs_m2m($HTTP_POST_VARS) {

			while(list($key, $val) = each($HTTP_POST_VARS)) {


			   if(substr($key,0,3)=='M2M' || substr($key,0,8)=='FLDXXXid')
			   {

				  $data = array_merge($data,array
				  (
					 $key=> $val
				  ));
			   }
			}
			return $data;
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

			$GLOBALS['phpgw']->preferences->change('jinn',$key,$prefs);
			$GLOBALS['phpgw']->preferences->save_repository(True);
		 }

		 /****************************************************************************\
		 * 	Config site_objects                                              *
		 \****************************************************************************/

		 function save_object_config()
		 {

			$prefs_order_new=$GLOBALS[HTTP_POST_VARS][ORDER];
			$prefs_show_hide_read=$this->read_preferences('show_fields');

			$show_fields_entry=$this->site_object[object_id];

			while(list($key, $x) = each($GLOBALS[HTTP_POST_VARS]))
			{
			   if(substr($key,0,4)=='SHOW')
			   $show_fields_entry.=','.substr($key,4);
			}

			if($prefs_show_hide_read) 
			{
			   $prefs_show_hide_arr=explode('|',$prefs_show_hide_read);

			   foreach($prefs_show_hide_arr as $pref_s_h)
			   {

				  $pref_array=explode(',',$pref_s_h);
				  if($pref_array[0]!=$this->site_object[object_id])
				  {
					 $prefs_show_hide_new.=implode(',',$pref_array);
				  }
			   }

			   if($prefs_show_hide_new) $prefs_show_hide_new.='|';
			   $prefs_show_hide_new.=$show_fields_entry;
			}
			else
			{
			   $prefs_show_hide_new=$show_fields_entry;
			}

			$this->save_preferences('show_fields',$prefs_show_hide_new);
			$this->save_preferences('default_order',$prefs_order_new);

			$this->common->exit_and_open_screen('jinn.uiuser.browse_objects');
		 }


		 /*--------------------------------------------------
		 FIXME all field related plugins must move to dedicated class
		 -------------------------------------------*

		 /**
		 * get storage filter from plugin 
		 */
		 function get_plugin_sf($key,$HTTP_POST_VARS,$HTTP_POST_FILES,$plugin_string=false)
		 {
			global $local_bo;

			$local_bo=$this;

			if(!$plugin_string)
			{
			   $plugin_string=$this->site_object['plugins'];
			}
			
			$plugins=explode('|',str_replace('~','=',$plugin_string));

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

			   if ( substr($key,-strlen($sets[0]))==$sets[0] )
			   {
				  
				  $data=@call_user_func('plg_sf_'.$sets[1],$key,$HTTP_POST_VARS,$HTTP_POST_FILES,$conf_arr);
				  if(!$data) return;
			   }
			}
			return $data;

		 }


		 /**
		 * get readonly view function from plugin 
		 */
		 function get_plugin_ro($fieldname,$value,$where_val_encoded,$attr)
		 {
			global $local_bo;
			$local_bo=$this;
			$plugins=explode('|',str_replace('~','=',$this->site_object['plugins']));
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

			   if ($fieldname==$sets[0])
			   {
				  if(!$new_value=@call_user_func('plg_ro_'.$sets[1],$value,$conf_arr,$where_val_encoded,$fieldname)) 
				  {
					 }
				  }
			   }
			   if (!$new_value)
			   {
				  $new_value=$value;
			   }

			   return $new_value;
			}


			/**
			* get browse view function from plugin 
			*/
			function get_plugin_bv($fieldname,$value,$where_val_encoded,$fieldname)
			{
			   global $local_bo;
			   $local_bo=$this;
			   $plugins=explode('|',str_replace('~','=',$this->site_object['plugins']));
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

				  if ($fieldname==$sets[0])
				  {
					 $new_value=@call_user_func('plg_bv_'.$sets[1],$value,$conf_arr,$where_val_encoded,$fieldname);
				  }
			   }

			   if (!$new_value)
			   {
				  $new_value=$value;
				  if(strlen($new_value)>15)
				  {
					 $new_value=strip_tags($new_value);
					 $new_value = substr($new_value,0,15). ' ...';
				  }
			   }
			   return $new_value;

			}

			/**
			* get input function from plugin 
			*/
			function get_plugin_fi($input_name,$value,$type,$attr_arr,$plugin_string=false)
			{
			   global $local_bo;
			   $local_bo=$this;

			   if(!$plugin_string)
			   {
				  $this->message['error'] = 'Warning: get_plugin_fi called with old behaviour';
				  $plugin_string = $this->site_object['plugins'];
			   }
			   
			   $plugins=explode('|',str_replace('~','=',$plugin_string));
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

				  // test for valid field-prefixes (MLTX##,FLDXXX,O2OX##)
				  if ( (substr($input_name,0,4)=='MLTX' && substr($input_name,6)==$sets[0]) || (substr($input_name,0,6)=='FLDXXX' && substr($input_name,6)==$sets[0]) || (substr($input_name,0,4)=='O2OX' && substr($input_name,6)==$sets[0]))
				  {
					 //FIXME all plugins must get an extra argument in the sf_func
					 $input=@call_user_func('plg_fi_'.$sets[1],$input_name,$value,$conf_arr,$attr_arr);
				  }
			   }

			   if (!$input) $input=call_user_func('plg_fi_def_'.$type,$input_name,$value,'',$attr_arr);

			   return $input;

			}

			/*!
			@function submit_to_plugin_afa
			@abstract wrapper for the autonome form action plugin caller which resides in the class plugins
			*/
			function submit_to_plugin_afa()
			{
			   if($this->site_object[plugins])
			   {
				  $this->get_plugin_afa();
			   }
			   else 
			   {
				  $field_values=$this->so->get_field_values($this->site_object[object_id],$_GET[field_name]);
				  $this->plug->call_plugin_afa($field_values);
			   }
			}
			
			/**
			@function get_plugin_afa
			@abstract get autonome form action function from plugin see visual ordering plugin how it works
			@note this function is here for backwards compatibility it will be removed someday
			@depreciated
			*/
			function get_plugin_afa()
			{
			   global $local_bo;
			   $local_bo=$this;

			   $action_plugin_name=$_GET[plg];
  
			   $plugins=explode('|',str_replace('~','=',$this->site_object['plugins']));
			   foreach($plugins as $plugin)
			   {	
				  $sets=explode(':',$plugin);

				  if($sets[3]) $conf_str = explode(';',$sets[3]);
				  if(is_array($conf_str))
				  {
					 unset($conf_arr);
					 foreach($conf_str as $conf_entry)
					 {
						list($key,$val)=explode('=',$conf_entry);	
						$conf_arr[$key]=$val;		
					 }
				  }

				  if ($action_plugin_name==$sets[1])
				  {
					 $call_plugin=$sets[1];
					 break;
				  }
			   }

			   if($call_plugin)
			   {
				  //FIXME all plugins must get an extra argument in the sf_func
				  $success=@call_user_func('plg_afa_'.$sets[1],$_GET[where],$_GET[attributes],$conf_arr);
			   }

			   if ($succes)
			   {
				  $this->message[info]=lang('Action was succesful.');

				  $this->save_sessiondata();
				  $this->common->exit_and_open_screen('jinn.uiuser.index');
			   }
			   else
			   {
				  $this->message[error]=lang('Action was not succesful. Unknown error');

				  $this->save_sessiondata();
				  $this->common->exit_and_open_screen('jinn.uiuser.index');
			   }
			}

			/**
			* include ALL plugins
			*/
/*			function include_plugins()
			{
			   global $local_bo;
			   $local_bo=$this;
			   if ($handle = opendir(PHPGW_SERVER_ROOT.'/jinn/plugins')) {

				  while (false !== ($file = readdir($handle))) 
				  { 
					 if (substr($file,0,7)=='plugin.')
					 {

						include_once(PHPGW_SERVER_ROOT.'/jinn/plugins/'.$file);
					 }
				  }
				  closedir($handle); 
			   }
			}
			*/

		 }
	  ?>
