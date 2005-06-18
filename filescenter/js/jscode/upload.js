
/**
 * Function: addNewUpload
 *
 *	Adds a new upload line	
 *
 *	Parameters:
 *
 *	enable_prefix - 1, to have prefix enabled. Defaults to disabled.
 *	enable_type - 1, to have type enabled. Defaults to disabled.
 *	type - 'normal' (default), 'from_fc'
 */
function addNewUpload(options)
{
	if (!options['type'])
	{
		options['type'] = 'normal';
	}

	if (options['type'] == 'normal')
	{
		var att = Element('attach');
	}
	else if (options['type'] == 'from_fc')
	{
		var att = Element('attachFC');
	}

	var attcount = att.childNodes.length;
	var fieldname;
	
	var tr  = document.createElement("tr");
	var td1 = document.createElement("td");
	var td4 = document.createElement("td");

	tr.appendChild(td1);

	if (options['enable_prefix'])
	{
		var selectprefix = Element("base_prefix").cloneNode(true);
		var locSelectfield = selectprefix.cloneNode(true);
		var td2 = document.createElement("td");
		
		if (options['type'] == 'normal')
		{
			fieldname = 'prefix';
		}
		else if (options['type'] == 'from_fc')
		{
			fieldname = 'fcprefix';
		}

		locSelectfield.name = fieldname + attcount;
		
		td2.style.textAlign = 'center';
		tr.appendChild(td2);
		td2.appendChild(locSelectfield);
	}

	if (options['enable_type'])
	{
		var typeprefix = Element("base_type").cloneNode(true);
		var locTypefield = typeprefix.cloneNode(true);
		var td3 = document.createElement("td");

		if (options['type'] == 'normal')
		{
			fieldname = 'type';
		}
		else if (options['type'] == 'from_fc')
		{
			fieldname = 'fctype';
		}

		locTypefield.name = fieldname + attcount;
		td3.style.textAlign = 'center';
		tr.appendChild(td3);
		td3.appendChild(locTypefield);
	}

	td1.className = 'td_left';
	td4.style.textAlign = 'center';
	tr.className = (attcount % 2) ? 'row_on' : 'row_off';

	tr.appendChild(td4);

	if (options['type'] == 'normal')
	{
		td1.innerHTML = '<input name="file'+ attcount +'" type="file" style="width:150px;"/>';
	}
	else if (options['type'] == 'from_fc')
	{
		td1.innerHTML = '<nobr><input id="fcfile'+ attcount +'" name="fcfile'+ attcount +'" type="text" style="width: 150px;"/><input type="button" value="'+GLOBALS['messages']['filescenter']['from_fc']+'" onClick="fromFilescenter(\'fcfile'+ attcount +'\')"></nobr>';
	}

	td4.innerHTML = '<input type="button" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);" value="'+GLOBALS['messages']['filescenter']['remove']+'">';

	att.appendChild(tr);
}

function fromFilescenter(ret_name)
{
	url_to_open = GLOBALS['serverRoot']+'index.php?menuaction=filescenter.ui_fm2.index&clean=1&ret_name='+ret_name;
	
	var myWindow = window.open(url_to_open,'f_browsefc',"width=730,height=450,scrollbars=yes,resizable=yes,status=yes");
	myWindow.focus();
}

//var tab = new Tabs(4,'activetab','inactivetab','tab','tabcontent','','','tabpage');

function initAll()
{
//	tab.init();
}
