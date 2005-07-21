function onNodeSelect(_nodeID)
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Loading '+_nodeID+' ...</span>';
	document.getElementById('divMessageList').innerHTML = '';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.updateMessageView",_nodeID);
}

function quickSearch(_searchString)
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Searching for '+document.getElementById('quickSearch').value+' ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	document.getElementById('quickSearch').select();

	selectBox = document.getElementById('filter');
	selectBox.options[1].selected = true; 

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.quickSearch',_searchString);
}

function extendedSearch(_selectBox)
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Applying filter '+_selectBox.options[_selectBox.selectedIndex].text+' ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	document.getElementById('quickSearch').value = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.extendedSearch',_selectBox.options[_selectBox.selectedIndex].value);
}

function skipForward()
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Skipping forward ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.skipForward');
}

function skipPrevious()
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Skipping previous ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.skipPrevious');
}

function jumpEnd()
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Jumping to end ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.jumpEnd');
}

function jumpStart()
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Jumping to start ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.jumpStart');
}
