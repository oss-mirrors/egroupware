<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Author:	Pim Snel
   Copyright (C)2005 Pim Snel <pim@lingewoud.nl>

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
   */
   $this->registry->plugins['colorpicker']['name']			= 'colorpicker';
   $this->registry->plugins['colorpicker']['title']			= 'ColorPicker';
   $this->registry->plugins['colorpicker']['version']		= '0.1';
   $this->registry->plugins['colorpicker']['enable']		= 1;
   $this->registry->plugins['colorpicker']['author']		= 'Peter Schilleman/Pim Snel';
   $this->registry->plugins['colorpicker']['description']	= 
   'ColorPicker with different methods. It picks colors from palets, images or free';
   $this->registry->plugins['colorpicker']['helper_fields_substring'] = 'CPR'; //this is for multiple records insert
   $this->registry->plugins['colorpicker']['db_field_hooks']	= array
   (
	  'text',
	  'string'
   );

   //

   $checkbox_arr=array(
	  'fromimg'=>lang('From Image'),
	  'free'=>lang('Free'),
	  'primpalet'=>lang('Primary Palet'),
	  'extrapalets'=>lang('Extra Palets'),
   );

   // We need a template in stead of this auto configure array
   $this->registry->plugins['colorpicker']['config2']		= array
   (
	  'activetabs' => array(
		 'name' => 'activetabs',
		 'label' => lang('Active Tabs'),
		 'type' => 'checkbox',
		 'checkbox_arr' => $checkbox_arr
	  ),
	  'primpalet' => array(
		 'name' => 'primpalet',
		 'label' => lang('Primary Palet Colors in hex seperated by commas'),
		 'type' => 'area'
	  ),
	  'palets' => array(
		 'name' => 'palets',
		 'label' => lang('Add optional extra palets'),
		 'type' => 'sitefile',
		 'subdir' =>'colorpicker_palets',
		 'allowempty' => true
	  ),
	  'defaultimage' => array(
		 'name' => 'defaultimage',
		 'label' => lang('Default image to choose from'),
		 'type' => 'sitefile',
		 'subdir' =>'colorpicker_img',
		 'allowempty' => true
	  ),
   );

   $this->registry->plugins['colorpicker']['config_execute']		= false;

?>
