  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Jonas Goes <jqhcb@users.sourceforge.net>                               *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

/***********************************************\
*                      TODO                     *
\***********************************************/

/*
 * function setHeightSpace ()
 *
 */

/***********************************************\
*                   CONSTANTS                   *
\***********************************************/

var CC_STATUS_FULL_ADD = 2;
var CC_STATUS_QUICK_ADD = 1;

var CC_card_image_width = 245;
var CC_card_image_height = 130;
var CC_card_extra = 16;


/***********************************************\
*               GLOBALS VARIABLES               *
\***********************************************/

var CC_visual = 'cards';

/* Cards Variables */
var CC_actual_letter = 'a';
var CC_last_letter = 'a';
var CC_actual_page = 1;
var CC_npages = 0;
var CC_max_cards = new Array();
var CC_conn_count=0;

var CC_old_icon_w = 0;
var CC_old_icon_h = 0;

/* Tabs Variables */
var CC_last_tab = 0;

/* Pseudo-Semafores */
var CC_tree_available = false;
var CC_full_add_const = false;
var CC_full_add_photo = false;
	
var CC_last_height = window.innerHeight;
var CC_last_width = window.innerWidth;

/* Contact Full Info */
var CC_contact_full_info;

/* Addresses Variables */
var CC_addr_last_selected = 0;

/* Connections Variables */
var CC_conn_last_selected = 0;

/***********************************************\
 *           FULL ADD/EDIT FUNCTIONS           *
\***********************************************/

function createPhotoFrame()
{
	photo_frame = document.createElement('iframe');
	document.body.appendChild(photo_frame);

	if (is_ie)
	{
		photo_form  = photo_frame.contentWindow.document.createElement('form');
		photo_input = photo_frame.contentWindow.document.createElement('input');
	}
	else
	{
		 photo_form  = photo_frame.contentDocument.createElement('form');
		 photo_input = photo_frame.contentDocument.createElement('input');
	}
	
	photo_frame.id = 'cc_photo_frame';
	photo_frame.style.position = 'absolute';
	//photo_frame.style.visibility = 'hidden';
	photo_frame.style.top = '600px';
	photo_frame.style.left = '0px';
	
	photo_form.id = 'cc_photo_form';
	photo_form.method = 'POST';
	photo_form.enctype = 'multipart/form-data';
	
	photo_input.id = 'cc_photo_input';
	photo_input.type = 'file';
	
	if (is_ie)
	{
		photo_frame.contentWindow.document.body.appendChild(photo_form);
	}
	else
	{
		photo_frame.contentDocument.body.appendChild(photo_form);
	}
	photo_form.appendChild(photo_input);
	
}

/********* Full Add Auxiliar Functions ****************/
function selectOption (id, option)
{
	var obj = Element(id);
	var max = obj.options.length;
	
	if (option == undefined)
	{
		obj.selectedIndex = 0;
	}
	else
	{
		for (var i = 0; i < max; i++)
		{
			if (obj.options[i].value == option)
			{
				obj.selectedIndex = i;
				break;
			}
		}
	}
}

function selectRadio (id, index)
{
	var obj = Element(id);
	var max = obj.options.length;
	for (var i = 0; i < max; i++)
	{
		i == index ? obj.options[i].checked = true : obj.options[i].checked = false;
	}
}

function clearSelectBox(obj, startIndex)
{
	var nOptions = obj.options.length;

	for (var i = nOptions - 1; i >= startIndex; i--)
	{
		obj.removeChild(obj.options[i]);
	}
}

/********** New Contact *************/
function newContact()
{
	if (!(ccTree.catalog_perms & 2))
	{
		return;
	}
	
	resetFullAdd();
	//populateFullAddConst();
	fullAddWin.open();
	tabs._showTab('cc_contact_tab_0');
}


/************ Edit Contact *************/
function editContact (id)
{
	resetFullAdd();
	setTimeout(function(){populateFullEdit(id);}, 100);
	fullAddWin.open();
	tabs._showTab('cc_contact_tab_0');
}

/*
	Updates all the constant fields in the
	full add window, like Prefixes, Suffixes,
	Countries and Types
*/
function populateFullAddConst()
{
	CC_full_add_const = false;
	
	setTimeout('populateFullAddConstAsync()', 10);
}

function populateFullAddConstAsync()
{
	var handler = function(responseText)
	{
		//Element('cc_debug').innerHTML = responseText;
		var data = unserialize(responseText);
		var i = 1;
		var j;
		
		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}
		
		/* Populate Prefixes */
		for (j in data[0])
		{
			Element('cc_pd_prefix').options[i] = new Option(data[0][j], j);
			i++;
		}
		
		/* Populate Suffixes */
		i = 1;
		for (j in data[1])
		{
			Element('cc_pd_suffix').options[i] = new Option(data[1][j], j);
			i++;
		}

		/* Populate Addresses Types */
		i = 1;
		for (j in data[2])
		{
			Element('cc_addr_types').options[i] = new Option(data[2][j], j);
			i++;
		}

		/* Populate Countries */
		i = 1;
		for (j in data[3])
		{
			Element('cc_addr_countries').options[i] = new Option(data[3][j], j);
			i++;
		}
		
		/* Populate Connection Types */
		i = 1;
		for (j in data[4])
		{
			Element('cc_conn_type').options[i] = new Option(data[4][j], j);
			i++;
		}
		
		/* Populate Relations Types */
		i = 0;
		for (j in data[5])
		{
			Element('cc_rels_type').options[i] = new Option(data[5][j], j);
			i++;
		}
		
		CC_full_add_const = true;

	};

	Connector.newRequest('populateFullAddConst', CC_url+'get_contact_full_add_const', 'GET', handler);
}

function populateFullEdit (id)
{
	var handler = function(responseText)
	{
		//Element('cc_debug').innerHTML = responseText;
		var data = unserialize(responseText);

		if (typeof(data) != 'object' || data['result'] != 'ok')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}

		resetFullAdd();

		CC_contact_full_info = data;
		Element('cc_full_add_contact_id').value = data['cc_full_add_contact_id'];
		populatePersonalData(data['personal']);
		//populateRelations(data['relations']);
	};

	Connector.newRequest('populateFullEdit', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_full_data&id=' + id, 'GET', handler);
}

function resetFullAdd()
{
	/* Clear information container */
	CC_contact_full_info = new Array();

	/* Clear Fields */
	Element('cc_full_add_form_personal').reset();
	Element('cc_full_add_form_addrs').reset();

	/* Personal Data */
	Element('cc_full_add_contact_id').value = null;
	Element('cc_pd_photo').src = 'templates/default/images/photo.png';

	/* Addresses */
	resetAddressFields();

	/* Connections */
	CC_conn_last_selected = '_NONE_';
	clearConn();
}

