
//var normal_item_color;


function item_click(check_box)
{
	var item = get_object(check_box.value);
/*	var par = item;

	while (!normal_item_color)
	{
		if (par.backgroundColor)
		{
			normal_item_color = par.backgroundColor;
		}
		else
		{
			par = par.parentNode;
			alert(par);
		}
	}*/

	if (check_box.checked)
	{
		item.style.backgroundColor = '#FFFFCC';
	}else
	{
		item.style.backgroundColor = '#FFFFFF';
	}
}

function invert_selection()
{
	for (var i=0;i<document.forms[0].elements.length;i++)
	{
		if(document.forms[0].elements[i].type == 'checkbox' && document.forms[0].elements[i].name != 'dummy')
		{
			document.forms[0].elements[i].checked = !(document.forms[0].elements[i].checked);
			item_click(document.forms[0].elements[i]);
		}
	}
}

function cut_items(no_select)
{
	var count = 0;
	for (var i=0;i<document.forms[0].elements.length;i++)
	{
		if(document.forms[0].elements[i].type == 'checkbox' && document.forms[0].elements[i].name != 'dummy')
		{
			if (document.forms[0].elements[i].checked == true)
			{
				count++;
			}
		}
	}

	if (count > 0)
	{
		document.forms[0].ftask.value = 'cut';
		document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.index";
		document.forms[0].submit();
	}else
	{
		alert(no_select);
	}
}

function copy_items(no_select)
{
	var count = 0;
	for (var i=0;i<document.forms[0].elements.length;i++)
	{
		if(document.forms[0].elements[i].type == 'checkbox' && document.forms[0].elements[i].name != 'dummy')
		{
			if (document.forms[0].elements[i].checked == true)
			{
				count++;
			}
		}
	}

	if (count > 0)
	{
		document.forms[0].ftask.value = 'copy';
		document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.index";
		document.forms[0].submit();
	}else
	{
		alert(no_select);
	}
}

function paste_items()
{
	document.forms[0].ftask.value = 'paste';
	document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.index";
	document.forms[0].submit();
}

/*
function mail_files(no_select)
{
	var count = 0;
	for (var i=0;i<document.forms[0].elements.length;i++)
	{
		if(document.forms[0].elements[i].name == 'files[]')
		{
			if (document.forms[0].elements[i].checked == true)
			{
				count++;
			}
		}
	}

	if(count > 0)
	{
		document.forms[0].task.value = 'mail_files';
		document.forms[0].submit();
	}else
	{
		alert(no_select);
	}
}
*/

function change_location(dropbox)
{
	document.forms[0].share_path.value = dropbox.value;
	document.forms[0].path.value = dropbox.value;
	document.forms[0].submit();
}

function properties(no_multi_select)
{
	var count = 0;
	var path = new String;

	
	for (var i=0;i<document.forms[0].elements.length;i++)
	{
		if(document.forms[0].elements[i].type == 'checkbox' && document.forms[0].elements[i].name != 'dummy')
		{
			if (document.forms[0].elements[i].checked == true)
			{
				count++;
				path = document.forms[0].elements[i].value;
			}
		}
	}
	switch (count)
	{
		case 0:
			
			document.forms[0].task.value = "properties";
			document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.properties";
			document.forms[0].submit();
		break;

		case 1:
			document.forms[0].task.value = "properties";
			document.forms[0].path.value = path;
			document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.properties";
			document.forms[0].submit();

		break;

		default:
			alert(no_multi_select);
		break;
	}
}


function new_folder_click()
{
  var fn = window.prompt('New folder name','');

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

	document.forms[0].formvar.value = fn_escaped;
	document.forms[0].ftask.value = "new_folder";
	document.forms[0].action = document.forms[0].action + "?menuaction=filescenter.ui_fm2.index";
	document.forms[0].submit();
  }
}
