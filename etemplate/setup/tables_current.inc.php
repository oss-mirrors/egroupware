<?php
  /**************************************************************************\
  * phpGroupWare - Editable Templates                                        *
  * http://www.phpgroupware.org                                              *
  " Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_baseline = array(
		'phpgw_etemplate' => array(
			'fd' => array(
				'et_name' => array('type' => 'char','precision' => '80','nullable' => False),
				'et_template' => array('type' => 'char','precision' => '20','default' => '','nullable' => False),
				'et_lang' => array('type' => 'char','precision' => '5','default' => '','nullable' => False),
				'et_group' => array('type' => 'int','precision' => '4','default' => '0','nullable' => False),
				'et_version' => array('type' => 'char','precision' => '20','default' => '','nullable' => False),
				'et_data' => array('type' => 'text','nullable' => True),
				'et_size' => array('type' => 'char','precision' => '128','nullable' => True),
				'et_style' => array('type' => 'text','nullable' => True),
				'et_modified' => array('type' => 'int','precision' => '4','default' => '0','nullable' => False)
			),
			'pk' => array('et_name','et_template','et_lang','et_group','et_version'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