function postFullAdd()
{
	if (!checkFullAdd())
	{
		return false;
	}
	
	/* First thing: Send Photo */
	if (Element('cc_pd_select_photo').value != '' && !is_ie)
	{
		var nodes;
		var form, frame, old_frame;

		CC_full_add_photo = false;

		old_frame = Element('cc_photo_frame');
		if (!old_frame)
		{
			frame = document.createElement('iframe');
		}
		else
		{
			frame = old_frame;
		}
		
		frame.id = 'cc_photo_frame';
		frame.style.visibility = 'hidden';
		frame.style.top = '0px';
		frame.style.left = '0';
		frame.style.position = 'absolute';
		document.body.appendChild(frame);

		form = frame.contentDocument.createElement('form');
		
		var id_contact = Element('cc_full_add_contact_id').value;
		form.id = 'cc_form_photo';
		form.method = 'POST';
		form.enctype = 'multipart/form-data';
		form.action = 'http://'+ document.domain + Element('cc_root_dir').value+'../index.php?menuaction=contactcenter.ui_data.data_manager&method=post_photo&id='+(id_contact != '' && id_contact != 'null' ? id_contact : '');
		
		var input_clone = Element('cc_pd_select_photo').cloneNode(false);
		form.appendChild(input_clone);
		
		frame.contentDocument.body.appendChild(form);
		form.submit();

		CC_full_add_photo = true;
	}
	else if (Element('cc_pd_select_photo_t').value != '' && is_ie)
	{
		CC_full_add_photo = false;

		var frame = Element('cc_photo_frame');
		var form = frame.contentWindow.document.all['cc_photo_form'];
		var id_contact = Element('cc_full_add_contact_id').value;
		form.action = 'http://'+ document.domain + Element('cc_root_dir').value+'../index.php?menuaction=contactcenter.ui_data.data_manager&method=post_photo&id='+(id_contact != '' && id_contact != 'null' ? id_contact : '');

		form.submit();

		setTimeout('Element(\'cc_photo_frame\').src = \'cc_photo_frame.html\'', 1000);
		CC_full_add_photo = true;
	}

	setTimeout('postFullAddInfo()', 100);
}

function postFullAddInfo()
{
	var handler = function (responseText)
	{
		var data = unserialize(responseText);

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}

		if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			return;
		}

		fullAddWin.close();
		updateCards();
	};

	Connector.newRequest('postFullAddInfo', CC_url+'post_full_add', 'POST', handler, getFullAddData());
}

function getFullAddData()
{
	var data = new Array();
	var empty = true;
	var replacer = '__##AND##__';
	
	data['commercialAnd'] = replacer;
	
	if (Element('cc_full_add_contact_id').value != '' && Element('cc_full_add_contact_id').value != 'null')
	{
		data['id_contact'] = replaceComAnd(Element('cc_full_add_contact_id').value, replacer);
		data.length++;
	}

	/* Status: Full Added */
	data['id_status'] = CC_STATUS_FULL_ADD;
	
	/* Personal Data */
	data['alias']         = replaceComAnd(Element('cc_pd_alias').value, replacer);
	data['id_prefix']     = replaceComAnd(Element('cc_pd_prefix').value, replacer);
	data['given_names']   = replaceComAnd(Element('cc_pd_given_names').value, replacer);
	data['family_names']  = replaceComAnd(Element('cc_pd_family_names').value, replacer);
	data['names_ordered'] = replaceComAnd(Element('cc_pd_full_name').value, replacer);
	data['id_suffix']     = replaceComAnd(Element('cc_pd_suffix').value, replacer);;
	data['birthdate_0']   = replaceComAnd(Element('cc_pd_birthdate_0').value, replacer);
	data['birthdate_1']   = replaceComAnd(Element('cc_pd_birthdate_1').value, replacer);
	data['birthdate_2']   = replaceComAnd(Element('cc_pd_birthdate_2').value, replacer);
	data['sex']           = Element('cc_pd_sex').value == 1 ? 'M' : Element('cc_pd_sex').value == 2 ? 'F' : null;
	data['pgp_key']       = replaceComAnd(Element('cc_pd_gpg_finger_print').value, replacer);
	data['notes']         = replaceComAnd(Element('cc_pd_notes').value, replacer);

	data.length += 14;

	/* Addresses */
	saveAddressFields();
	data['addresses'] = CC_contact_full_info['addresses'];

	/* Connection */
	saveConnFields();

	if (CC_contact_full_info['connections'])
	{
		var connNumber = 0;
		for (var type in CC_contact_full_info['connections'])
		{
			if (type == 'length')
			{
				continue;
			}

			if (typeof(data['connections']) != 'object')
			{
				data['connections'] = new Array();
			}
			
			for (var i in CC_contact_full_info['connections'][type])
			{
				if (i == 'length')
				{
					continue;
				}

				if (typeof(data['connections']['connection'+connNumber]) != 'object')
				{
					data['connections']['connection'+connNumber] = new Array();
				}
				
				data['connections']['connection'+connNumber]['id_connection'] = CC_contact_full_info['connections'][type][i]['id'];
				data['connections']['connection'+connNumber]['id_typeof_connection'] = type;
				data['connections']['connection'+connNumber]['connection_name'] = CC_contact_full_info['connections'][type][i]['name'];
				data['connections']['connection'+connNumber]['connection_value'] = CC_contact_full_info['connections'][type][i]['value'];
				data['connections']['connection'+connNumber]['connection_is_default'] = i == 0 || i == '0' ? '1': '0';

				data['connections']['connection'+connNumber].length = 5;
				
				empty = false;
				connNumber++;
				data['connections'].length++;
			}

		}
		
		if (!empty)
		{
			data.length++;
			empty = true;
		}
	}
	
	if (CC_contact_full_info['removed_conns'])
	{
		empty = false;
		
		if (typeof(data['connections']) != 'object')
		{
			data['connections'] = new Array();
			data.length++;
		}

		data['connections']['removed_conns'] = CC_contact_full_info['removed_conns'];
		data['connections'].length++;
	}

	return 'data=' + escape(serialize(data));
}

function checkFullAdd()
{
	/* Check Personal Data */
	if (Element('cc_pd_given_names').value == '')
	{
		showMessage(Element('cc_pd_given_names').name + ' ' + Element('cc_msg_err_empty_field').value);
		return false;
	}
	
	if (Element('cc_pd_full_name').value == '')
	{
		showMessage(Element('cc_pd_full_name').name + ' ' + Element('cc_msg_err_empty_field').value);
		return false;
	}

	/* Check Addresses */

	/* Check Connections */

	/* Check Relations */

	return true;
}

/********* Personal Data Functions *********/
/* 
 * data[0] => cc_pd_select_photo
 * data[1] => cc_pd_alias
 * data[2] => cc_pd_given_names
 * data[3] => cc_pd_family_names
 * data[4] => cc_pd_full_name
 * data[5] => cc_pd_suffix
 * data[6] => cc_pd_birthdate
 * data[7] => cc_pd_sex SELECT
 * data[8] => cc_pd_prefix
 * data[9] => cc_pd_gpg_finger_print
 * data[10] => cc_pd_notes
 */

