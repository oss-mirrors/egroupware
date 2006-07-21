function changeSorting(_sort) {
	resetMessageSelect();

	document.getElementById('messageCounter').innerHTML = '<span style="font-weight: bold;">Change sorting ...</span>';
	document.getElementById('divMessageList').innerHTML = '';
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.changeSorting",_sort);
}

function onNodeSelect(_nodeID) {
	window.location.href = nodeSelectURL+'&folderid='+_nodeID;
}

function onNodeSelectRight(_nodeID) {
	var nodeObject = false;
	var domObject;
	var top=0;
	var left=0;

	if(nodeObject = tree._globalIdStorageFind(_nodeID)) {
		domObject = nodeObject.htmlNode.childNodes[0].childNodes[0].childNodes[3].childNodes[0];
		top = getPosTop(domObject) + domObject.offsetHeight;
		left = getPosLeft(domObject) + 10;

		resultBox = document.getElementById('rightClickMenu');
		resultBox.style.top=top + 'px';
		resultBox.style.left=left + 'px';
		resultBox.style.display='block';
	}
}

function selectFolder(_folderID, _formName) {
	openDlg = egw_openWindowCentered(folderChooserURL+"&form="+_formName+"&mode=3&exlcude=-1&folderid="+_folderID,'openDlg',300,450);
}

function getPosLeft(_node) {
	var left=0;
	
	if(_node.offsetParent) {
		while (_node.offsetParent)
		{
			left += _node.offsetLeft;
			_node = _node.offsetParent;
		}
	} else if (_node.x) {
		left += _node.x;
	}
	
	return left;
}

function getPosTop(_node) {
	var top=0;
	
	if(_node.offsetParent) {
		while (_node.offsetParent) {
			top += _node.offsetTop;
			if(_node.parentNode.scrollTop) {
				top -= _node.parentNode.scrollTop
			}
			_node = _node.offsetParent;
		}
	} else if (_node.y) {
		left += _node.y;
	}
	
	return top;
}

