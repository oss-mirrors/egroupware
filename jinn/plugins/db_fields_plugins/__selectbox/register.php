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
   $this->registry->plugins['selectbox']['name'] 			= 'selectbox';
   $this->registry->plugins['selectbox']['title']			= 'Select Box';
   $this->registry->plugins['selectbox']['author']			= 'Pim Snel';
   $this->registry->plugins['selectbox']['version']			= '0.7';
   $this->registry->plugins['selectbox']['enable']			= 1;
   $this->registry->plugins['selectbox']['description']		= 'List a couple of values in a listbox....';
   $this->registry->plugins['selectbox']['db_field_hooks']	= array
   (
	  'string',
	  'int',
	  'blob'
   );
   $this->registry->plugins['selectbox']['help']			=  'If the hidden fields arent available they won\'t work';
   $this->registry->plugins['selectbox']['config']		= array
   (
	  'Keys_seperated_by_commas'=>array('one,two,three','area',''),
	  'Value_seperated_by_commas'=>array('one,two,three','area',''),
	  'Show_fields'=>array('','area',''),
	  'Hide_fields'=>array('','area',''),
	  'Default_value'=>array('one','text',''),
	  'Empty_option_available'=> array(array('yes','no'),'select','')
   );

   $this->registry->plugins['selectbox']['config_help']		= array
   (
	  'Keys_seperated_by_commas'=>'These keys are displayed to the user',
	  'Value_seperated_by_commas'=>'These values are stored in the database'
   );

?>
