<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
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
   */

	class db_fields_plugin_timestamp
	{
	
		function db_fields_plugin_timestamp()
		{
		}
	
	   function formview_edit($field_name,$value,$config,$attr_arr)
	   {	
		  global $local_bo;
		  $stripped_name=substr($field_name,6);	
	
		  global $local_bo;
		  if ($value)
		  {
			 $input=$local_bo->so->site_db->Link_ID->UserTimeStamp($value);
		  }
		  else
		  {
			 $input = lang('automatic');
		  }
	  
		  $usernewstamp='checked="checked"';
		  unset($userkeepstamp);
		  
		  $input.='<input type="hidden" name="'.$field_name.'" value="'.$value.'" />';
		  if($config[Default_action]=='Leave value untouched')
		  {	   
			 unset($usernewstamp);
			 $userkeepstamp='checked="checked"';
		  }
		 
		  if($config[Allow_users_to_choose_action]=='True')
		  {
			 $input.='<br/><input '.$userkeepstamp.' type="radio" name="NWSTMP'.$field_name.'" value="false" />'.lang('Keep current timestamp').'<br/>';
			 $input.='<input '.$usernewstamp.' type="radio" name="NWSTMP'.$field_name.'" value="true" />'.lang('Give a new timestamp').'<br/>';
		  }
	
		  return $input;
	   }
	
	   function formview_read($value,$config)
	   {	
		  return $this->listview_read($value,$config,'');
	   }
	
	   function listview_read($value,$config,$attr_arr)
	   {	
		  global $local_bo;
	
		  $fmt=($config[Display_format]?$config[Display_format]:'y-M-d H:i:s');
		  $input=$local_bo->so->site_db->Link_ID->UserTimeStamp($value,$fmt);
	
		  return $input;
	   }
	
	   function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	   {
		  if(!$HTTP_POST_VARS[$field_name] || $HTTP_POST_VARS['NWSTMP'.$field_name]=='true')
		  {	   
			 return 'Now()';
		  }
		  elseif($HTTP_POST_VARS['NWSTMP'.$field_name]!='false' && $config[Default_action]!='Leave value untouched') 
		  {
			 return 'Now()';
		  }
	   }
	}	
?>
