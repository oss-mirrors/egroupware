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

	---------------------------------------------------------------------

	/*-------------------------------------------------------------------
	NewLine to Break PLUGIN
	-------------------------------------------------------------------*/
 
	class db_fields_plugin_nl2br
	{
	
		function db_fields_plugin_nl2br()
		{
		}

		function formview_edit($field_name, $value, $config,$attr_arr)
		{
			$input='<textarea name="'.$field_name.'" style="width:100%; height:200px">'.str_replace('<br />','',$value).'</textarea>';
			return $input;
		}
	
		function on_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
		{
			$input=$HTTP_POST_VARS[$key];
			if ($config['Strip_HTML_TAGS']=='Yes')
			{
				$input=strip_tags($input);
			}
	
			$output=addslashes(nl2br($input));
	
			return $output;
		 }
	}	
 ?>
