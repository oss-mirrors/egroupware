<!-- BEGIN iframe -->
<script>
document.body.style.overflow='hidden';
//document.getElementById('divAppbox').style.marginLeft='100px';
</script>
<style>
body
{
/*height:100%*/
}

#subNorm
{
	background-image:url({imgDir}goto.png); 
	background-repeat:no-repeat; 
	background-position:center;
	padding-left: 14px;
}
#subGoogle
{
	background-image:url({imgDir}goto.png); 
	background-repeat:no-repeat; 
	background-position:center; 
	padding-left: 14px;
}
#frameBrowse
{
	border: none; 
	position: absolute; 
	left: 0; 
	right: 0; 
	bottom: 0;
	top: 75px; 
	margin:0px;
/*	_height:100%;
*/	
	/*_top:110px;*/
/*	_top:0px;
	_width:100%;
	_margin-top: 120px;
	*/
	/*_: 120px;*/
   background-color: #FFF;
}
#menudiv
{
	background-color:#eeeeee; 
width:100%;
padding: 0px 5px 0px 5px; 
height: 25px;
top:40px;
position:static;
}
.form
{
	display: block; 
	clear: none;
}
#formNorm
{
	float:left;
}
#formGoogle
{
	float:right;
}
</style>

<div id ="menudiv">
<form action="javascript: submitform();" class="form" id="formNorm">
url:<input type="text" name="inurl" id="inurl" value="http://www.egroupware.org" size="60" >
<input id="subNorm" type="submit" value="">
</form>

<form action="javascript: submitGoogle();" class="form" id="formGoogle">
Google:<input type="text" name="google_url" id="google_url" size="10">
<input id = "subGoogle" type="submit" value="">
</form>

</div>
<iframe src="http://www.egroupware.org" id="frameBrowse" ></iframe>

<script>
var detect = navigator.userAgent.toLowerCase();
place = detect.indexOf("msie") + 1;

//if(place && document.all) 
//{
//}

var historyAr = new Array(1);
var iCurrent = 0;
historyAr[0] = "{homepage}";
setUrl(historyAr[0]);
if(place && document.all) 
{
	iframe = document.getElementById('frameBrowse');
	height_old = iframe.clientHeight;
	//alert(height_old);
//	iframe.style.height = height_old - 120 + "px";
	//document.onmousedown = function(e) i
}

document.onresize = function()
{
//	if(place && document.all) 
//	{
		alert("test");
//	}
}

function submitGoogle()
{
	url="http://www.google.nl/search?q=";
	url = url + document.getElementById("google_url").value;
	setUrl(url);
	editHistory(url);
}

function submitform()
{
	var src = document.getElementById("inurl").value;
	if (src.substring(0,7) != "http://")
	{
		setUrl("http://" + src);
		editHistory("http://" + src);
	}
	else
	{	
		setUrl(src);
		editHistory(src);
	}
}

function editHistory(url)
{
	if(iCurrent < (historyAr.length-1))
	{
		historyAr[iCurrent] = url;
		iCurrent = iCurrent + 1;
	}
	else
	{
		historyAr.push(url);
		iCurrent = historyAr.length - 1;
	}
}
function back()
{
	//	alert(iCurrent);
	//	alert(historyAr.length-1);
	if(iCurrent > 0)
	{
		iCurrent = iCurrent - 1;
		setUrl(historyAr[iCurrent]);
	}
}
function forward()
{
	//	alert(iCurrent);
	//	alert(historyAr.length-1);
	if(iCurrent < (historyAr.length-1))
	{
		iCurrent = iCurrent + 1;
		setUrl(historyAr[iCurrent]);
	}
}
function reload()
{
	setUrl(document.getElementById("frameBrowse").src);
}
function home()
{
	setUrl(historyAr[0]);
	editHistory(historyAr[0]);
}
function setUrl(url)
{
	document.getElementById("frameBrowse").src = url;
	document.getElementById("inurl").value = url;
}
</script>

<!-- END iframe -->
