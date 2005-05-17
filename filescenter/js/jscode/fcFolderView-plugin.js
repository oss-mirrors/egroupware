  /***************************************************************************\
  * eGroupWare - Files Center                                                 *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  - Vinicius Cubas Brand <viniciuscb@users.sourceforge.net>                *
  *  sponsored by Think.e - http://www.think-e.com.br                         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

//THIS FILE IS NOT COMPLETE: SOME FUNCTIONS MUST BE PUT IN OBJECT FORMAT
//TODO in a very near future

//Class fcFolderViewPlugin: prototype
function fcFolderViewPlugin()
{
	this.postURL = GLOBALS['serverRoot']+'/xmlrpc.php';
    this.indexURL = GLOBALS['serverRoot']+'index.php';

	this.DOM = new Object();

	this.DOM.fcToolbar = Element('fcToolbar');
	this.DOM.fcContents = Element('fcContents');
	this.DOM.fcTfolders = Element('fcTfolders');
	this.DOM.fcTfiles = Element('fcTfiles');
	this.DOM.fcFolderInfo = Element('fcFolderInfo');
	this.DOM.fcDisplayLocation = Element('fcDisplayLocation');
	//this.DOM.form = Element('filesystem');

	this.checkboxes = new Array();

	this.columns = new Object();
	this.defaultPath = GLOBALS['fcFolderViewDefaultPath'];

}

var fc_tries=30;

//fcFolderViewPlugin.refresh: refreshes the Folder View Table

/**
 * Function: refresh
 *
 *		Refreshes the files center widget.
 *
 *		Note: use the pair {appname,appid} together or the path to specify
 *		a path for refreshing.
 *
 * Parameters:
 *
 *		path - the path (in eGW vfs) of the folder of the contents to be displayed
 *		window - if this is given, this will be closed when the refresh procedure is done
 *		appname - the application name in eGroupWare (in case the user wants to display
		        an application's dir and just have the application name and application id
 *		appid - the application id
 */
fcFolderViewPlugin.prototype.refresh = function (params)
{
	var _this = this;

	if (!params)
	{
		var params = new Object();
	}

	var _p_window = params['window'];
	
	if (_p_window != null) {
		_p_window.resizeTo(0,0); 
		_p_window.blur();

		delete params['window'];
	}

	if (!params['path'] && !params['appname'] && !params['appid'])
	{
		params['path'] = this.defaultPath;
	}

	if (GLOBALS['appname'])
	{
		params['appname'] = GLOBALS['appname'];
	}

	var xml_request_msg = js2xmlrpc('filescenter.ui_fm2.refresh', params);

	var handler = function(responseText)
	{
		var response = xmlrpc2js(responseText);
		_this._populate(response);

		if (_p_window != null) {
			_p_window.setTimeout(_p_window.close,10); 
		}
	}

	Connector.newRequest('FilesCenterWidget.refresh',this.postURL,'POST',handler,xml_request_msg);
}

fcFolderViewPlugin.prototype.new_folder = function ()
{
	var _this = this;
	var fn = window.prompt(GLOBALS['messages']['filescenter']['newFolder'],'');

	if(fn != 'null' && fn != '' && fn != null)
	{
		var fn_escaped = '';
		var fn_leng = fn.length;

		for(i=0;i<fn_leng;i++)
		{
			var fn_asc = fn.charCodeAt(i);
			if (fn_asc == 43) fn_escaped += '%2b';
			else if(fn_asc>128) fn_escaped += fn.charAt(i);
			else fn_escaped += escape(fn.charAt(i));
		}

		var data = { path: GLOBALS['fcFolderViewDefaultPath']+'/'+fn_escaped };

		var xml_request_msg = js2xmlrpc('filescenter.ui_fm2.new_folder',data);

		var handler = function(responseText)
		{
			var response = xmlrpc2js(responseText);

			if (response.status != 'ok')
			{
				alert(response.msg);
			}
			else
			{
				_this.refresh();
			}
		}

		Connector.newRequest('FilesCenterWidget.new_folder',this.postURL,'POST',handler,xml_request_msg);
	}
}