function populatePersonalData (data)
{
	for (i in data)
	{
		switch(i)
		{
			case 'cc_pd_suffix':
			case 'cc_pd_sex':
			case 'cc_pd_prefix':
				selectOption(i, data[i]);
				break;

			case 'cc_pd_photo':
				if (data[i])
				{
					Element(i).src =  data[i] + '&'+ Math.random();
				}
				break;

			default:
				Element(i).value = data[i] == undefined ? '' : data[i];
		}
	}

	return;
}

/********* End Personal Data Functions *********/


/********* Addresses Functions *********/
function resetAddressFields()
{
	Element('cc_addr_types').selectedIndex = 0;
	
	Element('cc_addr_countries').selectedIndex = 0;
	Element('cc_addr_countries').disabled = true;
	
	Element('cc_addr_states').selectedIndex = 0;
	Element('cc_addr_states').disabled = true;
	Element('cc_addr_states_new').disabled = true;
	Element('cc_addr_states_new').readonly = true;
	Element('cc_addr_states_new').value = '';

	Element('cc_addr_cities').selectedIndex = 0;
	Element('cc_addr_cities').disabled = true;
	Element('cc_addr_cities_new').disabled = true;
	Element('cc_addr_cities_new').readonly = true;
	Element('cc_addr_cities_new').value = '';

	Element('cc_addr_id').value = '';

	resetAddrFillingFields();
}

function resetAddrFillingFields()
{
	Element('cc_addr_1').value = '';
	Element('cc_addr_2').value = '';
	Element('cc_addr_other').value = '';
	Element('cc_addr_complement').value = '';
	Element('cc_addr_postal_code').value = '';
	Element('cc_addr_po_box').value = '';
	Element('cc_addr_is_default').checked = false;
}

function disableAddrFillingFields()
{
	Element('cc_addr_1').readonly = true;
	Element('cc_addr_1').disabled = true;
	Element('cc_addr_2').readonly = true;
	Element('cc_addr_2').disabled = true;
	Element('cc_addr_other').readonly = true;
	Element('cc_addr_other').disabled = true;
	Element('cc_addr_complement').readonly = true;
	Element('cc_addr_complement').disabled = true;
	Element('cc_addr_postal_code').readonly = true;
	Element('cc_addr_postal_code').disabled = true;
	Element('cc_addr_po_box').readonly = true;
	Element('cc_addr_po_box').disabled = true;
	Element('cc_addr_is_default').readonly = true;
	Element('cc_addr_is_default').disabled = true;
}

function updateAddressFields()
{
	var type = Element('cc_addr_types');
	var oldSelected = type.value;
	
	saveAddressFields();
	
	if (oldSelected == '_NONE_')
	{
		resetAddressFields();
		return true;
	}
	
	CC_addr_last_selected = type.selectedIndex;
	
	Element('cc_addr_countries').disabled = false;
	
	var data = CC_contact_full_info['addresses'];
	var addrIndex  = 'address'+Element('cc_addr_types').value;
	
	if (typeof(data) != 'object' || typeof(data[addrIndex]) != 'object') 
	{
		resetAddressFields();
		Element('cc_addr_countries').disabled = false;
		type.value = oldSelected;
		return true;
	}
	
	var addrTypeID = Element('cc_addr_types').value;
	
	data = CC_contact_full_info['addresses'][addrIndex];
	
	Element('cc_addr_id').value           = data['id_address']                ? data['id_address']         : '';
	Element('cc_addr_1').value            = data['address1']                  ? data['address1']           : '';
	Element('cc_addr_2').value            = data['address2']                  ? data['address2']           : '';
	Element('cc_addr_complement').value   = data['complement']                ? data['complement']         : '';
	Element('cc_addr_other').value        = data['address_other']             ? data['address_other']      : '';
	Element('cc_addr_postal_code').value  = data['postal_code']               ? data['postal_code']        : '';
	Element('cc_addr_po_box').value       = data['po_box']                    ? data['po_box']             : '';
	Element('cc_addr_is_default').checked = data['address_is_default'] == '1' ? true                       : false;

	Element('cc_addr_countries').value    = data['id_country'];
	updateAddrStates();
}

function updateAddrStates()
{
	var states = Element('cc_addr_states');
	if (Element('cc_addr_countries').value == '_NONE_')
	{
		states.disabled = true;
		states.selectedIndex = 0;
		clearSelectBox(states, 4);
		updateAddrCities();	
		return;
	}

	updateAddrFillingFields();
	populateStates();
}

function populateStates()
{
	var states = Element('cc_addr_states');

	var handler = function (responseText)
	{
		var data = unserialize(responseText);
		
		clearSelectBox(states, 4);
			
		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
	
			return;
		}

		if (data['status'] == 'empty')
		{
			showMessage(data['msg']);

			states.disabled = false;
			states.selectedIndex = 1;
			updateAddrNewStateOnMouseOut();
			updateAddrCities();
			return;
		}
		else if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			states.disabled = true;
			states.selectedIndex = 0;
			updateAddrCities();
			return;
		}

		var i = 4;
		for (var j in data['data'])
		{
			states.options[i] = new Option(data['data'][j], j);
			i++;
		}

		states.disabled = false;
		states.selectedIndex = 0;

		data = CC_contact_full_info['addresses'];
		var addrIndex = 'address'+Element('cc_addr_types').value;
		if (data && data[addrIndex])
		{
			states.value = data[addrIndex]['id_state'];
			if (states.value == '_NEW_')
			{
				if (CC_contact_full_info['addresses']['new_states'][addrIndex])
				{
					Element('cc_addr_states_new').value = CC_contact_full_info['addresses']['new_states'][addrIndex];
				}
				updateAddrNewStateOnMouseOut();
			}
			updateAddrCities();
		}
	};
	
	Connector.newRequest('populateStates', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_states&country='+Element('cc_addr_countries').value, 'GET', handler);
}

function updateAddrCities()
{
	var states = Element('cc_addr_states');
	var cities = Element('cc_addr_cities');
	var newState = Element('cc_addr_states_new');
	var requestStr;

	switch (states.value)
	{
		case '_NONE_':
			newState.readonly = true;
			newState.disabled = true;
			newState.value = '';

			cities.disabled = true;
			cities.selectedIndex = 0;
			updateAddrFillingFields();
			return;

		case '_NEW_':

			newState.readonly = false;
			newState.disabled = false;
			updateAddrNewStateOnMouseOut();
			
			cities.disabled = false;
			clearSelectBox(cities, 3);
			cities.selectedIndex = 1;
			updateAddrFillingFields();
			return;

		case '_SEP_': return;

		case '_NOSTATE_':
			clearSelectBox(cities, 3);
			
			cities.disabled = false;
			cities.selectedIndex = 0;
			
			requestStr = 'country='+Element('cc_addr_countries').value;
			break;
		
		default:
			requestStr = 'country='+Element('cc_addr_countries').value+'&state='+states.value;
	}

	newState.readonly = true;
	newState.disabled = true;
	newState.value = '';

	populateCities(requestStr);
}

