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
	$profileID = $config->config_data['profileID'];

	// create the imap/pop3 account
	$boemailadmin = CreateObject('emailadmin.bo');
	$imapClass = $boemailadmin->getIMAPClass($profileID);
	$imapClass->deleteAccount($GLOBALS['hook_values']['account_lid']);
        
}
?>
