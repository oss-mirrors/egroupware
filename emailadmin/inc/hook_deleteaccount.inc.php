<?php
{ 
	// that's what we have
	// $GLOBALS['hook_values']['account_lid']
	// print $GLOBALS['hook_values']['account_id'];
/*
	Set ACLs on a mailbox. The ACL may be one of the special strings none, 
	read (lrs), post (lrsp), append (lrsip), write (lrswipcd), or all (lrswipcda), 
	or any combinations of the ACL codes:

	l	Lookup (visible to LIST/LSUB/UNSEEN)
	r	Read (SELECT, CHECK, FETCH, PARTIAL, SEARCH, COPY source)
	s	Seen (STORE \SEEN)
	w	Write flags other than \SEEN and \DELETED
	i	Insert (APPEND, COPY destination)
	p	Post (send mail to mailbox)
	c	Create (subfolders)
	d	Delete (STORE \DELETED, EXPUNGE)
	a	Administer (SETACL)

*/	
	// get the config from felamimail
	$config = CreateObject('phpgwapi.config','felamimail');
	$config->read_repository();
	$felamimailConfig = $config->config_data;

	$config = CreateObject('phpgwapi.config','qmailldap');
	$config->read_repository();
	$qmailldapConfig = $config->config_data;

	unset($config);
	
	$userName	= $GLOBALS['hook_values']['account_lid'];
	$userPassword	= $GLOBALS['hook_values']['new_passwd'];
	
	// create a qmailldap object and set the emailaddress in ldap
	#$boQmailLDAP = CreateObject('qmailldap.boqmailldap');
        #$data["mailLocalAddress"]	= $GLOBALS['hook_values']['account_lid']."@".$felamimailConfig['mailSuffix'];
        #$boQmailLDAP->saveUserData($GLOBALS['hook_values']['account_id'], $data, 'save');
        
	if($mbox = imap_open ("{127.0.0.1:143}", $qmailldapConfig['imapAdminUser'], $qmailldapConfig['imapAdminPassword']))
	{
		$accountName = $GLOBALS['hook_values']['account_lid'];
		// delete the mailbox
		$mailBoxName = "user.".$GLOBALS['hook_values']['account_lid'];
		if(imap_setacl($mbox, $mailBoxName, $qmailldapConfig['imapAdminUser'], "lrswipcda"))
		{
		}
		if(imap_deletemailbox($mbox,imap_utf7_encode("{127.0.0.1}$mailBoxName"))) 
		{
		}
		imap_close($mbox);
	}
        
}
?>
