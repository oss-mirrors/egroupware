var tab = new Tabs(4,'activetab','inactivetab','tab','tabcontent','','','tabpage');

function initAll()
{
	tab.init();
}

function change_sharing()
{
	sharing_checkbox = document.getElementById('sharing_checkbox');
	
	ur_sel = document.getElementById('ur');
	uw_sel = document.getElementById('uw');
	gr_sel = document.getElementById('gr');
	gw_sel = document.getElementById('gw');


	if (sharing_checkbox.checked)
	{
		ur_sel.disabled = false;
		uw_sel.disabled = false;
		gr_sel.disabled = false;
		gw_sel.disabled = false;
	}
	else
	{
		ur_sel.disabled = true;
		uw_sel.disabled = true;
		gr_sel.disabled = true;
		gw_sel.disabled = true;
	}
}
