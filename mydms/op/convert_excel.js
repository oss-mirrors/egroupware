//var logFile = fs.CreateTextFile("convertlog.txt", true);

var source = WScript.Arguments(0);
var target = WScript.Arguments(1);

var ExcelApp;
ExcelApp = new ActiveXObject("Excel.Application");
var Newdoc;
Newdoc = ExcelApp.Workbooks.Open(source);
Newdoc.SaveAs(target, 44); // xlHTML = 44
ExcelApp.Quit();



//logFile.Close();