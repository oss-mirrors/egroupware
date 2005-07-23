<?php
   /**************************************************************************\
   * eGroupWare - Jinn Preferences                                            *
   * http://egroupware.org                                                    *
   * Written by Pim Snel <pim@egroupware.org>                                 *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; version 2 of the License.                     *
   \**************************************************************************/

   // In the future these settings go to the plugin file 

   /* $Id$ */

	$prev_img = array(
		'no'  => lang('Never'),
		'only_tn' => lang('Only if thumnails exits'),
		'yes' => lang('Yes')
	);

	$max_prev=array(
		'1'  => '1',
		'2'  => '2',
		'3'  => '3',
		'4'  => '4',
		'5'  => '5',
		'10' => '10',
		'20' => '20',
		'30' => '30',
		'-1' => lang('No max. number')
	);

	$yes_no = Array(
		'no'  => lang('No'),
		'yes' => lang('Yes')
	);

	$default_col_num = Array(
		'3'  => '3',
		''   => lang('4 (default)'),
		'5'  => '5',
		'6'  => '6',
		'7'  => '7',
		'8'  => '8',
		'9'  => '9',
		'10' => '10',
		'20' => '20',
		'-1' => lang('Show all colums, always')
	);

	/* Settings array for this app */
	$GLOBALS['settings'] = array(
		'img' => array(
			'type'  => 'section',
			'title' => 'Image Plugin',
			'xmlrpc' => True,
			'admin'  => False
		),
		'prev_img' => array(
			'type'   => 'select',
			'label'  => 'Preview thumbs or images in form',
			'name'   => 'prev_img',
			'values' => $prev_img,
			'help'   => "When you choose 'Never', only links to the images are displayed; when you choose 'Only if thumnails exists' previews are  shown if an thumbnail of the image exists; if you choose 'Yes' all images are shown",
			'xmlrpc' => True,
			'admin'  => False
		),
		'max_prev' => array(
			'type'   => 'select',
			'label'  => 'Max. number of previews in form',
			'name'   => 'max_prev',
			'values' => $max_prev,
			'help'   => 'When a lot of images are attached to a record, the form can
			load very slow. You can set a maximum number of images that is show in the form.',
			'xmlrpc' => True,
			'admin'  => False
		),
		'wysi' => array(
			'type'  => 'section',
			'title' => 'WYSIWYG Plugin',
			'xmlrpc' => True,
			'admin'  => False
		),
		'disable_htmlarea' => array(
			'type'   => 'select',
			'label'  => 'Disable the WYSIWYG/HTMLArea Plugin',
			'name'   => 'disable_htmlarea',
			'values' => $yes_no,
			'help'   => "The WYSIWYG plugin makes you edit text like you do in a program like OpenOffice Writer or Word. Some people don't like this feature though, so you can force JiNN not to use it.",
			'xmlrpc' => True,
			'admin'  => False
		),
		'default_record_num' => array(
			'type'    => 'input',
			'label'   => 'Number of records per page',
			'name'    => 'default_record_num',
			'help'    => 'How many records do you want to list per page?',
			'xmlrpc' => True,
			'admin'  => False
		),
		'listv' => array(
			'type'  => 'section',
			'title' => 'List view',
			'xmlrpc' => True,
			'admin'  => False
		),
		'default_col_num' => array(
			'type'   => 'select',
			'label'  => 'Default number of visable columns',
			'name'   => 'default_col_num',
			'values' => $default_col_num,
			'help'   => 'How many columns do you want to be visible by default in List View?',
			'xmlrpc' => False,
			'admin'  => False
		),
		'jinndev' => array(
			'type'  => 'section',
			'title' => 'JiNN Developer Settings',
			'xmlrpc' => True,
			'admin'  => True
		),
		'table_debugging_info' => array(
			'type'   => 'select',
			'label'  => 'Show extra table debugging information',
			'name'   => 'table_debugging_info',
			'values' => $yes_no,
			'help'   => 'When this is enables information like field length and field type is shown when editing record',
			'xmlrpc' => False,
			'admin'  => True
		),
		'experimental' => array(
			'type'   => 'select',
			'label'  => 'Activate experimental features which are in development',
			'name'   => 'experimental',
			'values' => $yes_no,
			'help'   => 'Only activate this if you know what your doing. You can destroy your data using experimental features.',
			'xmlrpc' => False,
			'admin'  => True
		),
		'debug_sql' => array(
			'type'   => 'select',
			'label'  => 'Show SQL-statements in msgbox',
			'name'   => 'debug_sql',
			'values' => $yes_no,
			'help'   => 'This is for debugging purposes.',
			'xmlrpc' => False,
			'admin'  => True
		),
		'debug_site_arr' => array(
			'type'   => 'select',
			'label'  => 'Show site_arr in msgbox',
			'name'   => 'debug_site_arr',
			'values' => $yes_no,
			'help'   => 'This is for debugging purposes.',
			'xmlrpc' => False,
			'admin'  => True
		),
		'debug_object_arr' => array(
			'type'   => 'select',
			'label'  => 'Show object_arr in msgbox',
			'name'   => 'debug_object_arr',
			'values' => $yes_no,
			'help'   => 'This is for debugging purposes.',
			'xmlrpc' => False,
			'admin'  => True
		)
	);
