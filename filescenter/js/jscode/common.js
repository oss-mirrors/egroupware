function popup(url,width,height)
{
	var centered;
	x = (screen.availWidth - width) / 2;
	y = (screen.availHeight - height) / 2;
	centered =',width=' + width + ',height=' + height + ',left=' + x + ',top=' + y + ',scrollbars=yes,resizable=yes,status=yes';
	var popup = window.open(url, '_blank', centered);
    	if (!popup.opener) popup.opener = self;
	popup.focus();
}

function confirm_action(url, message)
{
	if (confirm(unescape(message)))
	{
		window.location=url
	}
}

function get_object(name)
{
	if (document.getElementById)
	{
		return document.getElementById(name);
 	}
 	else if (document.all)
	{
  		return document.all[name];
 	}
 	else if (document.layers)
	{
  		return document.layers[name];
	}
	return false;
}

function check_checkbox(id)
{
	if(check_box = get_object(id))
	{
		if (!check_box.disabled)
		{
			check_box.checked = !check_box.checked;
			if (check_box.onclick)
			{
				check_box.onclick();
			}
		}
	}
}

function select_radio(id)
{
	if(radio_but = get_object(id))
	{
		radio_but.checked = true;
		if (radio_but.onclick)
		{
			radio_but.onclick();
		}
	}
}


function getSelected(opt) 
{
	var selected = new Array();
	var index = 0;
	for (var i=0; i<opt.length;i++) 
	{
		if ((opt[i].selected) || (opt[i].checked)) 
		{
			index = selected.length;
			selected[index] = new Object;
			selected[index].value = opt[i].value;
			selected[index].index = i;
		}
	}
	return selected;
}