function populateCities(requestStr)
{
	var cities = Element('cc_addr_cities');
	
	var handler = function (responseText)
	{
		var data = unserialize(responseText);
		
		clearSelectBox(cities, 3);
		
		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			
			return;
		}

		if (data['status'] == 'empty')
		{
			showMessage(data['msg']);

			cities.disabled = false;
			cities.selectedIndex = 1;
			updateAddrNewCityOnMouseOut();
			updateAddrFillingFields();
			return;
		}
		else if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			cities.disabled = true;
			cities.selectedIndex = 0;
			updateAddrFillingFields();
			return;
		}

		var i = 3;
		for (var j in data['data'])
		{
			cities.options[i] = new Option(data['data'][j], j);
			i++;
		}

		cities.disabled = false;
		cities.selectedIndex = 0;

		data = CC_contact_full_info['addresses'];
		var addrIndex = 'address'+Element('cc_addr_types').value;
		if (data && data[addrIndex])
		{
			cities.value = data[addrIndex]['id_city'];

			if (cities.value == '_NEW_')
			{
				if (CC_contact_full_info['addresses']['new_cities'][addrIndex])
				{
					Element('cc_addr_cities_new').value = CC_contact_full_info['addresses']['new_cities'][addrIndex];
				}
				updateAddrNewCityOnMouseOut();
			}
		}
	};
	
	Connector.newRequest('populateCities', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_cities&'+requestStr, 'GET', handler);
}

function updateAddrNewStateOnMouseOver ()
{
	if (Element('cc_addr_states_new').value == Element('cc_msg_type_state').value && Element('cc_addr_states').selectedIndex == 1) 
	{
		Element('cc_addr_states_new').value = '';
	}
}

function updateAddrNewStateOnMouseOut ()
{
	if (Element('cc_addr_states_new').value.length == 0 && Element('cc_addr_states').selectedIndex == 1) 
	{
		Element('cc_addr_states_new').value = Element('cc_msg_type_state').value;
	}
}

function updateAddrFillingFields()
{
	var countries = Element('cc_addr_countries');
	var cities = Element('cc_addr_cities');
	var newCity = Element('cc_addr_cities_new');

	if (countries.value == '_NONE_')
	{
		newCity.readonly = true;
		newCity.disabled = true;
		newCity.value = '';
		disableAddrFillingFields();
		return;
	}
	
	Element('cc_addr_1').readonly = false;
	Element('cc_addr_1').disabled = false;

	Element('cc_addr_2').readonly = false;
	Element('cc_addr_2').disabled = false;

	Element('cc_addr_other').readonly = false;
	Element('cc_addr_other').disabled = false;

	Element('cc_addr_complement').readonly = false;
	Element('cc_addr_complement').disabled = false;

	Element('cc_addr_postal_code').readonly = false;
	Element('cc_addr_postal_code').disabled = false;

	Element('cc_addr_po_box').readonly = false;
	Element('cc_addr_po_box').disabled = false;

	Element('cc_addr_is_default').readonly = false;
	Element('cc_addr_is_default').disabled = false;

	switch (cities.value)
	{
		case '_NONE_':
			newCity.readonly = true;
			newCity.disabled = true;
			newCity.value = '';

			//resetAddrFillingFields();
			
			return;

		case '_NEW_':

			newCity.readonly = false;
			newCity.disabled = false;
			updateAddrNewCityOnMouseOut();
			
			break;

		case '_SEP_': return;

		default:
			newCity.readonly = true;
			newCity.disabled = true;
			newCity.value = '';
	}
}

function updateAddrNewCityOnMouseOver ()
{
	if (Element('cc_addr_cities_new').value == Element('cc_msg_type_city').value && Element('cc_addr_cities').selectedIndex == 1) 
	{
		Element('cc_addr_cities_new').value = '';
	}
}

function updateAddrNewCityOnMouseOut ()
{
	if (Element('cc_addr_cities_new').value.length == 0 && Element('cc_addr_cities').selectedIndex == 1) 
	{
		Element('cc_addr_cities_new').value = Element('cc_msg_type_city').value;
	}
}

function saveAddressFields ()
{
	var lastIndex = CC_addr_last_selected;

	if (lastIndex == 0)
	{
		return true;
	}
	
	var addrFields = new Array('cc_addr_1', 
	                           'cc_addr_2', 
							   'cc_addr_complement', 
							   'cc_addr_other',
							   'cc_addr_postal_code', 
							   'cc_addr_po_box',
							   'cc_addr_countries',
							   'cc_addr_states',
							   'cc_addr_cities');

	var empty = true;
	
	for (var i = 0; i < 8; i++)
	{
		var field = Element(addrFields[i]);
		if (field.value && field.value != '_NONE_' && field.value != '_SEP_')
		{
			empty = false;
		}
	}
				
	if (empty)
	{
		return true;
	}

	if (!CC_contact_full_info['addresses'])
	{
		CC_contact_full_info['addresses'] = new Array();
	}

	var addrInfo = CC_contact_full_info['addresses']['address'+Element('cc_addr_types').options[lastIndex].value];

	if (!addrInfo)
	{
		addrInfo = new Array();
	}

	addrInfo['id_address'] = Element('cc_addr_id').value;

	switch(Element('cc_addr_countries').value)
	{
		case '_SEP_':
		case '_NONE_':
			addrInfo['id_country'] = false;
			break;

		default:
			addrInfo['id_country'] = Element('cc_addr_countries').value;
		
	}

	switch(Element('cc_addr_states').value)
	{
		case '_SEP_':
		case '_NONE_':
		case '_NEW_':
		case '_NOSTATE_':
			addrInfo['id_state'] = false;
			break;

		default:
			addrInfo['id_state'] = Element('cc_addr_states').value;
		
	}

	switch(Element('cc_addr_cities').value)
	{
		case '_SEP_':
		case '_NONE_':
		case '_NEW_':
			addrInfo['id_city'] = false;
			break;

		default:
			addrInfo['id_city'] = Element('cc_addr_cities').value;
		
	}	

	addrInfo['id_typeof_address']  = Element('cc_addr_types').options[lastIndex].value;
	addrInfo['address1']           = Element('cc_addr_1').value ? Element('cc_addr_1').value : false;
	addrInfo['address2']           = Element('cc_addr_2').value ? Element('cc_addr_2').value : false;
	addrInfo['complement']         = Element('cc_addr_complement').value ? Element('cc_addr_complement').value : false;
	addrInfo['address_other']      = Element('cc_addr_other').value ? Element('cc_addr_other').value : false;
	addrInfo['postal_code']        = Element('cc_addr_postal_code').value ? Element('cc_addr_postal_code').value : false;
	addrInfo['po_box']             = Element('cc_addr_po_box').value ? Element('cc_addr_po_box').value : false;
	addrInfo['address_is_default'] = Element('cc_addr_is_default').checked ? '1' : '0';

	CC_contact_full_info['addresses']['address'+Element('cc_addr_types').options[lastIndex].value] = addrInfo;

	if (Element('cc_addr_cities').value == '_NEW_' && 
	    Element('cc_msg_type_city').value !=  Element('cc_addr_cities_new').value &&
		Element('cc_addr_cities_new').value != '')
	{
		var addrRootInfo = CC_contact_full_info['addresses']['new_cities'];
		
		if (!addrRootInfo)
		{
			addrRootInfo = new Array();
		}
		
		var i = addrRootInfo.length;
		addrRootInfo[addrInfo['id_typeof_address']] = new Array();
		addrRootInfo[addrInfo['id_typeof_address']]['id_country'] = Element('cc_addr_countries').value;
		addrRootInfo[addrInfo['id_typeof_address']]['id_state']   = Element('cc_addr_states').value.charAt(0) != '_' ? Element('cc_addr_states').value : null;
		addrRootInfo[addrInfo['id_typeof_address']]['city_name']  = Element('cc_addr_cities_new').value;
		CC_contact_full_info['addresses']['new_cities'] = addrRootInfo;
	}

	if (Element('cc_addr_states').value == '_NEW_' && 
	    Element('cc_msg_type_state').value !=  Element('cc_addr_states_new').value && 
		Element('cc_addr_states_new').value != '')
	{
		var addrRootInfo = CC_contact_full_info['addresses']['new_states'];
		
		if (!addrRootInfo)
		{
			addrRootInfo = new Array();
		}
		
		var i = addrRootInfo.length;
		addrRootInfo[addrInfo['id_typeof_address']] = new Array();
		addrRootInfo[addrInfo['id_typeof_address']]['id_country'] = Element('cc_addr_countries').value;
		addrRootInfo[addrInfo['id_typeof_address']]['state_name'] = Element('cc_addr_states_new').value;
		CC_contact_full_info['addresses']['new_states'] = addrRootInfo;
	}

	return true;
}


