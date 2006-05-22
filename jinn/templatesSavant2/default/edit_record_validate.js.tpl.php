var valid = true;
/*
_console = window.open("","console", "width=600,height=600,resizable");
_console.document.open("text/plain");
_console.document.writeln("checking mandatory fields:<br>");
*/
for(var i = 0; i < document.frm.length; i++)
{
   var element = document.frm.elements[i];
   //_console.document.writeln(element.name + " > " + element.value + "<br>");
   if(element.mandatory)
   {
	  //_console.document.writeln("mandatory field. checking value: <br>");
	  //_console.document.writeln("field type: " + element.type + "<br>");
	  if(element.value == '' && element.type != "option")
	  {
		 //_console.document.writeln("error... element is empty!<br>");
		 valid=false;
		 element.style.backgroundColor="#FFAAAA";
	  }
	  else
	  {
		 element.style.backgroundColor="";
	  }
   }
}

//_console.document.close();

if(!valid)
{
   alert("<?= lang('please fill in all mandatory fields')?>");
   return false;
}

