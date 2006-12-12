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

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.bojinn.inc.php');

   /**
   * boadmin 
   * 
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class boadmin extends bojinn 
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
		 'upgrade_plugins'=>True,
//		 'save_object_events_conf' => True
	  );

	  /**
	  * boadmin 
	  * 
	  * @access public
	  * @return void
	  */
	  function boadmin()
	  {
		 parent::bojinn();

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

		 $this->plug = CreateObject('jinn.factory_plugins_db_fields'); 
		 $this->plug->local_bo = $this;

		 //$this->plugins = $this->plug->plugins; //fixme: THIS WILL BREAK WHEN WE GET RID OF THE OLD STYLE PLUGINS

		 $this->object_events_plugin_manager = CreateObject('jinn.factory_plugins_object_events'); 
		 $this->object_events_plugin_manager->local_bo = $this;
		 $this->object_events_plugins = $this->object_events_plugin_manager->object_events_plugins;

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');


		 global $local_bo;
		 $local_bo=$this;
	  }


	  /**
	  * get_db_plug_arr 
	  * 
	  * @param mixed $plugin_name 
	  * @access public
	  * @return void
	  */
	  function get_db_plug_arr($plugin_name)
	  {
		 //$plugin = $this->plugins[$plugin_name]; //OLD STYLE plugins
		 //_debug_array($plugin_name);
		 $plugin_reg_arr = $this->plug->registry->plugins[$plugin_name]; //NEW STYLE plugins (classes)

		 if(!is_array($plugin_reg_arr))
		 {
			$alias = $this->plug->registry->aliases[$plugin_name]; //This plugin may be Depreciated. Try if an Alias has been defined
			$plugin_reg_arr = $this->plug->registry->plugins[$alias];
		 }

		 if(!is_array($plugin_reg_arr))
		 {
			return array();
		 }
		 else
		 {
			return $plugin_reg_arr;
		 }
	  }


	  /**
	  * get_field_array 
	  * 
	  * @param mixed $HTTP_POST_VARS 
	  * @access public
	  * @return void
	  */
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
	  * plugins_for_field_type: get all available db plugins for db field
	  *
	  * @param array $field_meta_arr contains all database field metadata
	  * @param string $plg_name this validates the current plugin name
	  * @return array with pluginnames
	  */
	  function plugins_for_field_type($field_meta_arr,$plg_name)
	  {

		 $jinn_fieldtype=$this->db_ftypes->complete_resolve($field_meta_arr);
		 //_debug_array($jinn_fieldtype);
		 $plugin_default=$this->plug->get_default_plugin($jinn_fieldtype);
		 $plugin_hooks=$this->plug->plugin_hooks($jinn_fieldtype);

		 $plugin_hooks=array_merge($plugin_default,$plugin_hooks);

		 $doublecheck = array();
		 foreach($plugin_hooks as $key => $plugin_hook)
		 {
			if(array_key_exists($plugin_hook[value], $doublecheck))
			{
			   unset($plugin_hooks[$key]);
			}
			else
			{
			   $doublecheck[$plugin_hook[value]] = $key;
			}
		 }
		 $plugin_hooks = array_values($plugin_hooks); //reorder the array
		 if(!array_key_exists($plg_name, $doublecheck))
		 {
			if(array_key_exists($plg_name, $this->plug->registry->aliases))
			{
			   $alias = $this->plug->registry->aliases[$plg_name];
			   $aliasname = $this->plug->registry->plugins[$alias]['title'];
			   $plugin_hooks[] = array('value' => $plg_name, 'name' => $plg_name.' (alias:'.$aliasname.')');
			}
			elseif($plg_name != '')
			{
			   $plugin_hooks[] = array('value' => $plg_name, 'name' => $plg_name.' (unknown)');
			}
		 }

		 return $plugin_hooks;
	  }


	  /**
	  * save_object_events_conf: save a new object events plugin configuration in the database
	  * 
	  * @note this function uses new standard method for returning exit codes and status information
	  * @access public
	  * @return void
	  */
	  //todo post via argument
	  function save_object_events_conf($obj_id,$edit)
	  {
		 if(!$_GET[object_id] && !$_GET[edit])
		 {
			die('error');
		 }

		 if(is_array($_POST))
		 {
			// make array with conf values
			$plg_post_arr=$this->filter_array_with_prefix($_POST,'EPL',true);
			$plg_post_arr=$this->strip_prefix_from_keys($plg_post_arr,'EPL');
			$plg_post_arr['event']=$_POST['event']; 
			$plg_post_arr['plugin']=$_POST['plugin'];

			//_debug_array($plg_post_arr);
			//_debug_array($_POST);
			//die();

			$dirty = false;

			//get the already stored configurations
			$object_arr=$this->so->get_object_values($_GET[object_id]);
			$stored_configs = unserialize(base64_decode($object_arr[events_config]));
			
			if($_FILES['iconupload']['tmp_name'])
			{
			   $stored_file_arr=$this->site_fs->save_obj_event_plugin_file_from_post($this->so->get_site_id_by_object_id($_GET[object_id]),$plg_post_arr['plugin']);	
			   $iconfile=$_FILES['iconupload']['name'];
			}
			elseif($_POST['icondelete']) 
			{
			   $iconfile='';
			   // $this->site_fs->remove_file($this->so->get_site_id_by_object_id($_GET[object_id]),$val,$plug_reg_conf_arr[$key][subdir]);	
			}
			else
			{
			   $iconfile=$_POST['iconfile'];
			}

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
			   if($plg_post_arr['event'] != '' && $plg_post_arr['plugin'] != '')
			   {

				  
				  if(!is_array($stored_configs)) $stored_configs = array();
				  $conf=array
				  (
					 'name'=>$plg_post_arr['plugin'],
					 'conf'=>$plg_post_arr,
					 'eventlabel'=>$_POST['eventlabel'],
				  );
				  if($iconfile) $conf['iconfile']=$iconfile;
				  $stored_configs[] = $conf;
				  $dirty=true;
			   }
			}
			else
			{
			   if(is_array($stored_configs)) 
			   {
				  //todo test
				  //$_POST[event]  = $stored_configs[$_GET[edit]][conf][event];
				  //$_POST[plugin] = $stored_configs[$_GET[edit]][conf][plugin];

				  //replace the existing config with this one
				  if(!is_array($stored_configs)) $stored_configs = array();
				  $conf=array
				  (
					 'name'=>$plg_post_arr['plugin'],
					 'conf'=>$plg_post_arr,
					 'eventlabel'=>$_POST['eventlabel'],
//					 'iconfile'=>$_POST['iconfile']
				  );
				  if($iconfile) $conf['iconfile']=$iconfile;
				  $stored_configs[$_GET[edit]] = $conf;
				  $dirty=true;
			   }
			}

			if($dirty)
			{
			   $conf_serialed_string=base64_encode(serialize($stored_configs));

			   $status=$this->so->save_object_events_plugin_conf($_GET[object_id],$conf_serialed_string);
			   if($status[error])
			   {
				  $this->addError(lang('Plugin configuration NOT saved.'));
			   }
			   else
			   {
				  $this->addInfo(lang('Plugin configuration successfully saved'));
			   }
			}
			else
			{
			   $this->addError(lang('nothing to save. Please select a plugin to delete or configure a new plugin'));
			}
		 }

		 //_debug_array($stored_configs);
		 
		 //fixme give correct status
		 return true;


		 //fixme: this gives a strange error:
		 //$this->common->exit_and_open_screen('menuaction=jinn.uiadmin.object_events_config&close_me=true&object_id='.$_GET[object_id]);

		 //VERY dirty hack to solve this problem:		 
		 //echo('<input class="egwbutton"  type="button" onClick="self.close()" value="'.lang('close').'"/>');
		 //obviously this needs to be fixed. Then also the above _debug_arrays can be removed.

	  }

	  /**
	  * insert_or_update_egw_jinn_site: insert new site in table 
	  * 
	  * @todo complete msg
	  * @access public
	  * @return void
	  */
	  function insert_or_update_egw_jinn_site()
	  {
		 $data=$this->http_vars_pairs($_POST,$_FILES);

		 if($_POST[where_value])
		 {
			$status=$this->so->update_phpgw_data('egw_jinn_sites',$data, $this->where_key,$this->where_value);
		 }
		 else
		 {
			unset($this->site);
			$status=$this->so->insert_phpgw_data('egw_jinn_sites',$data);
		 }

		 if($status[error])	
		 {
			$this->addError(lang('Site NOT succesfully saved, unknown error'));
		 }
		 else 
		 {
			$this->set_site_version_info($status[where_value]);
			$this->addInfo(lang('Site succesfully saved'));
		 }

		 return $status;
	  }

	  function set_site_version_info($site_id)
	  {
		 $data[] = array('name' => 'jinn_version', 'value' => $GLOBALS['phpgw_info']['apps']['jinn']['version']);
		 $data[] = array('name' => 'site_version', 'value' => ($this->site['site_version']+1));
		 
		 $status=$this->so->update_phpgw_data('egw_jinn_sites',$data, 'site_id',$site_id);
	  }

	  /**
	  * insert_egw_jinn_object 
	  * 
	  * @access public
	  * @return void
	  */
	  function insert_egw_jinn_object()
	  {
		 $data=$this->http_vars_pairs($_POST,$_FILES);
		 $status=$this->so->insert_phpgw_data('egw_jinn_objects',$data);

		 if ($status[error])	
		 {
			$this->addError(lang('Site Object NOT succesfully added, unknown error'));
		 }
		 else 
		 {
			$this->addInfo(lang('Site Object succesfully added'));
		 }
		 
		 if($_POST['continue'])
		 {
			$this->exit_and_open_screen('jinn.uiadmin.add_edit_object&where_key=object_id&where_value='.$status[where_value].'&serial='.$status[serial]);
		 }
		 else
		 {
			$this->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$_POST[FLDparent_site_id]);
		 }
	  }

	  /**
	  * update_egw_jinn_site: updates site meta data in database and return to form 
	  * 
	  * @todo remove 
	  * @access public
	  * @return void
	  */
	  function update_egw_jinn_site()
	  {
		 $table='egw_jinn_sites';

		 $data=$this->http_vars_pairs($_POST,$_FILES);

		 $status=$this->so->update_phpgw_data($table,$data, $this->where_key,$this->where_value);

		 if($status[error])
		 {
			$this->addError(lang('Site NOT succesfully saved, unknown error'));
		 }
		 else 
		 {
			$this->addInfo(lang('Site succesfully saved'));
		 }

		 if($_POST['continue'])
		 {
			//FIXME 
			$this->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$this->where_value);
		 }
		 else
		 {
			$this->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
		 }
	  }

	  //get all fieldsnames or only the primary
	  function get_fieldnames_by_table($table,$only_primary=false)
	  {
		 $site_id=$this->site['site_id'];
		 
		 $fields=$this->so->site_table_metadata($site_id,$table);
		 /**
		 * one to one new style
		 */
		 if($fields)
		 {
			if($only_primary)
			{
			   foreach($fields as $field)
			   {
				  if(preg_match("/primary_key/i", $field[flags]))
				  {
					 $fields_arr[]=$field[name];
				  }
			   }
			}
			else
			{
			   foreach($fields as $field)
			   {
				  $fields_arr[]=$field[name];
			   }
			}
		 }
 
		return $fields_arr;
	  }


	  /**
	  * save_relations: save all relations to the database
	  * 
	  * @param $object_id object to save the relations in
	  * @access public
	  * @return void
	  */
	  function save_relations($object_id)
	  {
		 $rel_arr=array();

		 //type1
		 $post_type1=$this->filter_array_with_prefix($_POST,'REL1XXX_',true);
		 $post_type1=$this->strip_prefix_from_keys($post_type1,'REL1XXX_');

		 $_tmpids1=$this->filter_array_with_prefix($post_type1,'ID',true);
		 $_tmpdispl1=$this->filter_array_with_prefix($post_type1,'DISPLAY',true);

		 if(is_array($_tmpids1))
		 {
			foreach($_tmpids1 as $idkey=>$idval)
			{
			   if(strlen($post_type1['ID'.$idval])<4) $post_type1['ID'.$idval]=uniqid('');

			   $type1[id]=$post_type1['ID'.$idval];
			   $type1[type]=$post_type1['TYPE'.$idval];
			   $type1[local_key]=$post_type1['LOCAL_KEY'.$idval];
			   $type1[foreign_table]=$post_type1['FOREIGN_TABLE'.$idval];
			   $type1[foreign_key]=$post_type1['FOREIGN_KEY'.$idval];

			   unset($disp_arr);
			   foreach($_tmpdispl1 as $dsplkey=>$dsplval)
			   {
				  if(  substr($dsplkey,-strlen($idval) )==$idval )
				  {
					 $disp_arr[]=$dsplval;					
				  }
			   }

			   $type1[foreign_showfields]=serialize($disp_arr);

			   $rel_arr[]=$type1;
			}
		 }

		 //type2
		 $post_type2=$this->filter_array_with_prefix($_POST,'REL2XXX_',true);
		 $post_type2=$this->strip_prefix_from_keys($post_type2,'REL2XXX_');

		 $_tmpids2=$this->filter_array_with_prefix($post_type2,'ID',true);
		 $_tmpdispl2=$this->filter_array_with_prefix($post_type2,'DISPLAY',true);

		 if(is_array($_tmpids2))
		 {
			foreach($_tmpids2 as $idkey=>$idval)
			{
			   if(strlen($post_type2['ID'.$idval])<4) $post_type2['ID'.$idval]=uniqid('');
			   $type2[id]=$post_type2['ID'.$idval];
			   $type2[type]=$post_type2['TYPE'.$idval];
			   
			   $type2[foreign_table]=$post_type2['FOREIGN_TABLE'.$idval];
			   
			   // GET LOCAL PRIMARY KEY
			   $object_arr=$this->so->get_object_values($object_id);
			   $lkeyprim_arr=$this->get_fieldnames_by_table($object_arr['table_name'],true);
			   if(is_array($lkeyprim_arr))
			   {
				  $type2[local_key]=$lkeyprim_arr[0];
			   }

			   $rkeyprim_arr=$this->get_fieldnames_by_table($type2[foreign_table],true);
			   if(is_array($rkeyprim_arr))
			   {
				  $type2[foreign_key]=$rkeyprim_arr[0];
			   }


			   //must we reset the connection table yes or no
			   //disblable this automatic block
			   if($x=='xxxx' && $post_type2['RESETCONNECTTABLE'.$idval]=='1' || !$post_type2['CONNECT_KEY_LOCAL'.$idval] || !$post_type2['CONNECT_KEY_LOCAL'.$idval] )
			   {
				  $connection_table='JM2MCONN_'.$_POST['LOCAL_TABLE'].'_'.$type2[foreign_table];

				  $left_field = $type2[local_key].'_'.$_POST['LOCAL_TABLE'].'_l';
				  $right_field= $type2[foreign_key].'_'.$type2[foreign_table].'_f';

				  $type2[connect_key_local]= $connection_table.'.'.$left_field;
				  $type2[connect_key_foreign]= $connection_table.'.'.$right_field;

				  $create_fields[]=array(
					 'name'=> $left_field,
					 'type'=> 'int'
				  );
				  $create_fields[]=array(
					 'name'=> $right_field,
					 'type'=>'int'
				  );

				  //FIXME GET SITE ID FROM THIS_SITE
				  $site_id=$this->so->get_site_id_by_object_id($object_id);
				  $this->so->site_table_exist_or_create($site_id,$connection_table,$create_fields);

				  // check if connectiontable exist and else create it
			   }
			   else
			   {
				  $type2[connect_table]=$post_type2['CONNECT_TABLE'.$idval];
				  $type2[connect_key_local]=$post_type2['CONNECT_KEY_LOCAL'.$idval];
				  $type2[connect_key_foreign]=$post_type2['CONNECT_KEY_FOREIGN'.$idval];
			   }
			   //_debug_array($post_type2);

			   unset($disp_arr);
			   foreach($_tmpdispl2 as $dsplkey=>$dsplval)
			   {
				  if(  substr($dsplkey,-strlen($idval) )==$idval )
				  {
					 $disp_arr[]=$dsplval;					
				  }
			   }

			   $type2[foreign_showfields]=serialize($disp_arr);

			   $rel_arr[]=$type2;
			}
		 }

		 //type3
		 $post_type3=$this->filter_array_with_prefix($_POST,'REL3XXX_',true);
		 $post_type3=$this->strip_prefix_from_keys($post_type3,'REL3XXX_');

		 $_tmpids3=$this->filter_array_with_prefix($post_type3,'ID',true);

		 if(is_array($_tmpids3))
		 {
			foreach($_tmpids3 as $idkey=>$idval)
			{
			   if(strlen($post_type3['ID'.$idval])<4) $post_type3['ID'.$idval]=uniqid('');
			   $type3[id]=$post_type3['ID'.$idval];
			   $type3[type]=$post_type3['TYPE'.$idval];
			   $type3[local_key]=$post_type3['LOCAL_KEY'.$idval];
			   $type3[foreign_table]=$post_type3['FOREIGN_TABLE'.$idval];
			   $type3[foreign_key]=$post_type3['FOREIGN_KEY'.$idval];
			   $type3[object_conf]=$post_type3['OBJECT_CONF'.$idval];

			   $rel_arr[]=$type3;
			}
		 }

		 //type4
		 $post_type4=$this->filter_array_with_prefix($_POST,'REL4XXX_',true);
		 $post_type4=$this->strip_prefix_from_keys($post_type4,'REL4XXX_');

		 $_tmpids4=$this->filter_array_with_prefix($post_type4,'ID',true);

		 if(is_array($_tmpids4))
		 {
			foreach($_tmpids4 as $idkey=>$idval)
			{
			   if(strlen($post_type4['ID'.$idval])<4) $post_type4['ID'.$idval]=uniqid('');
			   $type4[id]=$post_type4['ID'.$idval];
			   $type4[type]=$post_type4['TYPE'.$idval];
			   $type4[local_key]=$post_type4['LOCAL_KEY'.$idval];
			   $type4[foreign_table]=$post_type4['FOREIGN_TABLE'.$idval];
			   $type4[foreign_key]=$post_type4['FOREIGN_KEY'.$idval];
			   $type4[object_conf]=$post_type4['OBJECT_CONF'.$idval];

			   $rel_arr[]=$type4;
			}
		 }

		 $relations=base64_encode(serialize($rel_arr));

		 $data[0]=array
		 (
			'name'=>'relations',
			'value'=> $relations
		 );

		 $status = $this->so->update_phpgw_data('egw_jinn_objects',$data,'object_id',$object_id);

		 if ($status[error])	
		 {
			$this->addError(lang('Relation NOT succesfully saved, unknown error'));
		 }
		 else 
		 {
			$this->set_site_version_info($this->site['site_id']);
			$this->addInfo(lang('Relation succesfully saved'));
		 }


		 return $ret_arr;	
	  }


	  /**
	  * update_egw_jinn_object 
	  * 
	  * @access public
	  * @return void
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
			$relations_to_delete=$this->filter_array_with_prefix($_POST,'DEL');
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
				  /*				  case 'PLG': //plugin type
					 $plugin[name]=$field[value];
					 break;
					 */
					 /*				  case 'PLC': //plugin configuration
						$plugin[conf]=$field[value];
						$conf_serialed_string=base64_encode(serialize($plugin)); 
						break;
						*/
					 case 'DEF': //show in listview by default?
						$show_default=$field[value];
						break;
					 case 'SHW': //show in formview by default?
						$show_in_form=$field[value];
						break;
					 case 'POS': //position of field in listview
						$position=$field[value];

						//BEWARE: if new properties are added, make sure the LAST one ends with saving the record!
						//POS is the last field, so now update the object field record:
						$status=$this->so->save_field($this->where_value,$field[name],$conf_serialed_string,$show_default, $show_in_form,$position);
						break;
					 default:
						break;
				  }
			   }
			}

			$data=$this->http_vars_pairs($_POST,$_FILES);
			$status=$this->so->update_phpgw_data($table,$data, $this->where_key,$this->where_value);

			if ($status[error])	
			{
			   $this->addError(lang('Site Object NOT succesfully saved, unknown error'));
			}
			else 
			{
			   $this->addInfo(lang('Site Object succesfully saved'));
			}

			if($_POST['continue'])
			{
			   $this->exit_and_open_screen('jinn.uiadmin.add_edit_object&where_key='.$this->where_key.'&where_value='.$this->where_value);
			}
			else
			{
			   $this->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$_POST[FLDparent_site_id]);
			}
		 }

		 /**
		 * del_egw_jinn_site abstract delelte site and return to list
		 * 
		 * @access public
		 * @return void
		 */
		 function del_egw_jinn_site()
		 {
			$status=$this->so->delete_phpgw_data('egw_jinn_sites',$this->where_key,$this->where_value);

			$removedir=$this->site_fs->get_jinn_sitefile_path($this->where_value);
			if(is_dir($removedir))
			{
			   if($this->site_fs->remove_site_files($removedir,true))
			   {
				  $this->addInfo(lang('Site files succesfully deleted'));
			   }
			   else
			   {
				  $this->addError(lang('Could not delete site files. Please remove manually'));
			   }
			}

			if ($status[error])
			{
			   $this->addError(lang('Site NOT succesfully deleted, Unknown error'));
			}
			else 
			{
			   $this->addInfo(lang('site succesfully deleted'));
			}
			$this->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
		 }


		 /**
		 * del_mult_egw_jinn_sites: delete multiple sites record and dauhter objects
		 * 
		 * @todo: normal re turn value
		 * @todo: give status msg
		 * @todo: also delete object_fields
		 * @access public
		 * @return void
		 */
		 function del_mult_egw_jinn_sites()
		 {
			$delete_arr=$this->filter_array_with_prefix($_POST,'sitedel');

			foreach($delete_arr as $del_site)
			{
			   $status=$this->so->delete_phpgw_data('egw_jinn_objects','parent_site_id ',$del_site);
			   $status=$this->so->delete_phpgw_data('egw_jinn_sites','site_id',$del_site);
			}

			if ($status==1)	$this->addInfo(lang('Sites succesfully deleted'));
			else $this->addError(lang('Sites NOT succesfully deleted, Unknown error'));

			return;
		 }

		 /**
		 * del_mult_egw_jinn_object: delete multiple object record
		 * del_mult_egw_jinn_object 
		 * 
		 * @todo: normal return value
		 * @todo: give status msg
		 * @access public
		 * @return void
		 */
		 function del_mult_egw_jinn_object()
		 {
			$delete_arr=$this->filter_array_with_prefix($_POST,'objdel');

			foreach($delete_arr as $del_obj)
			{
			   $status=$this->so->delete_phpgw_data('egw_jinn_obj_fields','field_parent_object ',$del_obj);
			   $status=$this->so->delete_phpgw_data('egw_jinn_objects','object_id',$del_obj);
			}

			if ($status==1)	$this->addInfo(lang('Site Objects succesfully deleted'));
			else $this->addError(lang('Site Objects NOT succesfully deleted, Unknown error'));

			return;
		 }

		 /**
		 * get_phpgw_records 
		 * 
		 * @param mixed $table 
		 * @param mixed $where_key 
		 * @param mixed $where_value 
		 * @param mixed $offset 
		 * @param mixed $limit 
		 * @param $value_reference can be 'name' or 'num'
		 * @param mixed $order_by 
		 * @access public
		 * @return void
		 */
		 function get_phpgw_records($table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by=false)
		 {
			if (!$value_reference)
			{
			   $value_reference='num';
			}

			$records = $this->so->get_phpgw_record_values($table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by);
			if(!is_array($records))
			{
			   return array();
			}

			return $records;
		 }

		 /**
		 * http_vars_pairs 
		 * 
		 * @param mixed $HTTP_POST_VARS 
		 * @param mixed $HTTP_POST_FILES 
		 * @access public
		 * @return void
		 */
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
		 * http_vars_pairs_plugins: make array with pairs of keys and values from http_post_vars 
		 *  
		 * 
		 * @note try this with filter_array_with_prefix
		 * @param mixed $HTTP_POST_VARS 
		 * @access public
		 * @return void
		 */
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

		 /**
		 * upgrade_plugins 
		 * 
		 * @param mixed $object_id 
		 * @param mixed $quite 
		 * @access public
		 * @return void
		 */
		 function upgrade_plugins($object_id=false,$quite=false)
		 {

			if($object_id)
			{
			   $object_arr=$this->so->get_object_values($object_id);
			}
			elseif($_GET[object_id])
			{
			   $object_arr=$this->so->get_object_values($_GET[object_id]);
			}

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
			if(!$quite) echo '<input class="egwbutton"  type="button" onclick="self.close()" value="'.lang('close this window').'"/>';
			if($quite) return $status;
		 }

	  }


   ?>
