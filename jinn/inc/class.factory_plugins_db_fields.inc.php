<?php
   /**************************************************************************\
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
   Copyright (C)2002, 2004 Pim Snel <pim@lingewoud.nl>

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
   \**************************************************************************/

   /**
   * Plugin wrapper class, everything about plugins 
   *
   * @package jinn_core
   * @author pim-AT-lingewoud-DOT-nl
   * @copyright (c) 2005 by Pim Snel
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class factory_plugins_db_fields
   {
	  var $local_bo;	//this is a reference to a bo class that must be set by the class that instanciates this
	  var $test;
	  var $registry;
	  var $_plugins;	//fixme: rename this to $plugins when OLD STYLE plugins have been removed entirely.

	  /**
	  * constructor standard contructure that includes all plugins
	  */
	  function factory_plugins_db_fields()
	  {
		 $this->include_plugins();
	  }

	  /**
	  * call formview_edit function from plugin 
	  *
	  * @param string $input_name name of the form field prefix included
	  * @param string $value value of the form field
	  * @param string $type JiNN data type of this field
	  * @param array $field_values $field configuration array
	  * @param array $attr_arr extra attributes like length of database field which can change the behaviour of the plugin
	  * @return a part of the form as html       
	  */
	  function call_plugin_fi($input_name, $value, $type, $field_values, $attr_arr)
	  {
		 $plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));
		 if($field_values[field_type] == '' && $type != '')
		 {
			$field_values[field_type] = $type;
		 }
		 //_debug_array($field_values);

		 if (substr($input_name,0,4)=='MLTX' || substr($input_name,0,6)=='FLDXXX' || substr($input_name,0,4)=='O2OX' || substr($input_name,0,4)=='M2OX')
		 {
			$plug_html = $this->call_plugin($plug_conf_arr[name], 'formview_edit', $value, $plug_conf_arr[conf], '', $input_name, $attr_arr,'','',$field_values);
		 }
		 else
		 {
			return null;
		 }

		 if($plug_html!=null)
		 {
			//return preferences button for user
			//return config button for developer
			$return_val['pref']=false;
			$return_val['plugname']=$plug_conf_arr[name];
			$return_val['config']=true;
			$return_val['html']=$plug_html;
		 }

		 return $return_val;
	  }

	  /**
	  * call list view (browse view) function from plugin
	  *
	  * @param $field_name fieldname including prefixes
	  * @param $value field value
	  * @param $where_value_encoded part of the SQL statement to select this record, base64_encoded
	  * @param $field_values $field configuration array
	  * @return one cell of the list in formatted html
	  */
	  function call_plugin_bv($field_name,$value,$where_val_encoded,$field_values, $type='')
	  {
		 $plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));
		 if($field_values[field_type] == '' && $type != '')
		 {
			$field_values[field_type] = $type;
		 }
		 //_debug_array($plug_conf_arr[conf]);
		 return $this->call_plugin($plug_conf_arr[name], 'listview_read', $value, $plug_conf_arr[conf], $where_val_encoded, $field_name,'','','',$field_values);
	  }

	  /**
	  * call storage filter function from plugin
	  *
	  * @param $form_field_name fieldname including prefixes
	  * @param $field_values $field configuration array
	  * @param $HTTP_POST_VARS $HTTP_POST_VARS
	  * @param $HTTP_POST_FILES $HTTP_POST_FILES
	  * @return the field data that gets stored into the database
	  */
	  function call_plugin_sf($form_field_name, $field_values,$HTTP_POST_VARS,$HTTP_POST_FILES)
	  {
		 $plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));
		 return $this->call_plugin($plug_conf_arr[name],'on_save_filter','',$plug_conf_arr[conf],'',$form_field_name,'',$HTTP_POST_VARS,$HTTP_POST_FILES,$field_values);
	  }


	  /**
	  * get view (readonly) function from plugin
	  *
	  * @param $value value of the field
	  * @param $field_values $field configuration array
	  */
	  function call_plugin_ro($value, $field_values, $type='')
	  {
		 $plug_arr=unserialize(base64_decode($field_values[field_plugins]));
		 if($field_values[field_type] == '' && $type != '')
		 {
			$field_values[field_type] = $type;
		 }
		 return $this->call_plugin($plug_arr[name], 'formview_read', $value, $plug_arr[conf], '', '', '', '', '',$field_values);
	  }

	  /**
	  * call autonome form action script-function from from plugin
	  * 
	  * @param string $field_values containes a base64_encoded serialed array with information to call the right plugin
	  */
	  function call_plugin_afa($field_values)
	  {
		 $plug_arr=unserialize(base64_decode($field_values[field_plugins]));
		 $success = $this->call_plugin($plug_arr[name], 'advanced_action', '', $plug_arr[conf], $_GET[where], '', $_GET[attributes], '', '',$field_values);

	/*	 if ($success)
		 {
			$this->session['message'][info]=lang('Action was succesful.');
			$this->sessionmanager->save();
			$this->exit_and_open_screen('jinn.uiuser.index');
		 }
		 else
		 {
			$this->session['message'][error]=lang('Action was not succesful. Unknown error');
			$this->sessionmanager->save();
			$this->exit_and_open_screen('jinn.uiuser.index');
		 }
		 */
	  }

	  function call_config_function($name,$config,$form_action)
	  {
		 if($this->loaded($name))
		 {
			$this->_plugins[$name]->config_dialog ($config,$form_action);
		 }
	  }


	  function call_plugin($name, $function, $value, $config, $where_val_encoded, $field_name, $attr_arr, $HTTP_POST_VARS, $HTTP_POST_FILES, $field_values)
	  {
		 //_debug_array($config);

		 //NEW STYLE PLUGIN CLASSES
		 if($this->loaded($name))
		 {
			if(method_exists($this->_plugins[$name], $function)) //plugins are not required to implement all functions
			{
			   switch($function)
			   {
				  case 'listview_read':
					 return $this->_plugins[$name]->listview_read  ($value, $config, $where_val_encoded, $field_name);
				  case 'listview_edit':
					 return $this->_plugins[$name]->listview_edit  ($field_name, $value, $config, $attr_arr);
				  case 'formview_read':
					 return $this->_plugins[$name]->formview_read  ($value, $config);
				  case 'formview_edit':
					 return $this->_plugins[$name]->formview_edit  ($field_name, $value, $config, $attr_arr);
				  case 'on_save_filter':
					 return $this->_plugins[$name]->on_save_filter ($field_name, $HTTP_POST_VARS, $HTTP_POST_FILES, $config);
				  case 'advanced_action':
					 return $this->_plugins[$name]->advanced_action($where_val_encoded, $attr_arr, $config);
				  default:
					 return false;
			   }
			}
			else
			{
			   return $value;
			}
		 }
		 elseif($this->plugins[$name])
		 //OLD STYLE PLUGINS
		 {
			switch($function)
			{
			   case 'listview_read':
				  return @call_user_func('plg_bv_'.$name, $value, $config, $where_val_encoded, $field_name);
			   case 'formview_edit':
				  return @call_user_func('plg_fi_'.$name, $field_name, $value, $config, $attr_arr);
			   case 'on_save_filter':
				  return @call_user_func('plg_sf_'.$name, $field_name, $HTTP_POST_VARS, $HTTP_POST_FILES, $config);
			   case 'formview_read':
				  return @call_user_func('plg_ro_'.$name, $value, $config);
			   case 'advanced_action':
				  return @call_user_func('plg_afa_'.$name, $where_val_encoded, $attr_arr, $config);
			   default: 
				  return false;
			}
		 }
		 elseif($replacement = $this->registry->aliases[$name])
		 //find explicit replacement
		 {
			if($this->loaded($replacement) || $this->plugins[$replacement]) 											// make SURE the replacing plugin exists, else we will die in a recursive loop
			{
			   return $this->call_plugin($replacement, $function, $value, $config, $where_val_encoded, $field_name, $attr_arr, $HTTP_POST_VARS, $HTTP_POST_FILES, $field_values);
			}
		 }
		 elseif($replacement = $this->get_default_plugin($field_values[field_type]))
		 //find default for this fieldtype
		 {
			if($this->loaded($replacement[0]['value']) || $this->plugins[$replacement[0]['value']]) 					// make SURE the replacing plugin exists, else we will die in a recursive loop
			{
			   return $this->call_plugin($replacement[0]['value'], $function, $value, $config, $where_val_encoded, $field_name, $attr_arr, $HTTP_POST_VARS, $HTTP_POST_FILES, $field_values);
			}
		 }
		 else
		 //we give up. use the default plugin as final fallback
		 {
			if($this->loaded('default_') || $this->plugins['default_'])
			{
			   return $this->call_plugin('default_', $function, $value, $config, $where_val_encoded, $field_name, $attr_arr, $HTTP_POST_VARS, $HTTP_POST_FILES, $field_values);
			}
		 }
	  }

	  /**
	  * include ALL plugins
	  */
	  function include_plugins()
	  {
		 include_once(PHPGW_SERVER_ROOT.'/jinn/plugins/db_fields_plugins/class.registry.php');
		 $this->registry = new db_fields_registry();

		 if ($handle = opendir(PHPGW_SERVER_ROOT.'/jinn/plugins/db_fields_plugins/')) 
		 {
			while (false !== ($file = readdir($handle))) 
			{ 
			   // OLD STYLE plugins
			   if (substr($file,0,7)=='plugin.')
			   {
				  include_once(PHPGW_SERVER_ROOT.'/jinn/plugins/db_fields_plugins/'.$file);
			   }
			   // NEW STYLE plugins (classes)
			   elseif(substr($file,0,2)=='__') //plugins have their individual folders which start with two underscores (i.e. __boolean)
			   {
				  include_once(PHPGW_SERVER_ROOT.'/jinn/plugins/db_fields_plugins/'.$file.'/register.php');	//each plugin has its own register.php file that fills the registry with info about the plugin
			   }
			}
			closedir($handle); 
		 }
	  }

	  function get_layout_plugins()
	  {
		 // NEW STYLE plugins (classes)
		 if (count($this->registry->plugins)>0)
		 {	
			foreach($this->registry->plugins as $plugin)
			{
			   if($plugin['element_type']=='lay-out')
			   {
				  $layout_plugins[]=array(
					 'value'=>$plugin['name'],
					 'name'=>$plugin['title']
				  ); 
			   }
			}
		 }
		 if(count($layout_plugins)>0)
		 {
			return $layout_plugins;
		 }
	  }
	  
	  /**
	  * get plugins that hook with the given fieldtype
	  *
	  * @param string $fieldtype is a jinn field type which is greatly generalized
	  * @return array with plugins
	  */
	  function plugin_hooks($fieldtype)
	  {
		 $plugin_hooks = array();

		 // OLD STYLE plugins
		 if (count($this->plugins)>0)
		 {	
			foreach($this->plugins as $plugin)
			{
			   foreach($plugin['db_field_hooks'] as $hook)
			   {
				  if ($hook==$fieldtype) 
				  {
					 $plugin_hooks[]=array(
						'value'=>$plugin['name'],
						'name'=>$plugin['title']
					 );
				  }
			   }
			}
		 }

		 // NEW STYLE plugins (classes)
		 if (count($this->registry->plugins)>0)
		 {	
			foreach($this->registry->plugins as $plugin)
			{
			   if(is_array($plugin['db_field_hooks']))
			   {
				  foreach($plugin['db_field_hooks'] as $hook)
				  {
					 if ($hook==$fieldtype) 
					 {
						$plugin_hooks[]=array(
						   'value'=>$plugin['name'],
						   'name'=>$plugin['title']
						);
					 }
				  }
			   }
			}
		 }

		 if(count($plugin_hooks) > 0) return $plugin_hooks;
	  }

	  /**
	  * layout_plugins: collect all form layout plugins
	  * 
	  * @access public
	  * @return void
	  */
	  function layout_plugins()
	  {
		 // only NEW STYLE plugins (classes)
		 if(count($this->registry->plugins)>0)
		 {	
			foreach($this->registry->plugins as $plugin)
			{

			   if($plugin['element_type']=='lay-out')
			   {
				  $layout_plugins[]=$plugin;
			   }
			}
		 }
		 if(count($layout_plugins) > 0) 
		 {
			return $layout_plugins;
		 }
		 else 
		 {
			return array();
		 }
	  }

	  /**
	  * get plugins that hook with the given fieldtype
	  *
	  * @param string $fieldtype 
	  * @return array with plugins
	  */
	  function get_default_plugin($fieldtype)
	  {
		 $plugin_hooks = array();
		 $i=1;
		 // OLD STYLE plugins
		 if (count($this->plugins)>0)
		 {	
			foreach($this->plugins as $plugin)
			{
			   foreach($plugin['db_field_hooks'] as $hook)
			   {
				  if ($hook==$fieldtype) 
				  {
					 if ($plugin['default']==1)
					 {
						$plugin_hooks[]=array(
						   'value'=>$plugin['name'],
						   'name'=>$plugin['title']
						);
					 }
				  }
			   }
			}
		 }
		 // NEW STYLE plugins (classes)
		 if (count($this->registry->plugins)>0)
		 {	
			foreach($this->registry->plugins as $plugin)
			{
			   if(is_array($plugin['db_field_hooks']))
			   {
				  foreach($plugin['db_field_hooks'] as $hook)
				  {
					 if ($hook==$fieldtype) 
					 {
						if ($plugin['default']==1)
						{
						   $plugin_hooks[]=array(
							  'value'=>$plugin['name'],
							  'name'=>$plugin['title']
						   );
						}
					 }
				  }
			   }
			}
		 }
		 if(count($plugin_hooks) > 0) return $plugin_hooks;
	  }

	  function loaded($pluginname)
	  {
///		 _debug_array($this->registry->plugins);	
		 if($this->registry->plugins[$pluginname]) //is this a NEW STYLE class type plugin?
		 {
			if(is_object($this->_plugins[$pluginname])) //is it already loaded?
			{
			   return true;
			}
			else
			{
			   include_once(PHPGW_SERVER_ROOT.'/jinn/plugins/db_fields_plugins/__'.$pluginname.'/class.'.$pluginname.'.php');
			   if(class_exists('db_fields_plugin_'.$pluginname))
			   {
				  eval('$this->_plugins['.$pluginname.'] = new db_fields_plugin_'.$pluginname.'();');	
				  $this->_plugins[$pluginname]->local_bo = &$this->local_bo;
				  $this->_plugins[$pluginname]->plug_root = PHPGW_SERVER_ROOT.'/jinn/plugins/db_fields_plugins/__'.$pluginname;
				  return true;
			   }
			   else
			   {
				  return false; // this should never happen
			   }
			}
		 }
		 else
		 {
			return false; // this could happen as long as there are still OLD STYLE (non class) plugins
		 }
	  }


   }

?>
