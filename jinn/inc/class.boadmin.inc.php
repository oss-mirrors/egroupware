<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

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

   class boadmin 
   {
	  var $public_functions = Array(
		 'del_egw_jinn_sites'=> True,
		 'del_egw_jinn_objects' => True,
		 'insert_egw_jinn_sites'=> True,
		 'insert_egw_jinn_objects'=> True,
		 'update_egw_jinn_sites'=> True,
		 'update_egw_jinn_objects' => True,
		 'access_rights'=> True,
		 'save_access_rights_object'=> True,
		 'save_access_rights_site'=> True,
		 'save_field_plugin_conf' => True,
		 'save_field_info_conf' => True
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

	  var $where_key;
	  var $where_value;

	  var $plugins;

	  var $db_ftypes;
	  function boadmin()
	  {
		 $this->common = CreateObject('jinn.bocommon');

		 $this->so = CreateObject('jinn.sojinn');
		 $this->current_config=$this->common->get_config();		

		 $this->read_sessiondata();
		 $this->use_session = True;

		 $_form = $GLOBALS['HTTP_POST_VARS']['form'] ? $GLOBALS['HTTP_POST_VARS']['form']   : $GLOBALS['HTTP_GET_VARS']['form'];
		 $_action = $GLOBALS['HTTP_POST_VARS']['action'] ? $GLOBALS['HTTP_POST_VARS']['action']   : $GLOBALS['HTTP_GET_VARS']['action'];
		 $_site_id = $GLOBALS['HTTP_POST_VARS']['site_id'] ? $GLOBALS['HTTP_POST_VARS']['site_id']   : $GLOBALS['HTTP_GET_VARS']['site_id'];
		 $_site_object_id = $GLOBALS['HTTP_POST_VARS']['site_object_id'] ? $GLOBALS['HTTP_POST_VARS']['site_object_id']    : $GLOBALS['HTTP_GET_VARS']['site_object_id'];

		 $_where_key = $GLOBALS['HTTP_POST_VARS']['where_key'] ? $GLOBALS['HTTP_POST_VARS']['where_key']    : $GLOBALS['HTTP_GET_VARS']['where_key'];

		 $_where_value = $GLOBALS['HTTP_POST_VARS']['where_value'] ? $GLOBALS['HTTP_POST_VARS']['where_value']    : $GLOBALS['HTTP_GET_VARS']['where_value'];

		 if(!empty($_where_key))
		 {
			$this->where_key  = $_where_key;
		 }

		 if(!empty($_where_value))
		 {
			$this->where_value  = $_where_value;
		 }

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

		 $this->plug = CreateObject('jinn.plugins'); //$this->include_plugins();
		 $this->plug->local_bo = $this;
		 $this->plugins = $this->plug->plugins;
		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');

		 global $local_bo;
		 $local_bo=$this;
	  }

	  function save_sessiondata()
	  {
		 $data = array(
			'message' => $this->message, # this must be recplaced with new message
			'site_id' => $this->site_id,
			'site_object_id' => $this->site_object_id,
			'last_where_string'=>$this->last_where_string
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
			$this->last_where_string = $data['last_where_string'];
		 }
	  }

	  /****************************************************************************\
	  * delete record from external table                                          *
	  \****************************************************************************/

	  function delete_phpgw_data($table,$where_key,$where_value)
	  {

		 return $status;
	  }

	  /****************************************************************************\
	  * insert data into phpgw table                                               *
	  \****************************************************************************/

	  function insert_phpgw_data($table,$HTTP_POST_VARS,$HTTP_POST_FILES)
	  {

		 $data=$this->http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES);
		 $status=$this->so->insert_phpgw_data($table,$data);

		 return $status;
	  }

	  /****************************************************************************\
	  * update data in phpgw table                                                 *
	  \****************************************************************************/

	  function update_phpgw_data($table,$HTTP_POST_VARS,$HTTP_POST_FILES,$where_key,$where_value)
	  {

		 /*************************************
		 * start relation section             *
		 *************************************/

		 if ($HTTP_POST_VARS[FLDrelations])
		 {
			// check if there are relations to delete
			$relations_to_delete=$this->common->filter_array_with_prefix($HTTP_POST_VARS,'DEL');
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

		 // check if new ONE TO ONE relation parts are complete else drop them
		 if($HTTP_POST_VARS['3_relation_org_field'] && $HTTP_POST_VARS['3_relation_table_field'] 
		 && $HTTP_POST_VARS['3_relation_object_conf'])
		 {
			$new_relation='3:'.$HTTP_POST_VARS['3_relation_org_field'].':null:'.$HTTP_POST_VARS['3_relation_table_field']
			.':'.$HTTP_POST_VARS['3_relation_object_conf'];

			if ($HTTP_POST_VARS['FLDrelations']) $HTTP_POST_VARS['FLDrelations'].='|';
			$HTTP_POST_VARS['FLDrelations'].=$new_relation;
		 }

		 // check all pluginfield for values
		 // put values in http_post_var

		 $plug_data=$this->http_vars_pairs_plugins($HTTP_POST_VARS);
		 if(is_array($plug_data))
		 {
			while (list ($key, $arr) = each ($plug_data))
			{
			   $conf_serialed_string=base64_encode(serialize($arr)); 
			   $status=$this->so->save_field_plugin_conf($where_value,$key,$conf_serialed_string);
			}
		 }

		 $data=$this->http_vars_pairs($HTTP_POST_VARS,$HTTP_POST_FILES);
		 $status=$this->so->update_phpgw_data($table,$data, $where_key,$where_value);

		 return $status;
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
			$this->message[error]=lang('An unknown error has occured (error code 110)');
		 }
		 else
		 {
			$this->message[info]=lang('Field info configuration successfully saved');
		 }
		 $this->save_sessiondata();

		 $this->common->exit_and_open_screen('jinn.uiadmin.field_help_config&field_name='.$_GET[field_name].'&object_id='.$_GET[object_id]);
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
			$this->message[error]=lang('An unknown error has occured (error code 109)');
		 }
		 else
		 {
			$this->message[info]=lang('Plugin configuration successfully saved');
		 }
		 $this->save_sessiondata();

		 $this->common->exit_and_open_screen('jinn.uiadmin.plug_config&plug_orig='.$_GET[plug_name].'&close_me=true&plug_name='.$_GET[plug_name].'&hidden_name=CFG_PLG'.$_GET[plug_name].'&=&field_name='.$_GET[plug_name].'&object_id='.$_GET[object_id].'&hidden_val=');
	  }



