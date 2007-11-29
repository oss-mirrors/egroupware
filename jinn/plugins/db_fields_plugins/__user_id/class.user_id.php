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

	/* $Id: class.user_id.php 22026 2006-07-07 11:18:42Z mipmip $

	/**
	* PRESET VARCHAR PLUGIN 
	*/
	class db_fields_plugin_user_id
	{
	
		function db_fields_plugin_user_id()
		{
		}
		
		function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
		{
		   if ($HTTP_POST_VARS['FLDXXXuser'] == '')
		   {
			  return $GLOBALS['phpgw_info']['user']['account_id'];
		   }
		}
	
		function formview_read($value,$config)
		{
		   $retval = "<input type=\"hidden\" name=\"$config\" value=\"$value\">".$this->get_username($value);
		   return $retval;
		}

		function formview_edit($config,$value)
		{
		   if ($value == '')
		   {
				$value = $GLOBALS['phpgw_info']['user']['account_id'];	  
		   }
		   $retval = "<input type=\"hidden\" name=\"$config\" value=\"$value\">".$this->get_username($value);
		   return $retval;
		}

		function listview_read($value,$config,$where_val_enc)
		{
		   $retval = "<input type=\"hidden\" name=\"$config\" value=\"$value\">".$this->get_username($value);
		   return $retval;
		}

		function get_username($id)
		{
		 $this->local_bo->so->site_db_connection($this->local_bo->session[site_id]);
		 $SQL="SELECT * FROM egw_accounts WHERE account_id=\"$id\" LIMIT 1";
		 $this->local_bo->so->site_db->query($SQL,__LINE__,__FILE__);
		 $this->local_bo->so->site_db->next_record();
		 $name = $this->local_bo->so->site_db->f('account_lid');
		 return $name;
		}
	}
?>
