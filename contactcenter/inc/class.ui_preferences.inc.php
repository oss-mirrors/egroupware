<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/


	class ui_preferences
	{
		var $public_functions = array(
			'index'           => true,
			'set_preferences' => true,
		);
		
		function index()
		{
			$GLOBALS['phpgw_info']['flags']['app_header'] = lang('ContactCenter').' - '.lang('Preferences');
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			$GLOBALS['phpgw']->template->set_file(array('pref' => 'preferences.tpl'));

			/* Get Saved Preferences */
			$actual = $this->get_preferences();
			
			if ($actual['displayConnector'] or !$actual['displayConnectorDefault'])
			{
				$GLOBALS['phpgw']->template->set_var('displayConnector', 'checked');
			}
			else
			{
				$GLOBALS['phpgw']->template->set_var('displayConnector', '');
			}
			
			/* Translate the fields */
			$this->translate('pref');

			$GLOBALS['phpgw']->template->set_var('form_action', $GLOBALS['phpgw']->link('/index.php', 'menuaction=contactcenter.ui_preferences.set_preferences'));

			$GLOBALS['phpgw']->template->pparse('out', 'pref');
		}
		
		function translate($handle)
		{
			$vars = $GLOBALS['phpgw']->template->get_undefined($handle);
			foreach($vars as $name => $value)
			{
				if (ereg('^lang_', $name) !== false)
				{
					$GLOBALS['phpgw']->template->set_var($name, lang(str_replace('_',' ',substr($name, 5))));
				}
			}
		}
		
		function set_preferences()
		{
			if ($_POST['save'])
			{
				$GLOBALS['phpgw']->preferences->read();
				$GLOBALS['phpgw']->preferences->delete('contactcenter', 'displayConnector');
				$GLOBALS['phpgw']->preferences->delete('contactcenter', 'displayConnectorDefault');
				
				$GLOBALS['phpgw']->preferences->add('contactcenter', 'displayConnectorDefault', '1');

				if($_POST['displayConnector'])
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'displayConnector', '1');
				}
				else
				{
					$GLOBALS['phpgw']->preferences->add('contactcenter', 'displayConnector', '0');
				}
				
				$GLOBALS['phpgw']->preferences->save_repository();
			}

			header('Location: '.$GLOBALS['phpgw']->link('/preferences/index.php'));
		}

		function get_preferences()
		{
			$prefs = $GLOBALS['phpgw']->preferences->read();

			if (!$prefs['contactcenter']['displayConnectorDefault'] and !$prefs['contactcenter']['displayConnector'])
			{
				$prefs['contactcenter']['displayConnector'] = true;
			}
			
			return $prefs['contactcenter'];
		}
	}
?>
