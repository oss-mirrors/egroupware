<?php
  /**************************************************************************\
  * phpGroupWare                                                             *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_baseline = array(
		'skel' => array(
			'fd' => array(
				'skel_id' => array('type' => 'auto', 'nullable' => false),
				'skel_owner' => array('type' => 'varchar', 'precision' => 25),
				'skel_access' => array('type' => 'varchar', 'precision' => 10),
				'skel_cat' => array('type' => 'int', 'precision' => 4),
				'skel_des' => array('type' => 'text'),
				'skel_pri' => array('type' => 'int', 'precision' => 4)
			),
			'pk' => array('skel_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
