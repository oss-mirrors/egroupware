var attcount=1;
var att; //pointer to parent of tr's
var selectprefix; //clone of select field for prefix
var typeprefix; //clone of type field for prefix

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

	td1.innerHTML = '<input name="file'+ attcount +'" type=file size=50/>';
	td2.appendChild(locSelectfield);
	td3.appendChild(locTypefield);
	td4.innerHTML = '<span class="lk" onclick="removeUpload('+ (attcount++) +')"">'+strremove+'</span><br>';

}

var tab = new Tabs(4,'activetab','inactivetab','tab','tabcontent','','','tabpage');

function initAll()
{
	tab.init();
}
