<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	
	/*
		This class is responsible for the LDAP control/generic functions and for
		configuration gathering
	*/

	class bo_ldap_manager 
	{
		
		var $srcs;

		
		function bo_ldap_manager ()
		{
			if (!($this->srcs = $GLOBALS['phpgw']->session->appsession('contactcenter','bo_ldap_manager.srcs')))
			{
				$c = CreateObject('phpgwapi.config','contactcenter');
				$data = $c->read_repository();
				
				if (!$data or $data['cc_global_source0'] !== 'ldap')
				{
					$this->srcs = null;
					return;
				}
				
				$this->srcs = array(
					1 => array(
						'name'   => $data['cc_catalog_name'],
						'host'   => $data['cc_ldap_host0'],
						'dn'     => $data['cc_ldap_context0'],
						'acc'    => $data['cc_ldap_browse_dn0'],
						'pw'     => $data['cc_ldap_pw0'],
						'obj'    => 'inetOrgPerson',
						'branch' => strtolower('ou')
					)
				);
			}
		}

		function new_ldap_source ( $source_name, $charset, $host, $port, $dn_root, $dn_admin, $admin_pass, $contact_objectclass )
		{
		}
	
		/*
		
			@function get_all_ldap_sources
			@abstract Returns an array containing all LDAP sources informations
			@author Raphael Derosso Pereira
		
			@return array All LDAP information
				$return = array(
					<id_source> => array(
						'host' => (string),
						'dn'   => (string),
						'acc'  => (string),
						'pw'   => (string)   
					),
					...
				)
				
			TODO: Return multiple sources...
		*/
		function get_all_ldap_sources (  )
		{
			return $this->srcs;
		}
		
		function get_ldap_fields_association ( $id_source )
		{
			
			$op_iop = array(
				'contact.id_contact'               => array('dn'),
				'contact.photo'                    => array('jpegPhoto'),
				'contact.prefixes.prefix'          => false,
				'contact.alias'                    => array('alias'),
				'contact.given_names'              => array('givenName'),
				'contact.family_names'             => array('sn'),
				'contact.names_ordered'            => array('cn'),//,'displayName'),
				'contact.suffixes.suffix'          => false,
				'contact.birthdate'                => false,
				'contact.sex'                      => false,
				'contact.pgp_key'                  => false,
				'contact.notes'                    => false,
				'contact.business_info.title'      => array('title'),
				'contact.business_info.department' => array('ou'),
				'contact.company.company_name'     => array('o'),
				'contact.company.company_notes'    => array('businessCategory'),
				
				'contact.contact_related.names_ordered' => 'contact.contact_related.typeof_relation.contact_relation_name',
				'contact.contact_related.typeof_relation.contact_relation_name' =>  array(
					'manager'   => array('manager'),
					'secretary' => array('secretary')
				),
				
				'contact.address.address1'         => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('street', 'st', 'postalAddress', 'homePostalAddress'),
				),
				
				'contact.address.postal_code'      => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('PostalCode'),
				),
				
				'contact.address.city.city_name'   => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('l'),
				),
				
				'contact.address.city.state.state_name'       => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => false,
				),
				
				'contact.address.city.country.id_country'     => 'contact.address.typeof_address.contact_address_type_name',
				'contact.address.typeof_address.contact_address_type_name' => array(
					'home' => array('c')
				),
				
				'contact.connection.connection_value'         => 'contact.connection.typeof_connection.contact_connection_type_name',
				'contact.connection.typeof_connection.contact_connection_type_name' => array (
					'email'  => array('mail'),
					'phone'  => array('telephoneNumber'),
					'mobile' => array('mobile'),
					'pager'  => array('pager'),
					'fax'    => array('facsimileTelephoneNumber'),
					'telex'  => array('telexNumber')
				),
			);
			
			return $op_iop;
		}

		/*!
		
			@function get_ldap_tree
			@abstract Returns the LDAP tree corresponding to the specified level
			@author Raphael Derosso Pereira
			
			@param (integer) $id_source The ID of the LDAP source
			
			@param (string)  $context The context to be used as root branch
				
			@param (boolean) $recursive Make it a recursive construction.
				CAUTION! This is EXTREMELY SLOW on large LDAP databases,
				specially when they're not indexed
		*/		
		function get_ldap_tree($id_source, $context = false, $recursive = false) 
		{
			if (!$this->srcs[$id_source])
			{
				return null;
			}
			
			$ldap = $GLOBALS['phpgw']->common->ldapConnect($this->srcs[$id_source]['host'], $this->srcs[$id_source]['acc'], $this->srcs[$id_source]['pw']);
			if (!$ldap)
			{
				return false;
			}
			
			if ($recursive)
			{
				$tree = $this->get_ldap_tree_recursive($ldap, $context, $this->srcs[$id_source]['obj'],$this->srcs[$id_source]['branch']);
				$tree['recursive'] = true;

				return $tree;
			}
			
			return $this->get_ldap_tree_level($id_source, $ldap, $context, $this->srcs[$id_source]['obj'],$this->srcs[$id_source]['branch']);
		}


		/*!

			THIS FUNCTION IS NOT TESTED AND IS PROBABLY BROKEN!
			I WILL CORRECT IT IN THE NEAR FUTURE

		*/
		function get_ldap_tree_recursive($resource, $context, $objectClass)
		{
			$filter = '(!(objectClass='.$objectClass.'))';
			$result_res = ldap_list($resource, $context, $filter);

			if ($result_res === false)
			{
				return null;
			}
			
			$count = ldap_count_entries($resource,$result_res);
			if ( $count == 0 )
			{
				$filter = 'objectClass='.$objectClass;
				$result_res2 = ldap_list($resource, $context, $filter);
				$entries_count = ldap_count_entries($resource, $result_res2);

				if ($result_res2 !== false && $entries_count > 0)
				{
					return $entries_count;
				}
				else
				{
					return null;
				}
			}
			
			$entries = ldap_get_entries($resource, $result_res);
			
			for ($i = 0; $i < $entries['count']; $i++)
			{
				$subtree = $this->get_ldap_tree_recursive($resource, $entries[$i]['dn'], $objectClass);
				
				$dn_parts=ldap_explode_dn($entries[$i]['dn'],1);
				
				if ($subtree !== null and is_array($subtree)) 
				{
					$tree[$i]['name'] = $dn_parts[0];
					$tree[$i]['type'] = 'catalog_group';
					$tree[$i]['recursive'] = true;
					$tree[$i]['sub_branch'] = $subtree;
				}
				else if (is_int($subtree) and $subtree !== null)
				{
					$tree[$i] = array(
						'name'       => $dn_parts[0],
						'type'       => 'catalog',
						'class'      => 'global_contact_manager',
						'icon'       => 'share-mini.png',
						'value'      => $entries[$i]['dn'],
						'sub_branch' => false
					);
				} 
			}

			if (is_array($tree))
			{
				return $tree;
			}
			else
			{
				return null;
			}
		}
		


		function get_ldap_tree_level($id_source, $resource, $context, $objectClass, $branch_dn)
		{
			$dn_parts = ldap_explode_dn($context,1);
			$filter = '(!(objectClass='.$objectClass.'))';
			$result_res = @ldap_list($resource, $context, $filter);
			@ldap_sort($resource, $result_res, 'ou');

			if ($result_res === false)
			{
				return null;
			}

			$count = ldap_count_entries($resource,$result_res);

			if ( $count == 0 )
			{
				$filter = 'objectClass='.$objectClass;
				$result_res2 = @ldap_list($resource, $context, $filter);
				$entries_count = ldap_count_entries($resource, $result_res2);

				if ($result_res2 !== false && $entries_count > 0)
				{
					return array(
						'name'       => $dn_parts[0],
						'type'       => 'catalog',
						'class'      => 'bo_global_ldap_catalog',
						'class_args' => array($id_source, $context),
						'icon'       => 'globalcatalog-mini.png',
						'value'      => $context,
						'sub_branch' => false
					);
				}
				else
				{
					return array(
						'name' => $dn_parts[0],
						'type' => 'empty'
					);
				}
			}

			$sub_branch_found = false;
			$i = 0;
			for ($entry = ldap_first_entry($resource, $result_res);
			     $entry != false;
			     $entry = ldap_next_entry($resource, $entry))
			{
				$dn = ldap_get_dn($resource, $entry);
				$dn_parts_1 = ldap_explode_dn($dn,1);
				$dn_parts_full = ldap_explode_dn($dn,0);
				list($group) = explode('=',$dn_parts_full[0]);
				
				if ($group == $branch_dn or $branch_dn === 'all')
				{
					$tree['sub_branch'][$i] = array(
						'name'  => $dn_parts_1[0],
						'type'  => 'unknown',
						'value' => $dn,
						'sub_branch' => false					
					);
					$sub_branch_found = true;
				}
				$i++;
			}
			
			$filter = 'objectClass='.$objectClass;
			$result_res2 = @ldap_list($resource, $context, $filter);
			$entries_count = ldap_count_entries($resource, $result_res2);

			if ($result_res2 !== false && $entries_count > 0 && $sub_branch_found)
			{
				$tree['name']       = $dn_parts[0];
				$tree['type']       = 'mixed_catalog_group';
				$tree['class']      = 'bo_global_ldap_catalog';
				$tree['class_args'] = array($id_source,$context);
				$tree['icon']       = 'globalcatalog-mini.png';
				$tree['value']      = $context;
			}
			elseif ($result_res2 !== false && $entries_count > 0 && !$sub_branch_found)
			{
				return array(
					'name'       => $dn_parts[0],
					'type'       => 'catalog',
					'class'      => 'bo_global_ldap_catalog',
					'class_args' => array($id_source, $context),
					'icon'       => 'globalcatalog-mini.png',
					'value'      => $context,
					'sub_branch' => false
				);
			}
			else
			{
				$tree['name']       = $dn_parts[0];
				$tree['type']       = 'catalog_group';
				$tree['class']      = 'bo_catalog_group_catalog';
				$tree['class_args'] = array('$this', '$this->get_branch_by_level($this->catalog_level[0])');
				$tree['value']      = $context;
				$tree['ldap']       = array('id_source' => $id_source, 'context' => $context);
			}
			
			return $tree;
		}
	}
?>
