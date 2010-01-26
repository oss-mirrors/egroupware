<?php
  /**************************************************************************\
  * eGroupWare - Setup                                                       *
  * http://www.egroupware.org                                                *
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
	'egw_bookmarks' => array(
		'fd' => array(
			'bm_id' => array('type' => 'auto','nullable' => False),
			'bm_owner' => array('type' => 'int','precision' => '4'),
			'bm_access' => array('type' => 'varchar','precision' => '255'),
			'bm_url' => array('type' => 'varchar','precision' => '255'),
			'bm_name' => array('type' => 'varchar','precision' => '255'),
			'bm_desc' => array('type' => 'text'),
			'bm_keywords' => array('type' => 'varchar','precision' => '255'),
			'bm_category' => array('type' => 'int','precision' => '4'),
			'bm_rating' => array('type' => 'int','precision' => '4'),
			'bm_info' => array('type' => 'varchar','precision' => '255'),
			'bm_visits' => array('type' => 'int','precision' => '4'),
			'bm_favicon' => array('type' => 'varchar','precision' => '255')
		),
		'pk' => array('bm_id'),
		'fk' => array(),
		'ix' => array(),
		'uc' => array()
	),
	'egw_bookmarks_extra' => array(
		'fd' => array(
			'bm_id' => array('type' => 'int','precision' => '4'),
			'bm_name' => array('type' => 'varchar','precision' => '64'),
			'bm_value' => array('type' => 'text')
		),
		'pk' => array('bm_id','bm_name'),
		'fk' => array('bm_id' => 'egw_bookmarks.bm_id'),
		'ix' => array(),
		'uc' => array()
	)
);
?>
