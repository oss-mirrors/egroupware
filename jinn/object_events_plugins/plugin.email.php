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

   $this->plugins['email']['name'] 				= 'email';
   $this->plugins['email']['title']				= 'email';
   $this->plugins['email']['author']			= 'Pim Snel';
   $this->plugins['email']['version']			= '0.1';
   $this->plugins['email']['enable']			= 1;
   $this->plugins['email']['description']		= 'send an email triggered by an event';
   $this->plugins['email']['event_hooks']		= array
   (
	  'on_record_update'
   );
   
   $this->plugins['email']['help']			=  'some help here...';
   $this->plugins['email']['config']		= array
   (
	  'fieldname_with_emailaddress'=>array('','text',''),
	  'CC'=>array('','area',''),
	  'BCC'=>array('','area',''),
	  'from_address'=>array('','text',''),
	  'subject'=> array('','text',''),
	  'messagebody'=>array('','area','')
   );

   $this->plugins['email']['config_help']		= array
   (
	  'fieldname_with_emailaddress'=>'specify the field in the object that stores an email adress',
	  'CC'=>'optionally specify one or more valid email addresses',
	  'BCC'=>'optionally specify one or more valid email addresses',
	  'from_address'=>'specify the from address',
	  'subject'=> 'email subject line',
	  'messagebody'=>'email contents'
   );

   function plg_fi_email($field_name,$value, $config,$attr_arr)
   {
   }

   function plg_ro_email($value, $config)
   {
   }

   function plg_bv_email($value, $config,$where_val_enc)
   {
   }
?>
