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
   class plugins
   {
	  var $local_bo;
	  var $test;
	  /*!
	  @function plugins
	  @abstract standard contructure that includes all plugins
	  */
	  function plugins()
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

		 /* first boobytrap */
		 if(!$field_values[field_plugins] || substr($input_name,6)!=$field_values[field_name])
		 {
			return call_user_func('plg_fi_def_'.$type,$input_name,$value,'',$attr_arr);
		 }

		 $plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));

		 /* second boobytrap */
		 if(!is_array($plug_conf_arr))
		 {
			return call_user_func('plg_fi_def_'.$type,$input_name,$value,'',$attr_arr);
		 }

		 // last boobytrap, look for valid field-prefixes (MLTX##, FLDXXX, O2OX##)
		 if ( substr($input_name,0,4)=='MLTX' || substr($input_name,0,6)=='FLDXXX' || substr($input_name,0,4)=='O2OX' )
		 {
			$input=@call_user_func('plg_fi_'.$plug_conf_arr[name],$input_name,$value,$plug_conf_arr[conf],$attr_arr);
		 }

		 if (!$input) $input=call_user_func('plg_fi_def_'.$type,$input_name,$value,'',$attr_arr);

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
		 global $local_bo;
		 $local_bo=$this->local_bo;

		 if($field_values[field_plugins] && $field_name==$field_values[field_name])
		 {
			$plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));

			if(is_array($plug_conf_arr))
			{
			   $new_value=@call_user_func('plg_bv_'.$plug_conf_arr[name],$value,$plug_conf_arr[conf],$where_val_encoded,$field_name);
			   if($plug_conf_arr[name])
			   {
				  return $new_value;
			   }
			}
		 }

		 $new_value=$value;
		 if(strlen($new_value)>20)
		 {
			$new_value = strip_tags($new_value);

			$new_value = '<span title="'.substr($new_value,0,200).'">' . substr($new_value,0,20). ' ...' . '</span>';
		 }

		 return $new_value;
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

		 if($field_values[field_plugins] && substr($form_field_name,6)==$field_values[field_name])
		 {
			$plug_conf_arr=unserialize(base64_decode($field_values[field_plugins]));

			if(is_array($plug_conf_arr))
			{
			   $data=@call_user_func('plg_sf_'.$plug_conf_arr[name],$form_field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$plug_conf_arr[conf]);
			}
		 }

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

		 if(is_array($plug_arr))
		 {

			$new_value=@call_user_func('plg_ro_'.$plug_arr[name],$value,$plug_arr[conf]);
		 }

		 if (!$new_value)
		 {
			return $value;
		 }

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

		 $success=@call_user_func('plg_afa_'.$action_plugin_name,$_GET[where],$_GET[attributes],$plug_arr[conf]);

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
	  @function include_plugins
	  @abstract include ALL plugins
	  */
	  function include_plugins()
	  {
		 global $local_bo;
		 $local_bo=$this;
		 if ($handle = opendir(PHPGW_SERVER_ROOT.'/jinn/plugins')) {

			/* This is the correct way to loop over the directory. */

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
	  
	  /**
	  @function plugin_hooks
	  @abstract get plugins that hook with the given fieldtype
	  @return array with plugins
	  @param string $fieldtype is a jinn field type which is greatly generalized
	  */
	  function plugin_hooks($fieldtype)
	  {
//		 if ($fieldtype=='blob') $fieldtype='text';

		 if (count($this->plugins>0))
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
			return $plugin_hooks;
		 }
	  }



	  
	  /**
	  @function plugin_hooks
	  @abstract get plugins that hook with the given fieldtype
	  @return array with plugins
	  @param string $fieldtype 
	  */
	  function get_default_plugin($fieldtype)
	  {
//		 if ($fieldtype=='blob') $fieldtype='text';

		 $i=1;
		 if (count($this->plugins>0))
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
			return $plugin_hooks;
		 }
	  }

   }

?>
