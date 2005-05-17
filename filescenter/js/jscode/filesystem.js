
//var normal_item_color;


function item_click(check_box)
{
	var item = get_object(check_box.value);
	if (item == null)
	{
		return false;
	}

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
	for (var i=0;i<Element('filesystem').elements.length;i++)
	{
		if(Element('filesystem').elements[i].type == 'checkbox' && Element('filesystem').elements[i].name != 'dummy')
		{
			Element('filesystem').elements[i].checked = !(Element('filesystem').elements[i].checked);
			item_click(Element('filesystem').elements[i]);
		}
	}
}

function cut_items(no_select)
{
	var count = 0;
	for (var i=0;i<Element('filesystem').elements.length;i++)
	{
		if(Element('filesystem').elements[i].type == 'checkbox' && Element('filesystem').elements[i].name != 'dummy')
		{
			if (Element('filesystem').elements[i].checked == true)
			{
				count++;
			}
		}
	}

	if (count > 0)
	{
		Element('filesystem').ftask.value = 'cut';
		Element('filesystem').action = Element('filesystem').action + "?menuaction=filescenter.ui_fm2.index";
		Element('filesystem').submit();
	}else
	{
		alert(no_select);
	}
}

function copy_items(no_select)
{
	var count = 0;
	for (var i=0;i<Element('filesystem').elements.length;i++)
	{
		if(Element('filesystem').elements[i].type == 'checkbox' && Element('filesystem').elements[i].name != 'dummy')
		{
			if (Element('filesystem').elements[i].checked == true)
			{
				count++;
			}
		}
	}

	if (count > 0)
	{
		Element('filesystem').ftask.value = 'copy';
		Element('filesystem').action = Element('filesystem').action + "?menuaction=filescenter.ui_fm2.index";
		Element('filesystem').submit();
	}else
	{
		alert(no_select);
	}
}

function paste_items()
{
	Element('filesystem').ftask.value = 'paste';
	Element('filesystem').action = Element('filesystem').action + "?menuaction=filescenter.ui_fm2.index";
	Element('filesystem').submit();
}

/*
function mail_files(no_select)
{
	var count = 0;
	for (var i=0;i<Element('filesystem').elements.length;i++)
	{
		if(Element('filesystem').elements[i].name == 'files[]')
		{
			if (Element('filesystem').elements[i].checked == true)
			{
				count++;
			}
		}
	}

	if(count > 0)
	{
		Element('filesystem').task.value = 'mail_files';
		Element('filesystem').submit();
	}else
	{
		alert(no_select);
	}
}
*/

function change_location(dropbox)
{
	Element('filesystem').share_path.value = dropbox.value;
	Element('filesystem').path.value = dropbox.value;
	Element('filesystem').submit();
}

function properties(no_multi_select)
{
	var count = 0;
	var path = new String;

	
	for (var i=0;i<Element('filesystem').elements.length;i++)
	{
		if(Element('filesystem').elements[i].type == 'checkbox' && Element('filesystem').elements[i].name != 'dummy')
		{
			if (Element('filesystem').elements[i].checked == true)
			{
				count++;
				path = Element('filesystem').elements[i].value;
			}
		}
	}
	switch (count)
	{
		case 0:
			
			Element('filesystem').task.value = "properties";
			Element('filesystem').action = Element('filesystem').action + "?menuaction=filescenter.ui_fm2.properties";
			Element('filesystem').submit();
		break;

		case 1:
			Element('filesystem').task.value = "properties";
			Element('filesystem').path.value = path;
			Element('filesystem').action = Element('filesystem').action + "?menuaction=filescenter.ui_fm2.properties";
			Element('filesystem').submit();

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

	Element('filesystem').formvar.value = fn_escaped;
	Element('filesystem').ftask.value = "new_folder";
	Element('filesystem').action = Element('filesystem').action + "?menuaction=filescenter.ui_fm2.index";
	Element('filesystem').submit();
  }
}
