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
	 * ContactCenter API - City Add/Modify Plugin
	 *
	 */

	function ccCityClass()
	{
		this.window = new dJSWin({
			id: 'ccCityWin',
			content_id: 'ccCity',
			win_class: 'row_off',
			width: '450px',
			height: '160px',
			title_color: '#3978d6',
			title: Element('ccCity-title').value,
			title_text_color: 'white',
			button_x_img: Element('cc_phpgw_img_dir').value+'/winclose.gif',
			border: true
		});

		this.window.draw();
		
		this.DOMcountries = Element('ccCity-country');
		this.DOMstates = Element('ccCity-state');
		this.DOMname = Element('ccCity-name');
		this.DOMtimezone = Element('ccCity-timezone');
		this.DOMgeoLat = Element('ccCity-geoLat');
		this.DOMgeoLon = Element('ccCity-geoLon');
		this.DOMgeoAlt = Element('ccCity-geoAlt');

		this.onSuccess = null;

		// Initialization
		this.clear();
	}

	ccCityClass.prototype.open = function ()
	{
		this.window.open();
	}

	ccCityClass.prototype.send = function ()
	{
		var _this = this;

		var handler = function(responseText)
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

			if (typeof(_this.onSuccess) == 'function')
			{
				_this.onSuccess(data['data']);
			}

			_this.window.close();
			_this._clearAll();
			_this.disableAll();
		}

		if (!this._validateData())
		{
			return false;
		}
		
		var data = new Array();
		
		data['id_country'] = this.DOMcountries.value;
		data['city_name']  = this.DOMname.value;
		
		switch (this.DOMstates.value)
		{
			case '_SEP_':
			case '_NONE_':
			case '_NOSTATE_':
				break;

			default:
				data['id_state'] = this.DOMstates.value;
		}
		
		switch (this.DOMtimezone.value)
		{
			case '_SEP_':
			case '_NONE_':
				break;

			default:
				data['city_timezone'] = this.DOMtimezone.value;
		}
		
		this.DOMgeoLat.value ? data['city_geo_latitude']  = this.DOMgeoLat.value : false;
		this.DOMgeoLon.value ? data['city_geo_longitude'] = this.DOMgeoLon.value : false;
		this.DOMgeoAlt.value ? data['city_geo_altitude']  = this.DOMgeoAlt.value : false;

		var sdata = 'data='+escape(serialize(data));

		Connector.newRequest('ccCity.send', CC_url+'add_city', 'POST', handler, sdata);
	}

	ccCityClass.prototype.clear = function ()
	{
		this._clearAll();
		this._disableAll();
	}

	ccCityClass.prototype.close = function ()
	{
		this.window.close();
	}

	ccCityClass.prototype.cancel = function ()
	{
		this.close();
	}

	ccCityClass.prototype.newState = function ()
	{
	}


	/****************************************************************************\
	 *                         Private Methods                                  *
	\****************************************************************************/

	ccCityClass.prototype._validateData = function()
	{
		switch (this.DOMcountries.value)
		{
			case '_NONE_':
			case '_SEP_':
				showMessage(Element('ccCity-errNoCountry').value);
				return false;
		}

		switch (this.DOMstates.value)
		{
			case '_NONE_':
			case '_SEP_':
				showMessage(Element('ccCity-errNoState').value);
				return false;
		}

		if (!this.DOMname.value)
		{
			showMessage(Element('ccCity-errNoName').value);
			return false;
		}

		this.DOMgeoLat.value = this.DOMgeoLat.value.replace(/,/g, '.');
		if (this.DOMgeoLat.value && isNaN(parseFloat(this.DOMgeoLat.value)))
		{
			showMessage(Element('ccCity-errLat').value);
			return false;
		}

		this.DOMgeoLon.value = this.DOMgeoLon.value.replace(/,/g, '.');
		if (this.DOMgeoLon.value && isNaN(parseFloat(this.DOMgeoLon.value)))
		{
			showMessage(Element('ccCity-errLon').value);
			return false;
		}

		this.DOMgeoAlt.value = this.DOMgeoAlt.value.replace(/,/g, '.');
		if (this.DOMgeoAlt.value && isNaN(parseFloat(this.DOMgeoAlt.value)))
		{
			showMessage(Element('ccCity-errAlt').value);
			return false;
		}

		return true;
	}
	
	ccCityClass.prototype._getStates = function ()
	{
		var _this = this;
		
		var handler = function (responseText)
		{
			var data = unserialize(responseText);
			
			clearSelectBox(_this.DOMstates, 3);
				
			if (typeof(data) != 'object')
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
		
				return;
			}

			if (data['status'] == 'empty')
			{
				showMessage(data['msg']);

				_this._enableAll();
				_this.DOMstates.selectedIndex = 1;
				return;
			}
			else if (data['status'] != 'ok')
			{
				showMessage(data['msg']);
				_this._disableAll();
				_this.DOMstates.selectedIndex = 0;
				return;
			}

			var i = 3;
			for (var j in data['data'])
			{
				_this.DOMstates.options[i] = new Option(data['data'][j], j);
				i++;
			}

			_this._enableAll();
			_this.DOMstates.selectedIndex = 0;
		};
		
		Connector.newRequest('ccCity._getStates', CC_url+'get_states&country='+this.DOMcountries.value, 'GET', handler);
	}

	ccCityClass.prototype._disableAll = function()
	{
		this.DOMstates.disabled = true;
		this.DOMname.disabled = true;
		this.DOMtimezone.disabled = true;
		this.DOMgeoLat.disabled = true;
		this.DOMgeoLon.disabled = true;
		this.DOMgeoAlt.disabled = true;
	}

	ccCityClass.prototype._clearAll = function()
	{
		clearSelectBox(this.DOMstates, 3);
		
		this.DOMcountries.value = '_NONE_';
		this.DOMstates.value = '_NONE_';
		this.DOMtimezone.value = '_NONE_';
		
		this.DOMname.value = '';
		this.DOMgeoLat.value = '';
		this.DOMgeoLon.value = '';
		this.DOMgeoAlt.value = '';
	}

	ccCityClass.prototype._enableAll = function()
	{
		this.DOMstates.disabled = false;
		this.DOMname.disabled = false;
		this.DOMtimezone.disabled = false;
		this.DOMgeoLat.disabled = false;
		this.DOMgeoLon.disabled = false;
		this.DOMgeoAlt.disabled = false;
	}

	var ccCity = new ccCityClass();
