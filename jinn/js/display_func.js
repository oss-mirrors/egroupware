/*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   phpGroupWare - http://www.phpgroupware.org
   
   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; either version 2 of the License, or (at your 
   option) any later version.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
*/


/*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   phpGroupWare - http://www.phpgroupware.org
   
   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; either version 2 of the License, or (at your 
   option) any later version.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
*/


function submitForm() {

     document.frm.submit();
}



function saveOptions(obj,hidden_fld) { //v1.0
//alert('hallo');
		var boxLength = document.frm[obj].length;
        var count = 0;
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
		document.frm[hidden_fld].value=strValues;
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

	if (document.frm[allObj].selectedIndex != -1) {
		// check if 5 places are selected

		//if (document.frm[obj].length == 5)
		//	alert ("U mag maximaal 5 objecten selecteren.");

		for (var i=0; i < document.frm[allObj].length; i++) {
			if (document.frm[allObj].options[i].selected == true && document.frm[allObj].options[i].text != '')
				//if (document.frm[obj].length <= 4)
					if ( !CheckForDoubles (objectSelectedItems, document.frm[allObj].options[i])) {
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

<!--
// --------------------------------------------------------------------------------
// PhpConcept Script - Explorer
// --------------------------------------------------------------------------------
// License GNU/GPL - Vincent Blavet - July 2001
// http://www.phpconcept.net
// --------------------------------------------------------------------------------
// Overview :
//   PcsExplorer is a Javascript/PHP script that compose a file or directory
//   selection window.
//
// Description :
//   PcsExplorer is composed of a Javascript file 'pcsexplorer.js', a PHP file
//   'pcsexplorer.php3' and a folder 'images'.
//   The PHP calling script must include the javascript file, and call PcsExplorer
//   like this (sample) :
//     <script language="javascript" src="pcsexplorer.js"></script>
//     <FORM  name="formulaire" method="POST">
//     File : <INPUT TYPE="TEXT" size=50 name="file"><br>
//     <INPUT TYPE="button" name="brows_button" value="Browse" onClick='PcjsOpenExplorer("path/pcsexplorer.php3", "forms.formulaire.file.value", "type=file", "calling_dir=<? echo dirname($PATH_INFO); ?>", "start_dir=../..")'>
//     </FORM>
//   The arguments took by PcjsOpenExplorer() are described in the function header
//   bellow.
//   The arguments took by the PcsExplorer PHP script are described in
//   pcsexplorer.php3 file.
// --------------------------------------------------------------------------------

// ----- Global variable
var v_win=0;

// --------------------------------------------------------------------------------
// Function : PcjsOpenExplorer(p_url, p_target, ... p_properties ...)
// Description :
// Parameters :
//   p_url : The URL where sit the pcsexplorer.php3 PHP script. Should be
//           an absolute value ("http://www.mysite.com/pcsexplorer.php3") or
//           a relative value from the calling PHP script ("../pcsexplorer.php3")
//   p_target : The javascript value target. For example for a form with name
//              'my_form', with a TEXT INPUT, with name 'my_text', it should be
//              "forms.my_form.my_text.value". Any javascript object contained
//              in object "document" can be used.
//   p_properties : A variable list of optional properties. Each element of this
//                  must be a string, with the name of the property, equal the
//                  value ("property_name=my_value").
//                  Warning : No space " " is allowed defore and after the "=".
//                  The following properties are defined :
//                    type=file|dir : PcsExplorer explore files & dir,
//                                    or only dir.
//                    filter=my_filter : my_filter is a regular expression
//                                       (future feature).
//                    position=absolute|relative : absolute means absolute from
//                                                 site home dir, relative means
//                                                 relative to the calling script
//                                                 position.
//                    calling_dir=<dirname> : the absolute path from site root
//                                            of the calling PHP script.
//                    start_dir=<dirname> : the relative path from the calling PHP
//                                          script where to start the exploration.
// --------------------------------------------------------------------------------
function PcjsOpenExplorer(p_url, p_target /*, p_properties, p_properties, ... */)
{
	// ----- Check the number of arguments
	if (arguments.length < 2)
	{
		alert("Invalid number of arguments for PcjsOpenExplorer()");
		return false;
	}

	// ----- Compose the basic called URL
	var v_url = p_url+"?a_target="+escape(p_target);

	// ----- Look for optional properties
	for (i=2; i<arguments.length; i++)
	{
		// ----- Extract the property name and property value
		var v_item = arguments[i].split("=", 2);

		// ----- Complete the URL
		v_url = v_url+"&a_"+escape(v_item[0])+"="+escape(v_item[1]);
	}

	// ----- Set & calculate window size
	var v_width=340;
	var v_height=400;
	var v_left = (screen.width-v_width)/2;
	var v_top = (screen.height-v_height)/2;

	// ----- Set window properties
	var v_settings = 'width='+v_width+',height='+v_height+',top='+v_top+',left='+v_left+',scrollbars=no,location=no,directories=no,status=no,menubar=no,toolbar=no,resizable=yes';

	// ----- Open window
	v_win = window.open(v_url,"PhpConceptExplorer",v_settings);

	// ----- Give focus to window
	v_win.focus();
}
// --------------------------------------------------------------------------------

// -->

