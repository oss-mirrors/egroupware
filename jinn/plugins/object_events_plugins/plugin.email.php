<?php
   /*******************************************************************\
   * eGroupWare - JiNN                                                 *
   * http://www.egroupware.org                                         *
   * ----------------------------------------------------------------- *
   * Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare.    *
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

   $this->object_events_plugins['email']['name'] 			= 'email';
   $this->object_events_plugins['email']['title']			= 'email';
   $this->object_events_plugins['email']['author']			= 'Lex Vogelaar';
   $this->object_events_plugins['email']['version']			= '0.2';
   $this->object_events_plugins['email']['enable']			= 1;
   $this->object_events_plugins['email']['description']		= 'send an email triggered by an event';
   $this->object_events_plugins['email']['event_hooks']		= array
   (
	  'on_update'
   );
   
   $this->object_events_plugins['email']['help']		=  'Substitute table fields with $$field$$, substitute foreign values with %%field%%.';
   $this->object_events_plugins['email']['config']		= array
   (
	  'fieldname_with_emailaddress'=>array('','text',''),
	  'CC'=>array('','area',''),
	  'BCC'=>array('','area',''),
	  'from_address'=>array('','text',''),
	  'subject'=> array('','text',''),
	  'messagebody'=>array('','area','')
   );

   $this->object_events_plugins['email']['config_help']		= array
   (
	  'fieldname_with_emailaddress'=>'specify the field in the object that stores an email adress. If a value from a related table is needed enclose the field %% like this %%fieldname%%',
	  'CC'=>'optionally specify one or more valid email addresses',
	  'BCC'=>'optionally specify one or more valid email addresses',
	  'from_address'=>'specify the from address',
	  'subject'=> 'email subject line',
	  'messagebody'=>'email contents'
   );

   function event_action_email($post, $config)
   {
		$m = array();

		if(substr($config[conf][fieldname_with_emailaddress],0,2)=='%%' && substr($config[conf][fieldname_with_emailaddress],-2,2)=='%%')
		{
		
		   $m[to] = $post[O2MXXX.str_replace('%','',$config[conf][fieldname_with_emailaddress])];
		}
		else
		{
		   $m[to] = $post[FLDXXX.$config[conf][fieldname_with_emailaddress]];
		}

		$m[subject] = $config[conf][subject];
		$m[message] = $config[conf][messagebody];
		
		//replace occurences of '$$fieldname$$' with the value of that field
		foreach($post as $key => $value)
		{
			$prefix = substr($key, 0, 6);
			if($prefix == 'FLDXXX')
			{
				$field = substr($key, 6);
				$m[subject] = str_replace('$$'.$field.'$$', $value, $m[subject]);
				$m[message] = str_replace('$$'.$field.'$$', $value, $m[message]);
			}
		}
	
		//replace occurences of '$$fieldname$$' with the value of that field
		foreach($post as $key => $value)
		{
		   $prefix = substr($key, 0, 6);
		   if($prefix == 'O2MXXX')
		   {
			  $field = substr($key, 6);
			  $m[subject] = str_replace('%%'.$field.'%%', $value, $m[subject]);
			  $m[message] = str_replace('%%'.$field.'%%', $value, $m[message]);
		   }
		}

		
		$m[headers] = '';
		$m[headers] .= 'From: '.$config[conf][from_address]."\r\n";
		$m[headers] .= 'Cc: '.$config[conf][CC]."\r\n";
		$m[headers] .= 'Bcc: '.$config[conf][BCC]."\r\n";
		
		return mail($m[to], $m[subject], $m[message], $m[headers]);
   }
?>
