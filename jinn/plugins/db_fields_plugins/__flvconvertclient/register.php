<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
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
   $this->registry->plugins['flvconvertclient']['name']				= 'flvconvertclient';
   $this->registry->plugins['flvconvertclient']['title']			= 'FLV Converter Client';
   $this->registry->plugins['flvconvertclient']['author']			= 'Pim Snel';
   $this->registry->plugins['flvconvertclient']['version']			= '0.1';
   $this->registry->plugins['flvconvertclient']['enable']			= 1;
   $this->registry->plugins['flvconvertclient']['description']		= 'This client can communicate with the remote flv converter server';
   $this->registry->plugins['flvconvertclient']['db_field_hooks']	= array
   (
	  'text'
   );

   $this->registry->plugins['flvconvertclient']['config2'] = array
   (
	  'source_movie_field' => array(
		 'name' => 'source_movie_field',
		 'label' => lang('Field with source movie'),
		 'type' => 'text',
		 'size' => 30
	  ),
	  'server' => array(
		 'name' => 'server',
		 'label' => lang('URL to the FLV conversion Server'),
		 'type' => 'text',
		 'size' => 200
	  ),
	  'subdirsource' => array(
		 'name' => 'subdirsource',
		 'label' => lang('Optional extra subdir for source video files'),
		 'type' => 'text',
		 'size' => 100
	  ),
	  'subdirdest' => array(
		 'name' => 'subdirdest',
		 'label' => lang('Optional extra subdir for converted flv files'),
		 'type' => 'text',
		 'size' => 100
	  )
   );
?>
