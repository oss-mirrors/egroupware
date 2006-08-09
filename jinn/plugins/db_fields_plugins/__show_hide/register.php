<?php
/*
	JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
	Author:	Lex Vogelaar for Lingewoud
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
   $this->registry->plugins['show_hide']['name'] 			= 'show_hide';
   $this->registry->plugins['show_hide']['title']			= 'Show/Hide';
   $this->registry->plugins['show_hide']['author']			= 'Rob van Kraanen';
   $this->registry->plugins['show_hide']['version']			= '0.1';
   $this->registry->plugins['show_hide']['enable']			= 1;
   $this->registry->plugins['show_hide']['description']		= '';
   $this->registry->plugins['show_hide']['db_field_hooks']	= array
   (
	  'string',
	  'int',
	  'blob'
   );
   $this->registry->plugins['show_hide']['help']			=  '
  	This plugin creates  a selectbox that can show and hide fields. It will always ignore itself when hiding fields.
   ';
   $this->registry->plugins['show_hide']['config2']     = array
   (
	  'multi1'=>array(
		 'name'=>'multi1',
		 'type'=>'multi',
		 'items'=>array(
			'Value'=>array(
			   'name' => 'Value',
			   'label' => lang('value'),
			   'type' => 'text'
			),
			'Label'=>array(
			   'name' => 'Label',
			   'label' => lang('label'),
			   'type' => 'text'
			),
			'show'=>array(
			'name' => 'show',
			'label' => lang('show'),
			'type' => 'select_form_elements'
		 ),
		 'spec_hide'=>array(
			'name' => 'spec_hide',
			'label' => lang('Which fields to hide?'),
			'type' => 'radio',
			'radio_arr'=>array('spec'=>lang('Specify hide fields below'),'inverse'=>lang('Use inverse show selection')),
		 ),
		 'hide'=>array(
			'name' => 'hide',
			'label' => lang('hide'),
			'type' => 'select_form_elements',
			'allowempty' => false
		 )
	  )
   )
);

$this->registry->plugins['show_hide']['config_help']		= array
   (
	  'Keys_seperated_by_commas'=>'These keys are displayed to the user',
	  'Value_seperated_by_commas'=>'These values are stored in the database'
   );

?>
