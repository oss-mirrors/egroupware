<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Storage Object Classes                                                    *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  *  sponsored by Thyamad - http://www.thyamad.com                            *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/


	include_once( "class.so_type.inc.php" );
	
	class so_company_address_type extends so_type {

		function so_company_address_type ( $id = false )
		{
			$this->init();
			
			$this->main_fields = array(
				'id_typeof_company_address' => array(
					'name'  => 'id_typeof_company_address',
					'type'  => 'primary',
					'state' => 'empty',
					'value' => &$this->id
				),
				'company_address_type_name' => array(
					'name'  => 'company_address_type_name',
					'type'  => false,
					'state' => 'empty',
					'value' => false
				)
			);
			
			$this->type_name = & $this->main_fields['company_address_type_name'];

			$this->db_tables = array(
				'phpgw_cc_typeof_co_addrs' => array(
					'type'   => 'main',
					'keys'   => array(
						'primary' => array(&$this->main_fields['id_typeof_company_address']),
						'foreign' => false
					),
					'fields' => & $this->main_fields
				)
			);

			if ($id)
			{
				if (!$this->checkout($id))
				{
					$this->reset_values();
					$this->state = 'new';
				}
			}
			else
			{
				$this->state = 'new';
			}
		}
	}

?>
