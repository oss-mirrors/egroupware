<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002 - 2005 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; version 2 of the License 

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   $setup_info['jinn']['name']			= 'jinn';
   $setup_info['jinn']['title']		= 'JiNN CMS/Database Manager';
   $setup_info['jinn']['version']		= '0.8.212';
   $setup_info['jinn']['app_order']	= 15;
   $setup_info['jinn']['author'] 		= 'Pim Snel';
   $setup_info['jinn']['license']  	= 'GPL';
   $setup_info['jinn']['note'] 		= 'PostgreSQL support is not stable. Wanna help?';

   $setup_info['jinn']['description'] = 'JiNN-DESCRIPTION';

   $plg = CreateObject('jinn.plugins_db_fields');
   $plg_obj_ev = CreateObject('jinn.plugins_object_events');

   if(@count($plg->plugins))
   {
	  $setup_info['jinn']['extra_untranslated'].= '<table border="0" style="width:550px" cellspacing="2"><tr><td valign="top" colspan="5" style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold;font-size:14px;">'.lang('Registered field plugins').'</td></tr>';
		 $setup_info['jinn']['extra_untranslated'].= '<tr><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Name').'</td><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Version').'</td><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Author').'</td><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Descrition').'</td><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Available for field types').'</td></tr>';

		 foreach($plg->plugins as $plugin)
		 {
			if(@count($plugin[db_field_hooks])) $fieldtypes=implode('<br/>',$plugin[db_field_hooks]);

			$setup_info['jinn']['extra_untranslated'].= '<tr><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.$plugin[title].'</td><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.$plugin[version].'</td><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.$plugin[author].'</td><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.($plugin[description]?lang($plugin[description]):'').'</td><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.$fieldtypes.'</td></tr>';
		 }

		 $setup_info['jinn']['extra_untranslated'].= '</table><br/>';
   }

   if(@count($plg_obj_ev->object_events_plugins))
   {
	  $setup_info['jinn']['extra_untranslated'].= '<table border="0" style="width:550px" cellspacing="2"><tr><td valign="top" colspan="5" style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold;font-size:14px;">'.lang('Registered object event plugins').'</td></tr>';
		 $setup_info['jinn']['extra_untranslated'].= '<tr><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Name').'</td><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Version').'</td><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Author').'</td><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Descrition').'</td><td style="border:solid 1px #6f6f6f;padding:3px;font-weight:bold">'.lang('Available for events').'</td></tr>';

		 foreach($plg_obj_ev->object_events_plugins as $plugin)
		 {
			if(@count($plugin[event_hooks])) $fieldtypes=implode('<br/>',$plugin[event_hooks]);

			$setup_info['jinn']['extra_untranslated'].= '<tr><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.$plugin[title].'</td><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.$plugin[version].'</td><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.$plugin[author].'</td><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.($plugin[description]?lang($plugin[description]):'').'</td><td valign="top" style="border:solid 1px #6f6f6f;padding:3px;">'.$fieldtypes.'</td></tr>';
		 }

		 $setup_info['jinn']['extra_untranslated'].= '</table>';
   }



   $setup_info['jinn']['maintainer'] = array(
	  'name'  => 'Pim Snel',
	  'email' => 'pim@lingewoud.nl');
	  $setup_info['jinn']['tables']	= array
	  (
		 'egw_jinn_acl',
		 'egw_jinn_sites',
		 'egw_jinn_objects',
		 'egw_jinn_obj_fields'
	  );

	  $setup_info['jinn']['enable']		= 1;

	  /* The hooks this app includes, needed for hooks registration */
	  $setup_info['jinn']['hooks']		= array
	  (
		 'admin',
		 'sidebox_menu',
		 'preferences',
		 'settings'
	  );

	  /* Dependencies for this app to work */
	  $setup_info['jinn']['depends'][]	= array
	  (
		 'appname'  => 'phpgwapi',
		 'versions' => Array('0.9.14','0.9.15','1.0.0','1.0.1','1.0.2')
	  );


































