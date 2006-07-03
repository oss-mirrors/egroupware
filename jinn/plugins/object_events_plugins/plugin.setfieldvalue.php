<?php
   /*******************************************************************\
   * eGroupWare - JiNN                                                 *
   * http://www.egroupware.org                                         *
   * ----------------------------------------------------------------- *
   * Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare.    *
   * Copyright (C)2002-2006 Pim Snel <pim@lingewoud.nl>                *
   * ----------------------------------------------------------------- *
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

   $this->object_events_plugins['setfieldvalue']['name'] 			= 'setfieldvalue';
   $this->object_events_plugins['setfieldvalue']['title']			= 'setfieldvalue';
   $this->object_events_plugins['setfieldvalue']['author']			= 'Pim Snel';
   $this->object_events_plugins['setfieldvalue']['version']			= '0.1';
   $this->object_events_plugins['setfieldvalue']['enable']			= 1;
   $this->object_events_plugins['setfieldvalue']['description']		= 'Set a configured field to a new configured value';
   $this->object_events_plugins['setfieldvalue']['event_hooks']		= array
   (
	  'on_update',
	  'on_export',
	  'run_on_record',
	  'on_walk_list_button'
   );
   
   $this->object_events_plugins['setfieldvalue']['help']		=  'Substitute table fields with field. Warning not tested.';
   $this->object_events_plugins['setfieldvalue']['config']		= array
   (
	  'fieldname_to_set'=>array('','text',''),
	  'new_value'=>array('','area',''),
   );

   $this->object_events_plugins['setfieldvalue']['config_help']		= array
   (
	  'fieldname_to_set'=>'specify the field in the object that must be set to a new value',
	  'new_value'=> 'value to set',
   );

   function event_action_setfieldvalue($post, $config)
   {
		global $local_bo;
		
		$where_string=$local_bo->create_where_string($post,'FLDXXX');
		$curr_table=$local_bo->site_object['table_name'];

		// controleer of tabel leeg en geef anders foutmelding
		$sql="UPDATE $curr_table SET {$config[conf]['fieldname_to_set']}='{$config[conf]['new_value']}' WHERE $where_string LIMIT 1";
		$res[] = $local_bo->so->site_db->query($sql,'__LINE__','__FILE__');

		return true;
   }
?>
