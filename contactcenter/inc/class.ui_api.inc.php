<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphael@think-e.com.br>                       *
  *  sponsored by Think.e - http://www.think-e.com.br                         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/*
	 * This is the Main ContactCenter API for other eGroupWare applications
	 *
	 */
  
	class ui_api
	{
		var $commons;
		var $commons_loaded = false;
		
		/*!

			@function ui_api (Constructor)
			@abstract Inserts all the common code to all CC API usage
			@author Raphael Derosso Pereira

		*/
		function ui_api()
		{
			$conns_types = ExecMethod('phpgwapi.config.read_repository', 'contactcenter');

			if (!is_array($conns_types) and !$conns_types['cc_people_email'])
			{
				$GLOBALS['phpgw']->exit('Default Connections Types Not Configured. Call Administrator!');
			}
			
			$preferences = ExecMethod('contactcenter.ui_preferences.get_preferences');
			
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template',$template_dir);

			$template->set_file(array('api' => 'api_common.tpl'));
			
			$template->set_var('cc_email_id_type', $conns_types['cc_people_email']);
			
			/* Messages */
			$template->set_var('cc_msg_err_invalid_catalog',lang('Unavailable or empty Catalog'));
			$template->set_var('cc_msg_err_contacting_server',lang('Couldn\'t contact server or server response is invalid. Contact Admin.'));
			$template->set_var('cc_msg_err_timeout',lang('Operation Timed Out.'));
			$template->set_var('cc_msg_err_serialize_data_unknown',lang('Data to be serialized is of unknown type!'));
			/* End Messages */

			if ($preferences['displayConnector'])
			{
				$template->set_var('cc_connector_visible', 'true');
			}
			else
			{
				$template->set_var('cc_connector_visible', 'false');
			}
			$template->set_var('cc_loading_1', lang('Contacting Server...'));
			$template->set_var('cc_loading_2', lang('Server Contacted. Waiting for response...'));
			$template->set_var('cc_loading_3', lang('Processing Information...'));
			$template->set_var('cc_loading_image', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/templates/default/images/loading_back.png');
			$template->set_var('cc_server_root', $GLOBALS['phpgw_info']['server']['webserver_url'] ? $GLOBALS['phpgw_info']['server']['webserver_url'] : '/');
			$template->set_var('cc_phpgw_img_dir', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/images');

			/* Style Sheets */
			$template->set_var('cc_css', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/styles/cc_api.css');
			$template->set_var('cc_dtree_css', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/dftree/dftree.css');

			/* JS Files */
			$template->set_var('cc_js_aux', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/ccAux.js');
			$template->set_var('cc_js_connector', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/connector.js');
			$template->set_var('cc_js_wz_dragdrop', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/wz_dragdrop/wz_dragdrop.js');
			$template->set_var('cc_js_dtree', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/dftree/dftree.js');
			$template->set_var('cc_js_dtabs', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/dTabs/dTabs.js');
			$template->set_var('cc_js_djswin', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/js/dJSWin/dJSWin.js');
			$template->set_var('cc_js_catalog_tree', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_tree.js');
			
			$template->parse('out','api');

			$this->commons = $template->get_var('out');
			$this->commons_loaded = false;
		}
		
		/*!

			@function get_email_win
			@abstract Returns the code to insert the Full-Featured Email Browser Window anywhere
			@author Raphael Derosso Pereira

		*/
		function get_email_win()
		{
			//$search = $this->ui_get_search_win();
			
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template', $template_dir);

			$template->set_file(array('email_win' => 'email_win.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			//$template->set_var('cc_search_win', $search);
			$template->set_var('cc_js_search', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_search.js');
			
			$template->set_var('cc_email_id_type', $search);
			
			$template->set_var('cc_email_win_title',lang('Contact Center').' - '.lang('Catalog Entries Emails'));
			$template->set_var('cc_email_status',lang('Status').':');
			$template->set_var('cc_email_search_text',lang('Search').'...');
			
			$template->set_var('cc_choose_catalogue',lang('Choose a catalogue').'...');
			$template->set_var('cc_choose_ordinance',lang('Choose a ordinance').'...');
			
			$template->set_var('cc_btn_to_add',lang('To').' >>');
			$template->set_var('cc_btn_to_del','<< '.lang('To'));
			$template->set_var('cc_btn_cc_add',lang('Cc').' >>');
			$template->set_var('cc_btn_cc_del','<< '.lang('Cc'));
			$template->set_var('cc_btn_cco_add',lang('Bcc').' >>');
			$template->set_var('cc_btn_cco_del','<< '.lang('Bcc'));
			$template->set_var('cc_btn_new',lang('New').'...');
			$template->set_var('cc_btn_details',lang('Details').'...');
			$template->set_var('cc_btn_update',lang('Update'));
			$template->set_var('cc_btn_ok',lang('Ok'));
			$template->set_var('cc_btn_cancel',lang('Cancel'));
			
			$template->set_var('cc_label_to',lang('To').':');
			$template->set_var('cc_label_cc',lang('Cc').':');
			$template->set_var('cc_label_cco',lang('Bcc').':');
			$template->set_var('cc_label_entries',lang('Entries').':');
			$template->set_var('cc_label_catalogues',lang('Catalogues').':');
			$template->set_var('cc_label_catalogue_type',lang('Type Of Catalogue').':');
			$template->set_var('cc_label_ordinance_type',lang('Type Of Ordinace').':');
			
			$template->set_var('phpgw_img_dir', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/images');
			
			$template->set_var('cc_js_email_win', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_email_win.js');
			
			$template->parse('out','email_win');

			return $template->get_var('out');
		}


		/*!

			@function get_search_win
			@abstract Returns the code to insert the Full-Featured Search Win anywhere
			@author Raphael Derosso Pereira

		*/
		function get_search_win()
		{
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template',$template_dir);

			$template->set_file(array('search' => 'search_win.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			$template->set_var('cc_search_title',lang('Contact Center - Search for Catalog Entries'));
			$template->set_var('cc_search_minimize',lang('Minimize'));
			$template->set_var('cc_search_close',lang('Close'));
			$template->set_var('cc_search_catalogues',lang('Catalogues'));
			$template->set_var('cc_search_for',lang('Search for...'));
			$template->set_var('cc_search_recursive',lang('Recursive Search?'));
			$template->set_var('cc_search_go',lang('Go'));
			$template->set_var('cc_search_cancel',lang('Cancel'));
			
			$template->set_var('cc_js_search_win', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_search_win.js');
			
			$template->set_var('phpgw_img_dir', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/images');
			
			$template->parse('out','search');

			return $template->get_var('out');
		}


		/*!
		
			@function get_search_obj
			@abstract Returns the code to insert CC Search Object anywhere
			@author Raphael Derosso Pereira

		*/
		function get_search_obj()
		{
			return "\n".'<script type="text/javascript" src="'.$GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/cc_search.js'.'"></script>'."\n";
		}


		/*!

			@function get_people_full_add
			@abstract Returns all the code to insert the People Catalog Full Add Window into anywhere
			@author Raphael Derosso Pereira

		*/
		function get_people_full_add()
		{
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template',$template_dir);

			$template->set_file(array('full_add' => 'full_add.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}

			/* Loads the Constant Fields */
			$bo_cc = CreateObject('contactcenter.bo_contactcenter');
			$last_level = $bo_cc->get_actual_level();
			$bo_cc->set_catalog('0.0');
			
			$prefixes = $bo_cc->catalog->get_all_prefixes();
			$suffixes = $bo_cc->catalog->get_all_suffixes();
			$addr_types = $bo_cc->catalog->get_all_addresses_types();
			$conn_types = $bo_cc->catalog->get_all_connections_types();
			$countries = $bo_cc->catalog->get_all_countries();

			$bo_cc->set_catalog($last_level);
			
			/* Title */
			$template->set_var('cc_contact_title',lang('Contact Center').' - '.lang('Contacts'));

			/* Messages */
			$template->set_var('cc_msg_err_empty_field',lang('field is empty'));
			$template->set_var('cc_msg_type_state',lang('Type new state here').'...');
			$template->set_var('cc_msg_type_city',lang('Type new city here').'...');
			/* End Messages */
			
			/* Contact */
			$template->set_var('cc_contact_save',lang('Save'));
			$template->set_var('cc_contact_cancel',lang('Cancel'));
			$template->set_var('cc_contact_reset',lang('Reset'));

			$template->set_var('cc_contact_personal',lang('Personal'));
			$template->set_var('cc_contact_addrs',lang('Addresses'));
			$template->set_var('cc_contact_conns',lang('Connections'));
			/* End Contact */
			
			/* Contact - Personal Data */
			$dateformat = $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat'];
		
			$j = 0;
			for ($i = 0; $i < 5; $i+=2)
			{
				switch($dateformat{$i})
				{
					case 'Y':
						$date[$j]['size'] = 4;
						$date[$j]['name'] = lang('Year');
						break;

					case 'm':
					case 'M':
						$date[$j]['size'] = 2;
						$date[$j]['name'] = lang('Month');
						break;

					case 'd':
						$date[$j]['size'] = 2;
						$date[$j]['name'] = lang('Day');
				}
				$j++;
			}
			
			$template->set_var('cc_pd_birth_size_0', "{$date[0]['size']}");
			$template->set_var('cc_pd_birth_size_1', "{$date[1]['size']}");
			$template->set_var('cc_pd_birth_size_2', "{$date[2]['size']}");
			
			$template->set_var('cc_pd_birthdate_0', "{$date[0]['name']}");
			$template->set_var('cc_pd_birthdate_1', "{$date[1]['name']}");
			$template->set_var('cc_pd_birthdate_2', "{$date[2]['name']}");

			$template->set_var('cc_pd_select_photo_b', lang('Browse').'...');
			$template->set_var('cc_form_photo_src', 'photo_form.html');
			
			$template->set_var('cc_pd_select_photo',lang('Select Photo'));
			$template->set_var('cc_pd_alias',lang('Alias'));
			$template->set_var('cc_pd_given_names',lang('Given Names'));
			$template->set_var('cc_pd_family_names',lang('Family Names'));
			$template->set_var('cc_pd_full_name',lang('Full Name'));
			$template->set_var('cc_pd_birthdate',lang('Birthdate'));
			$template->set_var('cc_pd_gpg_finger_print',lang('GPG Finger Print'));
			$template->set_var('cc_pd_suffix',lang('Suffix'));
			$template->set_var('cc_pd_choose_suffix',lang('Choose Suffix...'));
			$template->set_var('cc_pd_prefix',lang('Prefix'));
			$template->set_var('cc_pd_choose_prefix',lang('Choose Prefix...'));
			$template->set_var('cc_pd_notes',lang('Notes'));
			$template->set_var('cc_pd_sex',lang('Sex'));
			$template->set_var('cc_pd_choose_sex',lang('Choose Sex ...'));
			$template->set_var('cc_pd_male',lang('Male'));
			$template->set_var('cc_pd_female',lang('Female'));

			if (is_array($prefixes) and count($prefixes))
			{
				$prefixes_opts = '';
				foreach ($prefixes as $id => $prefix)
				{
					$prefixes_opts .= '<option value="'.$id.'">'.$prefix."</option>\n";
				}
			
				$template->set_var('cc_pd_prefix_opts', $prefixes_opts);
			}
			
			if (is_array($suffixes) and count($suffixes))
			{
				$suffixes_opts = '';
				foreach ($suffixes as $id => $suffix)
				{
					$suffixes_opts .= '<option value="'.$id.'">'.$suffix."</option>\n";
				}

				$template->set_var('cc_pd_suffix_opts', $suffixes_opts);
			}
			/* End Contact - Personal Data */
			
			/* Contact - Addresses */
			$template->set_var('cc_addr_types',lang('Type of Address'));
			$template->set_var('cc_addr_choose_types',lang('Choose Type of Address').'...');
			$template->set_var('cc_addr_countries',lang('Country'));
			$template->set_var('cc_addr_choose_countries',lang('Choose Country').'...');
			$template->set_var('cc_addr_states',lang('State'));
			$template->set_var('cc_addr_states_new',lang('New State').'...');
			$template->set_var('cc_addr_states_nostate',lang('No State'));
			$template->set_var('cc_addr_choose_states',lang('Choose State').'...');
			$template->set_var('cc_addr_cities',lang('City'));
			$template->set_var('cc_addr_cities_new',lang('New City').'...');
			$template->set_var('cc_addr_choose_cities',lang('Choose City').'...');
			$template->set_var('cc_addr_1',lang('Address 1'));
			$template->set_var('cc_addr_2',lang('Address 2'));
			$template->set_var('cc_addr_complement',lang('Complement'));
			$template->set_var('cc_addr_other',lang('Address Other'));
			$template->set_var('cc_addr_postal_code',lang('Postal Code'));
			$template->set_var('cc_addr_po_box',lang('PO Box'));
			$template->set_var('cc_addr_is_default',lang('Is Default?'));
			$template->set_var('cc_addr_yes',lang('Yes'));
			$template->set_var('cc_addr_no',lang('No'));
			$template->set_var('cc_available',lang('Available'));
			
			if (is_array($addr_types) and count($addr_types))
			{
				$addr_opts = '';
				foreach ($addr_types as $id => $type)
				{
					$addr_opts .= '<option value="'.$id.'">'.$type."</option>\n";
				}

				$template->set_var('cc_addr_types_opts', $addr_opts);
			}
						
			$country_opts = '';
			foreach ($countries as $id => $country)
			{
				$country_opts .= '<option value="'.$id.'">'.$country."</option>\n";
			}

			$template->set_var('cc_addr_countries_opts', $country_opts);
			/* End Contact - Addresses */
			
			/* Contact - Connections */
			$template->set_var('cc_conn_type',lang('Type of Connection'));
			$template->set_var('cc_conn_name',lang('Connection Name'));
			$template->set_var('cc_conn_value',lang('Connection Value'));
			
			$template->set_var('cc_new_same_type',lang('New from the same Type').'...');
			
			$template->set_var('cc_conn_type_none',lang('Choose Type of Connection').'...');

			if (is_array($conn_types) and count($conn_types))
			{
				$conn_opts = '';
				foreach ($conn_types as $id => $conn)
				{
					$conn_opts .= '<option value="'.$id.'">'.$conn."</option>\n";
				}

				$template->set_var('cc_conn_types_opts', $conn_opts);
			}
			/* End Contact - Connections */

			$template->parse('out_full', 'full_add');

			return $template->get_var('out_full').$this->get_city_plugin();
		}

		/*!

			@function get_quick_add_plugin
			@abstract Returns the code to insert the QuickAdd functionality into anywhere
			@author Raphael Derosso Pereira

		*/
		function get_quick_add_plugin()
		{
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template',$template_dir);

			$template->set_file(array('quickAdd' => 'quickAddPlugin.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			$template->set_var('ccQAPluginFile', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/ccQuickAdd-plugin.js');

			/* Fields Insertion */

			// TODO: get this from preferences
			$nFields = 5;
			$fields = array(
				lang('Alias'), 
				lang('Given Names'), 
				lang('Family Names'), 
				lang('Phone'),
				lang('Email')
			);
			
			$template->set_var('ccQAnFields', $nFields);
			
			$fieldsHTML = '';
			$fieldsTop = 10;
			$fieldsSpace = 30;
			for ($i = 0; $i < $nFields; $i++)
			{
				$fieldsHTML .= '<span id="ccQuickAddT'.$i.'" style="position: absolute; top: '.($fieldsTop+$i*$fieldsSpace).'px; left: 5px; width: 100px; text-align: right; border: 0px solid #999;">'.$fields[$i].':</span>'."\n";
				$fieldsHTML .= '<input id="ccQuickAddI'.$i.'" type="text" value="" maxlength="50" style="position: absolute; top: '.($fieldsTop+$i*$fieldsSpace).'px; left: 110px; width: 135px;">'."\n";
			}

			$template->set_var('ccQAFields', $fieldsHTML);
			$template->set_var('ccQAWinHeight', ($i+1)*$fieldsSpace+$fieldsTop);
			$template->set_var('ccQAFunctionsTop', ($fieldsTop+$i*$fieldsSpace).'px');
			
			/* Images Dir */
			$template->set_var('ccQACardImgRoot', $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/templates/default/images/');
			
			/* Texts */
			$template->set_var('ccQATitle', lang('Contact Center').' - '.lang('Quick Add'));
			$template->set_var('ccQASave', lang('Save'));
			$template->set_var('ccQAClear', lang('Clear'));
			$template->set_var('ccQACancel', lang('Cancel'));
			
			$template->parse('out_QA', 'quickAdd');

			return $template->get_var('out_QA');
		}

		/*!

			@function get_city_plugin
			@abstract Returns the code to insert the New City window anywhere
			@author Raphael Derosso Pereira

		*/
		function get_city_plugin()
		{
			$template_dir = PHPGW_SERVER_ROOT . '/contactcenter/templates/default/';
			$template = CreateObject('phpgwapi.Template',$template_dir);

			$template->set_file(array('city' => 'city.tpl'));
			
			if (!$this->commons_loaded)
			{
				$template->set_var('cc_api', $this->commons);
				$this->commons_loaded = true;
			}
			else
			{
				$template->set_var('cc_api', '');
			}
			
			$t = CreateObject('phpgwapi.datetime');

			$timezones_opts = '';
			foreach ($t->zone_offset_list as $zone => $offset)
			{
				$timezones_opts .= '<option value="'.$zone.'">'.$zone.' - GMT '.$offset."</option>\n";
			}
			
			$template->set_var('ccCity-jsFile', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/contactcenter/js/ccCity-plugin.js');

			$template->set_var('ccCity-title', lang('Contact Center').' - '.lang('City Creation/Edition'));
			
			$template->set_var('cc_available',lang('Available'));

			/* Error Messages */
	    	$template->set_var('ccCity-errNoCountry', lang('You must select a Country!'));
			$template->set_var('ccCity-errNoState', lang('You must select a State!'));
			$template->set_var('ccCity-errNoName', lang('You must specify a City name!'));
			$template->set_var('ccCity-errLat', lang('A Latitude is composed of a +/- sign (north/south) and a decimal value with up to 6 decimal places.'));
			$template->set_var('ccCity-errLon', lang('A Longitude is composed of a +/- sign (east/west) and a decimal value with up to 6 decimal places.'));
			$template->set_var('ccCity-errAlt', lang('A Altitude is composed of a +/- sign (above ocean/below ocean) and a decimal value with up to 6 decimal places.'));
			/* End Error Messages */

			$template->set_var('ccCity-country', lang('Country'));
			$template->set_var('ccCity-selectCountry', lang('Choose Country').'...');
			
			$template->set_var('ccCity-state', lang('State'));
			$template->set_var('ccCity-selectState', lang('Choose State').'...');
			$template->set_var('ccCity-noState', lang('No State'));
			$template->set_var('ccCity-newState', lang('New State'));
			$template->set_var('ccCity-newStateIcon', $GLOBALS['phpgw']->common->find_image('contactcenter','new_state'));
			
			$template->set_var('ccCity-name', lang('City Name'));
			
			$template->set_var('ccCity-timezone', lang('City Timezone'));
			$template->set_var('ccCity-selectTimezone', lang('Choose Timezone').'...');
			$template->set_var('ccCity-timezones', $timezones_opts);
			
			$template->set_var('ccCity-geoLat', lang('Latitude'));
			$template->set_var('ccCity-geoLon', lang('Longitude'));
			$template->set_var('ccCity-geoAlt', lang('Altitude'));
			$template->set_var('ccCity-geoExpLat', lang("In this field you can specify the Geographic Latitude of this city.\nThe number format is composed of a decimal degree, with its sign (+ for north, - for south) and 6 decimal places.\nEx.: -37.386013"));
			$template->set_var('ccCity-geoExpLon', lang("In this field you can specify the Geographic Longitude of this city.\nThe number format is composed of a decimal degree, with its sign (+ for east, - for west) and 6 decimal places.\nEx.: 122.082932"));
			$template->set_var('ccCity-geoExpAlt', lang("In this field you can specify the Geographic Altitude of this city.\nThe number format is composed of a decimal meter, with its sign (+ for above ocean, - for below ocean) and up to 6 decimal places.\nEx.: 850.366742"));
			
			$template->set_var('ccCity-save', lang('Save'));
			$template->set_var('ccCity-clear', lang('Clear'));
			$template->set_var('ccCity-cancel',lang('Cancel'));

			/* Loads the Constant Fields */
			$bo_cc = CreateObject('contactcenter.bo_contactcenter');
			$last_level = $bo_cc->get_actual_level();
			$bo_cc->set_catalog('0.0');
			
			$countries = $bo_cc->catalog->get_all_countries();

			$bo_cc->set_catalog($last_level);

			$country_opts = '';
			foreach ($countries as $id => $country)
			{
				$country_opts .= '<option value="'.$id.'">'.$country."</option>\n";
			}

			$template->set_var('ccCity-countryList', $country_opts);
			
			$template->parse('out_City', 'city');

			return $template->get_var('out_City');
		}
	}
?>