fcFolderViewPlugin.prototype.delete_items = function()
{
	var _this = this;
	var count = 0;
	var filename;
	var can_commit = false;

	var checked_elements = this._get_checked_elements();
	count = checked_elements.length;

	switch (count)
	{
		case 0:
			alert(GLOBALS['messages']['filescenter']['noItemsSelected']);
			break;

		case 1:
			filename = checked_elements[0].substring(checked_elements[0].lastIndexOf('/')+1, checked_elements[0].length);
			var rExp = /_filename_/;
			if (confirm(GLOBALS['messages']['filescenter']['deleteConfirmation'].replace(rExp,filename)))
			{
				can_commit = true;	
			}
			break;

		default:
			var rExp = /_count_/;
			if (confirm(GLOBALS['messages']['filescenter']['deleteItemsConfirmation'].replace(rExp,count)))
			{
				can_commit = true;
			}
			break;
	}

	if (can_commit)
	{
		data = { files : checked_elements };

		xml_request_msg = js2xmlrpc('filescenter.ui_fm2.delete',data)

		var handler = function(responseText)
		{
			var response = xmlrpc2js(responseText);

			if (response.msg != null)
			{
				alert(response.msg);
				_this.refresh();
			}
			else
			{
				_this.refresh();
			}
		}

		Connector.newRequest('FilesCenterWidget.delete',this.postURL,'POST',handler,xml_request_msg);
	}
}

fcFolderViewPlugin.prototype.properties = function()
{
    var checked_elms = this._get_checked_elements();
    var count = checked_elms.length;

	switch (count)
	{
		case 0:
            var propUrl = this.indexURL+"?menuaction=filescenter.ui_fm2.properties&path="+GLOBALS['fcFolderViewDefaultPath'];
            var myWindow = window.open(propUrl,"f_properties","width=730,height=450,scrollbars=yes,resizable=yes,status=yes");
		break;

		case 1:
            var propUrl = this.indexURL+"?menuaction=filescenter.ui_fm2.properties&path="+checked_elms[0];
            var myWindow= window.open(propUrl,"f_properties","width=730,height=450,scrollbars=yes,resizable=yes,status=yes");
		break;

		default:
			alert(GLOBALS['messages']['filescenter']['justOneItem']);
		break;
	}

}

fcFolderViewPlugin.prototype.open_upload_window = function()
{
	var url_upload = this.indexURL+'?menuaction=filescenter.ui_fm2.upload&path='+GLOBALS['fcFolderViewDefaultPath'];

	if (GLOBALS['options']['upload_other_opts'])
	{
		url_upload += '&' + GLOBALS['options']['upload_other_opts'];
	}
    var myWindow = window.open(url_upload,"f_upload","width=730,height=450,scrollbars=yes,resizable=yes,status=yes");
	myWindow.focus();
}

//FIXME must reformulate this.

/**
 * Method: select_files_for_upload
 *
 * Parameters:
 *
 *	mode - can be 'link' or 'path'
 *	param_name - name of the text field to fill in the opener window
 */
fcFolderViewPlugin.prototype.select_file_for_upload = function(mode,param_name)
{
    var checked_elms = this._get_checked_elements();
    var count = checked_elms.length;
	var returner;

	if (!param_name)
	{
		return false;
	}

	switch (count)
	{
		case 0:
			alert(GLOBALS['messages']['filescenter']['noItemsSelected']);
			break;
		break;

		case 1:
			if (mode=='link')
			{
				returner = this.indexURL+'?menuaction=filescenter.ui_fm2.view&path='+ checked_elms[0];
			}
			else
			{
				returner = checked_elms[0];
			}
			window.opener.document.getElementById(param_name).value = returner;
			window.close();
		break;

		default:
			alert(GLOBALS['messages']['filescenter']['justOneItem']);
		break;
	}
}


