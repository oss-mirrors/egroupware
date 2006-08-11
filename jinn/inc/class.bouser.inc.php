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

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.bojinn.inc.php');

   /**
   * bouser 
   * 
   * @uses bojinn
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class bouser extends bojinn
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
		 'mult_change_num_records'	=> True,
		 'copy_record'				=> True,
		 'scan_new_objects'			=> True,
		 'get_plugin_afa'			=> True,
		 'submit_to_plugin_afa'		=> True,
	  );

	  /**
	  * bouser 
	  * 
	  * @access public
	  * @return void
	  */
	  function bouser()
	  {
		 parent::bojinn();

		 /**
		 * fixme make a better structure for acl
		 */
		 $this->acl = CreateObject('jinn.boacl');

		 list($_where_string,$_where_key,$_where_value,$_repeat_input)=$this->get_global_vars(array('where_string','where_key','where_value','repeat_input'));

		 if(!empty($_repeat_input)) $this->repeat_input  = $_repeat_input;

		 if(!empty($_where_key))	$this->where_key  = $_where_key;

		 if(!empty($_where_value)) $this->where_value  = $_where_value;

		 if(!empty($_where_string)) 
		 {
			$this->where_string  = base64_decode($_where_string);
			$this->where_string_encoded  = $_where_string;
			$this->session['last_where_string'] = $this->where_string_encoded; //fixme: is this used at all??
		 }

		 $this->plug = CreateObject('jinn.factory_plugins_db_fields');
		 $this->plug->local_bo = &$this; //FIXME remove

		 // move to bojinn
		 $this->object_events_plugin_manager = CreateObject('jinn.factory_plugins_object_events'); 	
		 $this->object_events_plugin_manager->local_bo = &$this; //FIXME remove

		 //backwards compatibility: check if unique id field is filled. If not: fill it now.
		 if($this->session['site_object_id'] && $this->site_object[unique_id] == '')
		 {
			$status = $this->so->set_unique_id($this->session['site_object_id']);
			$this->site_object[unique_id] = $status[uid];
		 }

		 // if user changes site:
		 // check if a connection with database is possible
		 // check if new objects need to be created for user generated tables
		 if($this->session['site_id'] && !$this->session['site_object_id'])
		 {
			if(!$this->so->test_site_db_by_id($this->session['site_id']))
			{
			   $this->addError(lang('ERROR: Connection with database failed. Please check if database connection settings are correct'));
			}
			else
			{
			   $this->scan_new_objects_silent();
			}
		 }
	  }


	  /**
	  * field_is_enabled 
	  *
	  * this function checks if a field is enabled
	  *
	  * @param mixed $objectID 
	  * @param mixed $fieldname 
	  * @access public
	  * @return void
	  */
	  function field_is_enabled($objectID, $fieldname)
	  {
		 $field_conf_arr=$this->so->get_field_values($objectID,$fieldname);

		 if($field_conf_arr[field_enabled]!=null && $field_conf_arr[field_enabled]==0)
		 {
			return false;
		 }
		 else
		 {
			return true;
		 }

	  }

	  /**
	  * cur_upload_path 
	  * 
	  * @todo: these functions must be moved to sojinn
	  * @access public
	  * @return void
	  */
	  function cur_upload_path()
	  {
		 return $this->cur_upload('path');
	  }

	  /**
	  * cur_upload_url 
	  * 
	  * @todo: these functions must be moved to sojinn
	  * @access public
	  * @return void
	  */
	  function cur_upload_url()
	  {
		 return $this->cur_upload('url');
	  }

	  /**
	  * cur_upload 
	  * 
	  * @todo: these functions must be moved to sojinn
	  * @param mixed $path_or_url 
	  * @access public
	  * @return void
	  */
	  function cur_upload($path_or_url)
	  {
		 $path_or_url_key = 'cur_upload_'.$path_or_url;
		 if($this->site_object[$path_or_url_key] == '')
		 {
			if($this->site[$path_or_url_key] == '')
			{
			   return '' ; //FIXME
			}
			else
			{
			   $url = $this->site[$path_or_url_key];
			}
		 }
		 else
		 {
			$url = $this->site_object[$path_or_url_key];
		 }

		 return $url;
	  }

	  /**
	  * read_session_filter 
	  * 
	  * @param mixed $obj_id 
	  * @access public
	  * @return void
	  */
	  function read_session_filter($obj_id)
	  {
		 return $this->session['filter_settings'][$obj_id];
	  }

	  /**
	  * save_session_filter 
	  * 
	  * @param mixed $obj_id 
	  * @param mixed $data 
	  * @access public
	  * @return void
	  */
	  function save_session_filter($obj_id, $data)
	  {
		 $this->session['filter_settings'][$obj_id] = $data;
	  }

	  /**
	  * records_per_page: get number of records per page from user preferences
	  *
	  * first get object defines prefs and if there none 
	  * get general user defined and if none set to 10
	  * 
	  * @todo implement object specific preferences
	  * @access public
	  * @return void
	  */
	  function records_per_page()
	  {
		 $pref_key='_'.$this->site_object['parent_site_id'].'_'.$this->site_object['object_id'];
		 
		 $pref_default_record_num=intval($this->read_preferences($pref_key));

		 if($_GET['recperpage'])
		 {
			$this->save_preferences($pref_key,$_GET['recperpage']);

			return $_GET['recperpage'];
		 }
		 elseif(is_int($pref_default_record_num) && $pref_default_record_num > 0)
		 {
			return $pref_default_record_num;
		 }
		 else
		 {
			return 25;
		 }

	  }

	  /**
	  * get_offset: simple calculation to get sql-offset from current page and record per page
	  * 
	  * @param mixed $current_page 
	  * @param mixed $rec_per_page 
	  * @access public
	  * @return void
	  */
	  function get_offset($current_page,$rec_per_page)
	  {
		 $offset = ($current_page-1)*$rec_per_page;

		 return $offset;	
	  }

	  /**
	  * mult_change_num_records 
	  * 
	  * @access public
	  * @return void
	  */
	  function mult_change_num_records()
	  {
		 if(is_numeric)	$this->session['mult_records_amount']=intval($_POST['num_records']);

		 $this->sessionmanager->save();
	  }

	  /**
	  * get_records 
	  * 
	  * @param mixed $table 
	  * @param mixed $where_key 
	  * @param mixed $where_value 
	  * @param mixed $offset 
	  * @param mixed $range 
	  * @param mixed $value_reference 
	  * @param string $order_by 
	  * @param string $field_list 
	  * @param string $where_condition 
	  * @access public
	  * @return void
	  */
	  function get_records($table,$where_key,$where_value,$offset,$range,$value_reference,$order_by='',$field_list='*',$where_condition='')
	  {
		 if (!$value_reference)
		 {
			$value_reference='num';
		 }

		 $records = $this->so->get_record_values($this->session['site_id'],$table,$where_key,$where_value,$offset,$range,$value_reference,$order_by,$field_list,$where_condition);

		 return $records;
	  }

	  /**
	  * mult_to_fld 
	  * 
	  * when multiple records are updated this function translates all fields
	  * to single records so the records can be processed normally.
	  * 
	  * @param mixed $i 
	  * @param string $type 
	  * @note If a plugin developer wants to use a prefix it must be exactly charachter long
	  * @todo can't we remove this function 
	  * @access private
	  * @return array with single record fields
	  */
	  function mult_to_fld($i,$type = '_POST')
	  {
		 if($type == '_POST')
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
			   // If a plugin user wants to use a prefix it must be exactly charachter long
			   elseif (substr($key,7,4)=='MLTX' && intval(substr($key,11,2)) == $i) 
			   {
				  $post_arr[substr($key,0,7).'FLDXXX'.substr($key,13)]=$val;
			   }
			   // this is for the switchboard plugin. fixme: is the above case meant for the switchboard as well? in that case it was buggy and should be removed.
			   elseif (substr($key,6,4)=='MLTX' && intval(substr($key,10,2)) == $i) 
			   {
				  $post_arr[substr($key,0,6).'FLDXXX'.substr($key,12)]=$val;
			   }
			   // m2m relation fields
			   elseif(substr($key,0,3)=='M2M' && intval(substr($key,4,2)) == $i)
			   {
				  $post_arr['M2M'.substr($key,3,1).'XX'.substr($key,6)]=$val;
			   }
			   // m2o relation fields
			   elseif(substr($key,0,3)=='M2O' && intval(substr($key,4,2)) == $i)
			   {
				  $post_arr['FLDXXX'.substr($key,6)]=$val;
			   }
			   // extra table_field elements overwriting other FLD's 
			   elseif(substr($key,0,4)=='ELEX' && intval(substr($key,4,2)) == $i)
			   {
				  $key=ereg_replace("UNIQ[a-zA-Z0-9]{13}SOURCE", "", $key);
				  $post_arr['FLDXXX'.substr($key,6)]=$val;
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

	  /**
	  * multiple_actions: handler for multiple actions called from the list view
	  * 
	  * @access public
	  * @todo when we modernize the uilist class this function must go to the ui class
	  * @return void
	  */
	  function multiple_actions()
	  {
		 switch($_POST['action'])
		 {
			case 'del':
			   $where_arr=$this->set_multiple_where();
			   $this->multiple_records_delete($where_arr,$this->site_object);
			   break;
			case 'edit':
			   $this->session['mult_where_array']=$this->set_multiple_where();
			   $this->sessionmanager->save();
			   $this->exit_and_open_screen($this->japielink.'jinn.uiu_edit_record.edit_record');
			   break;
			case 'view':
			   $this->session['mult_where_array']=$this->set_multiple_where();
			   $this->sessionmanager->save();
			   $this->exit_and_open_screen($this->japielink.'jinn.uiu_edit_record.read_record');
			   break;
			case 'export':
			   $this->session['mult_where_array']=$this->set_multiple_where();
			   $this->sessionmanager->save();
			   $this->exit_and_open_screen($this->japielink.'jinn.uiu_export.export');
			   break;
			default:
			   $this->addError(lang('Operation on multiple records failed.'));
			   $this->addDebug(__LINE__,__FILE__);

			   $this->exit_and_open_screen('jinn.uiuser.index');
			}
		 }

		 /**
		 * set_multiple_where 
		 *
		 * @todo create documentation
		 * @access public
		 * @return void
		 */
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

		 /**
		 * multiple_records_insert 
		 * 
		 * @param mixed $numrecs 
		 * @param mixed $object_arr 
		 * @access public
		 * @todo maybe combine insert and update
		 * @return void
		 */
		 function multiple_records_insert($numrecs,$object_arr)
		 {
			//unset($this->session['mult_where_array']);

			if(is_numeric($numrecs) and intval($numrecs)>0)
			{
			   for($i=0;$i<$numrecs;$i++)
			   {
				  $post_arr=$this->mult_to_fld($i,'_POST');
				  $files_arr=$this->mult_to_fld($i,'_FILES');

				  $data=$this->remove_helper_fields($this->http_vars_pairs($post_arr,$files_arr,$object_arr[object_id]));
				  $status=$this->so->insert_object_data($this->session['site_id'],$object_arr[table_name],$data);

				  $status['mult_where_array'][]=$status[where_string];

				  $m2m_data=$this->http_vars_pairs_m2m($post_arr);
				  $m2m_data['FLDXXX'.$status['idfield']]=$status['id'];
				  $status[relations]=$this->so->update_object_many_data($object_arr['parent_site_id'], $m2m_data);
			   }
			}

			return $status;

		 }

		 /**
		 * multiple_records_update: the main update record function
		 *
		 *
		 * @todo make more efficient multiple vs single
		 * @param mixed $mult_where_array 
		 * @access public
		 * @return void
		 */
		 function multiple_records_update($mult_where_array=false,$numrecs,$object_arr,$illegal_prefix_arr=false)
		 {
			/* exit and go to del function */
			if($_POST['delete'])
			{
			   $this->multiple_records_delete($mult_where_array,$object_arr);
			}

			if(is_array($illegal_prefix_arr))
			{
			   foreach($illegal_prefix_arr as $ill_prefix)
			   {
				  $_POST = $this->filter_array_with_prefix($_POST,$ill_prefix,true,true);
			   }
			}

			//so its multiple
			if($numrecs>0)
			{
			   for($i=0;$i<$numrecs;$i++)
			   {
				  $post_arr=$this->mult_to_fld($i,'_POST');
				  $files_arr=$this->mult_to_fld($i,'_FILES');

				  $data = $this->remove_helper_fields($this->http_vars_pairs($post_arr,$files_arr,$object_arr[object_id]));
				  $where_string=base64_decode($_POST['MLTWHR'.sprintf("%02d",$i)]);
				  // $this->session['mult_where_array'][]=$where_string;
				  $status['mult_where_array'][]=$where_string;
				  
				  $m2m_data=$this->http_vars_pairs_m2m($post_arr);

				  $status[m2m]=$this->so->update_object_many_data($this->session['site_id'], $m2m_data);

				  $status[record]=$this->so->update_object_data($object_arr['parent_site_id'], $object_arr[table_name], $data, $where_key,$where_value,$where_string);

				  $status[eventstatus] = $this->run_event_plugins('on_update', $post_arr);
			   }
			}

			return $status;	

		 }

		 /**
		 * multiple_records_delete 
		 * 
		 * @param mixed $where_arr 
		 * @param mixed $object_arr 
		 * @param mixed $redirect 
		 * @access public
		 * @return void
		 */
		 function multiple_records_delete($where_arr,$object_arr,$redirect=true)
		 {
			foreach ($where_arr as $where_string)
			{
			   $where_string =stripslashes($where_string);
			   $status=$this->so->delete_object_data($object_arr['parent_site_id'], $object_arr['table_name'], false, false,$where_string);
			}

			if($redirect)
			{
			   if ($status[error])
			   {
				  $this->addError(lang('Records NOT succesfully deleted.'));
			   }
			   else 
			   {
				  $this->addInfo(lang('Records succesfully deleted'));
			   }
			   $this->addDebug(__LINE__,__FILE__);

			   $this->exit_and_open_screen($this->japielink.'jinn.uiuser.index');
			}
			else
			{
			   return $status;
			}
		 }

		 /**
		 * remove_helper_fields 
		 * 
		 * @param mixed $data 
		 * @access public
		 * @return void
		 */
		 function remove_helper_fields($data)
		 {
			//removes helper form fields created by plugins
			foreach($this->plug->registry->plugins as $plugin)
			{
			   foreach($data as $key => $field)
			   {
				  if(strpos($field[name], $plugin['helper_fields_substring']) === false)
				  {
					 //
				  }
				  else
				  {
					 unset($data[$key]);
				  }
			   }
			}
			return $data;
		 }

		 /**
		 * run_event_plugins 
		 * 
		 * @param mixed $event 
		 * @param mixed $post 
		 * @access public
		 * @return void
		 * @todo move to enent plugin class
		 */
		 function run_event_plugins($event, $post)
		 {
			//get all events plugins configured to this object
			$stored_configs = unserialize(base64_decode($this->site_object['events_config']));
			if(is_array($stored_configs))
			{
			   foreach($stored_configs as $config)
			   {
				  if($event == $config['conf']['event'])
				  {
					 /*run_event_plugins roept uit de event_plugin de functie event_action_[plg_naam]() aan 
					 met als argumenten de _POST array en de plugin configuratie. 
					 Deze functie geeft alleen een status terug dus geen waarde om weer verder te gebruiken. 
					 De functie gebruikt de config_data en de post_data om iets speciaals te doen.*/
					 $status = $this->object_events_plugin_manager->call_event_action($post, $config);
				  }
			   }
			}
		 }

		 /**
		 * o2o_update: inserts or updates a record which from the object that has a one to one relation
		 * 
		 * @note: this thing only worx at the moment with auto incrementing primaries, inform the developer
		 * @param mixed $primary_val 
		 * @access public
		 * @return void
		 */
		 function o2o_update($primary_val)
		 {		 
			if(!$primary_val)
			{
			   return;
			}
			$o2o_data=$this->http_vars_pairs_o2o($_POST, $_FILES,$primary_val);

			if(is_array($o2o_data))
			{
			   // FIXME implement m2m relations for o2o related objects
			   foreach($o2o_data as $o2o_entry)
			   {
				  $where= "{$o2o_entry[data][0][name]}={$o2o_entry[data][0][value]}";

				  $status=$this->so->update_object_record($this->session['site_id'], $o2o_entry[meta][O2OT], $o2o_entry[data], '','',$where,true);	   
			   }
			}
			return $status;
		 }

		 /**
		 * del_record 
		 * 
		 * @fixme: move this record to a gui class
		 * @access public
		 * @return void
		 */
		 function del_record() 
		 {
			$table=$this->site_object[table_name];
			$where_key=stripslashes($this->where_key);
			$where_value=stripslashes($this->where_value);
			$where_string=stripslashes($this->where_string);

			$status=$this->so->delete_object_data($this->session['site_id'], $table, $where_key,$where_value,$where_string);

			if ($status[error])
			{
			   $this->addError(lang('Record NOT succesfully deleted'));
			}
			else
			{
			   $this->addInfo(lang('Record succesfully deleted'));
			}
			$this->addDebug(__LINE__,__FILE__,$status[sql]);

			$this->exit_and_open_screen($this->japielink.'jinn.uiuser.index');
		 }


		 /**
		 * copy_record 
		 * 
		 * @access public
		 * @return void
		 */
		 function copy_record()
		 {
			// check if id is autoincrementing
			$autokey= $this->so->check_auto_incr($this->session['site_id'],$this->site_object['table_name']);
			if($autokey)
			{
			   $status=$this->so->copy_record($this->session['site_id'],$this->site_object[table_name],$this->where_string,$autokey);
			   if ($status[ret_code])
			   {
				  $this->addError(lang('Record NOT succesfully copied'));
			   }
			   else
			   {
				  $this->addInfo(lang('Record succesfully copied'));
			   }
			   $this->addDebug(__LINE__,__FILE__,$status[sql]);

			   if($status[where_string])
			   {
				  $this->exit_and_open_screen($this->japielink.'jinn.uiu_list_records.display_last_records_page');
				  #$this->exit_and_open_screen('jinn.uiu_edit_record.edit_record&where_string='.base64_encode($status[where_string]));
			   }
			}
			else
			{
			   // FIXME disable copy icon when its not possible
			   $this->addError(lang('Cannot copy a record from this table.'));
			   $this->addDebug(__LINE__,__FILE__);
			}

			$this->exit_and_open_screen($this->japielink.'jinn.uiuser.index');
		 }

		 /**
		 * get_data 
		 * 
		 * @param mixed $columns_arr 
		 * @param mixed $filter_where 
		 * @access public
		 * @return void
		 */
		 function get_data($columns_arr, $filter_where, $limit=false,$key_prefix='')
		 {
			//new function for fast and generic retrieval of object data, including 1-1, 1-many and many-many relations
			//partly implemented in bouser, partly in sojinn
			if(($filter_where == "" or $filter_where == "all"))
			{
			   $filter_where = $this->site_object['extra_where_sql_filter'];
			}
			elseif($this->site_object['extra_where_sql_filter'] != "")
			{
			   $filter_where = "($filter_where) AND ".$this->site_object['extra_where_sql_filter'];
			}
			$site_id = $this->session['site_id'];
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
			return $this->so->get_data($site_id, $table_name, $columns_arr, $filter_where, $limit,$key_prefix);
		 }

		 /**
		 * extract_m2o_relations 
		 * 
		 * @param mixed $string 
		 * @access public
		 * @return void
		 */
		 function extract_m2o_relations($string)
		 {
			$relations_arr = unserialize(base64_decode($string));

			if(is_array($relations_arr) && $relations_arr)
			{
			   foreach($relations_arr as $relation)
			   {
				  if ($relation[type]=='4')
				  {
					 $m2o_relations[]=$relation;
				  }
			   }
			   return $m2o_relations;
			}
		 }

		 /**
		 * extract_O2O_relations 
		 * 
		 * @param mixed $string 
		 * @access public
		 * @return void
		 */
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


		 /**
		 * extract_O2M_relations 
		 * 
		 * @param mixed $string 
		 * @access public
		 * @return void
		 */
		 function extract_O2M_relations($string)
		 {
			if(!$string) return;
			$relations_arr=$this->bcompat->relations_up2date($string);	

			if(is_array($relations_arr))
			{
			   foreach($relations_arr as $relation)
			   {
				  if ($relation[type]=='1')
				  {
					 $O2M_relations[$relation[local_key]]=$relation;
				  }
			   }
			   return $O2M_relations;
			}
			else // ERROR
			{
			   return; //die('FIXME do an automatic update of all relation');
			}
		 }

		 /**
		 * extract_M2M_relations 
		 * 
		 * @param mixed $string 
		 * @access public
		 * @return void
		 */
		 function extract_M2M_relations($string)
		 {
			if(!$string) return;
			$relations_arr=$this->bcompat->relations_up2date($string);	

			if(is_array($relations_arr))
			{
			   foreach($relations_arr as $relation)
			   {
				  if ($relation[type]=='2')
				  {
					 $M2M_relations[]=$relation;
				  }
			   }
			   return $M2M_relations;
			}
			else //error but pretend its empty ;)
			{
			   return;
			}
		 }

		 /**
		 * get_related_field: used by the one tro many relation widget for creating the selectbox  
		 * 
		 * @param mixed $relation_array 
		 * @access public
		 * @return void
		 */
		 function get_related_field($relation_array)
		 {
			$displ_arr=unserialize($relation_array[foreign_showfields]);		

			$allrecords=$this->get_records($relation_array[foreign_table],'','','','','name');

			if(is_array($allrecords))
			{
			   foreach ($allrecords as $record)
			   {
				  unset($displaystring);
				  foreach($displ_arr as $displ_str)
				  {
					 if( $displaystring)
					 {
						$displaystring.=' ';							  
					 }
					 $displaystring .= $record[$displ_str];
				  }
				  $related_fields[]=array
				  (
					 'value'=>$record[$relation_array[foreign_key]],
					 'name'=>$displaystring
				  );
			   }
			}
			return $related_fields;
		 }



		 /**
		 * get_related_value 
		 * 
		 * @param mixed $relation_array 
		 * @param mixed $value 
		 * @access public
		 * @fixme optimize use smarter sql!!!
		 * @return void
		 */
		 function get_related_value($relation_array,$value)
		 {
			$displ_arr=unserialize($relation_array[foreign_showfields]);		
			$table=$relation_array[foreign_table];
			$related_field=$relation_array[foreign_key];

			$allrecords=$this->get_records($table,'','','','','name');

			if(is_array($allrecords))

			foreach ($allrecords as $record)
			{
			   if($record[$related_field]==$value)
			   {
				  unset($displaystring);
				  foreach($displ_arr as $displ_str)
				  {
					 if( $displaystring)
					 {
						$displaystring.=' ';							  
					 }
					 $displaystring .= $record[$displ_str];
				  }

				  return $displaystring;
			   }
			}
		 }

		 /**
		 * http_vars_pairs: function which creata data pairs to store in the database 
		 * 
		 * @param mixed $HTTP_POST_VARS 
		 * @param mixed $HTTP_POST_FILES 
		 * @param string $prefix 
		 * @access private
		 * @return array with data pairs
		 */
		 function http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES,$object_id,$prefix='FLDXXX') 
		 {
			$prefix_len=strlen($prefix);
			while(list($key, $val) = each($HTTP_POST_VARS)) 
			{
			   if(substr($key,0,$prefix_len)==$prefix)
			   {
				  $field_values=$this->so->get_field_values($object_id,substr($key,6));

				  $filtered_data=$this->plug->call_plugin_sf($key,$field_values,$HTTP_POST_VARS,$HTTP_POST_FILES);

				  if ($filtered_data)				
				  {
					 if ($filtered_data==-1) $filtered_data='';
					 $data[] = array
					 (
						'name' => substr($key,$prefix_len),
						'value' =>  $filtered_data  //addslashes($val)
					 );
				  }
				  else // if there's no plugin, just save the vals
				  {
					 $data[] = array
					 (
						'name' => substr($key,$prefix_len),
						'value' => addslashes($val) 
					 );
				  }
			   }
			}

			return $data;
		 }

		 /**
		 * http_vars_pairs_o2o 
		 * 
		 * @param mixed $HTTP_POST_VARS 
		 * @param mixed $HTTP_POST_FILES 
		 * @param mixed $primary_val 
		 * @access public
		 * @return void
		 */
		 function http_vars_pairs_o2o($HTTP_POST_VARS,$HTTP_POST_FILES,$primary_val) 
		 {
			while(list($key, $val) = each($HTTP_POST_VARS)) 
			{
			   if(substr($key,0,4)=='O2OO')
			   {
				  $curr_object_arr=$this->so->get_object_values_by_uniq($val);
			   }

			   if(substr($key,0,4)=='O2OW' || substr($key,0,4)=='O2OT'  || substr($key,0,4)=='O2OO')
			   {
				  $idx=intval(substr($key,4,2));
				  $o2o_data_arr[$idx]['meta'][substr($key,0,4)]=$val;

			   }
			   // this setss the primary val 
			   elseif(substr($key,0,4)=='O2OR')
			   {
				  $idx=intval(substr($key,4,2));
				  $_o2o_info=unserialize(base64_decode($val));	


				  $data = array
				  (
					 'name' => $_o2o_info[foreign_key],
					 'value' =>  $primary_val  //addslashes($val)
				  );

				  $o2o_data_arr[$idx]['data'][]=$data;
			   }
			   elseif(substr($key,0,3)=='O2O')
			   {

				  $field_values=$this->so->get_field_values($curr_object_arr[object_id],substr($key,6));
				  $filtered_data=$this->plug->call_plugin_sf($key,$field_values,$HTTP_POST_VARS,$HTTP_POST_FILES);

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



		 /**
		 * http_vars_pairs_m2m 
		 * 
		 * @fixme autodetect primary val
		 * @param mixed $HTTP_POST_VARS 
		 * @access public
		 * @return void
		 */
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

		 /**
		 * save_object_config 
		 * 
		 * @access public
		 * @return void
		 */
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
			unset($this->session['browse_settings']['orderby']);
			$this->sessionmanager->save();

			$this->exit_and_open_screen('jinn.uiu_list_records.display');
		 }


		 /**
		 * set_adv_filter 
		 * 
		 * @access public
		 * @return void
		 */
		 function set_adv_filter()
		 {
			$this->session['browse_settings'][adv_filter_str]=$_POST[adv_filter];  
			$this->sessionmanager->save();
			$this->exit_and_open_screen('jinn.uiu_list_records.display');
		 }

		 /**
		 * scan_new_objects_silent: check if users have created new tables that need to become accessible as objects
		 * 
		 * @access public
		 * @return void
		 */
		 function scan_new_objects_silent()
		 {
			$status = array();
			if($this->site[object_scan_prefix] != '')
			{
			   $prefix_arr = explode(',', $this->site[object_scan_prefix]);
			   if(is_array($prefix_arr))
			   {

				  $tables = $this->so->site_tables_names($this->session['site_id']);
				  $status = array();
				  foreach($tables as $table)
				  {
					 //is this table wrapped by an object?
					 $objects = $this->so->get_objects_by_table($table[table_name], $this->session['site_id']);
					 if(count($objects) == 0)
					 {
						//if no, do we want ALL tables wrapped by an object?
						if($prefix_arr[0] == '*')
						{
						   //if yes, create an object from this table
						   $status[] = $this->save_scanned_object($this->session['site_id'], $table[table_name]);
						}
						//or does the table name start with one of the prefixes?
						else
						{
						   foreach($prefix_arr as $prefix)
						   {
							  if(substr($table[table_name], 0, strlen($prefix)) == $prefix)
							  {
								 $status[] = $this->save_scanned_object($this->session['site_id'], $table[table_name]);
							  }
						   }
						}
					 }
				  }
			   }
			}
			return $status;
		 }

		 /**
		 * scan_new_objects: scan for new objects, create messages and redirect to index 
		 * 
		 * @access public
		 * @return void
		 */
		 function scan_new_objects()
		 {
			$status = $this->scan_new_objects_silent();
			if(count($status) > 0)
			{
			   $this->addInfo(lang('%1 new objects where successfully created', count($status)));
			   foreach($status as $new)
			   {
				  if($new[ret_code] != 0)
				  {
					 $this->addError(lang('Error creating one or more new Objects'));
					 $this->addInfo(lang('%1 new objects where successfully created', count($status)));
				  }
			   }
			}
			else
			{
			   $this->addInfo(lang('No new objects created'));
			}
			$this->exit_and_open_screen('jinn.uiuser.index');
		 }

		 /**
		 * save_scanned_object: wraps an (empty) site object around a given table
		 * 
		 * @param mixed $site_id 
		 * @param mixed $table_name 
		 * @access public
		 * @return void
		 */
		 function save_scanned_object($site_id, $table_name)
		 {
			$data = array();
			$data[] = array('name' => 'name'			, 'value' => $table_name);
			$data[] = array('name' => 'table_name'		, 'value' => $table_name);
			$data[] = array('name' => 'parent_site_id'	, 'value' => $site_id	);
			$data[] = array('name' => 'hide_from_menu'	, 'value' => ''			);
			$data[] = array('name' => 'unique_id'		, 'value' => ''			);
			return $this->so->insert_new_object($data);
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
			   //die('hallo');
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
			   $this->session['message'][info]=lang('Action was succesful.');

			   $this->sessionmanager->save();
			   $this->common->exit_and_open_screen('jinn.uiuser.index');
			}
			else
			{
			   $this->session['message'][error]=lang('Action was not succesful. Unknown error');

			   $this->sessionmanager->save();
			   $this->common->exit_and_open_screen('jinn.uiuser.index');
			}
		 }

		 function create_where_string($post, $fldprefix='')
		 {
			//get current table
			$curr_table=$this->site_object['table_name'];

			//get meta table info
			$fields = $this->so->site_table_metadata($this->site_object['parent_site_id'],$curr_table);

			//get primary field
			foreach ( $fields as $onecol )
			{
			   // check for primaries and create array 
			   if ($onecol[primary_key] && $onecol[type]!='blob') // FIXME howto select long blobs
			   {						
				  $pkey_arr[]=$onecol[name];
			   }
			   elseif($onecol[type]!='blob') // FIXME howto select long blobs
			   {
				  $akey_arr[]=$onecol[name];
			   }
			}
			if(!is_array($pkey_arr))
			{
			   $pkey_arr=$akey_arr;
			   unset($akey_arr);
			}
			if(count($pkey_arr)>0)
			{
			   foreach($pkey_arr as $pkey)
			   {
				  if($where_string) $where_string.=' AND ';
				  $where_string.= '('.$curr_table.'.'.$pkey.' = \''. addslashes($post[$fldprefix.$pkey]).'\')';
			   }
			}

			return $where_string;

		 }



	  }
   ?>
