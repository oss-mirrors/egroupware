function updateACLView()
{
	xajax_doXMLHTTP('felamimail.ajaxfelamimail.updateACLView');
}

function resetACLAddView()
{
	xajax_doXMLHTTP('felamimail.ajaxfelamimail.addACL', document.getElementById('accountName').value, xajax.getFormValues('formAddACL') );
	document.getElementById('accountName').value = '';
	document.getElementById('acl_l').checked = false;
	document.getElementById('acl_r').checked = false;
	document.getElementById('acl_s').checked = false;
	document.getElementById('acl_w').checked = false;
	document.getElementById('acl_i').checked = false;
	document.getElementById('acl_p').checked = false;
	document.getElementById('acl_c').checked = false;
	document.getElementById('acl_d').checked = false;
	document.getElementById('acl_a').checked = false;
	opener.updateACLView();
}

function onNodeSelect(_nodeID)
{
	xajax_doXMLHTTP("felamimail.ajaxfelamimail.getFolderInfo",_nodeID);
}