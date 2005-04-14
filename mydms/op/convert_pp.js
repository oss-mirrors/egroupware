//var logFile = fs.CreateTextFile("convertlog.txt", true);

var source = WScript.Arguments(0);
var target = WScript.Arguments(1);

var PPApp;
PPApp = new ActiveXObject("Powerpoint.Application");
var Newdoc;
PPApp.Visible = true;
Newdoc = PPApp.Presentations.Open(source);
Newdoc.SaveAs(target, 12);	// ppSaveAsHTML = 12
PPApp.Quit();

//logFile.Close();