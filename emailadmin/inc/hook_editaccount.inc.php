<?php
{ 
	// that's what we have
	// $GLOBALS['hook_values']['account_lid']
	// print $GLOBALS['hook_values']['account_id'];
	// $GLOBALS['hook_values']['new_passwd']
	// $GLOBALS['hook_values']['account_firstname']
	// $GLOBALS['hook_values']['account_lastname']
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
	$config = CreateObject('phpgwapi.config','qmailldap');
	$config->read_repository();
	$qmailldapConfig = $config->config_data;

	unset($config);
	
	$userName	= $GLOBALS['hook_values']['account_lid'];
	// we don't know the password, when only edit the account
	//$userPassword	= $GLOBALS['hook_values']['new_passwd'];
	
        // login using the admin account, to create the account
	if($mbox = imap_open ("{127.0.0.1:143}", $qmailldapConfig['imapAdminUser'], $qmailldapConfig['imapAdminPassword']))
	{
		$accountName = $GLOBALS['hook_values']['account_lid'];
		#if(!imap_set_quota($mbox, "user.kalowsky", 3000)) 
		#{
		#	print "Error in setting quota\n";
		#	return;
		#}
		// create the inbox
		$mailBoxName = "user.".$GLOBALS['hook_values']['account_lid'];
		$folder = imap_list($mbox,"{127.0.0.1:143}",$mailBoxName);
		if(!is_array($folder))
		{
			if(@imap_createmailbox($mbox,imap_utf7_encode("{127.0.0.1:143}$mailBoxName"))) 
			{
				if(@imap_setacl($mbox, $mailBoxName, $accountName, "lrswipcd"))
				{
				}
			}
		}
		// create the trash folder
		$mailBoxName = "user.".$GLOBALS['hook_values']['account_lid'].".Trash";
		$folder = imap_list($mbox,"{127.0.0.1:143}",$mailBoxName);
		if(!is_array($folder))
		{
			if(@imap_createmailbox($mbox,imap_utf7_encode("{127.0.0.1:143}$mailBoxName"))) 
			{
				if(@imap_setacl($mbox, $mailBoxName, $accountName, "lrswipcd"))
				{
				}
			}
		}
		// create the Sent folder
		$mailBoxName = "user.".$GLOBALS['hook_values']['account_lid'].".Sent";
		$folder = imap_list($mbox,"{127.0.0.1:143}",$mailBoxName);
		if(!is_array($folder))
		{
			if(@imap_createmailbox($mbox,imap_utf7_encode("{127.0.0.1:143}$mailBoxName"))) 
			{
				if(@imap_setacl($mbox, $mailBoxName, $accountName, "lrswipcd"))
				{
				}
			}
		}


		imap_close($mbox);
	}        
}
?>
