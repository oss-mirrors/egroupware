<?
/*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   phpGroupWare - http://www.phpgroupware.org
   
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



class boplugins extends bojinn
{

	//var $bo;

	function boplugins($site,$site_object) {

		$this->bo->site=$site;
		$this->bo->site_object=$site_object;
		
		//$this->bo = CreateObject('jinn.bojinn');
		$this->include_plugins(); // include all form input plugins
		//$this->include_sfp_plugins(); // include all storage filter plugins

	}

	/****************************************************************************\
	* include ALL plugins
	\***************************************************************************/

	function include_plugins()
	{
		// read form_plugin directory
		// include all 'plugin.' files;


		//$local_bo=$this->bo;
		//var_dump($local_bo);
		if ($handle = opendir('jinn/plugins')) {

			/* This is the correct way to loop over the directory. */

			while (false !== ($file = readdir($handle))) 
			{ 
				if (substr($file,0,7)=='plugin.')
				{

					include('jinn/plugins/'.$file);


				}
			}

			closedir($handle); 
		}


	}

	/****************************************************************************\
	* include all FIP plugins							                         *
	\***************************************************************************/

	function include_sfp_plugins()
	{
		// read form_plugin directory
		// include all 'plugin.' files;


		if ($handle = opendir('jinn/plugins/sfp')) {

			/* This is the correct way to loop over the directory. */

			while (false !== ($file = readdir($handle))) 
			{ 
				if (substr($file,0,7)=='plugin.')
				{

					include('jinn/plugins/sfp/'.$file);


				}
			}

			closedir($handle); 
		}


	}

	/****************************************************************************\
	* make possible plugin options for this fieldtype                            *
	\****************************************************************************/

	function make_plugins_options($fieldtype,$value)
	{
		if ($fieldtype=='blob') $fieldtype='text';

		if (count($this->plugins>0))
		{

			foreach($this->plugins as $plugin)
			{
				$enable=false; //set off again



				foreach($plugin['db_field_hooks'] as $hook)
				{
					if ($hook==$fieldtype) $enable=true;
				}


				if ($enable)
				{
					unset($selected);
					//var_dump($value);
					if ($value==$plugin['name']) $selected='selected';
					$input.= '<option value="'.$plugin['name'].'" '.$selected.'>'.$plugin['title'].'</option>';
				}

			}
			if ($input) $input='<option></option>'.$input;
			return $input;
		}

	}

	/****************************************************************************\
	* make possible plugin options for this fieldtype                            *
	\****************************************************************************/

	function make_sfp_plugins_options($fieldtype,$value)
	{
		if ($fieldtype=='blob') $fieldtype='text';

				if (count($this->plugins['sfp']>0))
		{

			foreach($this->plugins['sfp'] as $plugin)
			{
				$enable=false; //set off again

				foreach($plugin['db_field_hooks'] as $hook)
				{
					if ($hook==$fieldtype) $enable=true;
				}


				if ($enable)
				{
					unset($selected);
					if ($value==$plugin['name']) $selected='selected';
					$input.= '<option value="'.$plugin['name'].'" '.$selected.'>'.$plugin['title'].'</option>';
				}

			}
			
			if ($input) $input='<option></option>'.$input;
			return $input;
		}

	}
/*
	function get_plugin($input_name,$value,$type)
	{
		$plugins=explode('|',$this->bo->site_object['plugins']);
		foreach($plugins as $plugin)
		{

			$sets=explode(':',$plugin);

			if (substr($input_name,3)==$sets[0])
			{
				$input=call_user_func('plugin_'.$sets[1],$input_name,$value,$this->bo);

				return $input;
			}
			else // anders terugvallen op standaard plugin
			{
				$input=call_user_func('plugin_def_'.$type,$input_name,$value);
				return $input;	
			}
		}
	}

*/

	
	/* form input plugin */
	function get_plugin($input_name,$value,$type)
	{
		$plugins=explode('|',$this->bo->site_object['plugins']);
		foreach($plugins as $plugin)
		{

			//var_dump($plugins);
			$sets=explode(':',$plugin);
			if (substr($input_name,3)==$sets[0])
			{
				$input=call_user_func('plugin_'.$sets[1],$input_name,$value,$sets[3],$this);

				return $input;
			}
			else /* fall back on default plugin */
			{
				$input=call_user_func('plugin_def_'.$type,$input_name,$value,'','');
				return $input;	
			}
		}
	}

	/* storage filter plugin */
	function get_sfp_plugin($key,$value)
	{
		$plugins=explode('|',$this->bo->site_object['plugins']);
		foreach($plugins as $plugin)
		{

			$sets=explode(':',$plugin);

			if ($sets[2]=='sf' && substr($input_name,3)==$sets[0])
			{
				$data=call_user_func('plugin_'.$sets[1],$key,$value,$sets[3],$this);

				return $data;
			}
			
			else return False;
			/* fall back on default plugin */
			/*
			{
				$input=call_user_func('plugin_def_'.$type,$input_name,$value,'','');
				return $input;	
			}
			*/
			
		}
	}





}




?>
