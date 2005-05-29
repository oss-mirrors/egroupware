<?php
  /**************************************************************************\
  * eGroupWare                                                               *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_baseline = array(
		'phpgw_felamimail_cache' => array(
			'fd' => array(
				'fmail_accountid' => array('type' => 'int','precision' => '4','nullable' => False),
				'fmail_hostname' => array('type' => 'varchar','precision' => '60','nullable' => False),
				'fmail_accountname' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'fmail_foldername' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'fmail_uid' => array('type' => 'int','precision' => '4','nullable' => False),
				'fmail_subject' => array('type' => 'text'),
				'fmail_striped_subject' => array('type' => 'text'),
				'fmail_sender_name' => array('type' => 'varchar','precision' => '120'),
				'fmail_sender_address' => array('type' => 'varchar','precision' => '120'),
				'fmail_to_name' => array('type' => 'varchar','precision' => '120'),
				'fmail_to_address' => array('type' => 'varchar','precision' => '120'),
				'fmail_date' => array('type' => 'int','precision' => '8'),
				'fmail_size' => array('type' => 'int','precision' => '4'),
				'fmail_attachments' => array('type' => 'varchar','precision' => '120')
			),
			'pk' => array('fmail_accountid','fmail_hostname','fmail_accountname','fmail_foldername','fmail_uid'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_felamimail_folderstatus' => array(
			'fd' => array(
				'fmail_accountid' => array('type' => 'int','precision' => '4','nullable' => False),
				'fmail_hostname' => array('type' => 'varchar','precision' => '60','nullable' => False),
				'fmail_accountname' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'fmail_foldername' => array('type' => 'varchar','precision' => '200','nullable' => False),
				'fmail_messages' => array('type' => 'int','precision' => '4'),
				'fmail_recent' => array('type' => 'int','precision' => '4'),
				'fmail_unseen' => array('type' => 'int','precision' => '4'),
				'fmail_uidnext' => array('type' => 'int','precision' => '4'),
				'fmail_uidvalidity' => array('type' => 'int','precision' => '4')
			),
			'pk' => array('fmail_accountid','fmail_hostname','fmail_accountname','fmail_foldername'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_felamimail_displayfilter' => array(
			'fd' => array(
				'fmail_filter_accountid' => array('type' => 'int','precision' => '4','nullable' => False),
				'fmail_filter_data' => array('type' => 'text')
			),
			'pk' => array('fmail_filter_accountid'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
