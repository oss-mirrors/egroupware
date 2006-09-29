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

	$this->registry->plugins['date']['name']			= 'date';
	$this->registry->plugins['date']['title']			= 'Date plugin';
	$this->registry->plugins['date']['version']			= '0.1.4';
	$this->registry->plugins['date']['author']			= 'Pim Snel, Rob van Kraanen';
	$this->registry->plugins['date']['description']		= 'create date input box and date storage method, (At this time Dutch only)';
	$this->registry->plugins['date']['enable']			= 1;
	$this->registry->plugins['date']['db_field_hooks']	= array('date','datetime');
 
    $option=array(
	   'OldWay'=>'OldWay',
	   'DHTML-Calendar'=>'DHTML-Calendar'
   );

   // We need a template in stead of this auto configure array
   $this->registry->plugins['date']['config2']		= array
   (
	  'style' => array(
		 'name' => 'style',
		 'label' => lang('The old style or the new way with the DHTML calendar'),
		 'type' => 'select',
		 'select_arr' => $option
	  ),
	  'defdate'=>array(
		 'name' => 'defdate',
		 'label' => lang('Default date'),
		 'type' => 'radio',
		 'radio_arr'=>array('null'=>lang('Null'),'today'=>lang('Today')),
	  )
   );
   /*
   $this->registry->plugins['date']['config']		= array
   (
	  'style'=> array( array('OldWay','DHTML-Calendar'),'select','')  
   );

 */
 ?>
