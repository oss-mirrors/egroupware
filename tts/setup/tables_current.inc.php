<?php
  /**************************************************************************\
  * phpGroupWare - Setup                                                     *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_baseline = array(
		'phpgw_tts_tickets' => array(
			'fd' => array(
				'ticket_id'             => array('type' => 'auto', 'nullable' => False),
				'ticket_group'          => array('type' => 'varchar', 'precision' => 40, 'nullable' => True),
				'ticket_priority'       => array('type' => 'int', 'precision' => 2, 'nullable' => False),
				'ticket_owner'          => array('type' => 'varchar', 'precision' => 10, 'nullable' => True),
				'ticket_assignedto'     => array('type' => 'varchar', 'precision' => 10, 'nullable' => True),
				'ticket_subject'        => array('type' => 'varchar', 'precision' => 255, 'nullable' => True),
				'ticket_category'       => array('type' => 'varchar', 'precision' => 25, 'nullable' => True),
				'ticket_billable_hours' => array('type' => 'decimal', 'precision' => 8, 'scale' => 2, 'nullable' => False),
				'ticket_billable_rate'  => array('type' => 'decimal', 'precision' => 8, 'scale' => 2, 'nullable' => False),
				'ticket_status'         => array('type' => 'char','precision' => 1, 'nullable' => False),
				'ticket_details'        => array('type' => 'text','nullable' => False)
			),
			'pk' => array('ticket_id'),
			'ix' => array(),
			'fk' => array(),
			'uc' => array()
		),
		'phpgw_tts_views' => array(
			'fd' => array(
				'view_id'             => array('type' => 'int', 'precision' => 4, 'nullable' => False),
				'view_account_id'     => array('type' => 'varchar', 'precision' => 40, 'nullable' => True),
				'view_time'           => array('type' => 'int', 'precision' => 4, 'nullable' => False)
			),
			'pk' => array(),
			'ix' => array(),
			'fk' => array(),
			'uc' => array()
		)
	);