/********* End Addresses Functions *********/



/********* Begin Connections Functions ************/
function connGetHTMLLine ()
{
	if (!document.all)
	{
		return '<td style="position: absolute; left: 0; top: 0; z-index: -1; visibility: hidden"><input id="cc_conn_id_' + CC_conn_count + '" type="hidden" value="_NEW_"></td>'+
		'<td align="center"><input id="cc_conn_name_'+CC_conn_count+'" style="width: 150px;" type="text"></td>' +
		'<td align="center"><input id="cc_conn_value_'+ CC_conn_count +'" style="width: 150px;" type="text"></td>' +
		'<td align="center" valign="middle" style="cursor: pointer; cursor: hand;"><img alt="X" src="templates/default/images/x.png" style="width:18px; height:18px" onclick="javascript:removeConnField(\'cc_conn_tr_' + CC_conn_count + '\')"></td>';
	}
	else
	{
		var tds = new Array();
		var inputs = new Array();
		var img = document.createElement('img');

		for (var i = 0; i < 4; i++)
		{
			tds[i] = document.createElement('td');

			tds[i].align = 'center';
		}

		tds[0].style.position = 'absolute';
		tds[0].style.visibility = 'hidden';
		tds[0].style.zIndex = '-1';

		tds[3].valign= 'middle';
		//tds[3].style.cursor = 'pointer';
		//tds[3].style.cursor = 'hand';
		
		var remove_id = 'cc_conn_tr_'+CC_conn_count;
		img.alt = 'X';
		img.src = 'templates/default/images/x.png';
		img.style.width = '18px';
		img.style.height = '18px';
		img.onclick = function(e){ removeConnField(remove_id);};
		
		for (var i = 0; i < 3; i++)
		{
			inputs[i] = document.createElement('input');
		}

		inputs[0].id = 'cc_conn_id_'+CC_conn_count;
		inputs[0].type = 'hidden';
		inputs[0].value = '_NEW_';

		inputs[1].id = 'cc_conn_name_'+CC_conn_count;
		inputs[1].type = 'text';
		inputs[1].style.width = '140px';

		inputs[2].id = 'cc_conn_value_'+CC_conn_count;
		inputs[2].type = 'text';
		inputs[2].style.width = '140px';

		tds[0].appendChild(inputs[0]);
		tds[1].appendChild(inputs[1]);
		tds[2].appendChild(inputs[2]);
		tds[3].appendChild(img);

		return tds;
	}
}

function connAddNewLine ()
{
	if (Element('cc_conn_type').options[Element('cc_conn_type').selectedIndex].value == '_NONE_')
	{
		return;
	}
	
	if (!document.all)
	{
		var obj = addHTMLCode('cc_conn', 'cc_conn_tr_'+CC_conn_count, connGetHTMLLine(),'tr');
	}
	else
	{
		var tds = connGetHTMLLine();
		var tr = document.createElement('tr');
		var tbody = Element('cc_conn');

		tr.id = 'cc_conn_tr_'+CC_conn_count;
		tbody.appendChild(tr);

		for (var i = 0; i < 4; i++)
		{
			tr.appendChild(tds[i]);
		}
	}
	CC_conn_count++;

	return CC_conn_count;

	if (is_ie5)
	{
		setTimeout('connRefreshClass(Element(\'cc_conn\').childNodes)');
	}
	else
	{
		connRefreshClass(Element('cc_conn').childNodes);
	}
	return CC_conn_count;
}

function connRemoveLine(id)
{	
	var p = Element(id).parentNode;

	removeHTMLCode(id);

	return;
	connRefreshClass(p.childNodes);
}

function connRefreshClass(Nodes)
{
	for (var i = 2; i < Nodes.length; i++)
	{
		Nodes.item(i).className = i % 2 ? 'row_on' : 'row_off';
	}
}

function clearConn()
{
	var connParent = Element('cc_conn').childNodes;
	var i;

	for (i = connParent.length - 1; i >= 0; i--)
	{
		if (connParent[i].id)
		{
			connRemoveLine(connParent[i].id);
		}
	}
	
	CC_conn_count = 0;
}

function removeConnField(id)
{
	var count = id.substring(id.lastIndexOf('_')+1);
	if (Element('cc_conn_id_'+count).value != '_NEW_')
	{
		if (typeof(CC_contact_full_info['removed_conns']) != 'object')
		{
			CC_contact_full_info['removed_conns'] = new Array();
		}

		CC_contact_full_info['removed_conns'][CC_contact_full_info['removed_conns'].length] = Element('cc_conn_id_'+count).value;
	}

	connRemoveLine(id);
}

function updateConnFields()
{
	
	var connID = Element('cc_conn_type').options[Element('cc_conn_type').selectedIndex].value;
	var i;

	/* First save the data */
	saveConnFields();

	CC_conn_last_selected = connID;
	
	clearConn();
	
	if (connID == '_NONE_')
	{
		return;
	}
	
	/* If no data already available, return */
	if (!CC_contact_full_info['connections'])
	{
		return;
	}
	
	/* Put the information that's already available */
	for (i in CC_contact_full_info['connections'][connID])
	{
		var num = connAddNewLine();
		Element('cc_conn_id_'+i).value = CC_contact_full_info['connections'][connID][i]['id'];
		Element('cc_conn_name_'+i).value = CC_contact_full_info['connections'][connID][i]['name'];
		Element('cc_conn_value_'+i).value = CC_contact_full_info['connections'][connID][i]['value'];
	}
}

