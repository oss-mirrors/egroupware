<?php
   /*******************************************************************\
   * eGroupWare - JiNN                                                 *
   * http://www.egroupware.org                                         *
   * ----------------------------------------------------------------- *
   * Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare.    *
   * Copyright (C)2002-2004, 2003 Pim Snel <pim@lingewoud.nl>          *
   * ----------------------------------------------------------------- *
   * Select-box Plugin                                                 *
   * This file is part of JiNN                                         *
   * ----------------------------------------------------------------- *
   * This library is free software; you can redistribute it and/or     *
   * modify it under the terms of the GNU General Public License as    *
   * published by the Free Software Foundation; Version 2 of the       *
   * License                                                           *
   *                                                                   *
   * This program is distributed in the hope that it will be useful,   *
   * but WITHOUT ANY WARRANTY; without even the implied warranty of    *
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  *
   * General Public License for more details.                          *
   *                                                                   *
   * You should have received a copy of the GNU General Public License *
   * along with this program; if not, write to the Free Software       *
   * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.         *
   \*******************************************************************/

   /**
    * db_fields_plugin_show_hide 
    * 
    * @package 
    * @version $Id$
    * @copyright Lingewoud B.V.
    * @author Rob van Kraanen<rob-AT-lingewoud-DOT-nl> 
    * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
    */
   class db_fields_plugin_show_hide
   {

	  function db_fields_plugin_show_hide() 
	  {
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
	  }
	  

	  function formview_edit($field_name,$value, $config,$attr_arr)
	  {

		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
		 foreach($config[multi1] as $option)
		 {
			if(! empty($option[show][value]))
			{
			   $show[] = "'{$option[Value]}:{$option[show][value]}'";
			}
			if(! empty($option[hide][value]))
			{
			   $hide[] = "'{$option[Value]}:{$option[hide][value]}'";
			}
		 }
		 if(!empty($show))
		 {
			$show_sel = implode(",",$show);
		 }
		 if(!empty($hide))
		 {
			$hide_sel = implode(",",$hide);
		 }

		 $this->tplsav2->assign('value',$value);
		 $this->tplsav2->assign('show_sel',$show_sel);
		 $this->tplsav2->assign('hide_sel',$hide_sel);
		 $this->tplsav2->assign('fieldname',$field_name);
		 $this->tplsav2->assign('options',$config[multi1]);
		 return($this->tplsav2->fetch('show_hide.formview_edit.tpl.php'));

	  }

	  function formview_read($value, $config)
	  {
		 return $this->listview_read($value, $config,'');
	  }

	  function listview_read($value, $config,$where_val_enc)
	  {
		 return $value;
	  }
   }
?>
