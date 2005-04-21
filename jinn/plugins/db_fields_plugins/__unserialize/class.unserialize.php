<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
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

	class db_fields_plugin_unserialize
	{
	
		function db_fields_plugin_unserialize()
		{
		}
	
	   // FIXME ad config:
	   // 1 readonly 
	   // serialize back again?
	   function formview_edit($field_name,$value,$config,$attr_arr)
	   {	
		  $stripped_name=substr($field_name,6);	
	
		  $input=unserialize($value);
		  if(is_array($input)) $input=var_export($input,true);
	
		  return $input;
	   }
	   
	   function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	   {
		  $input=$HTTP_POST_VARS[$field_name];
		  $output=serialize($input);
	
		  return $output;
	   }
	}
?>
