<?php
  /***************************************************************************\
  * eGroupWare - File Manager                                                 *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  sponsored by Think.e - http://www.think-e.com.br                         *
  * ------------------------------------------------------------------------- *
  * Description: SO Class for file manager                                    *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/


	class so_fm2
	{

/*		Testing db_highlevel with this class...
		var	$tables = array(
			'status' => array(
				'table' => 'phpgw_cc_status'
			),
			
			'prefix' => array(
				'table' => 'phpgw_cc_prefixes'
			),
			
			'suffix' => array(
				'table' => 'phpgw_cc_suffixes'
			),
			
			'contact' => array(
				'table'  => 'phpgw_cc_contact',
				
				'status' => 'id_status,id_status',
				'prefix' => 'id_prefix,id_prefix',
				'suffix' => 'id_suffix,id_suffix',
				
				'contact_related' => 'id_contact,id_contact',
				'related'         => array('alias' => 'contact_related'),
				
				'contact_connection' => 'id_contact,id_contact',
				'connection'         => array('path' => 'contact_connection'),

				'contact_address' => 'id_contact,id_contact',
				'address'         => array('path' => 'contact_address'),
				
				'business_info'   => 'id_contact,id_contact',
				'company'         => array('path' => 'business_info') 
			),
			
			'business_info' => array(
				'table'   => 'phpgw_cc_contact_company',
				'company' => 'id_company,id_company'
			),
			'company' => array(
				'table'              => 'phpgw_cc_company',
				
				'company_related'    => 'id_company.id_company',
				'related'            => array('alias' => 'company_related'),
				
				'company_address'    => 'id_company,id_company',
				'address'            => array('path' => 'company_address'),
				
				'company_connection' => 'id_company,id_company',
				'connection'         => array('path' => 'company_connection'),
				
				'business_info'      => 'id_company,id_company',
				'contact'            => array('path' => 'business_info'),
				
				'legal'              => 'id_company,id_company',
			),
			
			'company_related' => array(
				'table'                   => 'phpgw_cc_company_rels',
				'company'                 => 'id_related,id_company',

				'company_related'         => 'id_related,id_company',
				'related'                 => array('alias' => 'company_related'),

				'typeof_company_relation' => 'id_typeof_company_relation,id_typeof_company_relation',
				'typeof_relation'         => array('alias' => 'typeof_company_relation'),
				'type'                    => array('alias' => 'typeof_company_relation')
			),
			'contact_related' => array(
				'table'                   => 'phpgw_cc_contact_rels',
				'contact'                 => 'id_related,id_contact',
				
				'contact_related'         => 'id_contact,id_related',
				'related'                 => array('alias' => 'contact_related'),
				
				'typeof_contact_relation' => 'id_typeof_contact_relation,id_typeof_contact_relation',
				'typeof_relation'         => array('alias' => 'typeof_contact_relation'),
				'type'                    => array('alias' => 'typeof_contact_relation')
			),
			
			'company_address' => array(
				'table'          => 'phpgw_cc_company_addrs',
				'address'        => 'id_address,id_address',
				'typeof_address' => array('alias' => 'typeof_company_address')
			),
			'contact_address' => array(
				'table'          => 'phpgw_cc_contact_addrs',
				'address'        => 'id_address,id_address',
				'typeof_address' => array('alias' => 'typeof_contact_address')
			),
			'address' => array(
				'table' => 'phpgw_cc_addresses',
				'city'  => 'id_city,id_city'
			),
			'city' => array(
				'table'   => 'phpgw_cc_city',
				'state'   => 'id_state,id_state',
				'country' => 'id_country,id_country'
			),
			'state' => array(
				'table'   => 'phpgw_cc_state',
				'country' => 'id_country,id_country',
			),
			'country' => array(
				'table' => 'phpgw_cc_country'
			),
			
			'company_connection' => array(
				'table'             => 'phpgw_cc_company_conns',
				'connection'        => 'id_connection,id_connection',
				'typeof_connection' => array('alias' => 'typeof_company_connection')
			),
			'contact_connection' => array(
				'table'             => 'phpgw_cc_contact_conns',
				'connection'        => 'id_connection,id_connection',
				'typeof_connection' => array('alias' => 'typeof_contact_connection')
			),
			'connection' => array(
				'table'             => 'phpgw_cc_connections'
			),

			'legal' => array(
				'table'                => 'phpgw_cc_company_legals',
				'typeof_company_legal' => 'id_typeof_company_legal,id_typeof_company_legal',
				'typeof_legal'         => array('alias' => 'typeof_company_legal') 
			),
			
						
			'typeof_contact_relation' => array(
				'table' => 'phpgw_cc_typeof_ct_rels'
			),
			'typeof_company_relation' => array(
				'table' => 'phpgw_cc_typeof_co_rels'
			),
			'typeof_contact_address' => array(
				'table' => 'phpgw_cc_typeof_ct_addrs'
			),
			'typeof_company_address' => array(
				'table' => 'phpgw_cc_typeof_co_addrs'
			),
			'typeof_contact_connection' => array(
				'table' => 'phpgw_cc_typeof_ct_conns'
			),
			'typeof_company_connection' => array(
				'table' => 'phpgw_cc_typeof_co_conns'
			),
			'typeof_company_legal' => array(
				'table' => 'phpgw_cc_typeof_co_legals'
			)
		);*/


/*		var $tables = array(
			#main fields
			'mimetype' => array(
				'table'      => 'phpgw_vfs2_mimetypes',
				'file'       => 'mime_id,mime_id'
			),
			'file' => array(
				'table'      => 'phpgw_vfs2_files',
				'mimetype'   => 'mime_id,mime_id',
				'custom'     => 'file_id,file_id',
				'sharing'    => 'file_id,file_id',
				'versioning' => 'file_id,file_id'
			),
			'custom' => array(
				'table'      => 'phpgw_vfs2_customfields_data',
				'file'       => 'file_id,file_id'
			),
			'sharing' => array(
				'table'      => 'phpgw_vfs2_shares',
				'file'       => 'file_id,file_id'
			),
			'versioning' => array(
				'table'      => 'phpgw_vfs2_versioning',
				'file'       => 'file_id,file_id'
			),
			#other fields
			'custom_desc' => array(
				'table' => 'phpgw_vfs2_customfields'
			),
			'quota' => array(
				'table' => 'phpgw_vfs2_quota'
			)
		);
*/

		function so_fm2()
		{

	/*		$db_hl =& CreateObject('phpgwapi.db_highlevel','contactcenter');

			$db_hl->set_conversion_tables($this->tables);

//			$ins_arr = array( 'file.name' => 'bla.exe', 'file.directory' => '/home', 'file.mimetype.extension' => 'exe');


			$ins_arr = array(
				'contact.name' => 'Vinicius',
				'contact.address.address1' => 'Rua Rio Grande do Sul 116/ap.14',
				'contact.address.city.cityname' => 'Curitiba',
				'contact.prefix.prefix_name' => 'Sr.',
				'contact.company(1).company_name' => 'Thyamad',
				'contact.company(1).address.address1' => 'Luis Xavier 68, 909',
				'contact.company(2).company_name' => 'PET Informática UFPR',
				'contact.company(2).address.address1' => 'Centro Politécnico',
				'contact.business_info(2).title' => 'Bolsista'
			);

	
			$where_arr = array();
	
			$db_hl->insert($ins_arr,$where_arr,__LINE__,__FILE__);
*/

		}


	}


?>