/**
 * Function: copy
 *
 *		Copies some files
 *
 * Parameters:
 *
 *		p_operation - (string) can be 'copy' or 'cut'. If not set, defaults to 'copy';
 */
fcFolderViewPlugin.prototype.copy = function (p_operation)
{
    var checked_elms = this._get_checked_elements();
    var count = checked_elms.length;

	if (!p_operation)
	{
		p_operation = 'copy';
	}

	if (count > 0)
	{
		data = { 
			files : checked_elms ,
			operation: p_operation
			};

		xml_request_msg = js2xmlrpc('filescenter.ui_fm2.copy',data)

		var handler = function(responseText)
		{
			var response = xmlrpc2js(responseText);

			if (response.msg)
			{
				alert(response.msg);
			}
		}

		Connector.newRequest('FilesCenterWidget.copy',this.postURL,'POST',handler,xml_request_msg);
	}
	else
	{
		alert(GLOBALS['messages']['filescenter']['noItemsSelected']);
	}
}

/**
 * Function: paste
 *
 *		Paste copied files
 */
fcFolderViewPlugin.prototype.paste = function ()
{
    var checked_elms = this._get_checked_elements();
    var count = checked_elms.length;
	var _this = this;

	data = { path: this.defaultPath };

	xml_request_msg = js2xmlrpc('filescenter.ui_fm2.paste',data)

	var handler = function(responseText)
	{
		var response = xmlrpc2js(responseText);
		
		if (response.status == 'fail')
		{
			alert(response.msg);
		}
		else if (response.status == 'ok')
		{
			if (response.msg)
			{
				alert(response.msg);
			}
			_this.refresh();
		}
	}

	Connector.newRequest('FilesCenterWidget.paste',this.postURL,'POST',handler,xml_request_msg);
}

/**
 * Function: setPath
 *
 *		Sets the path for the files center widget. This will not refresh the
 *		widget, the user must also execute a refresh() method.
 */
fcFolderViewPlugin.prototype.setPath = function(p_path)
{
	if (p_path)
	{
		this.defaultPath = p_path;
	}
}

/**
 * Method: compress
 *
 */
fcFolderViewPlugin.prototype.compress = function()
{
	var checked_elms = this._get_checked_elements();
	var count = checked_elms.length;
	var _this = this;
	var strFiles = '';
  
	switch (count)
	{
		case 0:
			alert(GLOBALS['messages']['filescenter']['noItemsSelected']);
			break;
		default:
			for (var k = 0; k < count; k++)
			{
				strFiles += '&files['+k+']='+checked_elms[k];
			}

			var myWindow = window.open(GLOBALS['urlCompress']+strFiles,"f_compress","width=515,height=450,scrollbars=yes,resizable=yes,status=yes");
			myWindow.focus();
	}
}


/**
 * Method: extract
 *
 *
 */
fcFolderViewPlugin.prototype.extract = function()
{
	var checked_elms = this._get_checked_elements();
	var count = checked_elms.length;
	var _this = this;

	switch (count)
	{
		case 0:
			alert(GLOBALS['messages']['filescenter']['noItemsSelected']);
			return;
		case 1:
			break;
		default:
			alert(GLOBALS['messages']['filescenter']['justOneItem']);
			return;
	}

	params = {archive: checked_elms[0],  path: this.defaultPath};

	var xml_request_msg = js2xmlrpc('filescenter.ui_fm2.extract', params);

	var handler = function(responseText)
	{
		var response = xmlrpc2js(responseText);

		if (response.status == 'fail')
		{
			if (response.msg)
			{
				alert(response.msg);
			}
		}
		else if (response.status == 'ok')
		{
			_this.refresh();
		}
	}

	Connector.newRequest('FilesCenterWidget.extract',this.postURL,'POST',handler,xml_request_msg);
}

