var tab = new Tabs(6,'activetab','inactivetab','tab','tabcontent','','','tabpage');

function initTabs()
{
	tab.init();
}

function toggleJSCal(_selectBox)
{
	if(_selectBox.options[_selectBox.selectedIndex].value == 1)
	{
		document.getElementById("jscalspan").className = 'active';
	}
	else
	{
		document.getElementById("jscalspan").className = 'inactive';
	}
}

function toggleLock(_checkBox)
{
	if(_checkBox.checked == true)
	{
		document.getElementById("currentLockStatus").className = 'inactive';

		document.getElementById("unlockFile").className = 'active';
		document.getElementById("lockFile").className = 'inactive';
		document.getElementById("lockStatus").value = 'locked';
	}
	else
	{
		document.getElementById("currentLockStatus").className = 'inactive';

		document.getElementById("unlockFile").className = 'inactive';
		document.getElementById("lockFile").className = 'active';
		document.getElementById("lockStatus").value = 'unlocked';
	}
}

function toggleJSCalUpdate(_selectBox)
{
	if(_selectBox.options[_selectBox.selectedIndex].value == 1)
	{
		document.getElementById("jscalspan_update").className = 'active';
	}
	else
	{
		document.getElementById("jscalspan_update").className = 'inactive';
	}
}

