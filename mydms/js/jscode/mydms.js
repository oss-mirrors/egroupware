function changeSorting(_sort)
{
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Change sorting ...</span>';
	document.getElementById('divMessageList').innerHTML = '';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.changeSorting",_sort);
}

function onNodeSelect(_nodeID)
{
	window.location.href = nodeSelectURL+'&folderid='+_nodeID;
}

function selectFolder(_folderID, _formName)
{
	openDlg = egw_openWindowCentered(folderChooserURL+"&form="+_formName+"&mode=3&exlcude=-1&folderid="+_folderID,'openDlg',300,450);
}