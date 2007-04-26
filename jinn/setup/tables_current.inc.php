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
  * This file should be generated for you by setup. It should not need to be *
  * edited by hand.                                                          *
  \**************************************************************************/

  /* $Id$ */

  /* table array for jinn */
	$phpgw_baseline = array(
		'egw_jinn_acl' => array(
			'fd' => array(
				'site_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'site_object_id' => array('type' => 'varchar','precision' => '13','nullable' => False,'default' => '0'),
				'uid' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'rights' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0')
			),
			'pk' => array(),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_jinn_sites' => array(
			'fd' => array(
				'site_id' => array('type' => 'auto','nullable' => False),
				'site_name' => array('type' => 'varchar','precision' => '100','nullable' => False),
				'site_db_name' => array('type' => 'varchar','precision' => '250'),
				'site_db_host' => array('type' => 'varchar','precision' => '250'),
				'site_db_user' => array('type' => 'varchar','precision' => '30'),
				'site_db_password' => array('type' => 'varchar','precision' => '30'),
				'site_db_type' => array('type' => 'varchar','precision' => '10'),
				'upload_path' => array('type' => 'varchar','precision' => '250'),
				'dev_site_db_name' => array('type' => 'varchar','precision' => '100'),
				'dev_site_db_host' => array('type' => 'varchar','precision' => '50'),
				'dev_site_db_user' => array('type' => 'varchar','precision' => '30'),
				'dev_site_db_password' => array('type' => 'varchar','precision' => '30'),
				'dev_site_db_type' => array('type' => 'varchar','precision' => '10'),
				'dev_upload_path' => array('type' => 'varchar','precision' => '250'),
				'upload_url' => array('type' => 'varchar','precision' => '250'),
				'dev_upload_url' => array('type' => 'varchar','precision' => '250'),
				'object_scan_prefix' => array('type' => 'varchar','precision' => '100'),
				'jinn_version' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'host_profile' => array('type' => 'varchar','precision' => '30','nullable' => False),
				'uniqid' => array('type' => 'varchar','precision' => '30'),
				'site_version' => array('type' => 'int','precision' => '4','nullable' => False)
			),
			'pk' => array('site_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_jinn_objects' => array(
			'fd' => array(
				'object_id' => array('type' => 'varchar','precision' => '13','nullable' => False),
				'parent_site_id' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'name' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'table_name' => array('type' => 'varchar','precision' => '250','nullable' => False),
				'upload_path' => array('type' => 'varchar','precision' => '250'),
				'relations' => array('type' => 'text'),
				'plugins' => array('type' => 'text'),
				'help_information' => array('type' => 'text'),
				'dev_upload_path' => array('type' => 'varchar','precision' => '255'),
				'max_records' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'hide_from_menu' => array('type' => 'int','precision' => '1','default' => '0'),
				'upload_url' => array('type' => 'varchar','precision' => '250'),
				'dev_upload_url' => array('type' => 'varchar','precision' => '250'),
				'extra_where_sql_filter' => array('type' => 'text'),
				'events_config' => array('type' => 'text'),
				'unique_id' => array('type' => 'varchar','precision' => '13'),
				'layoutmethod' => array('type' => 'char','precision' => '1'),
				'formwidth' => array('type' => 'int','precision' => '4','default' => '0'),
				'formheight' => array('type' => 'int','precision' => '4','default' => '0'),
				'disable_multi' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_del_rec' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_edit_rec' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_view_rec' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_copy_rec' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_create_rec' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_simple_search' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_filters' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_export' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_import' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'disable_reports' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0')
			),
			'pk' => array('object_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_jinn_obj_fields' => array(
			'fd' => array(
				'field_id' => array('type' => 'auto','nullable' => False),
				'field_parent_object' => array('type' => 'varchar','precision' => '13','nullable' => False,'default' => '0'),
				'field_name' => array('type' => 'text','nullable' => False),
				'element_type' => array('type' => 'varchar','precision' => '20','nullable' => False),
				'element_label' => array('type' => 'varchar','precision' => '50'),
				'field_help_info' => array('type' => 'text'),
				'field_plugins' => array('type' => 'text'),
				'form_listing_order' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '999'),
				'list_visibility' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '1'),
				'form_visibility' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '1'),
				'field_enabled' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '1'),
				'canvas_field_x' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'canvas_field_y' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'canvas_label_x' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'canvas_label_y' => array('type' => 'int','precision' => '4','nullable' => False,'default' => '0'),
				'data_source' => array('type' => 'varchar','precision' => '100'),
				'label_visibility' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '1'),
				'single_col' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0'),
				'fe_readonly' => array('type' => 'int','precision' => '1','nullable' => False,'default' => '0')
			),
			'pk' => array('field_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_jinn_report' => array(
			'fd' => array(
				'report_id' => array('type' => 'auto','nullable' => False),
				'report_naam' => array('type' => 'varchar','precision' => '50','nullable' => False),
				'report_object_id' => array('type' => 'varchar','precision' => '20','nullable' => False,'default' => '0'),
				'report_header' => array('type' => 'text','nullable' => False),
				'report_body' => array('type' => 'text','nullable' => False),
				'report_footer' => array('type' => 'text','nullable' => False),
				'report_type_name' => array('type' => 'varchar','precision' => '15','nullable' => False),
				'report_type_confdata' => array('type' => 'varchar','precision' => '255')
			),
			'pk' => array('report_id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		),
		'egw_jinn_relation' => array(
			'fd' => array(
				'id' => array('type' => 'varchar','precision' => '20','nullable' => False),
				'object_id' => array('type' => 'varchar','precision' => '20'),
				'type_num' => array('type' => 'int','precision' => '4'),
				'type_text' => array('type' => 'varchar','precision' => '20'),
				'local_field' => array('type' => 'varchar','precision' => '100'),
				'foreign_table' => array('type' => 'varchar','precision' => '100'),
				'foreign_key' => array('type' => 'varchar','precision' => '100'),
				'foreign_show_fields' => array('type' => 'text'),
				'foreign_object_uniqid' => array('type' => 'varchar','precision' => '100'),
				'connect_table' => array('type' => 'varchar','precision' => '100'),
				'connect_key_local' => array('type' => 'varchar','precision' => '100'),
				'connect_key_foreign' => array('type' => 'varchar','precision' => '100')
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array('id'),
			'uc' => array('id')
		),
		'egw_jinn_domains' => array(
			'fd' => array(
				'id' => array('type' => 'auto'),
				'name' => array('type' => 'varchar','precision' => '250'),
				'db_name' => array('type' => 'varchar','precision' => '30'),
				'db_host' => array('type' => 'varchar','precision' => '30'),
				'db_user' => array('type' => 'varchar','precision' => '30'),
				'db_password' => array('type' => 'varchar','precision' => '30'),
				'db_type' => array('type' => 'varchar','precision' => '30'),
				'upload_path' => array('type' => 'varchar','precision' => '250'),
				'upload_url' => array('type' => 'varchar','precision' => '250'),
				'disabled' => array('type' => 'bool','default' => '0'),
				'parent_app_id' => array('type' => 'int','precision' => '4')
			),
			'pk' => array('id'),
			'fk' => array(),
			'ix' => array(),
			'uc' => array()
		)
	);
?>
