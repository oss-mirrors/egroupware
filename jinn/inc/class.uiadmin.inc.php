<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

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

   /* $Id$ */

   /* this file is startpoint for all admin functions */

   class uiadmin
   {
	  var $public_functions = Array(
		 'index' => True,
		 'import_egw_jinn_site' => True,
		 'import_object' => True,
		 'add_edit_site' => True,
		 'add_edit_object' => True,
		 'browse_egw_jinn_sites' => True,
		 'del_egw_jinn_sites'=> True,
		 'del_egw_jinn_objects' => True,
		 'insert_egw_jinn_sites'=> True,
		 'insert_egw_jinn_objects'=> True,
		 'update_egw_jinn_sites'=> True,
		 'update_egw_jinn_objects' => True,
		 'access_rights'=> True,
		 'set_access_rights_site_objects'=> True,
		 'set_access_rights_sites'=> True,
		 'save_access_rights_object'=> True,
		 'save_access_rights_site'=> True,
		 'export_site'=> True,
		 'export_object'=> True,
		 'plug_config'=> True,
		 'edit_this_jinn_site'=> True,
		 'edit_this_jinn_site_object'=> True,
		 'test_db_access'=> True,
		 'upgrade_plugins'=>True,
		 'field_help_config'=>True
	  );

	  var $bo;
	  var $template;
	  var $ui;
	  var $browse;

	  function uiadmin()
	  {
		 if(!$GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.index'));
			$GLOBALS['phpgw']->common->phpgw_exit();
		 }

		 $this->bo = CreateObject('jinn.boadmin');
		 $this->template = $GLOBALS['phpgw']->template;

		 $this->ui = CreateObject('jinn.uicommon');

		 if($this->bo->so->config[server_type]=='dev')
		 {
			$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
		 }

		 $this->ui->app_title=$dev_title_string.	lang('Administrator Mode');
	  }

	  function index()
	  {
		 $this->ui->header(lang('index'));

		 $this->ui->msg_box($this->bo->message);
		 unset($this->bo->message);


		 $this->bo->save_sessiondata();
	  }	

	  function edit_this_jinn_site()
	  {
		 $this->bo->where_key='site_id';
		 $this->bo->where_value=$this->bo->site_id;

		 $this->add_edit_site();
	  }

	  function edit_this_jinn_site_object()
	  {
		 $this->bo->where_key='object_id';
		 $this->bo->where_value=$this->bo->site_object_id;

		 $this->add_edit_object();
	  }

	  function add_edit_object()
	  {
		 $where_key=stripslashes($this->bo->where_key);
		 $where_value=stripslashes($this->bo->where_value);

		 if ($where_key && $where_value)
		 {
			$this->ui->header(lang('Edit Object'));

			$this->bo->message[help]=lang('Edit Object Configuration: <li>select a Object Name for display</li> <li>select a database table to use with this object</li> <li>if necessary an alternative correct absolute upload path</li><li>if necessary a corresponding alternative preview URL for uploaded elements</li><br><li>define field relations</li><li>configure fieldplugins</li>');
		 }
		 else
		 {
			$this->ui->header(lang('Add Object'));

			$this->bo->message[help]=lang('Object Configuration: <li>select a Object Name for display</li> <li>select a database table to use with this object</li> <li>if necessary an alternative correct absolute upload path</li><li>if necessary a corresponding alternative preview URL for uploaded elements</li>');

		 }

		 $this->ui->msg_box($this->bo->message);
		 unset($this->bo->message);

		 $add_edit = CreateObject('jinn.uia_edit_object',$this->bo);

		 $add_edit->render_form($where_key,$where_value);

		 unset($this->bo->message[help]);

		 $this->bo->save_sessiondata();
	  }

	  function add_edit_site()
	  {

		 // FIXME
		 $where_key=stripslashes($this->bo->where_key);
		 $where_value=stripslashes($this->bo->where_value);

		 if($GLOBALS[HTTP_GET_VARS][cancel]=='true')
		 {
			unset($this->bo->message[info]);
			unset($this->bo->message[error]);
		 }

		 if ($where_key && $where_value)
		 {
			$this->ui->header(lang('Edit Site'));
		 }
		 else
		 {
			$this->ui->header(lang('Add Site'));
			$helptext='Insert new Site configuration:<ol><li>Insert a Site Name for display</li><li>insert correct Database settings</li><li>insert a correct absolute upload path</li><li>insert a corresponding preview URL for uploaded elements</li></ol>';
			$this->bo->message[help]=lang($helptext);
		 }

		 $this->ui->msg_box($this->bo->message);
		 unset($this->bo->message);

		 $add_edit = CreateObject('jinn.uia_edit_site',$this->bo);
		 $add_edit->render_form($where_key,$where_value);

		 /* list objects for this site */
		 if ($where_key && $where_value)
		 {
			$new_where_key='parent_'.$where_key;

			$list_objects = CreateObject('jinn.uia_list_objects',$this->bo);
			$list_objects->render_list($new_where_key, $where_value);
		 }

		 unset($this->bo->message[help]);

		 $this->bo->save_sessiondata();
	  }

	  function test_db_access()
	  {
		 // FIXME use templates
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $this->ui->header(lang('Test Database Access'),false);

		 list($data['db_name'],$data['db_host'],$data['db_user'],$data['db_password'],$data['db_type'], $data['dev_db_name'],$data['dev_db_host'],$data['dev_db_user'],$data['dev_db_password'],$data['dev_db_type']  )=explode(":",$_GET['dbvals']);

		 //	_debug_array($data);

		 echo '<div align=center>';
			if ($this->bo->so->test_db_conn($data))
			{
			   echo '<span style="color:green">'.lang("Database connection was succesfull. <P>You can go on with the site-objects").'</span>';
			}
			else 
			{
			   echo '<span style="color:red">'.	lang("database connection failed! <P>Please recheck your settings.").'</span>';
			}

			echo '<P><input type=button value="'.lang('close this window').'" onClick="self.close();"></div>';

		 //		$this->bo->save_sessiondata();
	  }


	  function browse_egw_jinn_sites()
	  {
		 $this->ui->header(lang('List Sites'));
		 $this->ui->msg_box($this->bo->message);
		 unset($this->bo->message);

		 $browse = CreateObject('jinn.uia_list_sites',$this->bo);
		 $browse->render_list();

		 $this->bo->save_sessiondata();

	  }

	  function access_rights()
	  {
		 $this->ui->header(lang('Set Access Rights'));
		 $this->ui->msg_box($this->bo->message);
		 unset($this->bo->message);

		 $access_rights = CreateObject('jinn.uiadminacl', $this->bo);
		 $access_rights->main_screen();

		 unset($this->bo->message);

		 $this->bo->save_sessiondata();

	  }

	  /*
	  f unction set_access_rights_site_objects()
	  {
		 $this->ui->header(lang('Set Access Right for Site Objects'));
		 $this->ui->msg_box($this->bo->message);
		 unset($this->bo->message);

		 $access_rights = CreateObject('jinn.uiadminacl',$this->bo);
		 $access_rights->set_site_objects();

		 unset($this->bo->message);

		 $this->bo->save_sessiondata();
	  }

	  f unction set_access_rights_sites()
	  {
		 $this->ui->header(lang('Set Access Rights for Sites'));
		 $this->ui->msg_box($this->bo->message);
		 unset($this->bo->message);
		 $access_rights = CreateObject('jinn.uiadminacl',$this->bo);
		 $access_rights->set_sites();

		 unset($this->bo->message);

		 $this->bo->save_sessiondata();
	  }

	  */

	  /**
	  @function field_help_config
	  @abstract make form to set field help information 
	  */
	  function field_help_config()
	  {
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $object_arr=$this->bo->so->get_object_values($_GET[object_id]);

		 $this->template->set_file(array('config' => 'frm_conf_field_help.tpl'));

		 $this->template->set_block('config','head','head');
		 $this->template->set_block('config','bodyhead','bodyhead');
		 $this->template->set_block('config','row','row');
		 $this->template->set_block('config','footer','footer');

		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';
		 if(!file_exists($theme_css))
		 {
			$theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';
		 }

		 $app = $GLOBALS['phpgw_info']['flags']['currentapp'];
		 $app = $app ? ' ['.(isset($GLOBALS['phpgw_info']['apps'][$app]) ? $GLOBALS['phpgw_info']['apps'][$app]['title'] : lang($app)).']':'';

		 $var = Array(
			'img_icon'      => PHPGW_IMAGES_DIR . '/favicon.ico',
			'img_shortcut'  => PHPGW_IMAGES_DIR . '/favicon.ico',
			'charset'       => $GLOBALS['phpgw']->translation->charset(),
			'font_family'   => $GLOBALS['phpgw_info']['theme']['font'],
			'website_title' => $GLOBALS['phpgw_info']['server']['site_title'].$app,
			'theme_css'     => $theme_css,
			'css'           => $GLOBALS['phpgw']->common->get_css(),
			'java_script'   => $GLOBALS['phpgw']->common->get_java_script(),
			'css_file'		=> ''
		 );
		 $this->template->set_var($var);

		 $action=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.boadmin.save_field_info_conf&object_id='.$_GET[object_id].'&field_name='.$_GET[field_name]);

		 $this->template->set_var('action',$action);
		 $this->template->set_var('lang',$GLOBALS[phpgw_info][user][preferences][common][lang]);
		 $this->template->set_var('lang_field_orig_name',lang('Fieldname'));
		 $this->template->set_var('field_orig_name',$_GET[field_name]);
		 $this->template->set_var('lang_field_help_information',lang('Field Helpinfo and Alternative Name Configuration'));
		 $this->template->set_var('fld_info_cnf',lang('Field Info Configuration'));
		 $this->template->set_var('lang_alt_name',lang('Alternative Fieldname for display'));
		 $this->template->set_var('lang_help_text',lang('Extra help information for this field'));

		 $field_conf_arr=$this->bo->so->get_field_values($object_arr[object_id],$_GET[field_name]);

		 $this->template->set_var('val_field_alt_name',$field_conf_arr[field_alt_name]);
		 $this->template->set_var('val_field_help_info',$field_conf_arr[field_help_info]);

		 $this->template->pparse('out','head');

		 $this->ui->msg_box($this->bo->message,$true);
		 unset($this->bo->message);

		 $this->template->pparse('out','bodyhead');

		 $this->template->set_var('fld_name',$_GET[hidden_name]);
		 $this->template->set_var('buttons_visibility',$buttons_visibility);
		 $this->template->set_var('newconfig',$newconfig);
		 $this->template->set_var('save',lang('save'));
		 $this->template->set_var('cancel',lang('cancel'));
		 $this->template->pparse('out','footer');

		 $this->bo->save_sessiondata();
	  }

	  /**
	  @function plug_config
	  @abstract make form to set field plugin configuration
	  @fixme parse config options and help info through lang()
	  */
	  function plug_config()
	  {
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $object_arr=$this->bo->so->get_object_values($_GET[object_id]);

		 /* for backwards compatibility */
		 if(!empty($object_arr[plugins]))
		 {
			$this->bo->upgrade_plugins($object_arr[object_id]);
			$GLOBALS['phpgw']->common->phpgw_exit();
		 }

		 $this->template->set_file(array('config' => 'frm_conf_plugins.tpl'));

		 $this->template->set_block('config','head','head');
		 $this->template->set_block('config','bodyhead','bodyhead');
		 $this->template->set_block('config','row','row');
		 $this->template->set_block('config','footer','footer');

		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';
		 if(!file_exists($theme_css))
		 {
			$theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';
		 }

		 $app = $GLOBALS['phpgw_info']['flags']['currentapp'];
		 $app = $app ? ' ['.(isset($GLOBALS['phpgw_info']['apps'][$app]) ? $GLOBALS['phpgw_info']['apps'][$app]['title'] : lang($app)).']':'';

		 $var = Array(
			'img_icon'      => PHPGW_IMAGES_DIR . '/favicon.ico',
			'img_shortcut'  => PHPGW_IMAGES_DIR . '/favicon.ico',
			'charset'       => $GLOBALS['phpgw']->translation->charset(),
			'font_family'   => $GLOBALS['phpgw_info']['theme']['font'],
			'website_title' => $GLOBALS['phpgw_info']['server']['site_title'].$app,
			'theme_css'     => $theme_css,
			'css'           => $GLOBALS['phpgw']->common->get_css(),
			'java_script'   => $GLOBALS['phpgw']->common->get_java_script(),
			'css_file'		=> ''
		 );
		 $this->template->set_var($var);

		 $use_records_cfg=False;

		 $plugin_name=$_GET['plug_name'];

		 $action=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.boadmin.save_field_plugin_conf&object_id='.$_GET[object_id].'&field_name='.$_GET[field_name].'&plug_name='.$_GET[plug_name]);


		 if($_GET[close_me]=='true') $body_tags = 'onLoad="self.close()"';

		 $this->template->set_var('action',$action);
		 $this->template->set_var('body_tags',$body_tags);
		 $this->template->set_var('lang',$GLOBALS[phpgw_info][user][preferences][common][lang]);
		 $this->template->set_var('plug_name',$this->bo->plugins[$plugin_name]['title']);
		 $this->template->set_var('lang_plugin_name',lang('Plugin name'));
		 $this->template->set_var('lang_fieldname',lang('Fieldname'));
		 $this->template->set_var('fieldname',$_GET[field_name]);
		 $this->template->set_var('lang_version',lang('Version'));
		 $this->template->set_var('lang_plugin_configuration',lang('Plugin Configuration'));
		 $this->template->set_var('plug_version',$this->bo->plugins[$plugin_name]['version']);
		 $this->template->set_var('plug_descr',$this->bo->plugins[$plugin_name]['description']);

		 $screenshot_file=$GLOBALS['phpgw']->common->get_app_dir('jinn').'/plugins/plugin_images/'.$plugin_name.'.png';
		 if(is_file($screenshot_file)) $screenshot='<img style="border:solid 1px black" src="jinn/plugins/plugin_images/'.$plugin_name.'.png" alt="'.lang('screenshot').'"  />';

		 $this->template->set_var('screenshot',$screenshot);

		 $field_conf_arr=$this->bo->so->get_field_values($object_arr[object_id],$_GET[field_name]);


		 $plugins_conf_arr=unserialize(base64_decode($field_conf_arr[field_plugins]));

		 if (is_array($plugins_conf_arr))
		 {
			if ($_GET[plug_name]==$_GET[plug_orig]) $use_records_cfg=True;
		 }

		 // get config fields for this plugin
		 // if hidden value is empty get defaults vals for this plugin
		 $cfg=$this->bo->plugins[$plugin_name]['config'];
		 $cfg_help=$this->bo->plugins[$plugin_name]['config_help'];

		 if(is_array($cfg))
		 {
			$this->template->set_var('fld_plug_cnf',lang('Field plugin configuration'));
		 }
		 else
		 {
			$this->template->set_var('fld_plug_cnf','');
			$buttons_visibility='visibility:hidden;';
		 }

		 $this->template->pparse('out','head');

		 $this->ui->msg_box($this->bo->message,$true);
		 unset($this->bo->message);

		 $this->template->pparse('out','bodyhead');

		 if(is_array($cfg))
		 {
			foreach($cfg as $cfg_key => $cfg_val)
			{
			   /* replace underscores for spaces */
			   $render_cfg_key='<strong>'.ereg_replace('_',' ',$cfg_key).'</strong>';
			   if($cfg_help[$cfg_key]) $render_cfg_key .= '<br/><i>'.$cfg_help[$cfg_key].'</i>';

			   $val=$cfg_val;
			   $rowval=($rowval=='row_on')? 'row_off' : 'row_on';

			   /* if configuration is already set use these values */
			   if ($use_records_cfg)
			   {
				  $set_val=$plugins_conf_arr[conf][$cfg_key];
			   }

			   $this->template->set_var('rowval',$rowval);
			   $this->template->set_var('descr',$render_cfg_key);

			   switch ($val[1])
			   {
				  case 'radio' :
					 foreach($val[0] as $radio)
					 {
						unset($checked);
						if($set_val==$radio) $checked='checked';
						$output='<input name="'.$cfg_key.'" type="radio" '.$checked.' value="'.$radio.'">'.$radio.'<br/>';
					 }
					 break;
				  case 'text'  :
					 if ($use_records_cfg) $val[0]=$set_val;
					 $output= '<input name="'.$cfg_key.'" type="text" '.$val[2].' value="'.$val[0].'">';
					 break;
				  case 'area'  :
					 if ($use_records_cfg) $val[0]=$set_val;
					 $output= '<textarea name="'.$cfg_key.'" rows="5" cols="50">'.$val[0].'</textarea>';
					 break;
				  case 'select':
					 $output= '<select name="'.$cfg_key.'">';
						foreach($val[0] as $option)
						{
						   unset($selected);
						   if($set_val==$option) $selected='selected';
						   $output.='<option value="'.$option.'" '.$selected.'>'.$option.'</option>';
						}
						$output.='</select>';
					 break;
				  case 'none'  :
					 unset($output);
					 break;
					 default      :
					 $output.= '<input name="'.$cfg_key.'" type=text value="'.$val[0].'">';
				  }

				  $this->template->set_var('fld',$output);
				  $this->template->parse('rows','row',true);

				  if($newconfig) $newconfig.='+";"+';
				  $newconfig.='"'.$cfg_key.'~"+document.popfrm.'.$cfg_key.'.value';
			   }

			   $this->template->pparse('out','rows');
			}

			$this->template->set_var('fld_name',$_GET[hidden_name]);
			$this->template->set_var('buttons_visibility',$buttons_visibility);
			$this->template->set_var('newconfig',$newconfig);
			$this->template->set_var('save',lang('save'));
			$this->template->set_var('cancel',lang('cancel'));
			$this->template->pparse('out','footer');

			$this->bo->save_sessiondata();
		 }

		 /**
		 @function import_egw_jinn_site
		 @depreciated
		 */
		 function import_egw_jinn_site()
		 {
			$this->load_site_from_file();
		 }

		 function import_object()
		 {
			if (is_array($GLOBALS[HTTP_POST_FILES][importfile]))
			{
			   $num_objects=0;
			   $import=$GLOBALS[HTTP_POST_FILES][importfile];

			   @include($import[tmp_name]);
			   if ($import_object && $checkbit)
			   {
				  while(list($key, $val) = each($import_object)) 
				  {
					 if ($key=='parent_site_id') $val=$_POST[parent_site_id];
					 $data[] = array
					 (
						'name' => $key,
						'value' => addslashes($val) 
					 );

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
					 $this->bo->message[info].= lang('Import was succesfull'). '<br/>' .lang('The name of the new object is <strong>%1</strong>.',$new_name);

				  }

				  /* site import has succeeded, go on with objects */
				  if($proceed)
				  {
					 /* objects are imported , go on with obj-fields */
					 if(is_array($import_obj_fields))
					 {
						foreach($import_obj_fields as $obj_field)
						{
						   $obj_field[field_parent_object]=$new_object_id;

						   unset($data_fields);
						   while(list($key2, $val2) = each($obj_field)) 
						   {
							  if ($key2=='obj_serial') 
							  {
								 continue;  
							  }

							  $data_fields[] = array
							  (
								 'name' => $key2,
								 'value' => addslashes($val2) 
							  );

						   }
						   if ($field_id[]=$this->bo->so->validateAndInsert_phpgw_data('egw_jinn_obj_fields',$data_fields))
						   {
							  $num_fields=count($field_id);
						   } 
						}
					 }

					 $this->bo->message[info].='<br/>'.lang('%1 Site Objects have been imported.',1);
					 $this->bo->message[info].='<br/>'.lang('%1 Site Obj-fields have been imported.',$num_fields);
				  }
				  else
				  {
					 $this->bo->message[error].= lang('Import failed');
				  }
				  
				  $this->bo->save_sessiondata();
				  $this->bo->common->exit_and_open_screen('jinn.uiadmin.add_edit_site&where_key=site_id&where_value='.$_POST[parent_site_id]);
		 }

			}
			else
			{
			   $this->template->set_file(array(
				  'import_form' => 'import_object.tpl',
			   ));

			   $this->ui->header(lang('Import JiNN-Object'.$table));
			   $this->ui->msg_box($this->bo->message);
			   unset($this->bo->message);

			   $this->template->set_var('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.import_object'));
			   $this->template->set_var('lang_Select_JiNN_site_file',lang('Select JiNN object file (*.jobj'));
			   //			   $this->template->set_var('lang_Replace_existing_Site_with_the_same_name',lang('Replace existing site with the same name?'));
			   $this->template->set_var('parent_site_id',$this->bo->where_value);
			   $this->template->set_var('lang_submit_and_import',lang('submit and import'));
			   $this->template->pparse('out','import_form');
			}

			$this->bo->save_sessiondata();

		 }

		 /**
		 @function load_site_from_file
		 @abstract create a new site or replace an existing from a JiNN file
		 */
		 function load_site_from_file()
		 {
			if (is_array($GLOBALS[HTTP_POST_FILES][importfile]))
			{
			   $num_objects=0;
			   $import=$GLOBALS[HTTP_POST_FILES][importfile];

			   @include($import[tmp_name]);
			   if ($import_site && $checkbit)
			   {

				  while(list($key, $val) = each($import_site)) 
				  {
					 $data[] = array
					 (
						'name' => $key,
						'value' => addslashes($val) 
					 );

				  }

				  $new_site_name=$data[0][value];	
				  $thissitename=$this->bo->so->get_sites_by_name($new_site_name);

				  if($GLOBALS[HTTP_POST_VARS][replace_existing] && count($thissitename)>=1)
				  {
					 $new_site_id=$thissitename[0];
					 $this->bo->so->upAndValidate_phpgw_data('egw_jinn_sites',$data,'site_id',$new_site_id);

					 // remove all existing objects
					 $this->bo->so->delete_phpgw_data('egw_jinn_objects',parent_site_id,$new_site_id);


					 $this->bo->message[info].= lang('Import was succesfull').'<br/>'.lang('Replaced existing site named <strong>%1</strong>.',$new_site_name);

					 $proceed=true;
				  }
				  /* insert as new site */
				  elseif($status=$this->bo->so->insert_phpgw_data('egw_jinn_sites',$data))
				  {
					 $new_site_id=$status[where_value];

					 if(count($thissitename)>=1)
					 {
						$new_name=$new_site_name.' ('.lang('another').')';

						$datanew[]=array(
						   'name'=>'site_name',
						   'value'=>$new_name
						);
						$this->bo->so->upAndValidate_phpgw_data('egw_jinn_sites',$datanew,'site_id',$new_site_id);
					 }
					 else
					 {
						$new_name=$new_site_name;
					 }
					 $proceed=true;
					 $this->bo->message[info].= lang('Import was succesfull'). '<br/>' .lang('The name of the new site is <strong>%1</strong>.',$new_name);

				  }

				  /* site import has succeeded, go on with objects */
				  if($proceed)
				  {
					 if (is_array($import_site_objects))
					 {
						foreach($import_site_objects as $object)
						{
						   unset($data_objects);
						   while(list($key2, $val2) = each($object)) 
						   {
							  if ($key2=='parent_site_id') $val2=$new_site_id;

							  $data_objects[] = array
							  (
								 'name' => $key2,
								 'value' => addslashes($val2) 
							  );

						   }
						   if ($object_id[]=$this->bo->so->validateAndInsert_phpgw_data('egw_jinn_objects',$data_objects))
						   {
							  $num_objects=count($object_id);
						   } 
						}

					 }

					 /* objects are imported , go on with obj-fields */
					 if(is_array($import_obj_fields))
					 {
						foreach($import_obj_fields as $obj_field)
						{
						   $tmp_object_arr=$this->bo->so->get_object_values('',$obj_field[obj_serial]);
						   
						   if(!$tmp_object_arr[object_id]) 
						   {
							  continue;
						   }

						   $obj_field[field_parent_object]=$tmp_object_arr[object_id];

						   unset($data_fields);
						   while(list($key2, $val2) = each($obj_field)) 
						   {
							  if ($key2=='obj_serial') 
							  {
								 continue;  
							  }

							  $data_fields[] = array
							  (
								 'name' => $key2,
								 'value' => addslashes($val2) 
							  );

						   }
						   if ($field_id[]=$this->bo->so->validateAndInsert_phpgw_data('egw_jinn_obj_fields',$data_fields))
						   {
							  $num_fields=count($field_id);
						   } 
						}



					 }


					 $this->bo->message[info].='<br/>'.lang('%1 Site Objects have been imported.',$num_objects);
					 $this->bo->message[info].='<br/>'.lang('%1 Site Obj-fields have been imported.',$num_fields);
					 $this->bo->save_sessiondata();
					 $this->bo->common->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
				  }
				  else
				  {
					 $this->bo->message[error].= lang('Import failed');
					 $this->bo->save_sessiondata();
					 $this->bo->common->exit_and_open_screen('jinn.uiadmin.browse_egw_jinn_sites');
				  }
			   }

			}
			else
			{
			   $this->template->set_file(array(
				  'import_form' => 'import.tpl',
			   ));

			   $this->ui->header(lang('Import JiNN-Site'.$table));
			   $this->ui->msg_box($this->bo->message);
			   unset($this->bo->message);

			   $this->template->set_var('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.import_egw_jinn_site'));
			   $this->template->set_var('lang_Select_JiNN_site_file',lang('Select JiNN site file'));
			   $this->template->set_var('lang_Replace_existing_Site_with_the_same_name',lang('Replace existing site with the same name?'));
			   $this->template->set_var('lang_submit_and_import',lang('submit and import'));
			   $this->template->pparse('out','import_form');
			}

			$this->bo->save_sessiondata();

		 }

		 /**
		 @function export_site
		 @depreciated
		 */
		 function export_site()
		 {
			$this->save_site_to_file();
		 }

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
			$version=$GLOBALS[phpgw_info][apps][jinn][version];
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
			$out.='	** JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare   **'."\n";
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

			//			$out.= "/* OBJECT ARRAY */\n";
			/*
			$out.= '$import_object=array('."\n";

			while (list ($key, $val) = each($site_data[0])) 
			{
			   if($key!='site_id') $out.= "	'$key '=> '$val',\n";
			}
			$out.=");\n\n";
			*/

			//			$site_object_data=$this->bo->so->get_phpgw_record_values('egw_jinn_objects','parent_site_id', $this->bo->where_value ,'','','name');

			$out.= "\n/* OBJECT ARRAY */\n";

			if(is_array($object_data))
			{
			   foreach($object_data as $object)
			   {
				  $object[serialnumber]=time()+$i++;


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
				  store them as array with serialnumber as parent_object_id
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
							  $out .= "	'$key' => '".ereg_replace("'","\'",$val)."',\n"; 
						   }
						}
						$out .= "	'obj_serial' => '".$object[serialnumber]."',\n"; 

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
		 @function save_site_to_file
		 @abstract save site-, objects- and fields-configuration to a JiNN-file
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
			$out.='	** JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare   **'."\n";
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
			   if($key!='site_id') $out.= "	'$key '=> '$val',\n";
			}
			$out.=");\n\n";


			$site_object_data=$this->bo->so->get_phpgw_record_values('egw_jinn_objects','parent_site_id', $this->bo->where_value ,'','','name');

			$out.= "\n/* SITE_OBJECT ARRAY */\n";

			if(is_array($site_object_data))
			{
			   foreach($site_object_data as $object)
			   {
				  /* set serial to be backwards compitable */
				  //				  if(!$object[serialnumber])
				  //				  {
					 $object[serialnumber]=time()+$i++;
					 //				  }


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
					 store them as array with serialnumber as parent_object_id
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
								 $out .= "	'$key' => '".ereg_replace("'","\'",$val)."',\n"; 
							  }
						   }
						   $out .= "	'obj_serial' => '".$object[serialnumber]."',\n"; 

						   $out.=");\n\n";

						}
					 }

				  }
			   }

			   $out.='$checkbit=true;'."\n";
			   $out.='?>';
			   echo $out;
			}

		 }
	  ?>
