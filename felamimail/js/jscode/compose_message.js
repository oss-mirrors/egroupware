var tab;
egw.LAB.wait(function() {
	tab = new Tabs(3,'activetab','inactivetab','tab','tabcontent','','','tabpage');
	jQuery(document).ready(function(){
		tab.init();
	});
});
// var smtp = new Tabs(2,'activetab','inactivetab','smtp','smtpcontent','smtpselector','','smtppage');
// var imap = new Tabs(3,'activetab','inactivetab','imap','imapcontent','imapselector','','imappage');

function initAll()
{
//	tab.init();
// 	smtp.init();
//	imap.init();
}
