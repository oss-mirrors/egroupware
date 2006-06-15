function changeSorting(_sort)
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Change sorting ...</span>';
	document.getElementById('divMessageList').innerHTML = '';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.changeSorting",_sort);
}

function compressFolder()
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">' + msg_compressingFolder + ' </span>...';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.compressFolder");
}

function deleteMessages(_messageList)
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Deleting messages ...</span>';
	document.getElementById('divMessageList').innerHTML = '';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.deleteMessages",_messageList);
}

function displayMessage(_url,_windowName) 
{
	egw_openWindowCentered(_url, _windowName, 800, egw_getWindowOuterHeight());
}

function emptyTrash()
{
	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">' + msg_emptyTrashFolder + ' </span>...';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.emptyTrash");
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

function selectAll(inputBox, _refreshTimeOut) {
	maxMessages = 0;

	for (var i = 0; i < document.getElementsByTagName('input').length; i++) {
		if(document.getElementsByTagName('input')[i].name == 'msg[]') {
			//alert(document.getElementsByTagName('input')[i].name);
			document.getElementsByTagName('input')[i].checked = inputBox.checked;
			maxMessages++;
		}
	}

	folderFunctions = document.getElementById('folderFunction');

	if(inputBox.checked) {
		checkedCounter = maxMessages;
		while (folderFunctions.hasChildNodes()) {
		    folderFunctions.removeChild(folderFunctions.lastChild);
		}
		var textNode = document.createTextNode(lang_select_target_folder);
		folderFunctions.appendChild(textNode);
		document.getElementsByName("folderAction")[0].value = "moveMessage";
		fm_startTimerMessageListUpdate(1800000);
	} else {
		checkedCounter = 0;
		while (folderFunctions.hasChildNodes()) {
		    folderFunctions.removeChild(folderFunctions.lastChild);
		}
		var textNode = document.createTextNode('');
		folderFunctions.appendChild(textNode);
		document.getElementsByName("folderAction")[0].value = "changeFolder";
		fm_startTimerMessageListUpdate(_refreshTimeOut);
	}
}

function toggleFolderRadio(inputBox, _refreshTimeOut) {

	folderFunctions = document.getElementById("folderFunction");
	checkedCounter += (inputBox.checked) ? 1 : -1;
	if (checkedCounter > 0) {
		while (folderFunctions.hasChildNodes()) {
		    folderFunctions.removeChild(folderFunctions.lastChild);
		}
		var textNode = document.createTextNode('{lang_move_message}');
		//folderFunctions.appendChild(textNode);
		document.getElementById("folderFunction").innerHTML=lang_select_target_folder;
		document.getElementsByName("folderAction")[0].value = "moveMessage";
		fm_startTimerMessageListUpdate(1800000);
	} else {
		document.getElementById('messageCheckBox').checked = false;
		while (folderFunctions.hasChildNodes()) {
		    folderFunctions.removeChild(folderFunctions.lastChild);
		}
		//var textNode = document.createTextNode('{lang_change_folder}');
		//folderFunctions.appendChild(textNode);
		document.getElementsByName("folderAction")[0].value = "changeFolder";
		fm_startTimerMessageListUpdate(_refreshTimeOut);
	}
}

function extendedSearch(_selectBox) {
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Applying filter '+_selectBox.options[_selectBox.selectedIndex].text+' ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	document.getElementById('quickSearch').value = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.extendedSearch',_selectBox.options[_selectBox.selectedIndex].value);
}

function flagMessages(_flag, _messageList)
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Updating message status ...</span>';
	document.getElementById('divMessageList').innerHTML = '';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.flagMessages",_flag,_messageList);
	
	fm_startTimerMessageListUpdate(refreshTimeOut);
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

function skipPrevious() {
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Skipping previous ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.skipPrevious');
}

function jumpEnd() {
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Jumping to end ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.jumpEnd');
}

function jumpStart() {
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Jumping to start ...</span>';
	document.getElementById('divMessageList').innerHTML = '';

	xajax_doXMLHTTP('felamimail.ajaxfelamimail.jumpStart');
}

var searchesPending=0;

function refresh() {
	searchesPending++;
	document.title=searchesPending;

	resetMessageSelect();
	xajax_doXMLHTTP('felamimail.ajaxfelamimail.refreshMessageList');
}     

function refreshFolderStatus() {
	xajax_doXMLHTTP('felamimail.ajaxfelamimail.refreshFolderList');
}

function openComposeWindow(_url) {
	egw_openWindowCentered(_url,'test',700,750);
}

// timer functions
function fm_startTimerFolderStatusUpdate(_refreshTimeOut) {
	if(fm_timerFolderStatus) {
		window.clearTimeout(fm_timerFolderStatus);
	}
	if(_refreshTimeOut > 5000) {
		fm_timerFolderStatus = window.setInterval("refreshFolderStatus()", _refreshTimeOut);
	}
}

function fm_startTimerMessageListUpdate(_refreshTimeOut) {
	if(aktiv) {
		window.clearTimeout(aktiv);
	}
	if(_refreshTimeOut > 5000) {
		aktiv = window.setInterval("refresh()", _refreshTimeOut);
	}
}

function fm_readMessage(_url, _windowName, _node) {
	egw_openWindowCentered(_url, _windowName, 700, egw_getWindowOuterHeight());
	trElement = _node.parentNode.parentNode.parentNode;
	trElement.style.fontWeight='normal';

	aElements = trElement.getElementsByTagName("a");
	aElements[0].style.fontWeight='normal';
	aElements[1].style.fontWeight='normal';
}
