/*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; Version 2 of the License

   JiNN is distributed in the hope that it will be useful, but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
 */

	/*
	$id$
	*/

  /* 
  FIXME remove unused functions 
  */





function submitForm() {

   document.frm.submit();
}


function doCheckAll(chbx)
{
   for (i=0; i<document.frm.elements.length; i++)
   {
	  if (document.frm.elements[i].type == "checkbox" && document.frm.elements[i].name != chbx.name)
	  {
		 if (document.frm.elements[i].checked)
		 {
			document.frm.elements[i].checked = false;
		 }
		 else
		 {
			document.frm.elements[i].checked = true;
		 }
	  }
   }
}

function countSelectedCheckbox()
{
   checkednum=0;
   for (i=0; i<document.frm.elements.length; i++)
   {
	  if (document.frm.elements[i].type == "checkbox")// && document.frm.elements[i].name != chbx.name)
	  {
		 if (document.frm.elements[i].checked)
		 {
			checkednum++;
		 }
	  }
   }
   return checkednum;
}
function returnSelectedCheckbox()
{
   checkednum=0;
   values = '';
   for (i=0; i<document.frm.elements.length; i++)
   {
	  if (document.frm.elements[i].type == "checkbox")// && document.frm.elements[i].name != chbx.name)
	  {
		 if (document.frm.elements[i].checked)
		 {
		 	if(checkednum == 0)
			{
				values = document.frm.elements[i].value;
			}
			else
			{
				values = values+','+document.frm.elements[i].value;
			}
			checkednum++;
		 }
	  }
   }
   return values;
}

function saveOptions(obj,hidden_fld) { //v1.0
   //alert('hallo');
   var boxLength = document.frm[obj].length;
   var count = 0;
   var strValues;

   if (boxLength != 0) {
	  for (i = 0; i < boxLength; i++) {
		 if (count == 0) {
			selectAll(document.frm[obj],true)
			   strValues = document.frm[obj].options[i].value;
		 }
		 else {
			selectAll(document.frm[obj],true)
			   strValues = strValues + "," + document.frm[obj].options[i].value;
		 }
		 count++;
	  }
   }

   if (strValues)  document.frm[hidden_fld].value=strValues;
}

function selectAll(cbList,bSelect) {
   for (var i=0; i<cbList.length; i++) 
	  cbList[i].selected = cbList[i].checked = bSelect
}

function reverseAll(cbList) {
   for (var i=0; i<cbList.length; i++) {
	  cbList[i].checked = !(cbList[i].checked) 
		 cbList[i].selected = !(cbList[i].selected)
   }
}

// navagation menu
function MM_jumpMenu(targ,selObj,restore){ //v3.0
   eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
   if (restore) selObj.selectedIndex=0;
}

// open new windows
function MM_openBrWindow(theURL,winName,features) { //v2.0
   pop1=window.open(theURL,winName,features);
}


// function to give back color to parentformfield
var kleurhex = '';
function restart() {

   document.data.kleurhex.value = kleurhex;
   //document.layers.box.BgColor.value = hex;

   if (mywindow != null) mywindow.close();
   //window.location.reload( false )
}


// function to open colorpickerwindow
function newWindow() {
   var mywindows='';
   mywindow=open('colorpicker.php','Colorpicker','resizable=no,width=412,height=336');
   mywindow.location.href = 'colorpicker.php';
   if (mywindow.opener == null) mywindow.opener = self;
}

// function that's being called by the onload of the pages
function DoOnLoad ()
{
   if ((typeof(document.frm) != 'undefined') && (typeof(document.frm.nosf) == 'undefined'))
   {
	  vulForm();
	  setLocatieSoort();
   }
}




// Function to copy the values in the listbox sfselallenormen to the hidden field selPlaces
function SetSelPlaces (obj)
{
   var strSelectedPlaces = "";
   if (typeof(document.frm[obj]) != "undefined") {
	  for (i=0; i < document.frm[obj].length; i++) {
		 if (document.frm[obj].options[i].value!='')
			strSelectedPlaces += document.frm[obj].options[i].value + ";" + document.frm[obj].options[i].text + "&";
	  }

	  document.frm.selPlaces.value = strSelectedPlaces;
   }
}

// function that puts the selected items in listbox allenormen in listbox selallenormen
function SelectPlace (obj,allObj)
{
   var objectSelectedItems = copyOptionsObject (document.frm[obj].options);

   if (document.frm[allObj].selectedIndex != -1) 
   {
	  // check if 5 places are selected

	  //if (document.frm[obj].length == 5)
	  //	alert ("U mag maximaal 5 objecten selecteren.");

		for (var i=0; i < document.frm[allObj].length; i++) 
		{
		 if (document.frm[allObj].options[i].selected == true && document.frm[allObj].options[i].text != '')
			//if (document.frm[obj].length <= 4)
			if ( !CheckForDoubles (objectSelectedItems, document.frm[allObj].options[i])) 
			{
			   newOption = new Option(document.frm[allObj].options[i].text, document.frm[allObj].options[i].value, false, false);
			   document.frm[obj].options[document.frm[obj].length] = newOption;
			}
		}
	}
}

