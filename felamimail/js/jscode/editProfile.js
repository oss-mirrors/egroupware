var tab;
egw.LAB.wait(function() {
	tab = new Tabs(2,'activetab','inactivetab','tab','tabcontent','','','tabpage');
	jQuery(document).ready(function(){
		tab.init();
	});
});

function initAll(_editMode)
{
//	tab.init();
	
	switch(_editMode)
	{
		case 'vacation':
			tab.display(2);
			break;
	}
}
