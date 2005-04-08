<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Authors: Pim Snel <pim@lingewoud.nl>, 
			Lex Vogelaar <lex_vogelaar@users.sourceforge.net>
   Copyright (C)2002, 2003, 2004, 2005 Pim Snel <pim@lingewoud.nl>

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

   /* $Id$ */

   class bouser 
   {
	  var $public_functions = Array
	  (
		 'set_adv_filter'			=> True,
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
		 'copy_record'				=> True,
		 'scan_new_objects'			=> True
	  );

	  var $so;
	  var $session;
	  var $sessionmanager;

	  var $message;//depreciated

	  var $site_object_id; //depreciated
	  var $site_object; 
	  var $site_id; //depreciated
	  var $site; 
	  var $local_bo;
	  var $magick;

	  var $acl;

	  var $plug;
	  var $object_events_plugin_manager;

	  var $current_config;
	  var $action;
	  var $common;
	  var $browse_settings;
	  var $filter_settings;
	  
	  var $repeat_input;
	  var $where_key;
	  var $where_value;
	  var $where_string;
	  var $last_where_string;

	  var $mult_where_array;
	  var $mult_records_amount;

	  /* debugging vars set them in preferences */
	  var $debug_sql = false;
	  var $debug_session = false;
	  var $debug_site_arr =false;
	  var $debug_object_arr =false;



	  function bouser()
	  {
//_debug_array('bouser constructor start : ');
//_debug_array('   1');
		 $this->common = CreateObject('jinn.bocommon');
		 $this->session 		= &$this->common->session->sessionarray;	//shortcut to session array
		 $this->sessionmanager	= &$this->common->session;					//shortcut to session manager object
 		 $this->message 			= $this->session['message'];//depreciated
		 $this->site_id 			= $this->session['site_id'];//depreciated
		 $this->site_object_id		= $this->session['site_object_id'];//depreciated
		 $this->browse_settings		= $this->session['browse_settings'];//depreciated
		 $this->filter_settings		= $this->session['filter_settings'];//depreciated
		 $this->mult_where_array	= $this->session['mult_where_array'];//depreciated
		 $this->mult_records_amount = $this->session['mult_records_amount'];//depreciated
		 $this->last_where_string	= $this->session['last_where_string'];//depreciated

		 $this->current_config=$this->common->get_config();		

		 $this->so = CreateObject('jinn.sojinn');

//_debug_array('   2');
		 $this->acl = CreateObject('jinn.boacl');

//_debug_array('   3');
		 $this->magick = CreateObject('jinn.boimagemagick.inc.php');	

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

		 if ($this->site_id) $this->site = $this->so->get_site_values($this->site_id);
		 if ($this->site_object_id) $this->site_object = $this->so->get_object_values($this->site_object_id);
		 
		 $this->plug = CreateObject('jinn.plugins_db_fields');
		 $this->plug->local_bo = $this;

		 $this->object_events_plugin_manager = CreateObject('jinn.plugins_object_events'); //$this->include_plugins();
		 $this->object_events_plugin_manager->local_bo = $this;

		 /* this is for the sidebox */
		 global $local_bo;
		 $local_bo=$this;

		 /* do stuff for debugging */
		 if($GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			if($this->read_preferences('debug_sql')=='yes') $this->debug_sql=true;

			if($this->read_preferences('debug_site_arr')=='yes') 
			{
			   $this->message['debug'][]='SITE_ARRAY: '._debug_array($this->site,false);
			}
		
			if($this->read_preferences('debug_object_arr')=='yes')
			{
			   $this->message['debug'][]='OBJECT_ARRAY: '._debug_array($this->site_object,false);
			}
		 }

			//backwards compatibility: check if unique id field is filled. If not: fill it now.
		 if($this->site_object_id && $this->site_object[unique_id] == '')
		 {
			$status = $this->so->set_unique_id($this->site_object_id);
			$this->site_object[unique_id] = $status[uid];
		 }
		 
			//if user changes site, check if new objects need to be created for user generated tables
 		 if($_POST['site_id'])
		 {
			$this->scan_new_objects_silent();
		 }
// _debug_array('bouser constructor end');
//_debug_array($this->mult_where_array);
	  }

	  
	  /* 
	  @function field_is_enabled
	  @abstract this function asserts that the DisableField plugin is not in use on this field
	  */
	  function field_is_enabled($objectID, $fieldname)
	  {
		$field_conf_arr=$this->so->get_field_values($objectID,$fieldname);
		if($this->site_object[plugins])
		{
		   $testvalue=$this->get_plugin_bv($fieldname,'x','',$fieldname);
		}
		else
		{
		   $testvalue=$this->plug->call_plugin_bv($fieldname,'x','',$field_conf_arr);
		}

		return ($testvalue != '__disabled__');
		
	  }
	  
		//todo: these functions must be moved to sojinn
	  function cur_upload_path()
	  {
		return $this->cur_upload('path');
	  }
	  
	  function cur_upload_url()
	  {
		return $this->cur_upload('url');
	  }

	  function cur_upload($path_or_url)
	  {
		$path_or_url = 'cur_upload_'.$path_or_url;
		if($this->site_object[$path_or_url] == '')
		{
			if($this->site[$path_or_url] == '')
			{
				return '' ; //FIXME
			}
			else
			{
				return $this->site[$path_or_url];
			}
		}
		else
		{
			return $this->site_object[$path_or_url];
		}
	  }
	  
		function read_session_filter($obj_id)
		{
			return $this->filter_settings[$obj_id];
		}

		function save_session_filter($obj_id, $data)
		{
			$this->filter_settings[$obj_id] = $data;
		}

		
	  function save_sessiondata()
	  {
 		$this->session['message'] = $this->message;							//depreciated
		$this->session['site_id'] = $this->site_id;							//depreciated
	 	$this->session['site_object_id'] = $this->site_object_id;			//depreciated
		$this->session['browse_settings'] = $this->browse_settings;			//depreciated
		$this->session['filter_settings'] = $this->filter_settings;			//depreciated
		$this->session['mult_where_array'] = $this->mult_where_array;		//depreciated
		$this->session['mult_records_amount'] = $this->mult_records_amount;	//depreciated
		$this->session['last_where_string'] = $this->last_where_string;		//depreciated
		
		$this->sessionmanager->save();
	  }

	  /**
	  @function records_per_page
	  @abstract get number of records per page from user preferences
	  @description first get object defines prefs and if there none 
	  get general user defined and if none set to 10
	  @todo implement object specific preferences
	  **/
	  function records_per_page()
	  {
		 $pref_default_record_num=intval($this->read_preferences('default_record_num'));

		 if(is_int($pref_default_record_num) 
		 && $pref_default_record_num > 0)
		 {
			return $pref_default_record_num;
		 }
		 else
		 {
			return 20;
		 }

	  }

	  /**
	  @abstract get_offset
	  @abstract simple calculation to get sql-offset from current page and record per page
	  */
	  function get_offset($current_page,$rec_per_page)
	  {
		 // (1 - 1) * 8 = 0 -> LIMIT 0,8
		 // (2 - 1) * 8 = 8 -> LIMIT 8,8
		 $offset = ($current_page-1)*$rec_per_page;

		 return $offset;	
	  }


	  function mult_change_num_records()
	  {
		 if(is_numeric)	$this->mult_records_amount=intval($_POST['num_records']);

		 $this->save_sessiondata();

		 $this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries&insert=yes');
	  }

	  //remove this one
	  function get_records($table,$where_key,$where_value,$offset,$range,$value_reference,$order_by='',$field_list='*',$where_condition='')
	  {
		 if (!$value_reference)
		 {
			$value_reference='num';
		 }

		 $records = $this->so->get_record_values($this->site_id,$table,$where_key,$where_value,$offset,$range,$value_reference,$order_by,$field_list,$where_condition);


		 return $records;
	  }

	  function record_insert()
	  {
		 $data = $this->remove_helper_fields($this->http_vars_pairs($_POST, $_FILES));
		 $status=$this->so->insert_object_data($this->site_id,$this->site_object[table_name],$data);
		 $m2m_data=$this->http_vars_pairs_m2m($_POST);
		 $m2m_data['FLDXXX'.$status['idfield']]=$status['id'];
		 $status_relations=$this->so->update_object_many_data($this->site_id, $m2m_data);

		 if ($status[ret_code])	
		 {
			$this->message['error']=lang('Record NOT succesfully saved. Unknown error');
			$this->message['error_code']=111;
		 }
		 else
		 {
		 $this->message['info'][]='Record successfully added';
		 }

		 if($this->debug_sql==true)
		 {
			$this->message['debug'][]='SQL: '.$status[sql];
		 }

		 $this->save_sessiondata();

		 if($_POST['reopen'] && $status[where_string])
		 {
			$this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form&where_string='.base64_encode($status[where_string]));
		 }
		 elseif($_POST['add_new'])
		 {
			$this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form');
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		 }
	  }


	  /* 
	  @function mult_to_fld
	  @abstract when multiple records are updated this function translates all fields 
	  to single records so the records can be processed normally.
	  @note  If a plugin developer wants to use a prefix it must be exactly charachter long
	  */
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
			   /* special plugin fields */
			   /* If a plugin user wants to use a prefix it must be exactly charachter long  */ 
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
			case 'view':
			$this->mult_where_array=$this->set_multiple_where();
			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiu_edit_record.view_multiple_records');
			break;
			case 'export':
			$this->mult_where_array=$this->set_multiple_where();
			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiu_export.export');
			break;
			default:
			$this->message[error]=lang('Operation on multiple records failed.');
			$this->message[error_code]=100;
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

			   $data=$this->remove_helper_fields($this->http_vars_pairs($post_arr,$files_arr));
			   $status=$this->so->insert_object_data($this->site_id,$this->site_object[table_name],$data);
			   if($this->debug_sql==true)
			   {
				  $this->message['debug'][]='SQL: '.$status[sql];
			   }

			   $this->mult_where_array[]=$status[where_string];
			   $m2m_data=$this->http_vars_pairs_m2m($post_arr);
			   $m2m_data['FLDXXX'.$status['idfield']]=$status['id'];
			   $status_relations=$this->so->update_object_many_data($this->site_id, $m2m_data);
			}
		 }

		 if ($status[ret_code]==0)
		 {
			$this->message['info']='Records successfully added';
		 }
		 else 
		 {
			$this->message[error]=lang('One or more records NOT succesfully added.');
			$this->message[error_code]=107;
		 }


		 $this->save_sessiondata();

		 if($_POST['continue'] && is_array($this->mult_where_array) )
		 {

			$this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries'); //mult_where_string
		 }
		 elseif($_POST['add_new'])
		 {
			unset($this->mult_where_array);
			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries');
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		 }
	  }


	  function remove_helper_fields($data)
	  {
			//removes helper form fields created by plugins
		foreach($this->plug->plugins as $plugin)
		{
			foreach($data as $key => $field)
			{
				if(strpos($field[name], $plugin['helper_fields_substring']) === false)
				{
				}
				else
				{
					unset($data[$key]);
				}
			}
		}
		return $data;
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
			   $data = $this->remove_helper_fields($this->http_vars_pairs($post_arr,$files_arr));
			   
			   $where_string=base64_decode($_POST['MLTWHR'.sprintf("%02d",$i)]);
			   $this->mult_where_array[]=$where_string;

			   $table=$this->site_object[table_name];

			   $m2m_data=$this->http_vars_pairs_m2m($post_arr);

			   $status=$this->so->update_object_many_data($this->site_id, $m2m_data);

			   $status=$this->so->update_object_data($this->site_id, $table, $data, $where_key,$where_value,$where_string);
			   $eventstatus = $this->run_event_plugins('on_update', $post_arr);

			}
		 }

		 if ($status[status]==1)	
		 {
			$this->message['info']='Records successfully saved';
			if($eventstatus) $this->message[info].=', but error in event plugin';
		 }
		 else 
		 {
			$this->message[error]=lang('One or more records NOT succesfully saved.');
			$this->message[error_code]=106;
		 }

		 $this->save_sessiondata();

		 if($_POST['continue'] && is_array($this->mult_where_array) )
		 {

			$this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries'); //mult_where_string
		 }
		 elseif($_POST['add_new'])
		 {
			unset($this->mult_where_array);
			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiu_edit_record.multiple_entries');
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
		 else 
		 {
			$this->message[error]=lang('Records NOT succesfully deleted.');
			$this->message[error_code]=101;
		 }

		 $this->save_sessiondata();
		 $this->common->exit_and_open_screen('jinn.uiuser.index');
	  }


	  function run_event_plugins($event, $post)
	  {
		 //get all events plugins configured to this object
		 $object_arr=$this->so->get_object_values($this->site_object[object_id]);
		 $stored_configs = unserialize(base64_decode($object_arr[events_config]));
		 //_debug_array($stored_configs);
		 if(is_array($stored_configs))
		 {
			foreach($stored_configs as $config)
			{
			   if($event == $config[conf][event])
			   {
				  //_debug_array("valid configuration found");
				  /*run_event_plugins roept uit de event_plugin de functie event_action_[plg_naam]() aan 
				  met als argumenten de _POST array en de plugin configuratie. 
				  Deze functie geeft alleen een status terug dus geen waarde om weer verder te gebruiken. 
				  De functie gebruikt de config_data en de post_data om iets speciaals te doen.*/

				  $status = $this->object_events_plugin_manager->call_event_action($post, $config);
			   }
			}
		 }
	  }

	  function record_update()
	  {

		 if($_POST['delete'])
		 {
			$this->del_record();
		 }

		 $where_key = $this->where_key;
		 $where_value = $this->where_value;
		 $where_string=$this->where_string;
		 $table=$this->site_object[table_name];

		 $m2m_data=$this->http_vars_pairs_m2m($_POST);
		 $status[o2o]=$this->o2o_update();

		 $status=$this->so->update_object_many_data($this->site_id, $m2m_data);
//		 $data=$this->http_vars_pairs($_POST, $_FILES);
		 $data = $this->remove_helper_fields($this->http_vars_pairs($_POST, $_FILES));

		 $status=$this->so->update_object_data($this->site_id, $table, $data, $where_key,$where_value,$where_string);

		 if ($status[ret_code])
		 {
			$this->addtoErrorArr(lang('Record NOT succesfully saved'),104);
		 }
		 else 
		 {
			$this->message[info]='Record succesfully saved';
			$eventstatus = $this->run_event_plugins('on_update', $_POST);
			if($eventstatus) $this->message[info].=', but error in On Update event plugin';
		 }

		 $this->addtoDebugArr('SQL: '.$status[sql]);

		 $this->save_sessiondata();

		 if($_POST['continue'] || $_POST['reopen'])
		 {
			$this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form&where_string='.base64_encode($status[where_string]));
		 }
		 elseif($_POST['add_new'])
		 {
			$this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form');
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		 }
	  }

		/* fixme move to common */
	  function addtoErrorArr($msg,$error_code)
	  {
		 $this->message[error][]=$msg;
		 $this->message[error_code][]=$error_code;
	  }


		/* fixme move to common */
	  function addtoDebugArr($msg)
	  {
		 if($this->debug_sql==true)
		 {
			$this->message['debug'][]='SQL: '.$msg;
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
		 else 			
		 {
			$this->message[error]=lang('Record NOT succesfully deleted.');
			$this->message[error_code]=105;
		 }

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
			if ($status[ret_code])
			{
			   $this->addtoErrorArr(lang('Record NOT succesfully copied'),102);
			}
			else
			{
			   $this->message[info]=lang('Record succesfully copied');
			}
			$this->addtoDebugArr('SQL: '.$status[sql]);


			if($status[where_string])
			{
			   $this->save_sessiondata();
			   $this->common->exit_and_open_screen('jinn.uiu_edit_record.display_form&where_string='.base64_encode($status[where_string]));
			}
		 }
		 else
		 {
			// FIXME disable copy icon when its not possible
			$this->addtoErrorArr(lang('Cannot copy a record from this table.'),103);
		 }

		 $this->save_sessiondata();
		 $this->common->exit_and_open_screen('jinn.uiuser.index');
	  }

	  function get_data($columns_arr, $filter_where)
	  {
			//new function for fast and generic retrieval of object data, including 1-1, 1-many and many-many relations
			//partly implemented in bouser, partly in sojinn
			
		$site_id = $this->site_id;
		$table_name = $this->site_object['table_name'];

			//get 1-many relations and replace straight columns with relation definitions
		$relation_array = $this->extract_O2M_relations($this->site_object['relations']);
		foreach($columns_arr as $key => $column)
		{
			if(is_array($relation_array[$column]))
			{
				$columns_arr[$key] = $relation_array[$column];
			}
		}

		$relation_array = $this->extract_M2M_relations($this->site_object['relations']);
		if(is_array($relation_array))
		{
			foreach(array_values($relation_array) as $key => $relation)
			{
				$relation[name] = 'relation_'.($key+1);
				$columns_arr[] = $relation;
			}
		}
		return $this->so->get_data($site_id, $table_name, $columns_arr, $filter_where);

	  }

	  // one-to-one relations
	  function extract_O2O_relations($string)
	  {
		 $relations_arr = unserialize(base64_decode($string));

		if(is_array($relations_arr) && $relations_arr)
		{
			 foreach($relations_arr as $relation)
			 {
				if ($relation[type]=='3')
				{
					$O2O_relations[$relation[org_field]]=$relation;
				}
			 }
			 return $O2O_relations;
		}
		else //old format compatibility
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
					  'org_field'=>$relation_part[1],
					  'related_with'=>$relation_part[3],
					  'object_conf'=>$relation_part[4]
				   );
				}
	
			 }
			 return $relation_arr;
		}
	  }

	  // one-to-many relations
	  function extract_O2M_relations($string)
	  {
		 $relations_arr = unserialize(base64_decode($string));

		if(is_array($relations_arr) && $relations_arr)
		{
			 foreach($relations_arr as $relation)
			 {
				if ($relation[type]=='1')
				{
					$O2M_relations[$relation[org_field]]=$relation;
				}
			 }
			 return $O2M_relations;
		}
		else //old format compatibility
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
					  'org_field'=>$relation_part[1],
					  'related_with'=>$relation_part[3],
					  'display_field'=>$relation_part[4]
				   );
				}
	
			 }
			 return $relation_arr;
		}
	  }


	  // many-to-many relations
	  function extract_M2M_relations($string)
	  {
		 $relations_arr = unserialize(base64_decode($string));

		if(is_array($relations_arr) && $relations_arr)
		{
			 $i=0;
			 foreach($relations_arr as $relation)
			 {
				if ($relation[type]=='2')
				{
				   $tmp=explode('.',$relation[via_primary_key]);
				   $relation[via_table]=$tmp[0];
				   $tmp=explode('.',$relation[display_field]);
				   $relation[display_table]=$tmp[0];
				   
					$M2M_relations[$i]=$relation;
				}
				$i++;
			 }
			 return $M2M_relations;
		}
		else //old format compatibility
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
	  }



	  function get_related_field($relation_array)
	  {
		 $table_info=explode('.',$relation_array[related_with]);
		 $table=$table_info[0];
		 $related_field=$table_info[1];

		 $table_info_DF1=explode('.',$relation_array[display_field]);
		 $table_display=$table_info_DF1[0];
		 $display_field=$table_info_DF1[1];

 		 $table_info_DF2=explode('.',$relation_array[display_field_2]);
		 $display_field_2=$table_info_DF2[1];

  		 $table_info_DF3=explode('.',$relation_array[display_field_3]);
		 $display_field_3=$table_info_DF3[1];

		 $allrecords=$this->get_records($table,'','','','','name',$display_field);

		 if(is_array($allrecords))
		 {
			 foreach ($allrecords as $record)
			 {
			   $displaystring = $record[$display_field];
			   if($display_field_2!='') $displaystring .= ' '.$record[$display_field_2];
			   if($display_field_3!='') $displaystring .= ' '.$record[$display_field_3];

			   $related_fields[]=array
				(
				   'value'=>$record[$related_field],
				   'name'=>$displaystring
				);
			 }
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
				  //_debug_array($field_values);
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




	  function http_vars_pairs_m2m($HTTP_POST_VARS) 
	  {
		 while(list($key, $val) = each($HTTP_POST_VARS)) 
		 {
			if(substr($key,0,3)=='M2M' || substr($key,0,8)=='FLDXXXid')
			{
			   $data = array_merge($data,array($key=> $val));
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
		 $prefs_show_hide_read=$this->read_preferences('show_fields'.$this->site_object[unique_id]);

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

		 $this->save_preferences('show_fields'.$this->site_object[unique_id],$prefs_show_hide_new);
		 $this->save_preferences('default_order'.$this->site_object[unique_id],$prefs_order_new);
			//the browse settings overrule the preferences, so kill them. Otherwise we will not see any results until we chamge the Object and return
		 unset($this->browse_settings['orderby']);
		 $this->save_sessiondata();

		 $this->common->exit_and_open_screen('jinn.uiu_list_records.display');
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
			if($sets[3]) 
			{
			   $conf_str = explode(';',$sets[3]);
			}
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
			   $new_value=@call_user_func('plg_ro_'.$sets[1],$value,$conf_arr,$where_val_en);
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

		function set_adv_filter()
		{
		   $this->browse_settings[adv_filter_str]=$_POST[adv_filter];  
		   $this->save_sessiondata();
		   $this->common->exit_and_open_screen('jinn.uiu_list_records.display');
		}

		/*! 
		@function scan_new_objects_silent
		@abstract check if users have created new tables that need to become accessible as objects
		*/
		function scan_new_objects_silent()
		{
			$status = array();
			if($this->site[object_scan_prefix] != '')
			{
				$prefix_arr = explode(',', $this->site[object_scan_prefix]);
				if(is_array($prefix_arr))
				{
					$tables = $this->so->site_tables_names($this->site_id);
					$status = array();
					foreach($tables as $table)
					{
						//is this table wrapped by an object?
						$objects = $this->so->get_objects_by_table($table[table_name], $this->site_id);
						if(count($objects) == 0)
						{
							//if no, do we want ALL tables wrapped by an object?
							if($prefix_arr[0] == '*')
							{
								//if yes, create an object from this table
								$status[] = $this->save_scanned_object($this->site_id, $table[table_name]);
							}
							//or does the table name start with one of the prefixes?
							else
							{
								foreach($prefix_arr as $prefix)
								{
									if(substr($table[table_name], 0, strlen($prefix)) == $prefix)
									{
										$status[] = $this->save_scanned_object($this->site_id, $table[table_name]);
									}
								}
							}
						}
					}
				}
			}
			return $status;
		}
		/*! 
		@function scan_new_objects 
		@abstract scan for new objects, create messages and redirect to index
		*/
		function scan_new_objects()
		{
			$status = $this->scan_new_objects_silent();
			$endl = "<br>";
			if(count($status) > 0)
			{
				$this->message[info]=lang('%1 new objects where successfully created%2', count($status), $endl);
				foreach($status as $new)
				{
					if($new[ret_code] != 0)
					{
						$this->message[error]=lang('Error creating one or more new Objects%1', $endl);
						$this->message[info]=lang('%1 new objects where successfully created%2', count($status), $endl);
					}
				}
			}
			else
			{
				$this->message[info]=lang('no new objects created%1', $endl);
			}
			$this->save_sessiondata();
			$this->common->exit_and_open_screen('jinn.uiuser.index');
		}
		
		/*! 
		@function save_scanned_object
		@abstract wraps an (empty) site object around a given table
		*/
		function save_scanned_object($site_id, $table_name)
		{
			$data = array();
			$data[] = array('name' => 'name'			, 'value' => $table_name);
			$data[] = array('name' => 'table_name'		, 'value' => $table_name);
			$data[] = array('name' => 'parent_site_id'	, 'value' => $site_id	);
			$data[] = array('name' => 'hide_from_menu'	, 'value' => ''			);
			$data[] = array('name' => 'serialnumber'	, 'value' => ''			);
			$data[] = array('name' => 'unique_id'		, 'value' => ''			);
			return $this->so->insert_new_object($data);
		}
   }
?>