// function to make a hard-copy of an javascript object
function copyOptionsObject (optionsObject)
{
   var copyObject = new Array()

	  for (i=0; i < optionsObject.length; i++)
		 copyObject[i] = new Option (optionsObject[i].text, optionsObject[i].value, false, false);
   return copyObject;
}





// function to check if the optionObject is already in the listbox
function CheckForDoubles (optionsObject, optionObject)
{
   var doubleFound = false;

   if (optionsObject.length)
	  for (var i=0; i < optionsObject.length; i++) {
		 if (optionsObject[i].text == optionObject.text && 
			   optionsObject[i].value == optionObject.value)
			doubleFound = true;
	  }

   return doubleFound;
}

// function to delete selected items in a function
// This function uses the recursive function deleteItemsInListbox
function DeSelectPlace (obj)
{
   if (document.frm[obj].selectedIndex != -1)
	  DeleteItemsInListbox(document.frm[obj],obj);
}

// recursive function to delete selected items in a listbox
function DeleteItemsInListbox (listbox,obj)
{
   if (typeof(document.frm[obj]) != "undefined") {
	  var index = listbox.length;

	  if (index != 0 && listbox.selectedIndex != -1) {
		 listbox.options[listbox.selectedIndex] = null;
		 DeleteItemsInListbox (listbox);
	  }
	  return true;
   }
}

// Function to set the second checkbox for smartfunda if apliable
function CheckForOther(checkbox) {
   if ( typeof(document.frm.sfgebrhulp[0]) != 'undefined' ) {
	  if (checkbox == document.frm.sfgebrhulp[0])
		 document.frm.sfgebrhulp[1].checked = checkbox.checked;
	  else if (checkbox == document.frm.sfgebrhulp[1])
		 document.frm.sfgebrhulp[0].checked = checkbox.checked;
   }
}

// Function to set the radiobutton for a provincie or streek if the other is selected
function setLocatieSoort () {
   if (typeof(document.frm.sfLocatieSoort) != "undefined" && typeof(document.frm.sfStreek) != "undefined") {
	  if (document.frm.sfLocatieSoort[0].checked)
		 document.frm.sfStreek.options[0].selected = true;
	  else
		 document.frm.sfProvincie.options[0].selected = true;
   }
}


/*
* @function jinnHideFields
* @abstract hides all given id's
 */
function jinnHideFields()
{
   param=jinnHideFields.arguments; 
   if (param.length)
   {
	  for (var i=0; i < param.length; i++) 
	  {
		 for (j=0; j<document.frm.elements.length; j++)
		 {
			//to make it easier for users ;)
			if(typeof document.frm.elements[j] != 'undefined')
			{
			   var name = ""+document.frm.elements[j].name;
			   //	  if(document.frm.elements[j].name.substring(6) == param[i])
			   if(name.substring(6) == param[i])
			   {
				  el = document.frm.elements[j];//document.frm.elements['MLTX00'+param[i]];
				  tr = el.parentNode.parentNode;
				  tr.style.display="none";
//				  el.name = "DEL"+el.name;
			   }
			}
		 }
	  }
   }
}

/*
 * @function jinnShowFields
 * @abstract unhides all given id's
 */
function jinnShowFields()
{
   param=jinnShowFields.arguments; 
   if (param.length)
   {
		 for (var i=0; i < param.length; i++) 
	  {
		 for (j=0; j < document.frm.elements.length; j++)
		 {
			if(typeof document.frm.elements[j] != 'undefined' )
			{
			   var name = ""+document.frm.elements[j].name;
			   //alert(name.substring(6)+' = '+param[i]);
			   if(name.substring(6) == param[i])
			   {
				  el = document.frm.elements[j];//document.frm.elements['MLTX00'+param[i]];
				  tr = el.parentNode.parentNode;
				  tr.style.display="";
				  if (el.name.substring(0,3) =="DEL")
				  {
					 el.name = el.name.substring(3);
				  }
			   }
			}
		 }
	  }
   }
}

/*
* @function jinnShowHideFields
* @abstract used by selectbox plugin to hide fields based on just selected value
* @example  jinnShowHideFields(this.name,'Show','two:lang_code,title','three:type')
 */
function jinnShowHideFields()
{
   param=jinnShowHideFields.arguments; 
   
   if (param.length)
   {
	  var selval=param[0].options[param[0].selectedIndex].value;
	  var field_arr2 = new Array();
	  for (var i=2; i < param.length; i++) 
	  {
		 var option_arr = param[i].split(':');

		 if(selval == option_arr[0])
		 {
			var field_arr = option_arr[1].split(',');
			for (var ii=0; ii < field_arr.length; ii++) 
			{
			   field_arr[ii]='\''+field_arr[ii]+'\'';
			}
			//alert('jinn'+param[1]+'Fields('+field_arr.join(',')+')');
			eval('jinn'+param[1]+'Fields('+field_arr.join(',')+')');
				  }
				  }
				  }
				  }


function jinnIgnoreSpaces(string) {
   var temp = "";
   string = '' + string;
   splitstring = string.split(" ");
   for(i = 0; i < splitstring.length; i++)
	  temp += splitstring[i];
   return temp;
}

























