function __dlg_onclose() {
};

function debug()
{
	_console = open();
	_console.document.open("text/html");
	_console.document.writeln("<html><body><pre>");

	
	_console.document.writeln("#form elements = " + opener.document.frm.length);
	for(var i = 0; i < opener.document.frm.length; i++)
	{
		var e = opener.document.frm.elements[i];
		_console.document.writeln(e.name + " (" + e.type + ") "+ " = " + e.value);
	}
	
	
	_console.document.writeln("</body></html></pre>");
	_console.document.close();		
}

function __dlg_init() 
{
	//debug();

	if (!document.all) {
		// init dialogArguments, as IE gets it
//		window.dialogArguments = opener.Dialog._arguments;
		window.sizeToContent();
		window.sizeToContent();	
		// for reasons beyond understanding,
					// only if we call it twice we get the
					// correct size.
		window.addEventListener("unload", __dlg_onclose, true);
		/*
		// center on parent
		var px1 = opener.screenX;
		var px2 = opener.screenX + opener.outerWidth;
		var py1 = opener.screenY;
		var py2 = opener.screenY + opener.outerHeight;
		var x = (px2 - px1 - window.outerWidth) / 2;
		var y = (py2 - py1 - window.outerHeight) / 2; */

		//centre on screen instead

		var x = (screen.width - window.outerWidth) / 2;
		var y = (screen.height - window.outerHeight) / 2;

		window.moveTo(x, y);
		var body = document.body;
		window.innerHeight = body.offsetHeight;
		window.innerWidth = body.offsetWidth;
	} else {
		//var body = document.body;
		window.resizeBy(500, 350)
		//window.dialogHeight = body.offsetHeight + 50 + "px";
		//window.dialogWidth = body.offsetWidth + "px";
	}
};

// closes the dialog and passes the return info to the parent window.
function __dlg_close(fileurl, filetype) 
{
	if (fileurl != null)
	{
		opener.onSave(fileurl, filetype);
		//	debug();
	}
	self.close();
}