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
				't_id'                => array('type' => 'auto', 'nullable' => False),
				't_category'          => array('type' => 'varchar', 'precision' => 40, 'nullable' => True),
				't_detail'            => array('type' => 'text', 'nullable' => True),
				't_priority'          => array('type' => 'int', 'precision' => 2, 'nullable' => False),
				't_user'              => array('type' => 'varchar', 'precision' => 10, 'nullable' => True),
				't_assignedto'        => array('type' => 'varchar', 'precision' => 10, 'nullable' => True),
				't_timestamp_opened'  => array('type' => 'int', 'precision' => 4, 'nullable' => False),
				't_timestamp_closed'  => array('type' => 'int', 'precision' => 4, 'nullable' => False),
				't_subject'           => array('type' => 'varchar', 'precision' => 255, 'nullable' => True),
				't_department'        => array('type' => 'varchar', 'precision' => 25, 'nullable' => True),
				't_watchers'          => array('type' => 'text', 'nullable' => True)
			),
			'pk' => array('t_id'),
			'ix' => array(),
			'fk' => array(),
			'uc' => array()
		),
		'phpgw_tts_categories' => array(
			'fd' => array(
				'c_id'         => array('type' => 'auto', 'nullable' => False),
				'c_department' => array('type' => 'varchar', 'precision' => 25, 'nullable' => True),
				'c_name'       => array('type' => 'varchar', 'precision' => 40, 'nullable' => True)
			),
			'pk' => array('c_id'),
			'ix' => array(),
			'fk' => array(),
			'uc' => array()
		),
		'phpgw_tts_departments' => array(
			'fd' => array(
				'd_name' => array('type' => 'varchar', 'precision' => 25, 'nullable' => True)
			),
			'pk' => array(),
			'ix' => array(),
			'fk' => array(),
			'uc' => array()
		)
	);
