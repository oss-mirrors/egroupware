<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2005 Pim Snel <pim@lingewoud.nl>

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

   /**
   * String input field which automaticly removes spaces when store to database
   *
   * @package jinn_plugins
   * @author pim-AT-lingewoud-DOT-nl
   * @copyright (c) 2005 by Pim Snel
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class db_fields_plugin_removespaces
   {

	  /**
	  * Constructor
	  */
	  function db_fields_plugin_removespaces()
	  {}

	  /**
	  * Form edit plugin which return html to use in the form
	  *
	  * @param string $field_name name of the calling field
	  * @param string $value value of the calling field
	  * @param array $config contains the stored configuration data of this field concerning this plugin
	  * @param array $attr_arr this can contain dynamicly added attributes when the field metadata is read which can change the behaviour of the plugin.
	  * @return string/array normally return the generated html to create the input
	  */
	  function formview_edit($field_name,$value,$config,$attr_arr)
	  {	
		 if($attr_arr['max_size'])
		 {
			if($attr_arr['max_size']>40) 
			{
			   $size=40;
			}
			else
			{
			   $size=$attr_arr['max_size'];
			}

			$max='size="'.$size.'" maxlength="'.$attr_arr['max_size'].'"';	
		 }

		 $input='<input onBlur="this.value=jinnIgnoreSpaces(this.value);" type="text" name="'.$field_name.'" '.$max.' value="'.strip_tags($value).'">';

		 return $input;
	  }
   }	
?>
