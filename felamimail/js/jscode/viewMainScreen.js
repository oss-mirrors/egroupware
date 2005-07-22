function deleteMessages(_messageList)
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Deleting messages ...</span>';
	document.getElementById('divMessageList').innerHTML = '';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.deleteMessages",_messageList);
}

function onNodeSelect(_nodeID)
{

	if(document.getElementsByName("folderAction")[0].value == "moveMessage")
	{
		resetMessageSelect();
		formData = xajax.getFormValues('formMessageList');
		document.getElementById('messageCounter').innerHTML = movingMessages+' <span style="font-weight: bold;">'+_nodeID+' </span>...';
		document.getElementById('divMessageList').innerHTML = '';
		xajax_doXMLHTTP("felamimail.ajaxfelamimail.moveMessages", _nodeID, formData);
	}
	else
	{
		resetMessageSelect();
		document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Loading '+_nodeID+' ...</span>';
		document.getElementById('divMessageList').innerHTML = '';
		xajax_doXMLHTTP("felamimail.ajaxfelamimail.updateMessageView",_nodeID);
	}
}

function quickSearch(_searchString)
{
	resetMessageSelect();

 	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Searching for '+document.getElementById('quickSearch').value+' ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	document.getElementById('quickSearch').select();

	selectBox = document.getElementById('filter');
	selectBox.options[1].selected = true; 

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.quickSearch',_searchString);
}

function extendedSearch(_selectBox)
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Applying filter '+_selectBox.options[_selectBox.selectedIndex].text+' ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	document.getElementById('quickSearch').value = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.extendedSearch',_selectBox.options[_selectBox.selectedIndex].value);
}

function resetMessageSelect()
{
	document.getElementById('messageCheckBox').checked = false;
	checkedCounter = 0;
	folderFunctions = document.getElementById('folderFunction');
	
	while (folderFunctions.hasChildNodes())
		folderFunctions.removeChild(folderFunctions.lastChild);
	var textNode = document.createTextNode('');
	folderFunctions.appendChild(textNode);
	document.getElementsByName("folderAction")[0].value = "changeFolder";
}

function skipForward()
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Skipping forward ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.skipForward');
}

function skipPrevious()
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Skipping previous ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.skipPrevious');
}

function jumpEnd()
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Jumping to end ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.jumpEnd');
}

function jumpStart()
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Jumping to start ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.jumpStart');
}
