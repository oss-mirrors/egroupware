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
	var $local_bo;
	
	function boplugins($site,$site_object) {

		$this->bo->site=$site;
		$this->bo->site_object=$site_object;
		$this->include_plugins(); // include all form input plugins
	}

	/****************************************************************************\
	* include ALL plugins
	\***************************************************************************/

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

					include(PHPGW_SERVER_ROOT.'/jinn/plugins/'.$file);
				}
			}
			closedir($handle); 
		}
	}


	/****************************************************************************\
	* make possible plugin options for this fieldtype                            *
	\****************************************************************************/

	/* is function is called by admin_add_edit_site_object
	   maybe later also by preferences
	*/
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
					if ($value==$plugin['name']) $selected='selected';
					$input.= '<option value="'.$plugin['name'].'" '.$selected.'>'.$plugin['title'].'</option>';
				}

			}
			if ($input) $input='<option></option>'.$input;
			return $input;
		}

	}
	
	/* form input function from plugin */
	
	function get_plugin_fi($input_name,$value,$type)
	{
		$plugins=explode('|',$this->bo->site_object['plugins']);
		foreach($plugins as $plugin)
		{
			$sets=explode(':',$plugin);
			if (substr($input_name,3)==$sets[0])
			{
				$input=call_user_func('plg_fi_'.$sets[1],$input_name,$value,$sets[3]);

			}
		}
	
		if (!$input) $input=call_user_func('plg_fi_def_'.$type,$input_name,$value,'');
		return $input;
		

	}

	/* get storage filter from plugin */
	function get_plugin_sf($key,$HTTP_POST_VARS,$HTTP_POST_FILES)
	{
		$plugins=explode('|',$this->bo->site_object['plugins']);
		foreach($plugins as $plugin)
		{
			$sets=explode(':',$plugin);

			if (substr($key,3)==$sets[0])
			{
				$data=call_user_func('plg_sf_'.$sets[1],$key,$HTTP_POST_VARS,$HTTP_POST_FILES,$sets[3],$this->bo);
			}
		}
		
		return $data;
	}

}

?>
