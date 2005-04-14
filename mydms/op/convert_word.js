//var logFile = fs.CreateTextFile("convertlog.txt", true);

var source = WScript.Arguments(0);
var target = WScript.Arguments(1);

var WordApp;
WordApp = new ActiveXObject("Word.Application");
var Newdoc;
Newdoc = WordApp.Documents.Open(source);
Newdoc.SaveAs(target, 8);	// wdFormatHTML = 8
WordApp.Quit();



//logFile.Close();