/**
 * Method: invert_selection
 *
 *
 */
fcFolderViewPlugin.prototype.invert_selection = function()
{
	for (var i=0;i<this.checkboxes.length;i++)
	{
		if (this.checkboxes[i].checked)
		{
			this.checkboxes[i].checked = false;
		}
		else
		{
			this.checkboxes[i].checked = true;
		}
		//TODO change this to a method inside the current object
		item_click(this.checkboxes[i]);
	}
}

/**
 * Function: setApplicationPath
 *
 *		Sets the path for the files center widget, given the eGroupWare
 *		application name and the id in this application. This will not refresh
 *		the widget, the user must also execute a refresh() method.
 *
 *		WARNING: this method is in javascript and there is also a routine in
 *		php that does exactly the same job. This is put here just to shorten
 *		an RPC call everytime the user sets an application path;
 */
/*fcFolderViewPlugin.setApplicationPath = function(p_appname,p_appid)
{
	if (p_appname && p_appid)
	{
		this.defaultPath = this.appPath + '/' + p_appname + '/' + p_appid;
	}
}*/


//------- Private Methods -------------------

//fcFolderViewPlugin._populate: Populates the table with values
fcFolderViewPlugin.prototype._populate = function (values)
{
	this.checkboxes = new Array();

	//deletes old table values
	while (this.DOM.fcTfolders.hasChildNodes())
	{
		this.DOM.fcTfolders.removeChild(this.DOM.fcTfolders.firstChild);
	}
	
	while (this.DOM.fcTfiles.hasChildNodes())
	{
		this.DOM.fcTfiles.removeChild(this.DOM.fcTfiles.firstChild);
	}

	var options = new Array('files','folders');

	for (var k=0; k<options.length; k++)
	{
		var option = options[k];
		
		//iterate through rows
		for (var i = 0; i < values[option].length;i++)
		{
			var TblRow = document.createElement('tr');
			TblRow.id = values[option][i].filename;
			TblRow.className = 'Table1';
			TblRow.align = 'left';

			var TblData = document.createElement('td');
			var TblChkbox = document.createElement('input');
			TblChkbox.value = values[option][i].filename;
			TblChkbox.name = 'files[]';
			TblChkbox.type = 'checkbox';
			TblChkbox.onclick = function() { item_click(this); };

			this.checkboxes.push(TblChkbox);
			TblData.appendChild(TblChkbox);

			TblRow.appendChild(TblData);

			for (var field in values[option][i]['fields'])
			{
				var field_maxlength = values[option][i]['fields'][field]['maxlength'];
				var TblData = document.createElement('td');
				TblData.className = 'Table1';
				
				if (field_maxlength != null)
				{
					TblData.innerHTML = adjustString(values[option][i]['fields'][field]['content'],field_maxlength);
				}
				else
				{
					TblData.innerHTML = values[option][i]['fields'][field]['content'];
				}

				TblData.nowrap = 'nowrap';

				for (var prop in values[option][i]['fields'][field]['tdoptions'])
				{
					TblData[prop] = values[option][i]['fields'][field]['tdoptions'][prop];
				}

				TblRow.appendChild(TblData);
			}

			var containerName = 'fcT' + option;
			this.DOM[containerName].appendChild(TblRow);
		}
	}

	this.DOM.fcFolderInfo.innerHTML = values['dir_info'];
	this.DOM.fcDisplayLocation.innerHTML = values['display_location'];
}


//TODO: put this in object
fcFolderViewPlugin.prototype._get_checked_elements = function()
{
	var return_elms = new Array();

	for (var i=0;i<this.checkboxes.length;i++)
	{
		if (this.checkboxes[i].checked == true)
		{
			return_elms.push(this.checkboxes[i].value);
		}
	}
	return return_elms;
}



