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
   $this->registry->plugins['word2html']['name'] 			= 'word2html';
   $this->registry->plugins['word2html']['title']			= 'word2html converter';
   $this->registry->plugins['word2html']['author']			= 'Pim Snel';
   $this->registry->plugins['word2html']['version']		= '1.1';
   $this->registry->plugins['word2html']['enable']			= 1;
   $this->registry->plugins['word2html']['default']		= 1;
   $this->registry->plugins['word2html']['description'] 	= 'Converts html from an uploader word document, including images. It needs to vw toolkit to be installed';
   $this->registry->plugins['word2html']['db_field_hooks']	= array
   (
	  'string','blob'
   );

   $this->registry->plugins['word2html']['helper_fields_substring'] = 'W2HTM'; //this is for multiple records insert

   $this->registry->plugins['word2html']['config2'] = array
   (
	  'wvpath' => array(
		 'name' => 'wvpath',
		 'label' => lang('path to wv executable: e.g. /usr/local/bin'),
		 'type' => 'text',
		 'size' => 200
	  ),
	  'subdir' => array(
		 'name' => 'subdir',
		 'label' => lang('Subdirectory to use for extracted images'),
		 'type' => 'text',
		 'size' => 100
	  ),

   );

?>
