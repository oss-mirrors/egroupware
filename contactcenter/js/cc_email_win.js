  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/*
	 * ContactCenter API - Email Gathering Window
	 */

	function ccEmailWinClass(params)
	{
		if (typeof(params) != 'object')
		{
			return false;
		}

		this.Connector = new cConnector();
		this.searching = false;
		
		var _this = this;
		var search_params = new Array();
		search_params['holder'] = Element('cc_email_search');
		search_params['total_width'] = '550px';
		search_params['input_width'] = '200px';
		search_params['progress_top'] = '0px';
		search_params['progress_left'] = '380px';
		search_params['progress_color'] = '#3978d6';
		search_params['progress_width'] = '250px';
		search_params['conn_1_msg'] = Element('cc_loading_1').value;
		search_params['conn_2_msg'] = Element('cc_loading_2').value;
		search_params['conn_3_msg'] = Element('cc_loading_3').value;
		search_params['button_text'] = Element('cc_email_search_text').value;
		search_params['Connector'] = this.Connector;

		var catalog_params = new Array();
		catalog_params['name'] = 'ccEmailWin.catalogues';
		catalog_params['id_destination'] = 'cc_email_catalogues';
		
		this.window = params['window'];
		this.search = new ccSearchClass(search_params);
		this.catalogues = new ccCatalogTree(catalog_params);

		this.catalogues.afterSetCatalog = function() {_this._getAllEntries();};
		this.catalogues.Connector = this.Connector;
		
		this.entries = Element('cc_email_win_entries');
		this.to  = Element('cc_email_win_to');
		this.cc  = Element('cc_email_win_cc');
		this.cco = Element('cc_email_win_cco');

		this.to_count  = 0;
		this.cc_count  = 0;
		this.cco_count = 0;

		this.contents = new Array();

		this.onOk = null;

		this.entries.style.overflow = 'auto';

		this._search_set();

		this.window.buttons.xDIV.onclick = function() {_this.close();};
	}

	ccEmailWinClass.prototype.setContents = function(data)
	{
		if (typeof(data) != 'object')
		{
			return false;
		}

		this.clearAll();
		
		if (data['entries'])
		{
			for (var i in data['entries'])
			{
				this.entries = new Option(data['entries'][i], i);
			}
		}
		
		if (data['to'])
		{
			for (var i in data['to'])
			{
				this.to = new Option(data['to'][i], i);
			}
		}

		if (data['cc'])
		{
			for (var i in data['cc'])
			{
				this.cc = new Option(data['cc'][i], i);
			}
		}

		if (data['cco'])
		{
			for (var i in data['cco'])
			{
				this.cco = new Option(data['cco'][i], i);
			}
		}
	}

	ccEmailWinClass.prototype.getContents = function()
	{
		var i;
		
		this.contents = new Array();
		this.contents['entries'] = new Array();
		this.contents['to'] = new Array();
		this.contents['cc'] = new Array();
		this.contents['cco'] = new Array();
		
		for (i = 0; i < this.entries.options.length; i++)
		{
			this.contents['entries'][this.contents['entries'].length] = this.entries.options[i].text;
		}

		for (i = 0; i < this.to.options.length; i++)
		{
			this.contents['to'][this.contents['to'].length] = this.to.options[i].text;
		}

		for (i = 0; i < this.cc.options.length; i++)
		{
			this.contents['cc'][this.contents['cc'].length] = this.cc.options[i].text;
		}
		
		for (i = 0; i < this.cco.options.length; i++)
		{
			this.contents['cco'][this.contents['cco'].length] = this.cco.options[i].text;
		}
		
		return this.contents;
	}

	ccEmailWinClass.prototype.open = function()
	{
		this.Connector.setVisible(true);
		this.window.open()
	}
	
	ccEmailWinClass.prototype.ok = function()
	{
		if (this.onOk)
		{
			this.onOk(this.getContents());
		}

		this.clearAll();
		this.window.close();
	}
	
	ccEmailWinClass.prototype.clearAll = function()
	{
		this.clearEntries();
		this.clearTo();
		this.clearCC();
		this.clearCCO();
	}

	ccEmailWinClass.prototype.clearEntries = function()
	{
		var length;

		if (this.entries.options.length)
		{
			length = this.entries.options.length-1;
			for (var i = length; i >= 0; i--)
			{
				this.entries.removeChild(this.entries.options[i]);
			}
		}
	}

	ccEmailWinClass.prototype.clearTo = function()
	{
		var length;
		
		if (this.to.options.length)
		{
			length = this.to.options.length-1;
			for (var i = length; i >= 0; i--)
			{
				this.to.removeChild(this.to.options[i]);
			}
		}
	}
	
	ccEmailWinClass.prototype.clearCC = function()
	{
		var length;
		
		if (this.cc.options.length)
		{
			length = this.cc.options.length-1;
			for (var i = length; i >= 0; i--)
			{
				this.cc.removeChild(this.cc.options[i]);
			}
		}
	}

	ccEmailWinClass.prototype.clearCCO = function()
	{
		var length;
		
		if (this.cco.options.length)
		{
			length = this.cco.options.length-1;
			for (var i = length; i >= 0; i--)
			{
				this.cco.removeChild(this.cco.options[i]);
			}
		}
	}
	
	ccEmailWinClass.prototype.close = function()
	{
		this.Connector.setVisible(false);
		//this.clearAll();
		this.window.close();
	}

	ccEmailWinClass.prototype.entries_to = function()
	{
		var i;
		var length = this.entries.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.entries.options[i].selected)
			{
				this.to.options[this.to.options.length] = new Option(this.entries.options[i].text, this.entries.options[i].value);
			}
		}
	}

	ccEmailWinClass.prototype.entries_cc = function()
	{
		var i;
		var length = this.entries.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.entries.options[i].selected)
			{
				this.cc.options[this.cc.options.length] = new Option(this.entries.options[i].text, this.entries.options[i].value);
			}
		}
	}

	ccEmailWinClass.prototype.entries_cco = function()
	{
		var i;
		var length = this.entries.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.entries.options[i].selected)
			{
				this.cco.options[this.cco.options.length] = new Option(this.entries.options[i].text, this.entries.options[i].value);
			}
		}
	}

	ccEmailWinClass.prototype.to_entries = function()
	{
		var i;
		var length = this.to.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.to.options[i].selected)
			{
				this.to.removeChild(this.to.options[i]);
			}
		}
	}

	ccEmailWinClass.prototype.cc_entries = function()
	{
		var i;
		var length = this.cc.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.cc.options[i].selected)
			{
				this.cc.removeChild(this.cc.options[i]);
			}
		}
	}

	ccEmailWinClass.prototype.cco_entries = function()
	{
		var i;
		var length = this.cco.options.length-1;
		for (i = length; i >= 0; i--)
		{
			if (this.cco.options[i].selected)
			{
				this.cco.removeChild(this.cco.options[i]);
			}
		}
	}

	/****************************************************************************\
	 *                             Private Methods                              *
	\****************************************************************************/

	ccEmailWinClass.prototype._search_set = function()
	{
		var _this = this;
		
		this.search.onSearchFinish = function (result)
		{
			if (!result || typeof(result) != 'object')
			{
				_this.clearEntries();
				_this.searching = false;
				return false;
			}

			var sdata = new Array();
			
			sdata['ids'] = result;
			sdata['fields'] = new Array()
			sdata['fields']['names_ordered'] = true;
			sdata['fields']['connections'] = true;
			
			var str_data = 'data='+serialize(sdata);

			_this.Connector.newRequest('email_search', CC_url+'get_multiple_entries', 'POST', _this._getEntriesHandler(), str_data);
		};
	}
	
	ccEmailWinClass.prototype._getAllEntries = function(offset)
	{
		if (this.searching && !offset)
		{
			return false;
		}
/*
		this.searching = true;
		this.clearEntries();
		this._search_set();
		this.search.DOMinput.value = '*';
		this.search.go();
		this.search.DOMinput.value = '';
*/
		this.searching = true;
		
		var _this = this;
		var str_data;
		var sdata;
		
		sdata = new Array();
		sdata['fields'] = new Array();
		sdata['fields']['names_ordered'] = true;
		sdata['fields']['connections'] = true;
		sdata['maxlength'] = -1;
	
		if (offset)
		{
			sdata['offset'] = offset;
		}
		else
		{
			this.clearEntries();
			sdata['new'] = true;
		}
		
		str_data = 'data='+serialize(sdata);
		
		var handler = function(responseText)
		{
			Element('cc_debug').innerHTML = responseText;
			var data = unserialize(responseText);

			if (!data || typeof(data) != 'object')
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				_this.searching = false;
				return false;
			}
			
			if (data['status'] != 'ok')
			{
				_this.search.DOMresult.innerHTML = data['msg'];
				setTimeout(function(){_this.search.DOMresult.innerHTML = '';}, 1000);
				_this.searching = false;
				return false;
			}

			//_this._updateEntries(data['data']);
			eval(data['data']);

			if (!data['final'])
			{
				_this._getAllEntries(data['offset']);
			}
			else
			{
				_this.searching = false;
			}
		}

		setTimeout(function(){_this.Connector.newRequest('email_search', CC_url+'get_all_entries', 'POST', handler, str_data);}, 50);
	}

	ccEmailWinClass.prototype._getEntriesHandler = function()
	{
		var _this = this;

		var f = function (responseText)
		{
			Element('cc_debug').innerHTML = responseText;
			var data = unserialize(responseText);
			
			if (!data)
			{
				//showMessage(Element('cc_msg_err_invalid_catalog').value);
				//showMessage('Error getting user Info');
				_this.searching = false;
				return false;
			}
	
			_this.clearEntries();
	
			if (data['status'] == 'empty')
			{
				//showMessage(data['msg']);
				_this.searching = false;
				return false;
			}
	
			if (data['status'] != 'ok')
			{
				//showMessage(data['msg']);
				_this.searching = false;
				return false;
			}
	
			//showMessage(data['msg']);
	
			_this._updateEntries(data['data']);

			_this.searching = false;
		};
		
		return f;
	}

	ccEmailWinClass.prototype._updateEntries = function (data)
	{
		var name = '';
		var emails = new Array();
		for (var i in data)
		{
			emails_count = 0;
			for (var j in data[i])
			{
				if (j == 'names_ordered')
				{
					name = '"'+data[i][j]+'"';
				}
				else if (j == 'connections')
				{
					for (var k in data[i][j])
					{
						if (data[i][j][k]['id_type'] == Element('cc_email_id_type').value)
						{
							emails[emails.length] = ' <'+data[i][j][k]['connection_value']+'>';
						}
					}
				}
			}

			if (name != '' && emails.length)
			{
				for (var j in emails)
				{
					var scroll = this.entries.scrollTop;
					this.entries.options[this.entries.options.length] = new Option(name+emails[j], i+'_'+j);
					this.entries.scrollTop = scroll;
				}
			}
			
			name = '';
			emails = new Array();
		}
	}

	/****************************************************************************\
	 *                        Auxiliar Functions                                *
	\****************************************************************************/	
