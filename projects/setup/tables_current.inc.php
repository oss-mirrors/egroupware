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

  /**************************************************************************\
  * This file should be generated for you. It should never be edited by hand *
  \**************************************************************************/
  /* $Id$ */

	$phpgw_baseline = array(
		'phpgw_p_projects' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'parent' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'num' => array('type' => 'varchar','precision' => 20,'nullable' => False),
				'owner' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'access' => array('type' => 'varchar','precision' => 7,'nullable' => True),
				'category' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'entry_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'start_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'end_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'coordinator' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'customer' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'status' => array('type' => 'varchar','precision' => 9,'default' => 'active','nullable' => False),
				'descr' => array('type' => 'text','nullable' => True),
				'title' => array('type' => 'varchar','precision' => 255,'nullable' => False),
				'budget' => array('type' => 'decimal','precision' => 20,'scale' => 2,'default' => 0,'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','num'),
			'uc' => array('num')
		),
		'phpgw_p_activities' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'num' => array('type' => 'varchar','precision' => 20,'nullable' => False),
				'descr' => array('type' => 'varchar','precision' => 255,'nullable' => False),
				'remarkreq' => array('type' => 'char','precision' => 1,'default' => 'N','nullable' => False),
				'minperae' => array('type' => 'decimal','precision' => 4,'scale' => 0,'default' => 0,'nullable' => False),
				'billperae' => array('type' => 'decimal','precision' => 20,'scale' => 2,'default' => 0,'nullable' => False),
				'category' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','num'),
			'uc' => array()
		),
		'phpgw_p_projectactivities' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'project_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'activity_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'billable' => array('type' => 'char','precision' => 1,'default' => 'N','nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_p_hours' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'employee' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'project_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'activity_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'entry_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'start_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'end_date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'hours_descr' => array('type' => 'varchar','precision' => 255,'nullable' => False),
				'remark' => array('type' => 'text','nullable' => True),
				'minutes' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'minperae' => array('type' => 'decimal','precision' => 4,'scale' => 0,'default' => 0,'nullable' => False),
				'billperae' => array('type' => 'decimal','precision' => 20,'scale' => 2,'default' => 0,'nullable' => False),
				'status' => array('type' => 'varchar','precision' => 6,'default' => 'done','nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_p_projectmembers' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'project_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'account_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'type' => array('type' => 'char','precision' => 2,'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_p_invoice' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'num' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'project_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'customer' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'sum' => array('type' => 'decimal','precision' => 20,'scale' => 2,'default' => 0,'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','num'),
			'uc' => array('num')
		),
		'phpgw_p_invoicepos' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'invoice_id' => array('type' => 'int', 'precision' => 4,'default' => 0,'nullable' => False),
				'order_id' => array('type' => 'int', 'precision' => 4,'default' => 0,'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'phpgw_p_delivery' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'num' => array('type' => 'varchar','precision' => 20,'nullable' => False),
				'date' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'project_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'customer' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id','num'),
			'uc' => array('num')
		),
		'phpgw_p_deliverypos' => array(
			'fd' => array(
				'id' => array('type' => 'auto','nullable' => False),
				'delivery_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False),
				'hours_id' => array('type' => 'int','precision' => 4,'default' => 0,'nullable' => False)
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
