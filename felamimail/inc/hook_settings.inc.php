<?php
	/**************************************************************************\
	* eGroupWare - Preferences                                                 *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	$this->bofelamimail =& CreateObject('felamimail.bofelamimail',$GLOBALS['egw']->translation->charset());
	$this->bofelamimail->openConnection('',OP_HALFOPEN);
	$folderObjects = $this->bofelamimail->getFolderObjects();
	foreach($folderObjects as $folderName => $folderInfo)
	{
		#_debug_array($folderData);
		#$folderParts = explode($folderInfo->delimiter,$folderName);
		#if(count($folderParts) > 1)
		#{
		#}
		$folderList[$folderName] = $folderName;
	}

	$this->bofelamimail->closeConnection();

	$config =& CreateObject('phpgwapi.config','felamimail');
	$config->read_repository();
	$felamimailConfig = $config->config_data;
	#_debug_array($felamimailConfig);
	unset($config);

	#$boemailadmin =& CreateObject('emailadmin.bo');
	#$methodData = array($felamimailConfig['profileID']);
	#_debug_array($methodData);
	$felamimailConfig = ExecMethod('emailadmin.bo.getProfile',$felamimailConfig['profileID']);

	$refreshTime = array(
		'0' => lang('disabled'),
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10',
		'15' => '15',
		'20' => '20',
		'30' => '30'
	);

	$sortOrder = array(
		'0' => lang('date(newest first)'),
		'1' => lang('date(oldest first)'),
		'3' => lang('from(A->Z)'),
		'2' => lang('from(Z->A)'),
		'5' => lang('subject(A->Z)'),
		'4' => lang('subject(Z->A)'),
		'7' => lang('size(0->...)'),
		'6' => lang('size(...->0)')
	);

	$selectOptions = array(
		'0' => lang('no'),
		'1' => lang('yes'),
		'2' => lang('yes') . ' - ' . lang('small view')
	);

	$newWindowOptions = array(
		'0' => lang('no'),
		'1' => lang('only one window'),
		'2' => lang('allways a new window'),
	);

	$deleteOptions = array(
		'move_to_trash'   => lang('move to trash'),
		'mark_as_deleted' => lang('mark as deleted'),
		'remove_immediately' => lang('remove immediately')
	);

	$htmlOptions = array(
		'never_display'   => lang('never display html emails'),
		'only_if_no_text' => lang('display only when no plain text is available'),
		'always_display'  => lang('always show html emails')
	);

	$trashOptions = array_merge(
		array(
			'none' => lang("Don't use Trash")
		),
		$folderList
	);

	$sentOptions = array_merge(
		array(
			'none' => lang("Don't use Sent")
		),
		$folderList
	);

	/* Settings array for this app */
	$GLOBALS['settings'] = array(
		'refreshTime' => array(
			'type'   => 'select',
			'label'  => 'Refresh time in minutes',
			'name'   => 'refreshTime',
			'values' => $refreshTime,
			'xmlrpc' => True,
			'admin'  => False
		),
		'email_sig' => array(
			'type'   => 'notify',
			'label'  => 'email signature',
			'name'   => 'email_sig',
			'rows'   => 3,
			'cols'   => 50,
			'xmlrpc' => True,
			'admin'  => False,
			'help'   => ' ',	// this is to get the substitution help-texts
		),
		'sortOrder' => array(
			'type'   => 'select',
			'label'  => 'Default sorting order',
			'name'   => 'sortOrder',
			'values' => $sortOrder,
			'xmlrpc' => True,
			'admin'  => False
		),
		'mainscreen_showmail' => array(
			'type'   => 'select',
			'label'  => 'show new messages on main screen',
			'name'   => 'mainscreen_showmail',
			'values' => $selectOptions,
			'xmlrpc' => True,
			'admin'  => False
		),
		'message_newwindow' => array(
			'type'   => 'select',
			'label'  => 'display message in new window',
			'name'   => 'message_newwindow',
			'values' => $newWindowOptions,
			'xmlrpc' => True,
			'admin'  => False
		),
		'deleteOptions' => array(
			'type'   => 'select',
			'label'  => 'when deleting messages',
			'name'   => 'deleteOptions',
			'values' => $deleteOptions,
			'xmlrpc' => True,
			'admin'  => False
		),
		'htmlOptions' => array(
			'type'   => 'select',
			'label'  => 'display of html emails',
			'name'   => 'htmlOptions',
			'values' => $htmlOptions,
			'xmlrpc' => True,
			'admin'  => False
		),
		'trashFolder' => array(
			'type'   => 'select',
			'label'  => 'trash folder',
			'name'   => 'trashFolder',
			'values' => $trashOptions,
			'xmlrpc' => True,
			'admin'  => False
		),
		'sentFolder' => array(
			'type'   => 'select',
			'label'  => 'sent folder',
			'name'   => 'sentFolder',
			'values' => $sentOptions,
			'xmlrpc' => True,
			'admin'  => False
		)
	);

	if($felamimailConfig['userDefinedAccounts'] == 'yes')
	{
		$selectOptions = array(
			'no'  => lang('no'),
			'yes' => lang('yes')
		);
		$GLOBALS['settings']['use_custom_settings'] = array(
			'type'   => 'select',
			'label'  => 'use custom settings',
			'name'   => 'use_custom_settings',
			'values' => $selectOptions,
			'xmlrpc' => True,
			'admin'  => False
		);

		$GLOBALS['settings']['username'] = array(
			'type'   => 'input',
			'label'  => 'username',
			'name'   => 'username',
			'size'   => 40,
			'xmlrpc' => True,
			'admin'  => False
		);
		$GLOBALS['settings']['key'] = array(
			'type'   => 'password',
			'label'  => 'password',
			'name'   => 'key',
			'size'   => 40,
			'xmlrpc' => True,
			'admin'  => False
		);
		$GLOBALS['settings']['emailAddress'] = array(
			'type'   => 'input',
			'label'  => 'EMail Address',
			'name'   => 'emailAddress',
			'size'   => 40,
			'xmlrpc' => True,
			'admin'  => False
		);
		$GLOBALS['settings']['imapServerAddress'] = array(
			'type'   => 'input',
			'label'  => 'IMAP Server Address',
			'name'   => 'imapServerAddress',
			'size'   => 40,
			'xmlrpc' => True,
			'admin'  => False
		);

		$selectOptions = array(
			'no'  => lang('IMAP'),
			'yes' => lang('IMAPS Encryption only'),
			'imaps-encr-auth' => lang('IMAPS Authentication')
		);
		$GLOBALS['settings']['imapServerMode'] = array(
			'type'   => 'select',
			'label'  => 'IMAP Server type',
			'name'   => 'imapServerMode',
			'values' => $selectOptions,
			'xmlrpc' => True,
			'admin'  => False
		);
	}
?>
