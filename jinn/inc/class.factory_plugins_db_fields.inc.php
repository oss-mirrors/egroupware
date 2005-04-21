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

   /*!
   @class plgins
   @abstract JiNN field plugin class
   */
   class factory_plugins_db_fields
   {
	  var $local_bo;
	  var $test;
	  var $registry;
	  var $_plugins;	//fixme: rename this to $plugins when OLD STYLE plugins have been removed entirely.
	  
	  /*!
	  @function plugins
	  @abstract standard contructure that includes all plugins
	  */
	  function factory_plugins_db_fields()
	  {
		 $this->include_plugins();
	  }


	  /*!
	  @function call_plugin_fi
	  @abstract call input function from plugin
	  @param $input_name name of the form field prefix included
	  @param $value value of the form field
	  @param $type JiNN data type of this field
	  @param $field_values $field configuration array
	  @param $attr_arr extra $attributes like length of field which can change the behaviour of the plugin
	  @returns a part of the form as html       
	  */
	  function call_plugin_fi($input_name,$value,$type,$field_values,$attr_arr)
	  {
		 global $local_bo;

		 $local_bo=$this->local_bo;

		 $plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));
		 
		 if ( substr($input_name,0,4)=='MLTX' || substr($input_name,0,6)=='FLDXXX' || substr($input_name,0,4)=='O2OX' )
		 {
			$input = $this->call_plugin($plug_conf_arr[name], 'formview_edit', $value, $plug_conf_arr[conf], '', $input_name, $attr_arr,'','',$field_values);
		 }

		 return $input;
	  }
		
	  /*!
	  @function call_plugin_bv
	  @abstract call list view (browse view) function from plugin
	  @param $field_name fieldname including prefixes
	  @param $value field value
	  @param $where_value_encoded part of the SQL statement to select this record, base64_encoded
	  @param $field_values $field configuration array
	  @returns one cell of the list in formatted html
	  */
	  function call_plugin_bv($field_name,$value,$where_val_encoded,$field_values)
	  {
//	_debug_array('default_');
		 global $local_bo;
		 $local_bo=$this->local_bo;

		 //if($field_values[field_plugins] && $field_name==$field_values[field_name])
		 //{
			$plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));
			//if(is_array($plug_conf_arr))
			//{
			   //if($plug_conf_arr[name])
			   //{
					return $this->call_plugin($plug_conf_arr[name], 'listview_read', $value, $plug_conf_arr[conf], $where_val_encoded, $field_name,'','','',$field_values);
			   //}
			//}
		 //}
/*
		 $new_value=$value;
		 if(strlen($new_value)>20)
		 {
			$new_value = strip_tags($new_value);

			$new_value = '<span title="'.substr($new_value,0,200).'">' . substr($new_value,0,20). ' ...' . '</span>';
		 }

		 return $new_value;
		 */
	  }

	  /*!
	  @function call_plugin_sf
	  @abstract call storage filter function from plugin
	  @param $form_field_name fieldname including prefixes
	  @param $field_values $field configuration array
	  @param $HTTP_POST_VARS $HTTP_POST_VARS
	  @param $HTTP_POST_FILES $HTTP_POST_FILES
	  @returns the field data that gets stored into the database
	  */
	  function call_plugin_sf($form_field_name, $field_values,$HTTP_POST_VARS,$HTTP_POST_FILES)
	  {
		 global $local_bo;
		 $local_bo=$this->local_bo;

		 //if($field_values[field_plugins] && substr($form_field_name,6)==$field_values[field_name])
		 //{
			$plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));

			//if(is_array($plug_conf_arr))
			//{
			   //$data=@call_user_func('plg_sf_'.$plug_conf_arr[name],$form_field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$plug_conf_arr[conf]);
			   $data = $this->call_plugin($plug_conf_arr[name],'on_save_filter','',$plug_conf_arr[conf],'',$form_field_name,'',$HTTP_POST_VARS,$HTTP_POST_FILES,$field_values);
		   //}
		 //}
		
		 return $data;
	  }


	  /*!
	  @function call_plugin_ro
	  @abstract get view (readonly) function from plugin
	  @param $value value of the field
	  @param $field_values $field configuration array
	  */
	  function call_plugin_ro($value,$field_values)
	  {
		 global $local_bo;
		 $local_bo=$this->local_bo;
		 $plug_arr=unserialize(base64_decode($field_values[field_plugins]));

		 //if(is_array($plug_arr))
		 //{

			//$new_value=@call_user_func('plg_ro_'.$plug_arr[name],$value,$plug_arr[conf]);
			$new_value = $this->call_plugin($plug_arr[name], 'formview_read', $value, $plug_arr[conf], '', '', '', '', '',$field_values);
		 //}
/*
		 if (!$new_value)
		 {
			return $value;
		 }
*/
		 return $new_value;
	  }


	  /*!
	  @function call_plugin_afa
	  @abstract call autonome form action script-function from from plugin
	  */
	  function call_plugin_afa($field_values)
	  {
		 global $local_bo;
		 $local_bo=$this->local_bo;

		 if($field_values[field_plugins])
		 {
			$plug_arr=unserialize(base64_decode($field_values[field_plugins]));
		 }

		 $action_plugin_name=$plug_arr[name];

		 //$success=@call_user_func('plg_afa_'.$action_plugin_name,$_GET[where],$_GET[attributes],$plug_arr[conf]);
		 $success = $this->call_plugin($action_plugin_name, 'advanced_action', '', $plug_arr[conf], $_GET[where], '', $_GET[attributes], '', '',$field_values);

		 if ($success)
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
	  
  		function call_plugin($name, $function, $value, $config, $where_val_encoded, $field_name, $attr_arr, $HTTP_POST_VARS, $HTTP_POST_FILES, $field_values)
		{
 			if($this->loaded($name))
			//NEW STYLE PLUGIN CLASSES
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
	  @function include_plugins
	  @abstract include ALL plugins
	  */
	  function include_plugins()
	  {
		 global $local_bo;
		 $local_bo = $this;	//?? fixme..this can't be right..
		 
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
	  
	  /**
	  @function plugin_hooks
	  @abstract get plugins that hook with the given fieldtype
	  @return array with plugins
	  @param string $fieldtype is a jinn field type which is greatly generalized
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
	  @function plugin_hooks
	  @abstract get plugins that hook with the given fieldtype
	  @return array with plugins
	  @param string $fieldtype 
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
