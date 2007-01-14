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
   $this->registry->report_plugins['pdmlreport']['name']		= 'pdmlreport';
   $this->registry->report_plugins['pdmlreport']['title']		= 'pdml Report Engine';
   $this->registry->report_plugins['pdmlreport']['version']		= '0.1';
   $this->registry->report_plugins['pdmlreport']['enable']		= 1;
   $this->registry->report_plugins['pdmlreport']['author']		= 'Pim Snel';
   $this->registry->report_plugins['pdmlreport']['description']	= 'This engine uses the pdmlreport language to generate PDF reports';

   $this->registry->report_plugins['pdmlreport']['inputmethods'] = array
   (
	  'upload' => array(
		 'name' => 'upload',
		 'label' => lang('Directory where fonts are located'),
		 'type' => 'text',
	  ),
	  'textarea' => array(
		 'name' => 'textarea',
		 'label' => lang('Directory where fonts are located'),
		 'type' => 'custom_method',
		 'custom_method_function' => 'text_input',
	  ),
   );
   
   $this->registry->report_plugins['pdmlreport']['outputmethods'] = array('save', 'print','mail');

   $this->registry->report_plugins['pdmlreport']['config'] = array
   (
	  'fontdir' => array(
		 'name' => 'fontdir',
		 'label' => lang('Directory where fonts are located'),
		 'type' => 'text',
		 'size' => 100
	  ),
   );



?>
