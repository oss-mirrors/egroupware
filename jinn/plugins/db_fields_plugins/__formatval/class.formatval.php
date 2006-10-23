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

   /* $Id: class.formatval.php 22026 2006-07-07 11:18:42Z mipmip $ */

   class db_fields_plugin_formatval
   {

	  function db_fields_plugin_formatval()
	  {}

	  function formview_edit($field_name, $value, $config,$attr_arr)
	  {
		 return $this->format($value, $config);
	  }	

	  function formview_read($value,$config)
	  {
		 return $this->format($value, $config);
	  }

	  function listview_read($value,$config,$where_val_enc)
	  {
		 return $this->format($value, $config);
	  }

	  function format($value, $config)
	  {
		 if($value)
		 {
			$config['fstring'] = str_replace('$$VALUE$$', $value, $config['fstring']);
			return $config['fstring'];
		 }
		 else
		 {
			return lang("save records first");
		 }
	  }
   }
?>
