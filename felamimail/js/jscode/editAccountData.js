function initEditAccountData()
{
	onchange_active(document.getElementById('active'));
}

function onchange_active(_checkbox) {
	identityInputs = document.getElementById('identity').getElementsByTagName('input');
	incomingInputs = document.getElementById('incoming_server').getElementsByTagName('input');
	outgoingInputs = document.getElementById('outgoing_server').getElementsByTagName('input');

	if(_checkbox.checked) {
		for(i=0; i<identityInputs.length; i++) {
			identityInputs[i].disabled = false;
		}
		for(i=0; i<incomingInputs.length; i++) {
			incomingInputs[i].disabled = false;
		}
		for(i=0; i<outgoingInputs.length; i++) {
			outgoingInputs[i].disabled = false;
		}
	} else {
		for(i=0; i<identityInputs.length; i++) {
			identityInputs[i].disabled = true;
		}		
		for(i=0; i<incomingInputs.length; i++) {
			incomingInputs[i].disabled = true;
		}
		for(i=0; i<outgoingInputs.length; i++) {
			outgoingInputs[i].disabled = true;
		}
	}
	onchange_og_smtpauth(document.getElementById('og[smtpauth]'));
	onchange_ic_encryption(document.getElementById('ic[encryption]'));
}

function onchange_og_smtpauth(_checkbox) {
	if(_checkbox.checked)
	{
		document.getElementById('og[username]').disabled = false;
		document.getElementById('og[password]').disabled = false;
	}
	else
	{
		document.getElementById('og[username]').disabled = true;
		document.getElementById('og[password]').disabled = true;
	}
}

function onchange_ic_encryption(_checkbox) {

	if(_checkbox.checked)
	{
		if(document.getElementById('ic[port]').value == '143' || 
			document.getElementById('ic[port]').value == '')
		{
			document.getElementById('ic[port]').value = '993';
		}
		document.getElementById('ic[validatecert]').disabled = false;
	}
	else
	{
		if(document.getElementById('ic[port]').value == '993' || 
			document.getElementById('ic[port]').value == '')
		{
			document.getElementById('ic[port]').value = '143';
		}
		document.getElementById('ic[validatecert]').disabled = true;
	}
}