function saveConnFields()
{
	if (CC_conn_last_selected != 0 && CC_conn_last_selected != '_NONE_')
	{
		var nodes = Element('cc_conn').childNodes;
		var k = 0;

		if (typeof(CC_contact_full_info['connections']) != 'object' || CC_contact_full_info['connections'] == null)
		{
			CC_contact_full_info['connections'] = new Array();
			CC_contact_full_info['connections'][CC_conn_last_selected] = new Array();
		}
		else if (typeof(CC_contact_full_info['connections'][CC_conn_last_selected]) != 'object')
		{
			CC_contact_full_info['connections'][CC_conn_last_selected] = new Array();
		}
		else 
		{
			delete CC_contact_full_info['connections'][CC_conn_last_selected];
			CC_contact_full_info['connections'][CC_conn_last_selected] = new Array();
		}

		for (var i = 0; i < nodes.length; i++)
		{
			if (nodes[i].id)
			{
				var subNodes = nodes[i].childNodes;
				var found = false;
				
				for (var j = 0; j < subNodes.length; j++)
				{
					if (subNodes[j].childNodes.length > 0 && 
					    subNodes[j].childNodes[0].id)
					{
						/* Check for the Connection Info array */
						if (typeof(CC_contact_full_info['connections'][CC_conn_last_selected][k]) != 'object')
						{
							CC_contact_full_info['connections'][CC_conn_last_selected][k] = new Array();
						}
						
					    if (subNodes[j].childNodes[0].id.indexOf('cc_conn_name') != -1)
						{
							if (subNodes[j].childNodes[0].value)
							{
								CC_contact_full_info['connections'][CC_conn_last_selected][k]['name'] = subNodes[j].childNodes[0].value;
							}
							else
							{
								CC_contact_full_info['connections'][CC_conn_last_selected][k]['name'] = '';
							}
						}
						else if (subNodes[j].childNodes[0].id.indexOf('cc_conn_value') != -1)
						{
							if (subNodes[j].childNodes[0].value)
							{
								CC_contact_full_info['connections'][CC_conn_last_selected][k]['value'] = subNodes[j].childNodes[0].value;
							}
							else
							{
								CC_contact_full_info['connections'][CC_conn_last_selected][k]['value'] = ''; 
							}
						}
						else if (subNodes[j].childNodes[0].id.indexOf('cc_conn_id') != -1)
						{
							CC_contact_full_info['connections'][CC_conn_last_selected][k]['id'] = subNodes[j].childNodes[0].value;
						}

						found = true;
					}
				}
				
				if (found)
				{
					k++;
				}
			}
		}

		if (CC_contact_full_info['connections'].length == 0)
		{
			delete CC_contact_full_info['connections'];
		}

		if (CC_contact_full_info['connections'][CC_conn_last_selected].length == 0)
		{
			delete CC_contact_full_info['connections'][CC_conn_last_selected];
		}
		
	}

	return;
}

/***********************************************\
*               VIEW CARDS FUNCTIONS            *
\***********************************************/

function removeEntry(id)
{
	var question = showMessage(Element('cc_msg_card_remove_confirm').value, 'confirm');

	if (!question)
	{
		return;
	}
	
	var handler = function (responseText)
	{
		var data = unserialize(responseText);

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}
		
		if (data['status'] != 'ok')
		{
			showMessage(data['msg']);
			return;
		}
		
		setTimeout('updateCards()',80);;
	};
	
	Connector.newRequest('removeEntry', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=remove_entry&remove=' + id, 'GET', handler);
}

function updateCards()
{
	setHeightSpace();
	setMaxCards(getMaxCards());
	showCards(getActualLetter(), getActualPage());
}


window.onresize = function ()
{
	updateCards();
}


function setHeightSpace ()
{
	/*
	var w_height = 0;
	var w_extra = 200;
	
	if (document.body.clientHeight)
	{
		w_height = parseInt(document.body.clientHeight);
	}
	else
	{
		w_height = 500;
	}
	if (w_height < 500)
	{
		w_height = 500;
	}
	Element('cc_card_space').style.height = (w_height - w_extra) + 'px';
	*/
}

function selectLetter (letter_id)
{
	for (var i = 0; i < 28; i++)
	{
		if ( i == letter_id )
		{
			Element('cc_letter_' + i).className = 'letter_box_active';
		}
		else
		{
			Element('cc_letter_' + i).className = 'letter_box';
		}
	}
}

function getActualPage ()
{
	return CC_actual_page;
}

function getActualLetter ()
{
	return CC_actual_letter;
}

function getFirstPage ()
{
	return 1;
}

function getPreviousPage ()
{
	if ( CC_actual_page > 1 )
	{
		return CC_actual_page - 1;
	}
	else
	{
		return 1;
	}
}

function getNextPage ()
{
	if ( CC_actual_page < CC_npages )
	{
		return CC_actual_page + 1;
	}
	else
	{
		return CC_npages;
	}
}

function getLastPage ()
{
	return CC_npages;
}

function setPages (npages, actual_page, showing_page)
{
	var html_pages = '';
	var n_lines = 0;
	var page_count = 0;

	if (CC_npages == 0)
	{
		html_pages = '';
	}
	else
	{
		var page = 1;
		if (showing_page > 10 || (!showing_page && actual_page > 10))
		{
			var final_page = showing_page? showing_page-11 : actual_page-11;
			if (final_page < 1)
			{
				final_page = 1;
			}
			
			html_pages += '<a href="javascript:setPages('+npages+', '+ actual_page +', '+ final_page +')">...</a> ';

			page = showing_page ? showing_page : actual_page;
		}
		
		for (; page <= npages; page++)
		{
			if (page_count > 10)
			{
				html_pages += '<a href="javascript:setPages('+npages+', '+ actual_page +', '+ page +');">...</a>';
				break;
			}
			if ( page == actual_page )
			{
				html_pages += '<b>'+page+'</b>';
			}
			else
			{
				html_pages += '<a href="javascript:showCards(\'' + CC_actual_letter + '\',' + page + ')">' + page + '</a>';
			}
			html_pages += '&nbsp;';
			page_count++;
		}
	}

	if (actual_page <= 1)
	{
		Element('cc_panel_arrow_first').onclick = '';
		Element('cc_panel_arrow_previous').onclick = '';
		Element('cc_panel_arrow_first').style.cursor = 'auto';
		Element('cc_panel_arrow_previous').style.cursor = 'auto';
	}
	else
	{
		Element('cc_panel_arrow_first').onclick = function (event) { showCards(getActualLetter(), getFirstPage()); };
		Element('cc_panel_arrow_previous').onclick = function (event) { showCards(getActualLetter(), getPreviousPage()); };
		if (is_mozilla)
		{
			Element('cc_panel_arrow_first').style.cursor = 'pointer';
			Element('cc_panel_arrow_previous').style.cursor = 'pointer';
		}
		Element('cc_panel_arrow_first').style.cursor = 'hand';
		Element('cc_panel_arrow_previous').style.cursor = 'hand';
	}

	if (actual_page == CC_npages)
	{
		Element('cc_panel_arrow_next').onclick = '';
		Element('cc_panel_arrow_last').onclick = '';
		Element('cc_panel_arrow_next').style.cursor = 'auto';
		Element('cc_panel_arrow_last').style.cursor = 'auto';
	}
	else
	{
		Element('cc_panel_arrow_next').onclick = function (event) { showCards(getActualLetter(), getNextPage()); };
		Element('cc_panel_arrow_last').onclick = function (event) { showCards(getActualLetter(), getLastPage()); };
		if (is_mozilla)
		{
			Element('cc_panel_arrow_next').style.cursor = 'pointer';
			Element('cc_panel_arrow_last').style.cursor = 'pointer';
		}
		Element('cc_panel_arrow_next').style.cursor = 'hand';
		Element('cc_panel_arrow_last').style.cursor = 'hand';
	}
	
	Element('cc_panel_pages').innerHTML = html_pages;
}

