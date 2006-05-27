<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002 - 2006 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.eGroupware.org

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

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   /**
   * uiadmin this file is startpoint for all admin functions
   * 
   * @package jinn_core
   * @uses uijinn
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class uiadmin extends uijinn
   {
	  var $public_functions = Array(
		 'index' => True,
		 'import_object' => True,
		 'add_edit_site' => True,
		 'add_edit_object' => True,
		 'del_egw_jinn_object'=>True,
		 'browse_egw_jinn_sites' => True,
		 'export_site'=> True,
		 'export_object'=> True,
		 'edit_field_props'=> True,
		 'object_events_config'=> True,
		 'add_element' => True,
		 'edit_this_jinn_site'=> True,
		 'edit_this_jinn_site_object'=> True,
		 'test_db_access'=> True,
		 'edit_gen_obj_options'=> True,
		 'edit_relation_widgets'=>True,
		 'xmlhttpreq_get_fields'=>True,
		 'xmlhttp_req_toggle_field_visible'=>True,
		 'xmlhttp_req_toggle_field_listvisible'=>True,
		 'xmlhttp_req_toggle_field_enabled'=>True,
		 'field_help_config'=>True,
	  );

	  /**
	  * uiadmin 
	  * 
	  * @access public
	  * @return void
	  */
	  function uiadmin()
	  {
		 $this->bo = CreateObject('jinn.boadmin');
		 parent::uijinn();

		 $this->app_title = lang('Administrator Mode');

		 $this->permissionCheck();
	  }
	  
	  /**
	  * index 
	  * 
	  * @fixme remove?
	  * @access public
	  * @return void
	  */
	  function index()
	  {
		 $this->header(lang('index'));
		 $this->msg_box();
	  }	
	  
	  /**
	  * del_egw_jinn_object: delete table_object and return to parent site
	  * 
	  * @access public
	  * @return void
	  */
	  function del_egw_jinn_object()
	  {
		 //$status=$this->bo->so->delete_phpgw_data('egw_jinn_obj_fields','field_parent_object ',$_GET[where_value]);
		 $status=$this->bo->so->delete_phpgw_data('egw_jinn_objects','object_id',$_GET[where_value]);

		 if ($status==1)	
		 {
			$this->bo->set_site_version_info($this->bo->site['site_id']);
			$this->bo->addInfo(lang('Site Object succesfully deleted'));
		 }
		 else 
		 {
			$this->bo->addError(lang('Site Object NOT succesfully deleted, Unknown error'));
		 }

		 $this->edit_this_jinn_site();
		 //$this->exit_and_open_screen('jinn.uiadmin.add_edit_site&this_site=true');
	  }


	  /**
	  * edit_this_jinn_site 
	  * 
	  * @access public
	  * @return void
	  */
	  function edit_this_jinn_site()
	  {
		 $this->edit_this_site=true;
		 $this->add_edit_site();
	  }

	  /**
	   * add_edit_site 
	   * 
	   * @access public
	   * @return void
	   */
	  function add_edit_site()
	  {
		 if($_GET['this_site'] || $this->edit_this_site)
		 {
			$this->bo->where_key='site_id';
			$this->bo->where_value=$this->bo->session['site_id'];
		 }

		 if (!is_object($GLOBALS['phpgw']->js))
		 {
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
		 }
		 if (!strstr($GLOBALS['phpgw_info']['flags']['java_script'],'jinn'))
		 {
			$GLOBALS['phpgw']->js->validate_file('jinn','display_func','jinn');
		 }

		 $where_key='site_id';//stripslashes($this->bo->where_key);
		 $where_value=stripslashes($this->bo->where_value);

		 if($_POST[submitted]=='true')
		 {
			if($_POST[action]=='delete_mult_objects')
			{
			   $status=$this->bo->del_mult_egw_jinn_object();	
			}
			else
			{
			   $status=$this->bo->insert_or_update_egw_jinn_site();
			   if($status[where_value])
			   {
				  $where_value=$status[where_value];
				  $this->bo->sessionmanager->sessionarray->site_id=$where_value;
				  $this->bo->session['site_id']=$where_value;
				  $this->bo->sessionmanager->save();
			   }
			}
		 }

		 if ($where_key && $where_value)
		 {
			$this->header(lang('Edit Site'));

			$_site_vals_arr=$this->bo->get_phpgw_records('egw_jinn_sites',$where_key,$where_value,'','','name');
			$this->tplsav2->site_values=$_site_vals_arr[0];

			$this->tplsav2->where_key=$where_key;
			$this->tplsav2->where_value=$where_value;
		 }
		 else
		 {
			$this->header(lang('New Site'));

			$parent_site_id=$_POST['parent_site_id'];
		 }

		 $this->msg_box();

		 $this->tplsav2->assign('helplink',$GLOBALS[phpgw]->link('/manual/index.php'));
		 $this->tplsav2->form_action=$GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.uiadmin.add_edit_site");
		 $this->tplsav2->test_access_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.test_db_access');
		 $this->tplsav2->onclick_export=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.export_site&where_key=site_id&where_value='.$_site_vals_arr[0][site_id]);
		 $this->tplsav2->onclick_export_to_xml=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.exportsite.save_site_to_xml&where_key=site_id&where_value='.$_site_vals_arr[0][site_id]);

		 /* list objects for this site */
		 if ($where_key && $where_value)
		 {
			$new_where_key='parent_'.$where_key;

			$this->tplsav2->link_add_object=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_gen_obj_options&new=true&reloadparent=true&parent_site_id='.$where_value);
			$this->tplsav2->link_import_object=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.import_object&where_key=site_id&where_value='.$where_value);

			$this->tplsav2->icon_del=$GLOBALS[phpgw]->common->image('phpgwapi','delete');
			$this->tplsav2->icon_edit=$GLOBALS[phpgw]->common->image('phpgwapi','edit');
			$this->tplsav2->icon_export=$GLOBALS[phpgw]->common->image('phpgwapi','filesave');

			$_object_records = $this->bo->get_phpgw_records('egw_jinn_objects','parent_site_id',$where_value,'','','name',"ORDER BY name ASC");
			if ($_object_records)
			{
			   foreach($_object_records as $recordvalues)
			   {
				  //CONVERT RELATIONS TO NEW STYLE AND MOVE THEM TO RELATION TABLE
				  //TODO alert for old one2one relations
				  //TOD move relations to new table
				  if(trim($recordvalues['relations']))
				  {
					 if($recordvalues['relations']=='YToxOntpOjA7Tjt9')
					 {
						$recordvalues['num_relations']=0;
					 }
					 elseif(strstr($recordvalues['relations'],':'))
					 {
						$_rel=explode('|',$recordvalues['relations']);
						$recordvalues['num_relations']=count($_rel);
						$recordvalues[old_rel]=true;

						$this->tplsav2->did_upgrade=true;

						// update db to new relations mappings 

						$relations_field=$recordvalues[relations];

						$relations_arr = unserialize(base64_decode($relations_field));

						if(!is_array($relations_arr))
						{
						   $relations_arr=$this->bo->bcompat->convert_old_relations($relations_field);
						}

						$relations_arr = $this->bo->bcompat->convert_2modern_relations($relations_arr);

						$new_relations=base64_encode(serialize($relations_arr));

						$data[0]=array
						(
						   'name'=>'relations',
						   'value'=> $new_relations
						);

						$status = $this->bo->so->update_phpgw_data('egw_jinn_objects',$data,'object_id',$recordvalues[object_id]);
						if ($status[error])	
						{
						   $this->bo->addError(lang('Problems while upgrading %1 relation(s) in %2.',$recordvalues['num_relations'],$recordvalues['name']));
						}
						else 
						{
						   $this->bo->addInfo(lang('Upgraded %1 relation(s) in %2.',$recordvalues['num_relations'],$recordvalues['name']));
						}
						unset($status);
					 }
					 else
					 {
						$_rel=unserialize(base64_decode($recordvalues['relations']));
						$recordvalues['num_relations']=count($_rel);
					 }
				  }

				  if( $recordvalues['plugins'])
				  {
					 $recordvalues[upgrade]=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.boadmin.upgrade_plugins&site_id=".$recordvalues[parent_site_id]."&object_id=".$recordvalues[object_id]);
				  }

				  $recordvalues[link_edit] = $GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiu_edit_record.dev_edit_record&site_id=".$recordvalues[parent_site_id]."&site_object_id=".$recordvalues[object_id]);

				  //$recordvalues[link_del]=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.boadmin.del_egw_jinn_object&where_key=object_id&where_value=".$recordvalues[object_id]);
				  $recordvalues[link_del]=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.del_egw_jinn_object&where_key=object_id&where_value=".$recordvalues[object_id]);

				  $recordvalues[link_export]=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.export_object&where_key=object_id&where_value=".$recordvalues[object_id]);

				  $this->tplsav2->object_records[]=$recordvalues;
			   }

			}
		 }

		 $this->tplsav2->display('frm_conf_site_tpl.tpl.php');	 
	  }

	  /**
	  * browse_egw_jinn_sites: list all sites 
	  *
	  * @todo rename to list sites
	  */
	  function browse_egw_jinn_sites()
	  {
		 if($_POST[submitted]=='true')
		 {
			if($_POST[action]=='delete_mult_sites')
			{
			   $status=$this->bo->del_mult_egw_jinn_sites();	
			}
		 }

		 if (!is_object($GLOBALS['phpgw']->js))
		 {
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
		 }
		 if (!strstr($GLOBALS['phpgw_info']['flags']['java_script'],'jinn'))
		 {
			$GLOBALS['phpgw']->js->validate_file('jinn','display_func','jinn');
		 }	 

		 $this->header(lang('List Sites'));

		 $this->msg_box();

		 $this->tplsav2->helplink=$GLOBALS[phpgw]->link('/manual/index.php');
		 $this->tplsav2->link_add_site=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.add_edit_site");
		 $this->tplsav2->link_import_site=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.ui_importsite.import_egw_jinn_site");

		 $this->tplsav2->icon_del=$GLOBALS[phpgw]->common->image('phpgwapi','delete');
		 $this->tplsav2->icon_edit=$GLOBALS[phpgw]->common->image('phpgwapi','edit');
		 $this->tplsav2->icon_export=$GLOBALS[phpgw]->common->image('phpgwapi','filesave');

		 $records=$this->bo->get_phpgw_records('egw_jinn_sites','','','','','name');
		 if (count($records)>0)
		 {
			foreach($records as $recordvalues)
			{
			   $objects=$this->bo->get_phpgw_records('egw_jinn_objects','parent_site_id',$recordvalues[site_id],'','','name');
			   $recordvalues[num_objects]= @count($objects);

			   $recordvalues[link_edit] = $GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.add_edit_site&site_id=".$recordvalues[site_id]."&where_value=".$recordvalues[site_id]."&where_key=site_id");
			   $recordvalues[link_del]=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.boadmin.del_egw_jinn_site&where_key=site_id&where_value=".$recordvalues[site_id]);
			   $recordvalues[link_export]=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.export_site&where_key=site_id&where_value=".$recordvalues[site_id]);

			   $this->tplsav2->site_records[]=$recordvalues;
			}
		 }

		 $this->tplsav2->display('list_sites.tpl.php');
	  }

	  /**
	  * test_db_access 
	  *
	  * @fixme: rename to more appropriate name, like 'test_db_and_paths'
	  * @fixme: rewrite this
	  * @access public
	  * @return void
	  */
	  function test_db_access() 
	  {
		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
		 '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

		 $this->tplsav2->assign('img_icon',PHPGW_IMAGES_DIR . '/favicon.ico');
		 $this->tplsav2->assign('img_shortcut',PHPGW_IMAGES_DIR . '/favicon.ico');
		 $this->tplsav2->assign('charset',$GLOBALS['phpgw']->translation->charset());
		 $this->tplsav2->assign('website_title',lang('Field properties'));
		 $this->tplsav2->assign('theme_css',$theme_css);
		 $this->tplsav2->assign('css',$GLOBALS['phpgw']->common->get_css());
		 $this->tplsav2->assign('lang',$GLOBALS[phpgw_info][user][preferences][common][lang]);

		 list( $data['site_db_name'],
		 $data['site_db_host'],
		 $data['site_db_user'],
		 $data['site_db_password'],
		 $data['site_db_type'], 
		 $data['dev_site_db_name'],
		 $data['dev_site_db_host'],
		 $data['dev_site_db_user'],
		 $data['dev_site_db_password'],
		 $data['dev_site_db_type'])=explode(":",$_GET['dbvals']);

		 $data[host_profile] = $this->tplsav2->host_profile = $_GET[profile];

		 if ($this->bo->so->test_site_db_by_array($data))
		 {
			$this->tplsav2->dbconnect=true;
		 }	   

		 $filename = 'jinn.txt';
		 $paths = explode(";",$_GET['pathvals']);

		 if($_GET[profile]=='development')
		 {
			$path = $paths[1];
			$url = $paths[3];
			$dev = 'dev_';
		 }
		 else
		 {
			$path = $paths[0];
			$url = $paths[2];
			$dev='';
		 }

		 if(is_dir($path))
		 {
			$this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_path','#ffffff');
			$this->tplsav2->path_exist=true;
			if(is_writable($path))
			{
			   $this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_path','#ffffff');
			   $this->tplsav2->path_writeable=true;

			   if(!$file = @fopen($path.'/'.$filename, 'w'))
			   {
				  $this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_path','#ffaaaa');

			   }
			   else
			   {
				  $this->tplsav2->test_write=true;

				  $uid = uniqid('');
				  fwrite($file, $uid);
				  fclose($file);

				  if(!$file = @fopen($url, 'r'))
				  {
					 $this->tplsav2->url_open=false;
					 $this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_url','#ffaaaa');

				  }
				  else
				  {
					 $this->tplsav2->url_open=true;
					 $this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_url','#ffffff');


					 if(!$file = @fopen($url.'/'.$filename, 'r'))
					 {
						$this->tplsav2->url_correct=false;
						$this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_url','#ffaaaa');
					 }
					 else
					 {
						$result = fread($file, filesize($path.'/'.$filename));
						if($result==$uid)
						{
						   $this->tplsav2->url_correct=true;
						   $this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_url','#ffffff');
						}
						else
						{
						   $this->tplsav2->url_correct=false;
						   $this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_url','#ffaaaa');
						}
					 }
				  }
			   }
			}
			else
			{
			   $this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_url','#ffaaaa');
			   $this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_path','#ffaaaa');
			}
		 }
		 else
		 {
			$this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_url','#ffaaaa');
			$this->tplsav2->jsscript.=$this->set_field_color('FLD'.$dev.'upload_path','#ffaaaa');
		 }

		 $this->tplsav2->display('test_site_settings.tpl.php');
	  }

	  /**
	  * status 
	  *
	  * @fixme where is this for
	  * @fixme tplsav
	  * @param mixed $field 
	  * @param mixed $type 
	  * @param mixed $message 
	  * @param string $server 
	  * @access public
	  * @return void
	  */
	  function status($field, $type, $message, $server='')
	  {
		 if($type=='good')
		 {
			echo ('<span style="color:green">'.lang($message, $server).'</span><br/>');
			$this->set_field_color('FLD'.$field, '#FFFFFF');
		 }
		 else
		 {
			echo ('<span style="color:red">'.lang($message, $server).'</span><br/>');
			$this->set_field_color('FLD'.$field, '#FFAAAA');
		 }
	  }

	  /**
	  * set_field_color 
	  * 
	  * @fixme tplsav
	  * @param mixed $fieldname 
	  * @param mixed $color 
	  * @access public
	  * @return void
	  */
	  function set_field_color($fieldname, $color)
	  {
		 $script='<scr'.'ipt language="javascript">';
		 $script.='opener.document.frm.'.$fieldname.'.style.backgroundColor="'.$color.'";';
		 $script.='</s'.'cript>';
		 return $script;
	  }


	  /**
	  * getEventOptions 
	  * 
	  * @param mixed $selected 
	  * @access public
	  * @return void
	  */
	  function getEventOptions($selected)
	  {
		 $sel_arr[$selected]='selected="selected"';

		 $event_arr=array(
			'on_update',
			'on_export',
			'on_walk_list_button',
		 );

		 $this->tplsav2->optval = '';
		 $this->tplsav2->optselected = '';
		 $this->tplsav2->optdisplay = '-------';
		 $options .= $this->tplsav2->fetch('form_el_option.tpl.php');

		 foreach($event_arr as $event)
		 {
			$this->tplsav2->optval = $event;
			$this->tplsav2->optselected = $sel_arr[$event];
			$this->tplsav2->optdisplay = lang($event);
			$options .= $this->tplsav2->fetch('form_el_option.tpl.php');
		 }

		 return $options;
	  }

	  /**
	  * getPluginOptions 
	  * 
	  * @param mixed $event 
	  * @fixme tplsav
	  * @param mixed $selected 
	  * @access public
	  * @return void
	  */
	  function getPluginOptions($event, $selected)
	  {
		 $options  = '<option value="">-------------</option>';
		 $plugin_array = $this->bo->object_events_plugin_manager->plugin_hooks($event);
		 if(is_array($plugin_array))
		 {
			foreach($plugin_array as $plugin)
			{
			   if($plugin[value] == $selected)
			   {
				  $options .= '<option value="'.$plugin[value].'" selected>'.$plugin[name].'</option>';
			   }
			   else
			   {
				  $options .= '<option value="'.$plugin[value].'">'.$plugin[name].'</option>';
			   }
			}
		 }
		 return $options;
	  }

	  /**
	  * object_events_config: make form to set event plugin configuration
	  * 
	  * @access public
	  * @fixme tplsav
	  * @fixme parse config options and help info through lang()
	  * @return void
	  */

	  /**
	  * xmlhttpreq_get_fields 
	  * 
	  * @access public
	  * @return void
	  */
	  function xmlhttpreq_get_fields()
	  {
		 if($_GET[primary])
		 {
			$get_primary=true;
		 }

		 //		 $site_id=$this->bo->so->get_site_id_by_object_id($_GET[object_id]);
		 $this->tplsav2->fields_arr=$this->bo->get_fieldnames_by_table($_GET[table],$get_primary);

		 header( "Content-type: text/xml" );
		 header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // fix me kan nooit goed zijn
		 header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
		 header( "Cache-Control: no-cache, must-revalidate" );
		 header( "Pragma: no-cache" );

		 $this->tplsav2->display('xmlhttpreq_get_table_fields.tpl.php');
	  }

	  /**
	  * xmlhttp_req_toggle_field_visible: toggle a field to hidden or visible when editing a object 
	  * 
	  * @access public
	  * @return void
	  */
	  function xmlhttp_req_toggle_field_visible()
	  {
		 if($_GET[toggleTo]=='visible')
		 {
			$value=1;				
		 }
		 elseif($_GET[toggleTo]=='hide')
		 {
			$value=0;				

		 }
		 else
		 {
			return;
		 }

		 $data[]=array(
			'name'=>'field_name',
			'value'=>$_GET[field_name]
		 );

		 $data[]=array(
			'name'=>'field_parent_object',
			'value'=>$_GET[object_id]
		 );

		 $data[] = array(
			'name' => 'form_visibility', 
			'value' => $value
		 );
		 $where_string="`field_parent_object`='{$_GET[object_id]}' AND  `field_name`='{$_GET[field_name]}'";
		 $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true);
		 $this->bo->set_site_version_info($this->bo->site['site_id']);

		 header( "Content-type: text/xml" );
		 header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // fix me kan nooit goed zijn
		 header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
		 header( "Cache-Control: no-cache, must-revalidate" );
		 header( "Pragma: no-cache" );

		 echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		 echo '<response>';
		 echo '<status>'.$status[ret_code].'</status>';
		 echo '<visible>'.$value.'</visible>';
		 echo '</response>';
	  }
	  /**
	  * xmlhttp_req_toggle_field_visible: toggle a field to hidden or visible when editing a object 
	  * 
	  * @access public
	  * @return void
	  */
	  function xmlhttp_req_toggle_field_enabled()
	  {
		 if($_GET[toggleTo]=='enable')
		 {
			$value=1;				
		 }
		 elseif($_GET[toggleTo]=='disable')
		 {
			$value=0;				

		 }
		 else
		 {
			return;
		 }

		 $data[]=array(
			'name'=>'field_name',
			'value'=>$_GET[field_name]
		 );

		 $data[]=array(
			'name'=>'field_parent_object',
			'value'=>$_GET[object_id]
		 );

		 $data[] = array(
			'name' => 'field_enabled', 
			'value' => $value
		 );

		 $where_string="`field_parent_object`='{$_GET[object_id]}' AND  `field_name`='{$_GET[field_name]}'";
		 $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true);
		 $this->bo->set_site_version_info($this->bo->site['site_id']);

		 header( "Content-type: text/xml" );
		 header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // fix me kan nooit goed zijn
		 header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
		 header( "Cache-Control: no-cache, must-revalidate" );
		 header( "Pragma: no-cache" );

		 echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		 echo '<response>';
		 echo '<status>'.$status[ret_code].'</status>';
		 echo '<visible>'.$value.'</visible>';
		 echo '</response>';
	  }

	  /**
	  * xmlhttp_req_toggle_field_visible: toggle a field to hidden or visible when editing a object 
	  * 
	  * @access public
	  * @return void
	  */
	  function xmlhttp_req_toggle_field_listvisible()
	  {
		 if($_GET[toggleTo]=='visible')
		 {
			$value=1;				
		 }
		 elseif($_GET[toggleTo]=='hide')
		 {
			$value=0;				

		 }
		 else
		 {
			return;
		 }

		 $data[]=array(
			'name'=>'field_name',
			'value'=>$_GET[field_name]
		 );

		 $data[]=array(
			'name'=>'field_parent_object',
			'value'=>$_GET[object_id]
		 );

		 $data[] = array(
			'name' => 'list_visibility', 
			'value' => $value
		 );

		 $where_string="`field_parent_object`='{$_GET[object_id]}' AND  `field_name`='{$_GET[field_name]}'";
		 $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true);
		 $this->bo->set_site_version_info($this->bo->site['site_id']);

		 header( "Content-type: text/xml" );
		 header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
		 header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
		 header( "Cache-Control: no-cache, must-revalidate" );
		 header( "Pragma: no-cache" );

		 echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		 echo '<response>';
		 echo '<status>'.$status[ret_code].'</status>';
		 echo '<visible>'.$value.'</visible>';
		 echo '</response>';
	  }

	  /**
	  * edit_gen_obj_options 
	  * 
	  * creates a form in which the main options like name table etc... can be set
	  *
	  * @access public
	  * @return void
	  */
	  function edit_gen_obj_options()
	  {
		 // if we have to save the new data
		 if($_POST[submitted])
		 {
			$post=$this->bo->filter_array_with_prefix($_POST,'FLD',true);
			$post=$this->bo->strip_prefix_from_keys($post,'FLD');

			$data=$this->bo->sql_data_pairs($post,'');

			if($_GET[object_id])
			{
			   $where_string="`object_id`='{$_GET[object_id]}'";

			   $status = $this->bo->so->update_phpgw_data('egw_jinn_objects',$data,'','',$where_string,true); // do insert when not existing
			}
			else
			{
			   $status = $this->bo->so->validateAndInsert_phpgw_data('egw_jinn_objects',$data); // do insert when not existing
			   $_GET[object_id]=$status['where_value'];
			}

			$this->bo->set_site_version_info($this->bo->site['site_id']);
			
		 }

		 //for general sets
		 if($_GET[object_id])
		 {
			$object_values=$this->bo->so->get_object_values($_GET[object_id]);
			$where_key='object_id';
			$where_value=$object_values[object_id];
		 }

		 $this->tplsav2->assign('where_key',$where_key);
		 $this->tplsav2->assign('where_value',$where_value);

		 if($where_key && $where_value)
		 {
			$bool_edit_record=true;
			$object_values=$this->bo->so->get_object_values($where_value);
			$this->tplsav2->parent_site_id=$object_values[parent_site_id];
			$table_name = $object_values['table_name'];
			$this->tplsav2->object_name=$object_values['name'];
		 }
		 else
		 {
			$this->tplsav2->parent_site_id=$_GET[parent_site_id];
			$this->tplsav2->object_name=lang('New object');
		 }


		 if(!$this->tplsav2->parent_site_id)
		 {
			//FIXME
			die('no site ID. can\'t create new object');
		 }


		 $tables=$this->bo->so->site_tables_names($this->tplsav2->parent_site_id);

		 foreach($tables as $table)
		 {
			$tables_check_arr[]=$table[table_name];
		 }

		 if($bool_edit_record && in_array($table_name,$tables_check_arr)) // table exists
		 {
			$this->valid_table_name=true;
		 }
		 elseif(!$bool_edit_record) // new object
		 {
			$this->valid_table_name=true;
		 }
		 else
		 {
			$error_msg='<font color=red>'.lang('Tablename <i>%1</i> is not correct. Probably the tablename has changed or or the table is deleted. Please select a new table or delete this object',$table_name).'</font><br>';
		 }

		 //fixme move to tpl
		 echo($error_msg);

		 $this->tplsav2->assign('tables',$tables);	
		 $this->tplsav2->assign('global_values',$object_values);	

		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
		 '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

		 $this->tplsav2->assign('img_icon',PHPGW_IMAGES_DIR . '/favicon.ico');
		 $this->tplsav2->assign('img_shortcut',PHPGW_IMAGES_DIR . '/favicon.ico');
		 $this->tplsav2->assign('charset',$GLOBALS['phpgw']->translation->charset());
		 $this->tplsav2->assign('website_title',lang('Field properties'));
		 $this->tplsav2->assign('theme_css',$theme_css);
		 $this->tplsav2->assign('css',$GLOBALS['phpgw']->common->get_css());
		 $this->tplsav2->assign('lang',$GLOBALS[phpgw_info][user][preferences][common][lang]);
		 $this->tplsav2->assign('action',$action);

		 $this->tplsav2->display('frm_edit_general_object_options.tpl.php');
	  } 

	  /**
	  * edit_relation_widgets 
	  *
	  * relation type 1 = one to many relation
	  * relation type 2 = many to many relation
	  * relation type 3 = one to one relation
	  * relation type 4 = many to one relation
	  * 
	  * @access public
	  * @return void
	  */
	  function edit_relation_widgets()
	  {
		 // if we have to save the new data
		 if($_POST[submitted])
		 {
			// seperate the diffrent type of fields in different arrays

			$this->bo->save_relations($_POST[object_id]);

			$_GET[object_id]=$_POST[object_id];
		 }

		 //for general sets
		 if(!$_GET[object_id]  )
		 {
			die('error no object id');
		 }

		 $object_values=$this->bo->so->get_object_values($_GET[object_id]);

		 $this->tplsav2->xmlhttpreq_link_fields=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.xmlhttpreq_get_fields&object_id='.$_GET[object_id]);
		 $this->tplsav2->xmlhttpreq_link_objects=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.xmlhttpreq_get_objects&site_id='.$object_values[parent_site_id]);

		 $relations_field=$object_values[relations];

		 $relations_arr = unserialize(base64_decode($relations_field));

		 if(!is_array($relations_arr))
		 {
			$relations_arr=$this->bo->bcompat->convert_old_relations($relations_field);
		 }

		 $relations_arr = $this->bo->bcompat->convert_2modern_relations($relations_arr);

		 $this->tplsav2->delete_img=$GLOBALS[phpgw]->common->image('phpgwapi','delete');
		 $this->tplsav2->type1_arr=array();
		 $this->tplsav2->type2_arr=array();
		 $this->tplsav2->type3_arr=array();
		 $this->tplsav2->type4_arr=array();

		 //sort on type and assign to template
		 if(is_array($relations_arr))
		 {
			foreach($relations_arr as $relation)
			{
			   if ($relation[type]==1)
			   {
				  $this->tplsav2->type1_arr[]=$relation;
			   }

			   if ($relation[type]==2)
			   {
				  $this->tplsav2->type2_arr[]=$relation;
			   }

			   if ($relation[type]==3)
			   {
				  $this->tplsav2->type3_arr[]=$relation;
			   }

			   if ($relation[type]==4)
			   {
				  $this->tplsav2->type4_arr[]=$relation;
			   }
			}
		 }

		 $this->tplsav2->object_name=$object_values[name];
		 $this->tplsav2->relations_arr=$relations_arr;

		 $hidden_value=base64_encode(serialize($relations_arr));//kan weg straks
		 $this->tplsav2->assign('hiddenval',$hidden_value);	 

		 $table_name=$object_values[table_name];
		 $this->tplsav2->table_name=$object_values[table_name]; // used in the new method

		 $_tables=$this->bo->so->site_tables_names($object_values[parent_site_id]);

		 foreach($_tables as $table)
		 {
			$this->tplsav2->avail_table_arr[]=$table[table_name];

			//for the old method
			$avail_table_arr[]=array
			(
			   'name'=> $table[table_name],
			   'value'=> $table[table_name]
			);
		 }

		 $this->tplsav2->avail_objects_arr=$this->bo->get_phpgw_records('egw_jinn_objects','parent_site_id',$object_values[parent_site_id],'','','name');


		 $this->tplsav2->assign('relations',$arr_rel_format);


		 $fields=$this->bo->so->site_table_metadata($object_values[parent_site_id],$table_name);
		 foreach($fields as $field)
		 {
			$this->tplsav2->fields_arr[]=$field[name];
			if(preg_match("/primary_key/i", $field[flags]))
			{
			   $this->tplsav2->primary_arr[]=$field[name];
			}
		 }

		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
		 '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

		 $this->tplsav2->assign('img_icon',PHPGW_IMAGES_DIR . '/favicon.ico');
		 $this->tplsav2->assign('img_shortcut',PHPGW_IMAGES_DIR . '/favicon.ico');
		 $this->tplsav2->assign('charset',$GLOBALS['phpgw']->translation->charset());
		 $this->tplsav2->assign('website_title',lang('Field properties'));
		 $this->tplsav2->assign('theme_css',$theme_css);
		 $this->tplsav2->assign('css',$GLOBALS['phpgw']->common->get_css());
		 $this->tplsav2->assign('lang',$GLOBALS[phpgw_info][user][preferences][common][lang]);

		 $this->tplsav2->display('frm_edit_relation_widgets.tpl.php');
	  }

	  /**
	  * add_element: add new form element called from the edit_record developer screen
	  * 
	  * @access public
	  * @return void
	  */
	  function add_element()
	  {
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $object_values=$this->bo->so->get_object_values($_GET[object_id]);

		 //if we have to save the stuff to the db
		 //_debug_array();
		 if($_POST['submitted'] && $_POST['el_changes']!='true')
		 {
			// seperate the diffrent type of fields in different arrays
			$post_general=$this->bo->filter_array_with_prefix($_POST,'GENXXX',true);
			$post_general=$this->bo->strip_prefix_from_keys($post_general,'GENXXX');

			// general fields	
			$data=$this->bo->sql_data_pairs($post_general,'');

			//these fields are necessary for try_insert
			$new_field_name=uniqid('');
			$data[]=array(
			   'name'=>'field_name',
			   'value'=> $new_field_name //zie relaties
			);

			$data[]=array(
			   'name'=>'field_parent_object',
			   'value'=>$_GET['object_id']
			);

			$where_string="`field_parent_object`='{$_GET['object_id']}' AND  `field_name`='{$_GET['field_name']}'";

			$status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true); // do insert when not existing
			// end general fields	

			// plugin fields
			if($_POST['plugin_name'])
			{
			   $conf=array(
				  'name'=>$_POST['plugin_name']
			   );
			   $conf_serialed_string=base64_encode(serialize($conf));
			}

			$status=$this->bo->so->save_field_plugin_conf($_GET[object_id],$new_field_name,$conf_serialed_string);
			// end plugin fields
		 }

		 if($_POST['GENXXXelement_type']=='lay_out')
		 {
			$avail_layt_plugins_arr=$this->bo->plug->layout_plugins();

			foreach($avail_layt_plugins_arr as $layt_plugin)
			{
			   $this->tplsav2->optselected = '';
			   if($layt_plugin['name']==$_POST['plugin_name'])
			   {
				  $this->tplsav2->optselected = 'selected="selected"';
			   }
			   $this->tplsav2->optdisplay = $layt_plugin['title'];
			   $this->tplsav2->optval = $layt_plugin['name'];
			   $this->tplsav2->lay_out_plug_opt_arr .= $this->tplsav2->fetch('form_el_option.tpl.php');
			}

		 }
		 else//($_POST[element_type]=='input')
		 {
			$fields=$this->bo->so->site_table_metadata($object_values['parent_site_id'],$object_values['table_name']);
			foreach($fields as $field)
			{
			   $this->tplsav2->optselected = '';
			   if($field['name']==$_POST['GENXXXfield_name'])
			   {
				  $this->tplsav2->optselected = 'selected="selected"';
			   }
			   $this->tplsav2->optdisplay = $this->tplsav2->optval = $field['name'];
			   $this->tplsav2->fields_opt_arr .= $this->tplsav2->fetch('form_el_option.tpl.php');
			}
		 }

		 $action=$GLOBALS['egw']->link('/index.php','menuaction=jinn.uiadmin.add_element&object_id='.$_GET['object_id']);

		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
		 '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

		 $this->tplsav2->assign('charset',$GLOBALS['phpgw']->translation->charset());
		 $this->tplsav2->assign('website_title',lang('Add form element'));
		 $this->tplsav2->assign('theme_css',$theme_css);
		 $this->tplsav2->assign('css',$GLOBALS['phpgw']->common->get_css());
		 $this->tplsav2->assign('lang',$GLOBALS['phpgw_info']['user']['preferences']['common']['lang']);
		 $this->tplsav2->assign('action',$action);

		 //for general sets
		 if(!$_GET[object_id])
		 {
			die('error');
		 }

		 $this->tplsav2->display('pop_add_element.tpl.php');

	  }

	  function findInArray($array, $search) 
	  {
		 foreach ($array as $index => $value) {
			if ($index == $search)
			{
			   return $value;
			}
			if(is_array($array[$index])) 
			{
			   $temp = $this->findInArray($value,$search);			
			   //$array[$index] = findInArray($array[$index]);
			}
			if ($temp == $search)
			{
			   //_debug_array($temp);
			   return $value;
			}
			if(!is_array($temp)) return $temp;
		 }
		 return $array;
	  }

	  /**
	  * object_events_config 
	  * 
	  * @access public
	  * @return void
	  * @todo: implement the same widget which form plugin use
	  * @todo: rewrite config array in the register of the plugins
	  * @todo: rewrite plugins so that it are classes
	  * @todo: better flow
	  */
	  function object_events_config()
	  {
		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
		 '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

		 $this->tplsav2->assign('theme_css',$theme_css);
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $object_arr=$this->bo->so->get_object_values($_GET[object_id]);

		 if($_GET[close_me]=='true')
		 {
			$this->tplsav2->set_var('close', ' onLoad="self.close()"');		 
		 }
		 else
		 {
			$this->tplsav2->set_var('close', '');		 
		 }

		 $object_arr=$this->bo->so->get_object_values($_GET[object_id]);
		 $stored_configs = unserialize(base64_decode($object_arr[events_config]));

		 $this->tplsav2->stored_events_arr=array(); 
		 if(is_array($stored_configs))
		 {
			foreach($stored_configs as $key => $config)
			{
			   $stored_events_arr['edit_url']=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.object_events_config&object_id='.$_GET[object_id].'&edit='.$key);
			   $stored_events_arr['config_id']=$key;
			   $stored_events_arr['config_description']=lang('%3: event <b>%1</b> triggers plugin <b>%2</b>', $config[conf][event], $config[conf][plugin], $key+1);

			   $this->tplsav2->stored_events_arr[]=$stored_events_arr;
			}
		 }

		 if($_GET[edit]=='')
		 {
			$this->tplsav2->set_var('action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.boadmin.save_object_events_conf&object_id='.$_GET[object_id]));

			$this->tplsav2->set_var('event_options', $this->getEventOptions($_POST[event]));		 
			$this->tplsav2->set_var('plugin_options', $this->getPluginOptions($_POST[event], $_POST[plugin]));		 
			$this->tplsav2->set_var('option_selected', 'document.events_config.action=\''.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.object_events_config&object_id='.$_GET[object_id]).'\'; submit();');

			if($_POST[plugin] != '')
			{
			   $this->tplsav2->set_var('plug_name',$this->bo->object_events_plugins[$_POST[plugin]]['title']);

			   $cfg=$this->bo->object_events_plugins[$_POST[plugin]]['config'];
			   $cfg_help=$this->bo->object_events_plugins[$_POST[plugin]]['config_help'];
			}
		 }
		 else
		 {
			$this->tplsav2->set_var('action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.boadmin.save_object_events_conf&object_id='.$_GET[object_id].'&edit='.$_GET[edit]));

			if(is_array($stored_configs))
			{
			   $edit_conf = $stored_configs[$_GET[edit]];
			}

			$this->tplsav2->set_var('plug_name',$this->bo->object_events_plugins[$edit_conf[name]]['title']);

			$cfg=$this->bo->object_events_plugins[$edit_conf[name]]['config'];
			$cfg_help=$this->bo->object_events_plugins[$edit_conf[name]]['config_help'];
		 }

		 if(is_array($cfg))
		 {
			foreach($cfg as $key => $val)
			{
			   $cfg_arr=array(
				  'name'=>$key,
				  'label'=>ereg_replace('_',' ',$key),
				  'help'=>$cfg_help[$key],
				  'type'=>$val[1],
				  'attrib'=>$val[2],
			   );
			   if(!$edit_conf)
			   {
				  $cfg_arr['value']=$val[0];
			   }
			   else
			   {
				  $cfg_arr['value']=$edit_conf['conf'][$key];
			   }

			   $this->tplsav2->cfg_arr[]=$cfg_arr;
			}
		 }
		 $this->tplsav2->assign('lang',$GLOBALS[phpgw_info][user][preferences][common][lang]);

		 $this->tplsav2->display('frm_conf_object_events.tpl.php');

		 $this->bo->sessionmanager->save();
	  }

	  /**
	  * edit_field_props: form to edit field properties
	  * 
	  * @todo cleanup
	  * @access public
	  * @return void
	  */
	  function edit_field_props()
	  {
		 //for general sets
		 if(!$_GET[object_id] || !$_GET[field_name])
		 {
			die('error general flds');
		 }

		 $object_arr=$this->bo->so->get_object_values($_GET[object_id]);

		 /* for backwards compatibility */
		 // how long do we need this
		 if(!empty($object_arr[plugins]))
		 {
			$this->bo->upgrade_plugins($object_arr[object_id]);
		 }

		 $field_conf_arr=$this->bo->so->get_field_values($object_arr[object_id],$_GET[field_name]);

		 if($_POST[plugin_name]) 
		 {
			$plugin_name=$_POST[plugin_name];
		 }
		 else
		 {
			$fld_plug_conf_arr=unserialize(base64_decode($field_conf_arr[field_plugins]));

			$plugin_name=$fld_plug_conf_arr[name];
		 }

		 $plug_reg_arr=$this->bo->get_db_plug_arr($plugin_name);

		 $old_cfg= $this->bo->bcompat->convert_old_dbplug_array($plug_reg_arr['config']);
		 $plug_reg_conf_arr=array_merge($old_cfg,$plug_reg_arr['config2']);

		 if($plug_reg_arr['element_type']=='lay-out')
		 {
			$avail_plugins_arr=$this->bo->plug->get_layout_plugins();
		 }
		 elseif($field_conf_arr['element_type']=='table_field')
		 {
			$field_meta_arr=$this->bo->so->object_field_metadata($_GET[object_id],$field_conf_arr['data_source']);
			$avail_plugins_arr=$this->bo->plugins_for_field_type($field_meta_arr,($_POST['submitted']?$_POST[newplug]:$plugin_name));
			$this->tplsav2->assign('data_source',$field_conf_arr['data_source']);
		 }
		 else
		 {
			$field_meta_arr=$this->bo->so->object_field_metadata($_GET[object_id],$_GET[field_name]);
			$avail_plugins_arr=$this->bo->plugins_for_field_type($field_meta_arr,($_POST['submitted']?$_POST[newplug]:$plugin_name));
			$this->tplsav2->assign('data_source',$_GET[field_name]);
		 }

		 // A. CAN BE POSTED BY ITSELF
		 if(is_array($_POST))
		 {
			// seperate the diffrent type of fields in different arrays
			$post_general=$this->bo->filter_array_with_prefix($_POST,'GENXXX',true);
			$post_general=$this->bo->strip_prefix_from_keys($post_general,'GENXXX');

			$post_plugins=$this->bo->filter_array_with_prefix($_POST,'PLGXXX',true);
			$post_plugins=$this->bo->strip_prefix_from_keys($post_plugins,'PLGXXX');

			//NEW FOR MULTIS
			$post_multi=$this->bo->filter_array_with_prefix($_POST,'MLT',true);
			$post_multi=$this->bo->strip_prefix_from_keys($post_multi,'MLT');


			// THE FORM IS SUBMITTED BY ITSELF
			if($_POST['submitted'])
			{
			   // WE COME FROM ANOTHER PLUG CONF SO WE DO NOTHING
			   if($_POST['plugchanges'])
			   {
				  unset($post_plugins);
				  $plug_reg_arr=$this->bo->get_db_plug_arr($_POST[newplug]);
				  $plugin_name = $plug_reg_arr[name]; // reset name in case plugin is an alias for another plugin
				  $old_cfg= $this->bo->bcompat->convert_old_dbplug_array($plug_reg_arr['config']);
				  $plug_reg_conf_arr=array_merge($old_cfg,$plug_reg_arr['config2']);
			   }
			   // FILES ARE UPLOADED/DELETED
			   elseif($_POST['uploaddelete']=='true')
			   {
				  if(is_array($post_plugins))
				  {
					 foreach($post_plugins as $key => $val)
					 {
						if($post_plugins[$key.'_plgupload']) 
						{
						   //TODO ??? set key to new file name in POST
						   $stored_files_arr=$this->bo->site_fs->save_site_file_from_post($this->bo->so->get_site_id_by_object_id($_GET[object_id]),'PLGXXX'.$key,$plug_reg_conf_arr[$key][subdir]);	
						}
						elseif($post_plugins[$key.'_plgdelete']) 
						{
						   $this->bo->site_fs->remove_file($this->bo->so->get_site_id_by_object_id($_GET[object_id]),$val,$plug_reg_conf_arr[$key][subdir]);	
						}

						if(is_array($post_plugins[$key]))
						{
						   $val=serialize(($post_plugins[$key])); 
						}

						$_conf[$key]=$val;
					 }
				  }
				  if(is_array($post_multi))
				  {
					 foreach($post_multi as $key => $val)
					 {
						if($post_multi[$key.'_plgupload'])
						{
						   //TODO ??? set key to new file name in POST
						   $arr = explode("_SEP_",$key);
						   $stored_files_arr=$this->bo->site_fs->save_site_file_from_post($this->bo->so->get_site_id_by_object_id($_GET[object_id]),'MLT'.$key,$plug_reg_conf_arr[substr($arr[0],3)]['items'][$arr[1]][subdir]);
						}
						elseif($post_multi[$key.'_plgdelete'])
						{
						   //_debug_array($val);
						   $arr = explode("_SEP_",$key);
						   $this->bo->site_fs->remove_file($this->bo->so->get_site_id_by_object_id($_GET[object_id]),$val,$plug_reg_conf_arr[substr($arr[0],3)]['items'][$arr[1]][subdir]);
						}

						if(is_array($post_multi[$key]))
						{
						   $val=serialize(($post_multi[$key]));
						}

						$_conf[$key]=$val;
					 }
				  }
			   }
			   //FORM CAN BE STORED IN DB
			   else
			   {
				  // general fields	
				  $data=$this->bo->sql_data_pairs($post_general,'');

				  //these two are necessary for try_insert
				  $data[]=array(
					 'name'=>'field_name',
					 'value'=>$_GET[field_name]
				  );

				  $data[]=array(
					 'name'=>'field_parent_object',
					 'value'=>$_GET[object_id]
				  );

				  $where_string="`field_parent_object`='{$_GET[object_id]}' AND  `field_name`='$_GET[field_name]'";

				  $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true); // do insert when not existing
				  $this->bo->set_site_version_info($this->bo->site['site_id']);

				  // end general fields	

				  // plugin fields
				  $conf=array(
					 'name'=>$_POST[plugin_name],
					 'conf'=>$post_plugins
				  );

				  if(is_array($post_multi))
				  {
					 foreach($post_multi as $name => $value)
					 {
						$number = intval(substr($name,0,3));
						$temp = explode("_SEP_",$name);
						$multi_name= substr($temp[0],3);
						$subname = $temp[1];
						$confM[$multi_name][$number][$subname] = $value;
					 }
					 $conf['conf'] = $confM;
					 $_POST[$multi_name] = $confM[$multi_name];
				  }
				  $conf_serialed_string=base64_encode(serialize($conf));
				  $status=$this->bo->so->save_field_plugin_conf($_GET[object_id],$_GET[field_name],$conf_serialed_string);
			   }
			}
		 }

		 // FROM HERE THE FORM IS RENDERED

		 //formaction
		 $action=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_field_props&object_id='.$_GET[object_id].'&field_name='.$_GET[field_name].'&plug_name='.$plugin_name);

		 /* if the plugin wants to generate is own page manually */
		 //fixme remove?
		 if($plug_reg_arr[config_execute])
		 {
			$this->bo->plug->call_config_function($plugin_name,$plug_reg_conf_arr,$action);
			$GLOBALS['phpgw']->common->phpgw_exit();
		 }

		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $app = $GLOBALS['phpgw_info']['flags']['currentapp'];
		 $app = $app ? ' ['.(isset($GLOBALS['phpgw_info']['apps'][$app]) ? $GLOBALS['phpgw_info']['apps'][$app]['title'] : lang($app)).']':'';

		 $use_records_cfg=False;

		 if($_GET[close_me]=='true') $body_tags = 'onLoad="self.close()"';

		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
		 '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

		 $this->tplsav2->assign('img_icon',PHPGW_IMAGES_DIR . '/favicon.ico');
		 $this->tplsav2->assign('img_shortcut',PHPGW_IMAGES_DIR . '/favicon.ico');
		 $this->tplsav2->assign('charset',$GLOBALS['phpgw']->translation->charset());
		 $this->tplsav2->assign('website_title',lang('Field properties'));
		 $this->tplsav2->assign('theme_css',$theme_css);
		 $this->tplsav2->assign('css',$GLOBALS['phpgw']->common->get_css());
		 $this->tplsav2->assign('lang',$GLOBALS[phpgw_info][user][preferences][common][lang]);
		 $this->tplsav2->assign('action',$action);
		 $this->tplsav2->assign('avail_plugins_arr',$avail_plugins_arr);
		 $this->tplsav2->assign('post_general',$post_general);
		 $this->tplsav2->assign('use_records_cfg',$use_records_cfg);
		 $this->tplsav2->assign('fld_plug_conf_arr',$fld_plug_conf_arr);
		 $this->tplsav2->assign('body_tags',$body_tags);
		 $this->tplsav2->assign('plug_title',$plug_reg_arr['title']);
		 $this->tplsav2->assign('plug_name',$plugin_name);
		 $this->tplsav2->assign('lang_plugin_name',lang('Plugin name'));
		 $this->tplsav2->assign('lang_fieldname',lang('Fieldname'));
		 $this->tplsav2->assign('fieldname',$_GET[field_name]);

		 $this->tplsav2->assign('field_conf_arr',$field_conf_arr);
		 $this->tplsav2->assign('val_element_label',$field_conf_arr[element_label]);
		 $this->tplsav2->assign('val_field_help_info',$field_conf_arr[field_help_info]);

		 $this->tplsav2->assign('lang_version',lang('Version'));
		 $this->tplsav2->assign('lang_plugin_configuration',lang('Plugin Configuration'));
		 $this->tplsav2->assign('plug_version',$plug_reg_arr['version']);
		 $this->tplsav2->assign('plug_descr',$plug_reg_arr['description']);
		 $this->tplsav2->assign('plug_help',$plug_reg_arr['help']);
		 $this->tplsav2->assign('jinn_sitefile_path',$this->bo->site_fs->get_jinn_sitefile_path($object_arr[parent_site_id]));

		 /* display shouldnt be in this if construction */
		 if(is_array($plug_reg_conf_arr))
		 {
			$this->tplsav2->assign('fld_plug_cnf',lang('Field plugin configuration'));
			$this->tplsav2->assign('plug_reg_conf_arr',$plug_reg_conf_arr);
			$temp ='';
			$configuration_widget= CreateObject('jinn.plg_conf_widget');
			foreach($plug_reg_conf_arr as $cval)
			{
			   $temp .= $configuration_widget->display_plugin_widget($cval[type],$this->tplsav2, $cval,$fld_plug_conf_arr);
			}
			$this->tplsav2->assign('plugjes',$temp);
			$this->tplsav2->display('frm_conf_field.tpl.php');
		 }

		 $this->bo->sessionmanager->save();
	  }


	  /**
	  * import_object 
	  * 
	  * @access public
	  * @return void
	  */
	  function import_object()
	  {
		 if (is_array($GLOBALS[HTTP_POST_FILES][importfile]))
		 {
			$num_objects=0;
			$import=$GLOBALS[HTTP_POST_FILES][importfile];

			@include($import[tmp_name]);
			if ($import_object && $checkbit)
			{
			   $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_objects');
			   unset($validfields[object_id]);
			   $imported = array();

			   while(list($key, $val) = each($import_object)) 
			   {
				  if(array_key_exists($key, $validfields))
				  {
					 $imported[$key] = true;
					 if ($key=='parent_site_id') $val=$_POST[parent_site_id];
					 $data[] = array
					 (
						'name' => $key,
						'value' => addslashes($val) 
					 );
				  }
				  else
				  {
					 $this->bo->addError(lang('incompatibility result: Object <b>\'%3\'</b>, property <b>\'%1\'</b> with value <b>\'%2\'</b> could not be imported because it does not exist in this JiNN version', $key, $val, $import_object['name']));
				  }
			   }
			   foreach($validfields as $fieldname => $yes)
			   {
				  if(!array_key_exists($fieldname, $imported))
				  {
					 $this->bo->addError(lang('incompatibility result: Object <b>\'%2\'</b>, property <b>\'%1\'</b> was not present in the file', $fieldname, $import_object['name']));
				  }
			   }

			   $new_object_name=$data[1][value];	
			   $thisobjectname=$this->bo->so->get_objects_by_name($new_object_name,$_POST[parent_site_id]);

			   /* insert as new object */
			   if($status=$this->bo->so->insert_phpgw_data('egw_jinn_objects',$data))
			   {
				  $new_object_id=$status[where_value];

				  if(count($thisobjectname)>=1)
				  {
					 $new_name=$new_object_name.' ('.lang('another').')';

					 $datanew[]=array(
						'name'=>'name',
						'value'=>$new_name
					 );
					 $this->bo->so->upAndValidate_phpgw_data('egw_jinn_objects',$datanew,'object_id',$new_object_id);
				  }
				  else
				  {
					 $new_name=$new_object_name;
				  }
				  $proceed=true;
				  $this->bo->addInfo(lang('Import was succesfull'));
				  $this->bo->addInfo(lang('The name of the new object is <strong>%1</strong>.',$new_name));
			   }

			   if($proceed)
			   {
				  /* objects are imported , go on with obj-fields */
				  if(is_array($import_obj_fields))
				  {
					 $validfields = $this->bo->so->phpgw_table_fields('egw_jinn_obj_fields');
					 unset($validfields['field_id']);
					 foreach($import_obj_fields as $obj_field)
					 {
						$obj_field['field_parent_object'] = $new_object_id; // no matching by unique_id is required here (like in load_site_from_file()), because only one object is imported

						unset($data_fields);
						unset($imported);
						while(list($key2, $val2) = each($obj_field)) 
						{
						   if ($key2 == 'obj_serial') 
						   {
							  continue;  
						   }
						   if ($key2 == 'unique_id') 
						   {
							  continue;  
						   }

						   if(array_key_exists($key2, $validfields))
						   {
							  $imported[$key2] = true;
							  $data_fields[] = array
							  (
								 'name' => $key2,
								 'value' => addslashes($val2) 
							  );
						   }
						   else
						   {
							  $this->bo->addError(lang('incompatibility result: Object <strong>\'%3\'</strong>, field <strong>\'%1\'</strong>, property <strong>\'%2\'</strong> could not be imported because it does not exist in this JiNN version', $obj_field['field_name'], $key2, $import_object['name']));
						   }
						}
						foreach($validfields as $fieldname => $yes)
						{
						   if(!array_key_exists($fieldname, $imported))
						   {
							  $this->bo->addError(lang('incompatibility result: Object <strong>\'%2\'</strong>, field <strong>\'%1\'</strong>, property <strong>\'%3\'</strong> was not present in the file', $obj_field['field_name'], $import_object['name'], $fieldname));
						   }
						}

						if ($field_id[]=$this->bo->so->validateAndInsert_phpgw_data('egw_jinn_obj_fields',$data_fields))
						{
						   $num_fields=count($field_id);
						} 
					 }
				  }

				  $this->bo->addInfo(lang('%1 Site Objects have been imported.',1));
				  $this->bo->addInfo(lang('%1 Site Obj-fields have been imported.',$num_fields));
			   }
			   else
			   {
				  $this->bo->addError(lang('Import failed'));
			   }

			   $this->bo->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$_POST[parent_site_id]);
			}

		 }
		 else
		 {
			$this->template->set_file(array(
			   'import_form' => 'import_object.tpl',
			));

			$this->header(lang('Import JiNN-Object'.$table));
			$this->msg_box();

			$this->template->set_var('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.import_object'));
			$this->template->set_var('lang_Select_JiNN_site_file',lang('Select JiNN object file (*.jobj'));
			//			   $this->template->set_var('lang_Replace_existing_Site_with_the_same_name',lang('Replace existing site with the same name?'));
			$this->template->set_var('parent_site_id',$this->bo->where_value);
			$this->template->set_var('lang_submit_and_import',lang('submit and import'));
			$this->template->pparse('out','import_form');
		 }

		 $this->bo->sessionmanager->save();

	  }

		  /**
	  * export_site 
	  * 
	  * @depreciated
	  * @access public
	  * @return void
	  */
	  function export_site()
	  {
		 $this->save_site_to_file();
	  }

	  /**
	  * export_object 
	  * 
	  * @access public
	  * @return void
	  */
	  function export_object()
	  {
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $object_data=$this->bo->so->get_phpgw_record_values('egw_jinn_objects',$this->bo->where_key,$this->bo->where_value,'','','name');

		 $filename=ereg_replace(' ','_',$object_data[0][name]).'.jobj';
		 $date=date("d-m-Y",time());
		 $version=$GLOBALS[egw_info][apps][jinn][version];
		 header("Content-type: text");
		 header("Content-Disposition:attachment; filename=$filename");

		 for($s=0;$s<(50-strlen($filename));$s++)
		 {
			$spaces1.=' ';
		 }
		 $spaces1.='**'."\n";

		 for($s=0;$s<(50-strlen($date));$s++)
		 {
			$spaces2.=' ';
		 }
		 $spaces2.='**'."\n";

		 for($s=0;$s<(50-strlen($version));$s++)
		 {
			$spaces3.=' ';
		 }
		 $spaces3.='**'."\n";

		 $out='<'.'?p'.'hp'."\n\n"; 
		 /* strange but for nice vim indent file */
		 $out.='	/***************************************************************************'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.="	** JiNN Object Export : ".$filename.$spaces1;
		 $out.="	** Date               : ".$date.$spaces2;
		 $out.="	** JiNN Version       : ".$version.$spaces3;
		 $out.='	** ---------------------------------------------------------------------- **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare   **'."\n";
		 $out.='	** Copyright (C)2002, 2004 Pim Snel <pim.jinn@lingewoud.nl>               **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN - http://linuxstart.nl/jinn                                       **'."\n";
		 $out.='	** eGroupWare - http://www.egroupware.org                                 **'."\n";
		 $out.='	** This file is part of JiNN                                              **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN is free software; you can redistribute it and/or modify it under  **'."\n";
		 $out.='	** the terms of the GNU General Public License as published by the Free   **'."\n";
		 $out.='	** Software Foundation; either version 2 of the License.                  **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN is distributed in the hope that it will be useful,but WITHOUT ANY **'."\n";
		 $out.='	** WARRANTY; without even the implied warranty of MERCHANTABILITY or      **'."\n";
		 $out.='	** FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License  **'."\n";
		 $out.='	** for more details.                                                      **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** You should have received a copy of the GNU General Public License      **'."\n";
		 $out.='	** along with JiNN; if not, write to the Free Software Foundation, Inc.,  **'."\n";
		 $out.='	** 59 Temple Place, Suite 330, Boston, MA 02111-1307  USA                 **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	***************************************************************************/'."\n";
		 $out.="\n";

		 $out.= "\n/* OBJECT ARRAY */\n";

		 if(is_array($object_data))
		 {
			foreach($object_data as $object)
			{
			   $out.= '$import_object=array('."\n";

			   while (list ($key, $val) = each ($object)) 
			   { 
				  $field[value]=$serial;

				  if ($key != 'object_id')
				  {
					 $out .= "	'$key' => '".ereg_replace("'","\'",$val)."',\n"; 
				  }
			   }
			   $out.=");\n\n";

			   /*
			   get array whith fielddata
			   store them as array
			   */
			   $object_field_data=$this->bo->so->get_phpgw_record_values('egw_jinn_obj_fields','field_parent_object', $object['object_id'],'','','name');
			   if(is_array($object_field_data))
			   {
				  foreach ($object_field_data as $field)
				  {
					 $out.= '$import_obj_fields[]=array('."\n";

					 while (list ($key, $val) = each ($field)) 
					 { 
						if ($key != 'field_id' && $key !='field_parent_object') 
						{
						   // fix problem with wrong storage of null values causing a 0 value with mean something different
						   if($val!=null)
						   {
							  $val= "'".ereg_replace("'","\'",$val)."'";
						   }
						   else
						   {
							  $val='null';
						   }
						   $out .= "	'$key' => $val,\n"; 
						}
					 }
					 $out.=");\n\n";
				  }
			   }
			}
		 }

		 $out.='$checkbit=true;'."\n";
		 $out.='?>';
		 echo $out;

	  }

	  /**
	  * save_site_to_file: save site-, objects- and fields-configuration to a JiNN-file
	  * 
	  * @access public
	  * @return void
	  */
	  function save_site_to_file()
	  {
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $site_data=$this->bo->so->get_phpgw_record_values('egw_jinn_sites',$this->bo->where_key,$this->bo->where_value,'','','name');
		 $filename=ereg_replace(' ','_',$site_data[0][site_name]).'.JiNN';
		 $date=date("d-m-Y",time());

		 header("Content-type: text");
		 header("Content-Disposition:attachment; filename=$filename");

		 $out='<'.'?p'.'hp'."\n\n"; 
		 /* strange but for nice vim indent file */
		 $out.='	/***************************************************************************'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.="	** JiNN Site Export : ".$filename."\n";
		 $out.="	** Date             : ".$date."\n";
		 $out.="	** JiNN Version     : ".$GLOBALS[phpgw_info][apps][jinn][version]."\n";
		 $out.='	** ---------------------------------------------------------------------- **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare   **'."\n";
		 $out.='	** Copyright (C)2002, 2004 Pim Snel <pim.jinn@lingewoud.nl>               **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN - http://linuxstart.nl/jinn                                       **'."\n";
		 $out.='	** eGroupWare - http://www.egroupware.org                                 **'."\n";
		 $out.='	** This file is part of JiNN                                              **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN is free software; you can redistribute it and/or modify it under  **'."\n";
		 $out.='	** the terms of the GNU General Public License as published by the Free   **'."\n";
		 $out.='	** Software Foundation; either version 2 of the License.                  **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** JiNN is distributed in the hope that it will be useful,but WITHOUT ANY **'."\n";
		 $out.='	** WARRANTY; without even the implied warranty of MERCHANTABILITY or      **'."\n";
		 $out.='	** FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License  **'."\n";
		 $out.='	** for more details.                                                      **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	** You should have received a copy of the GNU General Public License      **'."\n";
		 $out.='	** along with JiNN; if not, write to the Free Software Foundation, Inc.,  **'."\n";
		 $out.='	** 59 Temple Place, Suite 330, Boston, MA 02111-1307  USA                 **'."\n";
		 $out.='	**                                                                        **'."\n";
		 $out.='	***************************************************************************/'."\n";
		 $out.="\n";

		 $out.= "/* SITE ARRAY */\n";

		 $out.= '$import_site=array('."\n";

		 while (list ($key, $val) = each($site_data[0])) 
		 {
			if($key!='site_id') $out.= "	'$key' => '$val',\n";
		 }
		 $out.=");\n\n";

		 $sitefilespath=$this->bo->site_fs->get_jinn_sitefile_path($site_data[0][site_id]);
		 if(is_dir($sitefilespath))
		 {
			$tempfile=$this->bo->site_fs->create_archive($site_data[0][site_id]); 
			// Encode the file ready to send it off
			$handle = fopen($tempfile,'rb');
			$file_content = fread($handle,filesize($tempfile));
			fclose($handle);
			$out.= '$import_site_files="'."\n";
			$out.= chunk_split(base64_encode($file_content)); 
			$out.='";'."\n\n";
			unlink($tempfile);
		 }

		 $site_object_data=$this->bo->so->get_phpgw_record_values('egw_jinn_objects','parent_site_id', $this->bo->where_value ,'','','name');
		 $out.= "\n/* SITE_OBJECT ARRAY */\n";

		 if(is_array($site_object_data))
		 {
			foreach($site_object_data as $object)
			{
			   $out.= '$import_site_objects[]=array('."\n";

			   while (list ($key, $val) = each ($object)) 
			   { 
				  $field[value]=$serial;

				  if ($key != 'object_id')
				  {
					 $out .= "	'$key' => '".ereg_replace("'","\'",$val)."',\n"; 
				  }
			   }
			   $out.=");\n\n";

			   /*
			   get array whith fielddata
			   store them as array with unique_id as parent object identifier
			   */
			   $object_field_data=$this->bo->so->get_phpgw_record_values('egw_jinn_obj_fields','field_parent_object', $object['object_id'],'','','name');
			   if(is_array($object_field_data))
			   {
				  foreach ($object_field_data as $field)
				  {
					 $out.= '$import_obj_fields[]=array('."\n";

					 while (list ($key, $val) = each ($field)) 
					 { 
						if ($key != 'field_id' && $key !='field_parent_object') 
						{
						   // fix problem with wrong storage of null values causing a 0 value with mean something different
						   if($val!=null)
						   {
							  $val= "'".ereg_replace("'","\'",$val)."'";
						   }
						   else
						   {
							  $val='null';
						   }
						   $out .= "	'$key' => $val,\n"; 
						}
					 }
					 $out .= "	'unique_id' => '".$object['unique_id']."',\n"; 
					 $out.=");\n\n";
				  }
			   }
			   /*reports*/
			   $object_reports=$this->bo->so->get_phpgw_record_values('egw_jinn_report','report_object_id', $object['unique_id'],'','','name');
			   if(is_array($object_reports))
			   {
				  foreach($object_reports as $report_single)
				  {
					 //print_r($report);
					 $report.= '$import_reports[]=array('."\n";

					 while (list ($key, $val) = each ($report_single))
					 {
						if ($key != 'report_id' )
						{
						   $report .= "    '$key' => '".ereg_replace("'","\'",$val)."',\n";
						}
					 }
					 $report .=");\n\n";
				  }
			   }
			   /*reports*/

			}
		 }
		 $out .= $report;
		 $out.='$checkbit=true;'."\n";
		 $out.='?>';
		 echo $out;
	  }





   }
?>
