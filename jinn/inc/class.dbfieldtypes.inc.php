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
   @class dbfieldtypes  
   @abstract dbfieldtypes class that help resolving db-native fieldtypes
   to field types JiNN understands.
   */
   class dbfieldtypes
   {
	  
	  var $field_types_arr = array();

	  /*!
	  @function dbfieldtypes
	  @abstract standard constructor that sets all main class variables
	  */
	  function dbfieldtypes()
	  {
		 $this->set_type_arr(); 
	  }


	  /*!
	  @function set_type_arr
	  @abstract set_type_arr() sets the main array with al known field types
	  */
	  function set_type_arr()
	  {
		 $this->$field_types_arr=array(
			'varchar'		=>		'string',
			'char'			=>		'string',
			'string'		=>		'string',
			'int'			=>		'int',
			'real'			=>		'int',
			'smallint'		=>		'int',
			'int'			=>		'int',
			'tinyint'		=>		'int',
			'timestamp'		=>		'timestamp',
			'blob'			=>		'blob',
			'text'			=>		'blob',
		 );
	  }
	  
	  /*!
	  @function get_db_f_type
	  @abstract get_db_f_type() resolves a db-native field-type to a type JiNN understands
	  @param $in_type database native field type
	  */
	  function get_db_f_type($in_type)
	  {
		 if($this->field_types_arr[$in_type])
		 {
			return $this->field_types_arr[$in_type];
		 }
		 else
		 {
			return false;
		 }
	  }
   }

?>