function populateCards(data)
{
	var pos = 0;
	var ncards = data[3].length;
	
	if (typeof(data[3]) == 'object' && ncards > 0)
	{
		for (var i = 0; i < CC_max_cards[1]; i++)
		{
			for (var j = 0; j < CC_max_cards[0]; j++)
			{
				id = 'cc_card:'+j+':'+i;
			
				for (var k = 0; k < data[2].length; k++)
				{
					if(data[3][pos][k] != 'none')
					{
						switch (data[2][k])
						{
							case 'cc_name':
								if (data[3][pos][k].length > 30)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 30);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = data[3][pos][k];
								}
								break;
							
							case 'cc_mail':
								if (data[3][pos][k].length > 20)
								{
									Element(id+':'+data[2][k]).innerHTML = '<a href="../email/compose.php?to=' + data[3][pos][k] + '">'+adjustString(data[3][pos][k], 20)+'</a>';
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = '<a href="../email/compose.php?to=' + data[3][pos][k] + '">'+data[3][pos][k]+'</a>';
								}
								break;
							
							case 'cc_phone':
								if (data[3][pos][k].length > 20)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 20);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 20);
								}
									
								break;

							case 'cc_title':
								if (data[3][pos][k].length > 15)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 15);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = data[3][pos][k];
								}
								break;

							case 'cc_id':
								var id_contact = data[3][pos][k];
								Element(id+':'+data[2][k]).value = data[3][pos][k];
								if (data[4][pos] == 1)
								{
									Element(id+':cc_photo').src = '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_photo&id='+data[3][pos][k]+'&none=' + Math.random();
								}
								else
								{
									Element(id+':cc_photo').src = '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_photo';
								}
								break;

							default:
								if (data[3][pos][k].length > 10)
								{
									Element(id+':'+data[2][k]).innerHTML = adjustString(data[3][pos][k], 10);
									Element(id+':'+data[2][k]).title = data[3][pos][k];
								}
								else
								{
									Element(id+':'+data[2][k]).innerHTML = data[3][pos][k];
								}
						}
					}
				}
	
				if (--ncards == 0)
				{
					j = CC_max_cards[0];
					i = CC_max_cards[1];
				}
	
				pos++;
			}
		}
	}
}

function adjustString (str, max_chars)
{
	if (str.length > max_chars)
	{
		return str.substr(0,max_chars) + '...';
	}
	else
	{
		return str;
	}
}

