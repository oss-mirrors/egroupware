var attcount=1;
var att; //pointer to parent of tr's
var selectprefix; //clone of select field for prefix
var typeprefix; //clone of type field for prefixa

var attcount2=1;
var att2;
var fcselectprefix;
var fctypeprefix;

function removeUpload(number)
{
	if (att == null)
	{
		att = document.getElementById("attach").parentNode;
	}
	if (selectprefix == null)
	{
		selectprefix = document.form1["prefix0"].cloneNode(true);
		typeprefix = document.form1["type0"].cloneNode(true);
	}

	var div = document.form1["file"+number].parentNode.parentNode;
	div.parentNode.removeChild(div);

}

function removeFCUpload(number)
{
	if (att2 == null)
	{
		att2 = document.getElementById("attachFC").parentNode;
	}
	if (fcselectprefix == null)
	{
		fcselectprefix = document.form1["fcprefix0"].cloneNode(true);
		fctypeprefix = document.form1["fctype0"].cloneNode(true);
	}

	var div = document.form1["fcfile"+number].parentNode.parentNode.parentNode;
	div.parentNode.removeChild(div);

}

function addNewUpload(strremove)
{
	if (att == null)
	{
		att = document.getElementById("attach").parentNode;
	}
	if (selectprefix == null)
	{
		selectprefix = document.form1["prefix0"].cloneNode(true);
		typeprefix = document.form1["type0"].cloneNode(true);
	}
 
	var tr  = document.createElement("tr");
	var td1 = document.createElement("td");
	var td2 = document.createElement("td");
	var td3 = document.createElement("td");
	var td4 = document.createElement("td");

	var locSelectfield = selectprefix.cloneNode(true);
	var locTypefield = typeprefix.cloneNode(true);

	locSelectfield.name = 'prefix' + attcount;
	locTypefield.name = 'type' + attcount;
	

	td1.className = 'td_left';
	td2.style.textAlign = 'center';
	td3.style.textAlign = 'center';
	td4.style.textAlign = 'center';
	tr.className = (att.childNodes.length%2) ? 'row_on' : 'row_off';

	tr.appendChild(td1);
	tr.appendChild(td2);
	tr.appendChild(td3);
	tr.appendChild(td4);

	att.appendChild(tr);

	td1.innerHTML = '<input name="file'+ attcount +'" type="file" style="width:150px;"/>';
	td2.appendChild(locSelectfield);
	td3.appendChild(locTypefield);
	td4.innerHTML = '<input type="button" onclick="removeUpload('+ (attcount++) +')" value="'+strremove+'">';

}

function addNewFCUpload(strremove,str_add_from_filescenter)
{
	if (att2 == null)
	{
		att2 = document.getElementById("attachFC").parentNode;
	}
	if (fcselectprefix == null)
	{
		fcselectprefix = document.form1["fcprefix0"].cloneNode(true);
		fctypeprefix = document.form1["fctype0"].cloneNode(true);
	}
 
	var tr  = document.createElement("tr");
	var td1 = document.createElement("td");
	var td2 = document.createElement("td");
	var td3 = document.createElement("td");
	var td4 = document.createElement("td");

	var locSelectfield = fcselectprefix.cloneNode(true);
	var locTypefield = fctypeprefix.cloneNode(true);

	locSelectfield.name = 'fcprefix' + attcount2;
	locTypefield.name = 'fctype' + attcount2;
	

	td1.className = 'td_left';
	td2.style.textAlign = 'center';
	td3.style.textAlign = 'center';
	td4.style.textAlign = 'center';
	tr.className = (att2.childNodes.length%2) ? 'row_on' : 'row_off';

	tr.appendChild(td1);
	tr.appendChild(td2);
	tr.appendChild(td3);
	tr.appendChild(td4);

	att2.appendChild(tr);

	td1.innerHTML = '<nobr><input id="fcfile'+ attcount2 +'" name="fcfile'+ attcount2 +'" type="text" style="width: 150px;"/><input type="button" value="'+str_add_from_filescenter+'" onClick="fromFilescenter(\'fcfile'+ attcount2 +'\')"></nobr>';
	td2.appendChild(locSelectfield);
	td3.appendChild(locTypefield);
	td4.innerHTML = '<input type="button" onclick="removeFCUpload('+ (attcount2++) +')" value="'+strremove+'"><br>';

}

function fromFilescenter(ret_name)
{
	url_to_open = GLOBALS['serverRoot']+'index.php?menuaction=filescenter.ui_fm2.index&clean=1&ret_name='+ret_name;
	
	var myWindow = window.open(url_to_open,'f_browsefc',"width=730,height=450,scrollbars=yes,resizable=yes,status=yes");
	myWindow.focus();
}

//var tab = new Tabs(4,'activetab','inactivetab','tab','tabcontent','','','tabpage');

function initAll()
{
//	tab.init();
}
