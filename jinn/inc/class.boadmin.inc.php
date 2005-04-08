<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Authors: Pim Snel <pim@lingewoud.nl>, 
			Lex Vogelaar <lex_vogelaar@users.sourceforge.net>
   Copyright (C)2002, 2003, 2004, 2005 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

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

   /* $Id$ */
   class boadmin 
   {
	  var $public_functions = Array(
		 'del_egw_jinn_site'=> True,
		 'del_egw_jinn_object' => True,
		 'insert_egw_jinn_site'=> True,
		 'insert_egw_jinn_object'=> True,
		 'update_egw_jinn_site'=> True,
		 'update_egw_jinn_object' => True,
		 'save_field_plugin_conf' => True,
		 'save_field_info_conf' => True,
		 'save_object_events_conf' => True
	  );

	  var $so;
	  var $session;
	  var $sessionmanager;

	  var $site_object; 
	  var $site; 
	  var $local_bo;
	  var $magick;

	  var $current_config;
	  var $action;
	  var $common;

	  var $where_key;
	  var $where_value;

	  var $plug;
	  var $plugins;
	  var $object_events_plugin_manager;
	  var $object_events_plugins;

	  var $db_ftypes;

	  function boadmin()
	  {
		 $this->common = CreateObject('jinn.bocommon');
		 $this->session 		= &$this->common->session->sessionarray;	//shortcut to session array
		 $this->sessionmanager	= &$this->common->session;					//shortcut to session manager object
		 
		 $this->current_config=$this->common->get_config();		

		 $this->so = CreateObject('jinn.sojinn');

		 $this->use_session = True; //fixme: what does this do?
		
		$_where_key = $_POST['where_key'] ? $_POST['where_key']    : $_GET['where_key'];
		if(!empty($_where_key))
		{
			$this->where_key  = $_where_key;
		}
		
		$_where_value = $_POST['where_value'] ? $_POST['where_value']    : $_GET['where_value'];
		if(!empty($_where_value))
		{
			$this->where_value  = $_where_value;
		}
		
		$_action = $_POST['action'] ? $_POST['action']   : $_GET['action'];
		if((!empty($_action) && empty($this->action)) || !empty($_action))
		{
			$this->action  = $_action;
		}

		 // get array of site and object
		 $this->site = $this->so->get_site_values($this->session['site_id']);

		 if ($this->session['site_object_id'])
		 {
			$this->site_object = $this->so->get_object_values($this->session['site_object_id']);
		 }

		 $this->plug = CreateObject('jinn.plugins_db_fields'); //$this->include_plugins();
		 $this->plug->local_bo = $this;
		 $this->plugins = $this->plug->plugins;


		 $this->object_events_plugin_manager = CreateObject('jinn.plugins_object_events'); //$this->include_plugins();
		 $this->object_events_plugin_manager->local_bo = $this;
		 $this->object_events_plugins = $this->object_events_plugin_manager->object_events_plugins;

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');

		 global $local_bo;
		 $local_bo=$this;
	  }

	  function get_field_array($HTTP_POST_VARS)
	  {
		 while(list($key, $val) = each($HTTP_POST_VARS)) 
		 {
			if(substr($key,0,6)=='FIELD_')
			{
			   $data[] = array
			   (
				  name      => substr(substr($key, 6), 0, -4),
				  property  => substr(substr($key, 6), -3),
				  value     => addslashes($val)
			   );
			}
		 }
		 return $data;
	  }

	  /**
	  @function save_field_info_conf
	  @abstract save changes made to a field info configuration in database
	  @note this function uses new standard method for returning exit codes and status information
	  */
	  function save_field_info_conf()
	  {
		 if(!$_GET[object_id] || !$_GET[field_name])
		 {
			die('error');
		 }

		 $data=$this->http_vars_pairs($_POST,$_FILES);

		 $where_string="`field_parent_object`={$_GET[object_id]} AND  `field_name`='$_GET[field_name]'";
		 $status =$this->so->save_field_info_conf($_GET[object_id],$_GET[field_name],$data,$where_string);

		 if($status[ret_code])
		 {
			$this->session['message'][error]=lang('An unknown error has occured (error code 110)');
			$this->session['message'][error_code]=110;
		 }
		 else
		 {
			$this->session['message'][info]=lang('Field info configuration successfully saved');
		 }
		 $this->sessionmanager->save();

		 $this->common->exit_and_open_screen('jinn.uiadmin.field_help_config&field_name='.$_GET[field_name].'&object_id='.$_GET[object_id]);
	  }


	  /**
	  @function save_object_events_conf
	  @abstract save a new object events plugin configuration in the database
	  @note this function uses new standard method for returning exit codes and status information
	  */
	  function save_object_events_conf()
	  {
		 if(!$_GET[object_id] && !$_GET[edit])
		 {
			die('error');
		 }


		 if(is_array($_POST))
		 {
			$dirty = false;

			//get the already stored configurations
			$object_arr=$this->so->get_object_values($_GET[object_id]);
			$stored_configs = unserialize(base64_decode($object_arr[events_config]));

			if($_GET[edit]=='')
			{
			   //check if configurations need to be deleted
			   if(is_array($stored_configs)) 
			   {
				  $numconfigs=count($stored_configs);
				  for($i=0; $i<$numconfigs; $i++)
				  {
					 if($_POST['delete_'.$i] == 'true')
					 {
						unset($stored_configs[$i]);
						unset($_POST['delete_'.$i]); //or else it gets stored in the plugin config
						$dirty=true;
					 }
				  }
				  $stored_configs = array_values($stored_configs);
			   }

			   //if a new plugin was configured, add it to the store
			   if($_POST[event] != '' && $_POST[plugin] != '')
			   {
				  if(!is_array($stored_configs)) $stored_configs = array();
				  $conf=array
				  (
					 'name'=>$_POST[plugin],
					 'conf'=>$_POST
				  );
				  $stored_configs[] = $conf;
				  $dirty=true;
			   }
			}
			else
			{
			   if(is_array($stored_configs)) 
			   {
				  $_POST[event]  = $stored_configs[$_GET[edit]][conf][event];
				  $_POST[plugin] = $stored_configs[$_GET[edit]][conf][plugin];

				  //replace the existing config with this one
				  if(!is_array($stored_configs)) $stored_configs = array();
				  $conf=array
				  (
					 'name'=>$_POST[plugin],
					 'conf'=>$_POST
				  );
				  $stored_configs[$_GET[edit]] = $conf;
				  $dirty=true;
			   }
			}

			if($dirty)
			{
			   $conf_serialed_string=base64_encode(serialize($stored_configs));
			   $status=$this->so->save_object_events_plugin_conf($_GET[object_id],$conf_serialed_string);
			   if($status[ret_code])
			   {
				  $this->session['message'][error]=lang('An unknown error has occured (error code 109)');
				  $this->session['message'][error_code]=-1;
				  _debug_array(lang('An unknown error has occured (error code 109)'));
			   }
			   else
			   {
				  $this->session['message'][info]=lang('Plugin configuration successfully saved');
				  _debug_array(lang('Plugin configuration successfully saved'));
			   }
			}
			else
			{
			   $this->session['message'][error]=lang('nothing to save. Please select a plugin to delete or configure a new plugin');
			   _debug_array(lang('nothing to save. Please select a plugin to delete or configure a new plugin'));
			}
		 }

		 $this->sessionmanager->save();

		 //fixme: this gives a strange error:
		 //$this->common->exit_and_open_screen('menuaction=jinn.uiadmin.object_events_config&close_me=true&object_id='.$_GET[object_id]);

		 //VERY dirty hack to solve this problem:		 
		 echo('<input type="button" onClick="self.close()" value="'.lang('close').'"/>');
		 //obviously this needs to be fixed. Then also the above _debug_arrays can be removed.

	  }

	  /**
	  @function save_field_plugin_conf
	  @abstract save changes made to a field plugin configuration in database
	  @note this function uses new standard method for returning exit codes and status information
	  */
	  function save_field_plugin_conf()
	  {
		 if(!$_GET[object_id] || !$_GET[field_name] || !$_GET[plug_name])
		 {
			die('error');
		 }

		 if(is_array($_POST))
		 {
			$conf=array(
			   'name'=>$_GET[plug_name],
			   'conf'=>$_POST
			);
			$conf_serialed_string=base64_encode(serialize($conf));
		 }

		 $status=$this->so->save_field_plugin_conf($_GET[object_id],$_GET[field_name],$conf_serialed_string);

		 if($status[ret_code])
		 {
			$this->session['message'][error]=lang('An unknown error has occured (error code 109)');
			$this->session['message'][error_code]=109;
		 }
		 else
		 {
			$this->session['message'][info]=lang('Plugin configuration successfully saved');
		 }
		 $this->sessionmanager->save();

		 $this->common->exit_and_open_screen('jinn.uiadmin.plug_config&plug_orig='.$_GET[plug_name].'&close_me=true&plug_name='.$_GET[plug_name].'&hidden_name=CFG_PLG'.$_GET[plug_name].'&=&field_name='.$_GET[plug_name].'&object_id='.$_GET[object_id].'&hidden_val=');
	  }


	  /*
	  @function insert_egw_jinn_site
	  @abstract insert new site with meta data
	  */
	  function insert_egw_jinn_site()
	  {
		 $data=$this->http_vars_pairs($_POST,$_FILES);
		 $status=$this->so->insert_phpgw_data('egw_jinn_sites',$data);

		 if ($status>0)	
		 {
			$this->session['message'][info]=lang('Site succesfully added');
		 }
		 else 
		 {
			$this->session['message'][error]=lang('Site NOT succesfully added, unknown error');
		 }

		 $this->sessionmanager->save();
		 if($_POST['continue'])
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$status[where_value].'&serial='.$status[serial]);
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
		 }
	  }

	  /*
	  @function insert_egw_jinn_object
	  @absrtact insert new object
	  */
	  function insert_egw_jinn_object()
	  {
		 $data=$this->http_vars_pairs($_POST,$_FILES);
		 $status=$this->so->insert_phpgw_data('egw_jinn_objects',$data);

		 if ($status>0)	$this->session['message'][info]=lang('Site Object succesfully added');
		 else $this->session['message'][error]=lang('Site Object NOT succesfully added, unknown error');

		 $this->sessionmanager->save();
		 if($_POST['continue'])
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_object&where_key=object_id&where_value='.$status[where_value].'&serial='.$status[serial]);
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$_POST[FLDparent_site_id]);
		 }
	  }

	  /**
	  @function update_egw_jinn_site
	  @abstract updates site meta data in database and return to form
	  */
	  function update_egw_jinn_site()
	  {
		 $table='egw_jinn_sites';

		 $data=$this->http_vars_pairs($_POST,$_FILES);
		 $status=$this->so->update_phpgw_data($table,$data, $this->where_key,$this->where_value);


		 if ($status[ret_code]==0)	$this->session['message'][info]=lang('Site succesfully saved');
		 else $this->session['message'][error]=lang('Site NOT succesfully saved, unknown error');

		 $this->sessionmanager->save();
		 if($_POST['continue'])
		 {
			//FIXME 
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$this->where_value);
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
		 }
	  }

		
	  /*
	  @function update_egw_jinn_object
	  @absract update object data
	  */
	  function update_egw_jinn_object()
	  {
		 $table='egw_jinn_objects';


		 /* start relation section */

		 if ($_POST[FLDrelations])
		 {
			//unpack the array for add/remove actions
			$relations_arr = unserialize(base64_decode($_POST[FLDrelations]));

			// check if there are relations to delete
			$relations_to_delete=$this->common->filter_array_with_prefix($_POST,'DEL');
			if (is_array($relations_to_delete))
			{
			   foreach($relations_to_delete as $relation_to_delete)
			   {
				  unset($relations_arr[$relation_to_delete]);
			   }
			   $relations_arr = array_values($relations_arr); //reorder the index values
			}
		 }

		 // check if new ONE TO MANY relation parts are complete else ignore them
		 if($_POST['1_relation_org_field'] && $_POST['1_relation_table_field'] 
		 && $_POST['1_display_field'])
		 {
			unset($new_relation_arr);
			$new_relation_arr[type] = 1; //one-to-many
			$new_relation_arr[org_field] = $_POST['1_relation_org_field'];
			$new_relation_arr[related_with] = $_POST['1_relation_table_field'];
			$new_relation_arr[display_field] = $_POST['1_display_field'];
			$new_relation_arr[display_field_2] = $_POST['1_display_field_2'];
			$new_relation_arr[display_field_3] = $_POST['1_display_field_3'];
			$new_relation_arr[default_value] = $_POST['1_default'];

			$relations_arr[count($relations_arr)] = $new_relation_arr;
		 }

		 // check if new MANY TO MANY relation parts are complete else ignore them
		 if($_POST['2_relation_via_primary_key'] && $_POST['2_relation_foreign_key'] 
		 && $_POST['2_relation_via_foreign_key'] && $_POST['2_display_field'])
		 {

			unset($new_relation_arr);
			$new_relation_arr[type] = 2; //many-to-many
			$new_relation_arr[via_primary_key] = $_POST['2_relation_via_primary_key'];
			$new_relation_arr[via_foreign_key] = $_POST['2_relation_via_foreign_key'];
			$new_relation_arr[foreign_key] = $_POST['2_relation_foreign_key'];
			$new_relation_arr[display_field] = $_POST['2_display_field'];
			$new_relation_arr[display_field_2] = $_POST['2_display_field_2'];
			$new_relation_arr[display_field_3] = $_POST['2_display_field_3'];

			$relations_arr[count($relations_arr)] = $new_relation_arr;
		 }

		 // check if new ONE TO ONE relation parts are complete else ignore them
		 if($_POST['3_relation_org_field'] && $_POST['3_relation_table_field'] 
		 && $_POST['3_relation_object_conf'])
		 {
			unset($new_relation_arr);
			$new_relation_arr[type] = 3; //one-to-one
			$new_relation_arr[org_field] = $_POST['3_relation_org_field'];
			$new_relation_arr[related_with] = $_POST['3_relation_table_field'];
			$new_relation_arr[object_conf] = $_POST['3_relation_object_conf'];

			$relations_arr[count($relations_arr)] = $new_relation_arr;
		 }

		 //repack the array for storage
		 $_POST['FLDrelations']=base64_encode(serialize($relations_arr));


		 //create an array of field properties from the FIELD_ form section
		 $fields=$this->get_field_array($_POST);

		 //iterate through that array, compile all properties from ONE field and save those properties all at once.
		 if(is_array($fields))
		 {
			foreach($fields as $field)
			{
			   switch($field[property])
			   {
				  case 'PLG': //plugin type
					 $plugin[name]=$field[value];
					 break;
				  case 'PLC': //plugin configuration
					 $plugin[conf]=$field[value];
					 $conf_serialed_string=base64_encode(serialize($plugin)); 
					 break;
				  case 'MAN': //is this a mandatory field?
					 $mandatory=$field[value];
					 break;
				  case 'DEF': //show in listview by default?
					 $show_default=$field[value];
					 break;
				  case 'POS': //position of field in listview
					 $position=$field[value];

					 //BEWARE: if new properties are added, make sure the LAST one ends with saving the record!
					 //POS is the last field, so now update the object field record:
					 $status=$this->so->save_field($this->where_value,$field[name],$conf_serialed_string,$mandatory,$show_default,$position);
					 break;
				  default:
					 break;
			   }
			}
		 }

		 $data=$this->http_vars_pairs($_POST,$_FILES);
		 $status=$this->so->update_phpgw_data($table,$data, $this->where_key,$this->where_value);


		 if ($status[ret_code]==0)	$this->session['message'][info]=lang('Site Object succesfully saved');
		 else $this->session['message'][error]=lang('Site Object NOT succesfully saved, unknown error');

		 $this->sessionmanager->save();
		 if($_POST['continue'])
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_object&where_key='.$this->where_key.'&where_value='.$this->where_value);
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$_POST[FLDparent_site_id]);
		 }
	  }

	  /*
	  @function del_egw_jinn_site
	  @abstract delelte site and return to list
	  */
	  function del_egw_jinn_site()
	  {
		 $status=$this->so->delete_phpgw_data('egw_jinn_sites',$this->where_key,$this->where_value);

		 if ($status==1)	$this->session['message'][info]=lang('site succesfully deleted');
		 else $this->session['message'][error]=lang('Site NOT succesfully deleted, Unknown error');

		 $this->sessionmanager->save();
		 $this->common->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
	  }

	  /*
	  @function  del_egw_jinn_object
	  @abstract delete table_object and return to parent site
	  */
	  function del_egw_jinn_object()
	  {
		 $records = $this->so->get_phpgw_record_values('egw_jinn_objects',$this->where_key,$this->where_value,'','','name');	

		 $status=$this->so->delete_phpgw_data('egw_jinn_objects',$this->where_key,$this->where_value);

		 if ($status==1)	$this->session['message'][info]=lang('Site Object succesfully deleted');
		 else $this->session['message'][error]=lang('Site Object NOT succesfully deleted, Unknown error');

		 $this->sessionmanager->save();

		 $this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$records['0']["parent_site_id"]);

	  }

	  function get_phpgw_records($table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by=false)
	  {
		 if (!$value_reference)
		 {
			$value_reference='num';
		 }

		 $records = $this->so->get_phpgw_record_values($table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by);

		 return $records;
	  }

	  function http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES) 
	  {
		 while(list($key, $val) = each($HTTP_POST_VARS)) 
		 {
			if(substr($key,0,3)=='FLD')
			{
			   $data[] = array
			   (
				  'name' => substr($key,3),
				  'value' => addslashes($val) 
			   );
			}
		 }

		 return $data;
	  }

	  /**
	  * make array with pairs of keys and values from http_post_vars 
	  */
	  // try this with filter_array_with_prefix
	  function http_vars_pairs_plugins($HTTP_POST_VARS) 
	  {
		 reset($HTTP_POST_VARS);	

		 while(list($key, $val) = each($HTTP_POST_VARS)) 
		 {
			if(substr($key,0,3)=='PLG')
			{
			   $plug_data[substr($key,3)]=array(
				  'name'=>$val,
				  'conf'=> false
			   );
			}
		 }

		 return $plug_data;
	  }

	  function upgrade_plugins($object_id,$quite=false)
	  {

		 if($object_id) $object_arr=$this->so->get_object_values($object_id);
		 if(!is_array($object_arr)) die(lang('unexpected error'));


		 if(!$quite)
		 {
			echo lang('we must upgrade your plugin configuration. <p>please don\'t click on back or stop untill I\'m finished and a close button appears</p>');
		 }

		 $plugin_string=$object_arr['plugins'];

		 $plugins=explode('|',str_replace('~','=',$plugin_string));

		 $errors=0;

		 foreach($plugins as $plugin)
		 {
			$sets=explode(':',$plugin);

			if($sets[3]) $conf_str = explode(';',$sets[3]);

			if(is_array($conf_str))
			{
			   unset($conf_arr);
			   foreach($conf_str as $conf_entry)
			   {
				  list($conf_key,$val)=explode('=',$conf_entry);	
				  $conf_arr[$conf_key]=$val;
			   }

			   $plugin_arr=array(
				  'name'=>$sets[1],
				  'conf'=>$conf_arr
			   );
			   $conf_serialed_string=base64_encode(serialize($plugin_arr));
			   $status=$this->so->save_field_plugin_conf($object_arr[object_id],$sets[0],$conf_serialed_string);					
			   if($status==1) $errors++;
			}
		 }

		 if($errors>0)
		 {
			echo lang('Some errors accured while upgrading. we have not touched the old settings so you can try again. If this error keeps coming back please contact your administrator or the JiNN developers<br/><br/>');
			die();
		 }
		 else
		 {
			$data[0]=array(
			   'name'=>'plugins',
			   'value'=> ''
			);
			$status = $this->so->update_phpgw_data('egw_jinn_objects',$data,'object_id',$object_arr[object_id]);

			// update object record with empty plugins field
			if(!$quite) echo lang('The old confguration data is replaced<br/><br/>');

		 }

		 if(!$quite) echo '<script language=""JavaScript"">window.opener.location.reload();</script>';
		 if(!$quite) echo lang('The upgrade process has is finished. You can now close this windows and start over again<br/><br/>');
		 if(!$quite) echo '<input type="button" onclick="self.close()" value="'.lang('close this window').'"/>';
		 if($quite) return $status;
	  }

   }


?>