function setMaxCards (maxcards)
{
	CC_max_cards = maxcards;
	ncards = maxcards[0] * maxcards[1];

	var handler = function (responseText)
	{
		showMessage('ok');
	};

	Connector.newRequest('setMaxCards', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=set_n_cards&ncards=' + ncards, 'GET'); 
}

function getMaxCards ()
{
	var coord = new Array();
	
	//Element('cc_card_space').innerHTML = '';
	//return;

	card_space_width = parseInt(Element('cc_main').offsetWidth) - parseInt(Element('cc_left').offsetWidth) - parseInt(CC_card_extra);
	//card_space_width = parseInt(is_ie ? document.body.offsetWidth : window.innerWidth) - parseInt(Element('cc_left').offsetWidth) - parseInt(CC_card_extra) - 40;
	card_space_height = parseInt(Element('cc_card_space').offsetHeight) - parseInt(CC_card_extra);
	
	card_width = CC_card_image_width + CC_card_extra;
	card_height = CC_card_image_height + CC_card_extra;

	ncols = parseInt(card_space_width / card_width);
	nlines = parseInt(card_space_height / card_height);
	
	coord[0] = ncols;
	//coord[1] = nlines;
	coord[1] = 10;

	//alert( 'WIDTH: ' + card_space_width + ' / ' + card_width + ' = ' + card_space_width / card_width + "\n" +
	//	'HEIGHT: ' + card_space_height + ' / ' + card_height + ' = ' + card_space_height / card_height );

	return coord;
}

function getCardHTML (id)
{
	html_card = '<td id="' + id + '" style="width: ' + CC_card_image_width + 'px; height: ' + CC_card_image_height + '">' +
		'<div style="border: 0px solid #999; position: relative;">' +
			'<img src="templates/default/images/card.png" border="0" width="' + CC_card_image_width +'" height="' + CC_card_image_height + '"i ondblclick="editContact(Element(\'' + id + ':cc_id\').value);">' + 
				//'<img title="'+Element('cc_msg_card_new').value+'" id="' + id + ':cc_card_new" style="position: absolute; top: 28px; left: 224px; width: 18px; height: 18px; cursor: pointer; cursor: hand; z-index: 1" onclick="alert(\'Function not working yet...\')" onmouseover="resizeIcon(\''+id+':cc_card_new\',0)" onmouseout="resizeIcon(\''+id+':cc_card_new\',1)" src="templates/default/images/cc_new_card.png">' +
				(ccTree.catalog_perms & 2 ?
				'<img title="'+Element('cc_msg_card_edit').value+'" id="' + id + ':cc_card_edit" style="position: absolute; top: 35px; left: 222px; width: 18px; height: 18px; cursor: pointer; cursor: hand; z-index: 1" onclick="editContact(Element(\'' + id + ':cc_id\').value);" onmouseover="resizeIcon(\''+id+':cc_card_edit\',0)" onmouseout="resizeIcon(\''+id+':cc_card_edit\',1)" src="templates/default/images/cc_card_edit.png">' +
				'<img title="'+Element('cc_msg_card_remove').value+'" id="' + id + ':cc_card_remove" style="position: absolute; top: 78px; left: 223px; width: 15px; height: 14px; cursor: pointer; cursor: hand; z-index: 1" onclick="removeEntry(Element(\'' + id + ':cc_id\').value);" onmouseover="resizeIcon(\''+id+':cc_card_remove\',0)" onmouseout="resizeIcon(\''+id+':cc_card_remove\',1)" src="templates/default/images/cc_x.png">' : '') +
				'<img id="' + id + ':cc_photo" style="position: absolute; top: 10px; left: 10px;" src="" border="0" width="60px" height="80px" ondblclick="editContact(Element(\'' + id + ':cc_id\').value);">' + 
				'<span id="' + id + ':cc_company" style="position: absolute; top: 5px; left: 75px; width: 135px; border: 0px solid #999; font-weight: bold; font-size: 14px; text-align: center; height: 10px;" onmouseover="//Element(\''+id+':cc_company_full\').style.visibility=\'visible\'" onmouseout="//Element(\''+id+':cc_company_full\').style.visibility=\'hidden\'"></span>' + 
				'<span id="' + id + ':cc_name" style="position: absolute; top: 30px; left: 75px; width: 135px; border: 0px solid #999; font-weight: bold; font-size: 10px; text-align: center; height: 10px;" onmouseover="//Element(\''+id+':cc_name_full\').style.visibility=\'visible\'" onmouseout="//Element(\''+id+':cc_name_full\').style.visibility=\'hidden\'"></span>' + 
				'<span id="' + id + ':cc_title" style="position: absolute; top: 60px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 12px; text-align: center; height: 10px;"></span>' + 
				'<span id="' + id + ':cc_phone" style="position: absolute; top: 90px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 10px; text-align: center; height: 10px;"></span>' + 
				'<span id="' + id + ':cc_mail" style="position: absolute; top: 105px; left: 75px; width: 135px; border: 0px solid #999; font-weight: normal; font-size: 10px; text-align: center; height: 10px;"></span>' + 
				'<span id="' + id + ':cc_alias" style="position: absolute; top: 95px; left: 10px; width: 60px; border: 0px solid #999; font-weight: normal; font-size: 9px; text-align: center; height: 10px;"></span>' + 
			'<input id="' + id + ':cc_id" type="hidden">' +
		'</div>' + '</td>';

	return html_card;
}

function drawCards(ncards)
{
	var pos;

	html_cards = '<table  border="0" cellpadding="0" cellspacing="' + CC_card_extra + '">';
	
	if (ncards > 0)
	{
		for (var i = 0; i < CC_max_cards[1]; i++)
		{
			html_cards += '<tr>';
			for (var j = 0; j < CC_max_cards[0]; j++)
			{
				html_cards += getCardHTML('cc_card:' + j + ':' + i);
				if (--ncards == 0)
				{
					j = CC_max_cards[0];
					i = CC_max_cards[1];
				}
			}
			html_cards += '</tr>';
		}
	}	
	else if (CC_max_cards != 0)
	{
		html_cards += '<tr><td>' + Element('cc_msg_no_cards').value + '</td></tr>';
	}
	else
	{
		html_cards += '<tr><td>' + Element('cc_msg_err_no_room').value + '</td></tr>';
	}

	html_cards += '</table>';

	Element('cc_card_space').innerHTML = html_cards;
}

function showCards (letter,page, ids)
{
	var data  = new Array();

	if ( letter != CC_actual_letter )
	{
		CC_actual_page = '1';
	}
	else
	{
		CC_actual_page = page;
	}

	CC_actual_letter = letter;

	if (CC_max_cards[0] == 0)
	{
		drawCards(0);
		setPages(0,0);
		return;
	}

	var handler = function (responseText)
	{
		var data = new Array();

		if (responseText.charAt(0) == '0')
		{
			CC_npages = 0;
			CC_actual_page = 1;
			drawCards(0);
			setPages(0,0);
			return;
		}
		
//				Element('cc_debug').innerHTML = responseText;
		data = unserialize(responseText);

		if (typeof(data) != 'object')
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}
		
		if (typeof(data[3]) == 'object')
		{
			CC_npages = parseInt(data[0]);
			CC_actual_page = parseInt(data[1]);
			drawCards(data[3].length);
			populateCards(data);
			setPages(data[0], data[1]);
		}
		else
		{
			showMessage(Element('cc_msg_err_contacting_server').value);
			return;
		}
	};

	var info = "letter="+letter+"&page="+CC_actual_page+"&ids="+ids;
	
	Connector.newRequest('showCards', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_cards_data', 'POST', handler, info);
}

/***********************************************\
*        COMMON ENTRY FUNCTIONS                *
\***********************************************/

function ccChangeVisualization(type)
{
	var table_h = Element('cc_panel_table');
	var cards_h = Element('cc_panel_cards');
	
	switch (type)
	{
		case 'cards':
			cards_h.style.display = 'none';
			table_h.style.display = 'inline';
			break;

		case 'table':
			table_h.style.display = 'none';
			cards_h.style.display = 'inline';
			break;
	}
	
	CC_visual = type;
	showCards(getActualLetter(), getActualPage());
}

function ccSearchUpdate(ids)
{
	Element('cc_panel_letters').style.display = 'none';
	Element('cc_panel_search').style.display  = 'inline';
	
	drawCards(0);

	if (!ids)
	{
		//ccSearchHide();
		return;
	}
	
	var sIds = serialize(ids);

	if (CC_actual_letter != 'search')
	{
		CC_last_letter = CC_actual_letter;
	}
	showCards('search', '1', sIds);
}

function ccSearchHidePanel()
{
	Element('cc_panel_search').style.display  = 'none';
	Element('cc_panel_letters').style.display = 'inline';
	if (CC_actual_letter == 'search')
	{
		CC_actual_letter = CC_last_letter;
	}
}

function ccSearchHide()
{
	Element('cc_panel_search').style.display  = 'none';
	Element('cc_panel_letters').style.display = 'inline';
	showCards(CC_last_letter, '1');
}

/***********************************************\
*               QUICK ADD FUNCTIONS             *
\***********************************************/

function resetQuickAdd ()
{
	Element('cc_qa_alias').value = '';
	Element('cc_qa_given_names').value = '';
	Element('cc_qa_family_names').value = '';
	Element('cc_qa_phone').value = '';
	Element('cc_qa_email').value = '';
}

function getQuickAdd ()
{
	var data = new Array();
	data[0] = Element('cc_qa_alias').value;
	data[1] = Element('cc_qa_given_names').value;
	data[2] = Element('cc_qa_family_names').value;
	data[3] = Element('cc_qa_phone').value;
	data[4] = Element('cc_qa_email').value;
	
	return data;
}

function sendQuickAdd ()
{
	var data = getQuickAdd();
	
	var str = serialize(data);

	if (!str)
	{
		return false;
	}

	var handler = function (responseText)
	{
		setTimeout('updateCards()',100);;
	}

	resetQuickAdd();

	Connector.newRequest('quickAdd', '../index.php?menuaction=contactcenter.ui_data.data_manager&method=quick_add', 'POST', handler, 'add='+escape(str));
}
