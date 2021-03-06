<?php
/**
 * Addressbook - admin, preferences and sidebox-menus and other hooks
 *
 * @link http://www.egroupware.org
 * @package addressbook
 * @author Ralf Becker <RalfBecker@outdoor-training.de>
 * @copyright (c) 2006-13 by Ralf Becker <RalfBecker@outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Class containing admin, preferences and sidebox-menus and other hooks
 */
class addressbook_hooks
{
	/**
	 * hooks to build projectmanager's sidebox-menu plus the admin and preferences sections
	 *
	 * @param string/array $args hook args
	 */
	static function all_hooks($args)
	{
		$appname = 'addressbook';
		$location = is_array($args) ? $args['location'] : $args;
		//echo "<p>contacts_admin_prefs::all_hooks(".print_r($args,True).") appname='$appname', location='$location'</p>\n";

		if ($location == 'sidebox_menu')
		{
			if ($_GET['menuaction'] == 'addressbook.addressbook_ui.view')
			{
				display_sidebox($appname, lang('Contact data'), array(
					array(
						'text'    => '<div id="addressbook_view_sidebox"/>',
						'no_lang' => true,
						'link'    => false,
						'icon'    => false,
					),
					'menuOpened'  => true,	// display it open by default
				));
			}
			// Magic etemplate2 favorites menu (from nextmatch widget)
			display_sidebox($appname, lang('Favorites'), egw_framework::favorite_list('addressbook'));

			$file = array(
				'Addressbook list' => egw::link('/index.php',array(
					'menuaction' => 'addressbook.addressbook_ui.index',
					'ajax' => 'true')),
				array(
					'text' => lang('Add %1',lang(egw_link::get_registry($appname, 'entry'))),
					'no_lang' => true,
					'link' => "javascript:egw.open('','$appname','add')"
				),
				'Advanced search' => "javascript:egw_openWindowCentered2('".
					egw::link('/index.php',array('menuaction' => 'addressbook.addressbook_ui.search'),false).
					"','_blank',870,480,'yes')",
				'Placeholders'    => egw::link('/index.php','menuaction=addressbook.addressbook_merge.show_replacements')
			);
			display_sidebox($appname,lang('Addressbook menu'),$file);
		}

		if ($GLOBALS['egw_info']['user']['apps']['admin'] && $location != 'preferences')
		{
			$file = Array(
				'Site configuration' => egw::link('/index.php',array(
					'menuaction' => 'admin.uiconfig.index',
					'appname'    => $appname,
				)),
				'Global Categories'  => egw::link('/index.php',array(
					'menuaction' => 'admin.admin_categories.index',
					'appname'    => $appname,
					'global_cats'=> True,
				)),
			);
			// custom fields are not availible in LDAP
			if ($GLOBALS['egw_info']['server']['contact_repository'] != 'ldap')
			{
				$file['Custom fields'] = egw::link('/index.php',array(
					'menuaction' => 'admin.customfields.index',
					'appname'    => $appname,
					'use_private'=> 1,
					'ajax'       => 'true'
				));
			}
			if ($location == 'admin')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Admin'),$file);
			}
		}
	}

	/**
	 * populates $settings for the preferences
	 *
	 * @param array|string $hook_data
	 * @return array
	 */
	static function settings($hook_data)
	{
		$settings = array(
			array(
				'type'  => 'section',
				'title' => lang('General settings'),
				'no_lang'=> true,
				'xmlrpc' => False,
				'admin'  => False
			),
		);
		$settings['add_default'] = array(
			'type'   => 'select',
			'label'  => 'Default addressbook for adding contacts',
			'name'   => 'add_default',
			'help'   => 'Which addressbook should be selected when adding a contact AND you have no add rights to the current addressbook.',
			'values' => !$hook_data['setup'] ? ExecMethod('addressbook.addressbook_ui.get_addressbooks',EGW_ACL_ADD) : array(),
			'xmlrpc' => True,
			'admin'  => False,
		);
		if ($GLOBALS['egw_info']['server']['contact_repository'] != 'ldap')
		{
			$settings['private_addressbook'] = array(
				'type'   => 'check',
				'label'  => 'Enable an extra private addressbook',
				'name'   => 'private_addressbook',
				'help'   => 'Do you want a private addressbook, which can not be viewed by users, you grant access to your personal addressbook?',
				'xmlrpc' => True,
				'admin'  => False,
				'forced' => false,
			);
		}
		$settings['hide_accounts'] = array(
			'type'   => 'check',
			'label'  => 'Hide accounts from addressbook',
			'name'   => 'hide_accounts',
			'help'   => 'Hides accounts completly from the adressbook.',
			'xmlrpc' => True,
			'admin'  => false,
		);
		$fileas_options = ExecMethod('addressbook.addressbook_bo.fileas_options');
		$settings['link_title'] = array(
			'type'   => 'select',
			'label'  => 'Link title for contacts show',
			'name'   => 'link_title',
			'values' => array(
				'n_fileas' => lang('own sorting').' ('.lang('default').': '.lang('Company').': '.lang('lastname').', '.lang('firstname').')',
			)+$fileas_options,	// plus all fileas types
			'help'   => 'What should links to the addressbook display in other applications. Empty values will be left out. You need to log in anew, if you change this setting!',
			'xmlrpc' => True,
			'admin'  => false,
			'default'=> 'org_name: n_family, n_given',
		);
    	$settings['link_title_cf'] = array(
			'type'  => 'select',
			'label' => 'Add a customfield to link title',
			'name'  => 'link_title_cf',
			'values' => addressbook_bo::cf_options(),
			'help'  =>  'Add customfield to links of addressbook, which displays in other applications. The default value is none customfield.',
			'xmlrpc' => True,
			'admin'  => false,
		);
		$settings['addr_format'] = array(
			'type'   => 'select',
			'label'  => 'Default address format',
			'name'   => 'addr_format',
			'values' => array(
				'postcode_city' => lang('zip code').' '.lang('City'),
				'city_state_postcode' => lang('City').' '.lang('State').' '.lang('zip code'),
			),
			'help'   => 'Which address format should the addressbook use for countries it does not know the address format. If the address format of a country is known, it uses it independent of this setting.',
			'xmlrpc' => True,
			'admin'  => false,
			'default'=> 'postcode_city',
		);
		$settings['fileas_default'] = array(
			'type'   => 'select',
			'label'  => 'Default file as format',
			'name'   => 'fileas_default',
			'values' => $fileas_options,
			'help'   => 'Default format for fileas, eg. for new entries.',
			'xmlrpc' => True,
			'admin'  => false,
			'default'=> 'org_name: n_family, n_given',
		);
		$crm_list_options = array(
			'~edit~'    => lang('Edit contact'),
			'infolog' => lang('Open %1 CRM view', lang('infolog')),
		);
		if($GLOBALS['egw_info']['user']['apps']['tracker'])
		{
			$crm_list_options['tracker'] = lang('Open %1 CRM view', lang('tracker'));
		}
		$settings['crm_list'] = array(
			'type'   => 'select',
			'label'  => 'Default action on double-click',
			'name'   => 'crm_list',
			'values' => $crm_list_options,
			'help'   => 'When viewing a contact, show linked entries from the selected application',
			'xmlrpc' => True,
			'admin'  => false,
			'default'=> 'infolog',
		);
		$settings[] = array(
			'type'  => 'section',
			'title' => lang('Data exchange settings'),
			'no_lang'=> true,
			'xmlrpc' => False,
			'admin'  => False
		);
		// CSV Export

		if ($GLOBALS['egw_info']['user']['apps']['filemanager'])
		{
			$settings['default_document'] = array(
				'type'   => 'vfs_file',
				'size'   => 60,
				'label'  => 'Default document to insert contacts',
				'name'   => 'default_document',
				'help'   => lang('If you specify a document (full vfs path) here, %1 displays an extra document icon for each entry. That icon allows to download the specified document with the data inserted.', lang('addressbook')).' '.
					lang('The document can contain placeholder like {{%1}}, to be replaced with the data.','n_fn').' '.
					lang('The following document-types are supported:'). implode(',',bo_merge::get_file_extensions()),
				'run_lang' => false,
				'xmlrpc' => True,
				'admin'  => False,
			);
			$settings['document_dir'] = array(
				'type'   => 'vfs_dirs',
				'size'   => 60,
				'label'  => 'Directory with documents to insert contacts',
				'name'   => 'document_dir',
				'help'   => lang('If you specify a directory (full vfs path) here, %1 displays an action for each document. That action allows to download the specified document with the data inserted.',lang('addressbook')).' '.
					lang('The document can contain placeholder like {{%1}}, to be replaced with the data.','n_fn').' '.
					lang('The following document-types are supported:'). implode(',',bo_merge::get_file_extensions()),
				'run_lang' => false,
				'xmlrpc' => True,
				'admin'  => False,
				'default' => '/templates/addressbook',
			);
		}

		if ($GLOBALS['egw_info']['user']['apps']['felamimail'] || $GLOBALS['egw_info']['user']['apps']['mail'])
		{
			$settings['force_mailto'] = array(
				'type'   => 'check',
				'label'  => 'Open EMail addresses in external mail program',
				'name'   => 'force_mailto',
				'help'   => 'Default is to open EMail addresses in EGroupware EMail application, if user has access to it.',
				'xmlrpc' => True,
				'admin'  => False,
				'default'=> false,
			);
		}

		// Import / Export for nextmatch
		if ($GLOBALS['egw_info']['user']['apps']['importexport'])
		{
			$definitions = new importexport_definitions_bo(array(
				'type' => 'export',
				'application' => 'addressbook'
			));
			$options = array(
				'~nextmatch~'	=>	lang('Old fixed definition')
			);
			$default_def = 'export-addressbook';
			foreach ((array)$definitions->get_definitions() as $identifier)
			{
				try
				{
					$definition = new importexport_definition($identifier);
				}
				catch (Exception $e)
				{
					// permission error
					continue;
				}
				if ($title = $definition->get_title())
				{
					$options[$title] = $title;
				}
				unset($definition);
			}
			$settings['nextmatch-export-definition'] = array(
				'type'   => 'select',
				'values' => $options,
				'label'  => 'Export definition to use for nextmatch export',
				'name'   => 'nextmatch-export-definition',
				'help'   => 'If you specify an export definition, it will be used when you export',
				'run_lang' => false,
				'xmlrpc' => True,
				'admin'  => False,
				'default'=> isset($options[$default_def]) ? $default_def : false,
			);
			$settings['csv_fields'] = array(
				'type'   => 'select',
				'label'  => 'Fields for the CSV export',
				'name'   => 'csv_fields',
				'values' => array(
					'all'      => lang('All'),
					'business' => lang('Business address'),
					'home'     => lang('Home address'),
				),
				'help'   => 'Which fields should be exported. All means every field stored in the addressbook incl. the custom fields. The business or home address only contains name, company and the selected address.',
				'xmlrpc' => True,
				'admin'  => false,
				'default'=> 'business',
			);

			$settings['vcard_charset'] = array(
				'type'   => 'select',
				'label'  => 'Charset for the vCard import and export',
				'name'   => 'vcard_charset',
				'values' => translation::get_installed_charsets(),
				'help'   => 'Which charset should be used for the vCard import and export.',
				'xmlrpc' => True,
				'admin'  => false,
				'default'=> 'iso-8859-1',
			);
		}
		return $settings;
	}

	/**
	 * Hook called by link-class to include calendar in the appregistry of the linkage
	 *
	 * @param array/string $location location and other parameters (not used)
	 * @return array with method-names
	 */
	static function search_link($location)
	{
		$links = array(
			'query' => 'addressbook.addressbook_bo.link_query',
			'title' => 'addressbook.addressbook_bo.link_title',
			'titles' => 'addressbook.addressbook_bo.link_titles',
			'view' => array(
				'menuaction' => 'addressbook.addressbook_ui.view',
				'ajax' => 'true'
			),
			'view_id' => 'contact_id',
			'list'	=>	array(
				'menuaction' => 'addressbook.addressbook_ui.index',
				'ajax' => 'true'
			 ),
			'edit' => array(
				'menuaction' => 'addressbook.addressbook_ui.edit'
			),
			'edit_id' => 'contact_id',
			'edit_popup'  => '859x550',
			'add' => array(
				'menuaction' => 'addressbook.addressbook_ui.edit'
			),
			'add_app'    => 'link_app',
			'add_id'     => 'link_id',
			'add_popup'  => '859x550',
			'file_access_user' => true,	// file_access supports 4th parameter $user
			'file_access'=> 'addressbook.addressbook_bo.file_access',
			'default_types' => array('n' => array('name' => 'contact', 'options' => array('icon' => 'navbar.png','template' => 'addressbook.edit'))),
			// registers an addtional type 'addressbook-email', returning only contacts with email, title has email appended
			'additional' => array(
				'addressbook-email' => array(
					'query' => 'addressbook.addressbook_bo.link_query_email',
					'view' => array(
						'menuaction' => 'addressbook.addressbook_ui.view',
						'ajax' => 'true'
					),
					'view_id' => 'contact_id',
				),
			),
			'merge' => true,
			'entry' => 'Contact',
			'entries' => 'Contacts',
		);
		return $links;
	}

	/**
	 * Hook called to retrieve a app specific exportLimit
	 *
	 * @param array/string $location location and other parameters (not used)
	 * @return the export_limit to be applied for the app, may be empty, int or string
	 */
	static function getAppExportLimit($location)
	{
		return $GLOBALS['egw_info']['server']['contact_export_limit'];
	}

	/**
	 * Register contacts as calendar resources (items which can be sheduled by the calendar)
	 *
	 * @param array $args hook-params (not used)
	 * @return array
	 */
	static function calendar_resources($args)
	{
		return array(
			'type' => 'c',// one char type-identifiy for this resources
			'info' => 'addressbook.addressbook_bo.calendar_info',// info method, returns array with id, type & name for a given id
		);
	}

	/**
	 * Register addressbook for group-acl
	 *
	 * @param array $args hook-params (not used)
	 * @return boolean|string true=standard group acl link, of string with link
	 */
	static function group_acl($args)
	{
		// addressbook uses group-acl, only if contacts-backend is NOT LDAP, as the ACL can not be modified there
		return $GLOBALS['egw_info']['server']['contact_repository'] != 'ldap';
	}

	/**
	 * For which groups should no group acl be used: addressbook always
	 *
	 * @param string|array $data
	 * @return boolean|array true, false or array with group-account_id's
	 */
	static function not_enum_group_acls($data)
	{
		return true;
	}

	/**
	 * ACL rights and labels used
	 *
	 * @param string|array string with location or array with parameters incl. "location", specially "owner" for selected acl owner
	 * @return array acl::(READ|ADD|EDIT|DELETE|PRIVAT|CUSTOM(1|2|3)) => $label pairs
	 */
	public static function acl_rights($params)
	{
		return array(
			acl::READ    => 'read',
			acl::EDIT    => 'edit',
			acl::ADD     => 'add',
			acl::DELETE  => 'delete',
		);
	}

	/**
	 * Hook to tell framework we use standard categories method
	 *
	 * @param string|array $data hook-data or location
	 * @return boolean
	 */
	public static function categories($data)
	{
		return true;
	}

	/**
	 * Hook returning options for deny_acl groups
	 *
	 * @param array $config
	 */
	public static function allow_account_edit($config)
	{
		$accountsel = new uiaccountsel();

		return '<input type="hidden" value="" name="newsettings[allow_account_edit]" />'.
			$accountsel->selection('newsettings[allow_account_edit]', 'allow_account_edit', $config['allow_account_edit'], 'groups', 4);
	}
}
