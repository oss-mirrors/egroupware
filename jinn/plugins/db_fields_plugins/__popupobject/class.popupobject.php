<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Copyright (C)2005 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN
   ---------------------------------------------------------------------
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
   */

   /* $Id$ */

   /**
   * popupobject
   *
   * @package jinn_plugins
   * @author pim-AT-lingewoud-DOT-nl
   * @copyright (c) 2005 by Pim Snel
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class db_fields_plugin_popupobject
   {
	  /**
	  * Constructor
	  */
	  function db_fields_plugin_popupobject()
	  {
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
	  }

	  /**
	  * Form edit plugin which return html to use in the form
	  *
	  * This one askes for text input to generate the menu's from, 
	  * it also shows the available generated images
	  *
	  * @param string $field_name name of the calling field
	  * @param string $value value of the calling field
	  * @param array $config contains the stored configuration data of this field concerning this plugin
	  * @param array $attr_arr this can contain dynamicly added attributes when the field metadata is read which can change the behaviour of the plugin.
	  * @return string/array normally return the generated html to create the input
	  * @todo implement more than one image
	  */
	  function formview_edit($field_name, $value, $config,$attr_arr)
	  {
		 $this->tplsav2->field_name=$field_name;
		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');

		 $this->tplsav2->site_id=$this->local_bo->site['site_id'];
		 
		 $this->tplsav2->config2base64=base64_encode(serialize($config));
		 $input=$this->tplsav2->fetch('popupobject.formview_edit.tpl.php');
		 return $input;
	  }

   }	
?>