/*	  f unction save_access_rights_site()
	  {
		 reset ($GLOBALS[HTTP_POST_VARS]);
		 $site_id=$GLOBALS[HTTP_POST_VARS]['site_id'];

		 while (list ($key, $val) = each ($GLOBALS[HTTP_POST_VARS])) 
		 {
			if (substr($key,0,6)=='editor')	$editors[]=$val;
		 }

		 if (is_array($editors)) $editors=array_unique($editors);

		 $status=$this->so->update_site_access_rights($editors,$site_id);

		 if ($status==1)	$this->message[info]=lang('Access rights for Site succesfully editted');
		 else $this->message[error]=lang('Access rights for Site NOT succesfully editted');

		 $this->save_sessiondata();
		 //			$this->common->exit_and_open_screen('jinn.uiadmin.access_rights');
		 // FIXME keep nextmatch sorting filter etc...
		 $this->common->exit_and_open_screen('jinn.uiadmin.set_access_rights_sites&site_id='.$site_id);
	  }


	  f unction save_access_rights_object()
	  {
		 reset ($GLOBALS[HTTP_POST_VARS]);
		 $site_id=$GLOBALS[HTTP_POST_VARS]['site_id'];
		 $object_id=$GLOBALS[HTTP_POST_VARS]['object_id'];

		 while (list ($key, $val) = each ($GLOBALS[HTTP_POST_VARS])) 
		 {
			if (substr($key,0,6)=='editor')	$editors[]=$val;
			//if (substr($key,0,7)=='xeditor')	$existing_editors[]=$val;//existing editors
		 }

		 if (is_array($editors)) $editors=array_unique($editors);

		 $status=$this->so->update_object_access_rights($editors,$GLOBALS[HTTP_POST_VARS]['object_id']);

		 if ($status==1)	$this->message[info]=lang('Access rights for site-object succesfully editted');
		 else $this->message[error]=lang('Access rights for site-object NOT succesfully editted');

		 $this->save_sessiondata();

		 //			$this->common->exit_and_open_screen('jinn.uiadmin.access_rights');
		 // FIXME keep nextmatch sorting filter etc...
		 $this->common->exit_and_open_screen('jinn.uiadmin.set_access_rights_site_objects&object_id='.$object_id.'&site_id='.$site_id);
	  }
*/
	  // FIXME rename
	  function insert_egw_jinn_sites()
	  {
		 $table='egw_jinn_sites';

		 $status=$this->insert_phpgw_data($table,$GLOBALS[HTTP_POST_VARS],$GLOBAL[HTTP_POST_FILES]);

		 if ($status>0)	
		 {
			$this->message[info]=lang('Site succesfully added');
		 }
		 else 
		 {
			$this->message[error]=lang('Site NOT succesfully added, unknown error');
		 }

		 $this->save_sessiondata();
		 if($GLOBALS[HTTP_POST_VARS]['continue'])
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$status[where_value].'&serial='.$status[serial]);
			_debug_array($serial);
			die();
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
		 }
	  }



	  // FIXME rename
	  function insert_egw_jinn_objects()
	  {
		 $status=$this->insert_phpgw_data('egw_jinn_objects',$GLOBALS[HTTP_POST_VARS],$GLOBAL[HTTP_POST_FILES]);
		 if ($status>0)	$this->message[info]=lang('Site Object succesfully added');
		 else $this->message[error]=lang('Site Object NOT succesfully added, unknown error');

		 $this->save_sessiondata();
		 if($GLOBALS[HTTP_POST_VARS]['continue'])
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_object&where_key=object_id&where_value='.$status[where_value].'&serial='.$status[serial]);
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$GLOBALS[HTTP_POST_VARS][FLDparent_site_id]);
		 }
	  }

	  // FIXME rename
	  function update_egw_jinn_sites()
	  {
		 $table='egw_jinn_sites';

		 $status = $this->update_phpgw_data($table,$_POST,$_POST,$this->where_key,$this->where_value);
		 if ($status[ret_code]==0)	$this->message[info]=lang('Site succesfully saved');
		 else $this->message[error]=lang('Site NOT succesfully saved, unknown error');

		 $this->save_sessiondata();
		 if($GLOBALS[HTTP_POST_VARS]['continue'])
		 {
			//FIXME 
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$this->where_value);
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
		 }
	  }

	  function update_egw_jinn_objects()
	  {
		 $table='egw_jinn_objects';

		 $status = $this->update_phpgw_data($table,$GLOBALS[HTTP_POST_VARS],$GLOBAL[HTTP_POST_FILES],$this->where_key,$this->where_value);

		 if ($status[ret_code]==0)	$this->message[info]=lang('Site Object succesfully saved');
		 else $this->message[error]=lang('Site Object NOT succesfully saved, unknown error');

		 $this->save_sessiondata();
		 if($GLOBALS[HTTP_POST_VARS]['continue'])
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_object&where_key='.$this->where_key.'&where_value='.$this->where_value);
		 }
		 else
		 {
			$this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$GLOBALS[HTTP_POST_VARS][FLDparent_site_id]);
		 }
	  }

	  // FIXME rename
	  function del_egw_jinn_sites()
	  {
		 //			var_dump($this->where_value);
		 //			die();
		 $status=$this->so->delete_phpgw_data('egw_jinn_sites',$this->where_key,$this->where_value);

		 if ($status==1)	$this->message[info]=lang('site succesfully deleted');
		 else $this->message[error]=lang('Site NOT succesfully deleted, Unknown error');

		 $this->save_sessiondata();
		 $this->common->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
	  }

	  //FIXME rename
	  function del_egw_jinn_objects()
	  {
		 $records = $this->so->get_phpgw_record_values('egw_jinn_objects',$this->where_key,$this->where_value,'','','name');	

		 $status=$this->so->delete_phpgw_data('egw_jinn_objects',$this->where_key,$this->where_value);

		 if ($status==1)	$this->message[info]=lang('Site Object succesfully deleted');
		 else $this->message[error]=lang('Site Object NOT succesfully deleted, Unknown error');

		 $this->save_sessiondata();

		 $this->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$records['0']["parent_site_id"]);

	  }

	  function get_phpgw_records($table,$where_key,$where_value,$offset,$limit,$value_reference)
	  {
		 if (!$value_reference)
		 {
			$value_reference='num';
		 }

		 $records = $this->so->get_phpgw_record_values($table,$where_key,$where_value,$offset,$limit,$value_reference);

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
		 //		 if(!$quite) 
		 if($quite) return $status;
	  }

   }


?>
