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
	 * ContactCenter API - Search for Entries Window
	 */

	function ccSearchClass(params)
	{
		if (!params || typeof(params) != 'object')
		{
			return false;
		}

		/* Attributes */
		this.onSearchFinish = null;
		this.onClose = null;
		this.onOpen = null;

		this.DOMholder = params['holder'];
		this.DOMdiv = document.createElement('div');
		this.DOMfields = document.createElement('select');
		this.DOMinput = document.createElement('input');
		this.DOMbtn = document.createElement('input');
		this.DOMprogHold = document.createElement('div');
		this.DOMresult = document.createElement('div');

		this.Connector = params['Connector'];
		this.Connector.setProgressContent(1, params['conn_1_msg']);
		this.Connector.setProgressContent(2, params['conn_2_msg']);
		this.Connector.setProgressContent(3, params['conn_3_msg']);
		this.Connector.setProgressHolder(this.DOMprogHold);

		/* Initialization */
		var _this = this;
		var spacer = document.createTextNode(' ');

		this.DOMdiv.style.position = 'relative';
		this.DOMdiv.style.display = 'inline';
		this.DOMdiv.style.width    = params['total_width'] ? params['total_width'] : params['input_width'] ? parseInt(params['input_width'])+210 + 'px' : '300px';
		//this.DOMdiv.style.height   = '25px';

		this.DOMfields.style.width = '50px';
		this.DOMfields.style.display = 'none';
		this.DOMfields.style.position = 'absolute';
		this.DOMfields.style.visibility = 'hidden';
		//this.DOMfields.style.height = parseInt(this.DOMdiv.style.height)/2 + 'px';

		this.DOMinput.type = 'text';
		this.DOMinput.style.width = params['input_width'] ? params['input_width'] : '200px';
		this.DOMinput.onkeypress = function (e) { 
				if (is_ie)
				{
					if (window.event.keyCode == 13) _this.go();
				}
				else
				{
					if (e.which == 13) _this.go();
				}
			};
		//this.DOMinput.style.height = parseInt(this.DOMdiv.style.height)/2 + 'px';

		this.DOMbtn.type = 'button';
		//this.DOMbtn.style.height = parseInt(this.DOMdiv.style.height)/2 + 'px';
		this.DOMbtn.style.width = '60px';
		this.DOMbtn.value = params['button_text'];
		this.DOMbtn.onclick = function () {_this.go();};

		this.DOMprogHold.style.position = 'absolute';
		this.DOMprogHold.style.top = params['progress_top'] ? params['progress_top'] : '0px';
		this.DOMprogHold.style.left = params['progress_left'] ? params['progress_left'] : '0px';
		this.DOMprogHold.style.fontWeight = 'bold';
		this.DOMprogHold.style.width = params['progress_width'] ? params['progress_width'] : '200px';

		if (params['progress_color'])
			this.DOMprogHold.style.color = params['progress_color'];
		
		this.DOMresult.style.position = 'absolute';
		this.DOMresult.style.top = params['progress_top'] ? params['progress_top'] : '0px';
		this.DOMresult.style.left = params['progress_left'] ? params['progress_left'] : '0px';
		this.DOMresult.style.fontWeight = 'bold';
		this.DOMresult.style.width = params['progress_width'] ? params['progress_width'] : '200px';

		if (params['progress_color'])
			this.DOMresult.style.color = params['progress_color'];

		this.DOMholder.appendChild(this.DOMdiv);	
		this.DOMdiv.appendChild(this.DOMfields);
		this.DOMdiv.appendChild(this.DOMinput);
		this.DOMdiv.appendChild(spacer);
		this.DOMdiv.appendChild(this.DOMbtn);
		this.DOMdiv.appendChild(this.DOMprogHold);
		this.DOMdiv.appendChild(this.DOMresult);
	}
	
	ccSearchClass.prototype.go = function()
	{
		var data = new Array();
		
		this.DOMresult.innerHTML = '';

		//TODO: Make Generic!
		data['fields']           = new Array();
		data['fields']['id']     = 'contact.id_contact';
		data['fields']['search'] = 'contact.names_ordered';
		data['search_for']       = this.DOMinput.value;
		//data['recursive']        = this.recursive.checked ? true : false;
		
		var _this = this;

		var handler = function (responseText)
		{
			Element('cc_debug').innerHTML = responseText;
			var data = unserialize(responseText);
			
			if (!data || !data['status'])
			{
				showMessage(Element('cc_msg_err_contacting_server').value);
				return false;
			}

			if (data['status'] == 'empty')
			{
				//showMessage(data['msg']);
				_this.DOMresult.innerHTML = data['msg'];
				setTimeout(function(){_this.DOMresult.innerHTML = '';}, 1000);
				
				if (_this.onSearchFinish)
				{
					_this.onSearchFinish(null);
				}
				return false;
			}

			if (data['status'] != 'ok')
			{
				//showMessage(data['msg']);
				_this.DOMresult.innerHTML = data['msg'];
				setTimeout(function(){_this.DOMresult.innerHTML = '';}, 1000);
				return false;
			}

			//showMessage(data['msg']);

			if (_this.onSearchFinish)
			{
				_this.onSearchFinish(data['data']);
			}
		};

		this.Connector.newRequest('search', CC_url+'search&data='+serialize(data), 'GET', handler);
	}
