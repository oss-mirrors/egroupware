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

   $this->object_events_plugins['redirect']['name'] 			= 'redirect';
   $this->object_events_plugins['redirect']['title']			= 'redirect';
   $this->object_events_plugins['redirect']['author']			= 'Rob van Kraanen';
   $this->object_events_plugins['redirect']['version']			= '0.1';
   $this->object_events_plugins['redirect']['enable']			= 1;
   $this->object_events_plugins['redirect']['description']		= 'Set a configured field to a new configured value';
   $this->object_events_plugins['redirect']['event_hooks']		= array
   (
	  'on_update',
	  'on_export',
	  'run_on_record',
   );
   
   $this->object_events_plugins['redirect']['help']		=  'Substitute table fields with field. Warning not tested.';
   $this->object_events_plugins['redirect']['config']		= array
   (
	  'url_to_redirect_to'=>array('','text',''),
	  'field_to_use'=>array('','text',''),
	  'egroupware'=>array(array('YES','NO'),'select',''),
   );

   $this->object_events_plugins['redirect']['config_help']		= array
   (
	  'url_to_redirect_to'=>'Give url to redirect to',
   );

   function event_action_redirect($post, $config)
   {
	  global $local_bo;
	  $where_string=$local_bo->create_where_string($post,'FLDXXX');

	  $url = $config['conf']['url_to_redirect_to'];
	  if($config[conf][egroupware] == 'YES')
	  {
		 $url = $GLOBALS['egw_info']['server']['webserver_url'].'/'.$url;
	  }
	  $url = $url.$post["FLDXXX".$config[conf][field_to_use]];
	  #_debug_array($url);
	  #die();
	  if (!headers_sent())
	  {
		 header("Location: $url");
	  }
	  else
	  {
		 echo "<meta http-equiv=\"refresh\" content=\"0;url=$url\">\r\n";
	  }
	  exit;

	  return true;
   }
?>
