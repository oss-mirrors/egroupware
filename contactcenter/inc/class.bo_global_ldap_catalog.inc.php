<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphael@think-e.com.br>                       *
  *  sponsored by Think.e - http://www.think-e.com.br                         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	/*
		This class is responsible for manipulating the Global LDAP Contact Manager
	*/
	include_once('class.abo_catalog.inc.php');
	
	class bo_global_ldap_catalog extends abo_catalog
	{
		var $ldap;
	
		var $src_info;
		
		var $trans_table;
		
		var $fields = array(
			'id_contact'    => true,
			'status'        => true,
			'photo'         => true,
			'alias'         => true,
			'prefix'        => true,
			'given_names'   => true,
			'family_names'  => true,
			'names_ordered' => true,
			'suffix'        => true,
			'birthdate'     => true,
			'sex'           => true,
			'pgp_key'       => true,
			'notes'         => true,
			
			/* Array fields */
			'companies'     => true,
			'relations'     => true,
			'addresses'     => true,
			'connections'   => true
		);
		
		/*
		
			@function global_ldap_catalog
			@abstract Constructor
			@author Raphael Derosso Pereira
			
			@param integer $id_source The ID of the LDAP source
			
		*/
		function bo_global_ldap_catalog ( $id_source, $context )
		{
			if (!function_exists('ldap_search'))
			{
				exit('PHP LDAP support Unavailable!');
			}
			
			$this->ldap = CreateObject('contactcenter.bo_ldap_manager');
			
			$all_src = $this->ldap->get_all_ldap_sources();

			if (!$all_src[$id_source] or !$context)
			{
				exit('Unavailable LDAP source.');
			}

			$this->src_info = $all_src[$id_source];
			$this->src_info['context'] = $context;
			$this->trans_table = $this->ldap->get_ldap_fields_association($id_source);
		}
		
		/*
		
			@function find
			@abstract Searches the LDAP directory for the specified fields with
				the specified rules and retuns an array containing all the DNs
				that matches the rules.
			@author Raphael Derosso Pereira
			
			@param array $what The fields to be taken
			@param array $rules The rules to be match. See class.abo_catalog.inc.php
				for reference
			@param array $other Other parameters:
				$return = array(
					'limit'  => (integer),
					'offset' => (integer) [NOT IMPLEMENTED]
				)
		
		*/
		function find($what, $rules=false, $other=false)
		{
			$restric_fields = $this->get_restrictions_without_branch($rules);
			
			$trans_f = $this->translate_fields($what, $restric_fields);
			
			foreach($trans_f as $orig => $field_a)
			{
				foreach($field_a as $field)
				{
					$fields[] = $field;
				}
			}
			
			$fields = array_unique($fields);
			
			$filter = $this->process_restrictions($rules, $trans_f);

			$ldap = $GLOBALS['phpgw']->common->ldapConnect($this->src_info['host'], $this->src_info['acc'], $this->src_info['pw']);
			
			if (!$ldap)
			{
				return false;
			}

			$result_r = @ldap_search($ldap, $this->src_info['context'], $filter, $fields);

			if (!$result_r)
			{
				return false;
			}
			
			if ($other['order'])
			{
				$sort_f = array($other['order']);
				$ldap_sort_by = $this->translate_fields($sort_f, $restric_fields);
			}

			if ($ldap_sort_by)
			{
				if (!ldap_sort($ldap, $result_r, $ldap_sort_by[$other['order']][0]))
				{
					return false;
				}
			}
			
			$result_u = ldap_get_entries($ldap, $result_r);
			
			$i = 0;
			foreach ($result_u as $index => $result_p)
			{
				if ($index === 'count' or $index === 'dn')
				{
					continue;
				}
				
				foreach ($trans_f as $orig => $trans)
				{
					$orig = substr($orig, strrpos($orig, '.')+1, strlen($orig));
					foreach ($trans as $f)
					{
						if ($f === 'dn')
						{
							$return[$i][$orig] = $result_p['dn'];
						}
						else if ($result_p[$f][0])
						{
							$return[$i][$orig] = $result_p[$f][0];
						}
					}
				}
				$i++;
			}
			
			return $return;
		}
		
		/*
		
			@function translate_fields
			@abstract Return the LDAP objectClass fields that corresponds to the
				specified parameter fields
			@author Raphael Derosso Pereira
			
			@param array $fields The fields in the standard ContactCenter format
			@param array $rules The rules
		
		*/
		function translate_fields ( $fields, &$restric_fields )
		{
			$return = array();
			
			$i = 0;
			foreach ($fields as $field)
			{
				if (!array_key_exists($field,$this->trans_table) or !$this->trans_table[$field])
				{
					continue;
				}
				
				if (!is_array($this->trans_table[$field]))
				{
					$reference = $this->trans_table[$field];
					
					reset($restric_fields);
					while(list(,$field_r) = each($restric_fields))
					{
						if ($field_r['field'] === $reference and array_key_exists($field_r['value'], $this->trans_table[$reference]))
						{
							array_push($return[$field], $this->trans_table[$reference][$field_r['value']]);
						}
					}
				}
				else
				{
					if (!is_array($return[$field]))
					{
						$return[$field] = $this->trans_table[$field];
					}
					else
					{
						array_push($return[$field], $this->trans_table[$field]);
					}
				}
			}
			
			if (count($return))
			{
				return $return;
			}
			
			return false;
		}
		
		/*
		
			@function process_restrictions
			@abstract Returns a LDAP filter string that corresponds to the
				specified restriction rules
			@author Raphael Derosso Pereira
			
			@param string $rules The restriction rules
		
		*/
		function process_restrictions( $rules, &$trans_table, $join_type='&' )
		{
			if (!is_array($rules) or !count($rules))
			{
				return null;
			}
			
			foreach($rules as $rule_i => $rule)
			{
				$t = array();
				switch($rule['type'])
				{
					case 'branch':
						switch(strtoupper($rule['value']))
						{
							case 'OR':
								$join = '|';
								break;
								
							case 'AND':
								$join = '&';
								break;
								
							case 'NOT':
								$join = '!';
								break;
								
							default:
								$join = $join_type;
						}
						$return_t[] = $this->process_restrictions($rule['sub_branch'], $trans_table, $join);
						break;
						
					case '=':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$rule['value'].')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
					
					case '!=':	
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '(!('.$field.'='.$rule['value'].'))';
							}
							$return_t[] = '(&'.implode(' ',$t).')';
						}
						break;
					
					case '<=':
					case '<':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'<='.$rule['value'].')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
					
					case '>':
					case '>=':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'>='.$rule['value'].')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
						
					case 'NULL':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '(!('.$field.'=*'.'))';
							}
							$return_t[] = '(&'.implode(' ',$t).')';
						}
						break;
					
					case 'IN':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								foreach($rule['value'] as $value)
								{
									$t[] = '('.$field.'='.$value.')';
								}
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;

					case 'iLIKE':
/*						if (array_key_exists($rule['field'], $trans_table))
						{
							$value_1 = strtoupper(str_replace('%', '*', $rule['value']));
							$value_2 = strtolower($value_1);
							
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$value_1.')';
								$t[] = '('.$field.'='.$value_2.')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
						
*/					case 'LIKE':
						if (array_key_exists($rule['field'], $trans_table))
						{
							$value = str_replace('%', '*', $rule['value']);
							
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$value.')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
						
					case 'NOT NULL':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'=*'.')';
							}
							$return_t[] = '(|'.implode(' ',$t).')';
						}
						break;
					
					case 'NOT IN':
						if (array_key_exists($rule['field'], $trans_table))
						{
							foreach($trans_table[$rule['field']] as $field)
							{
								foreach($rule['value'] as $value)
								{
									$t[] = '('.$field.'='.$value.')';
								}
							}
							$return_t[] = '(!(|'.implode('',$t).'))';
						}
						break;

					case 'NOT iLIKE':
						if (array_key_exists($rule['field'], $trans_table))
						{
							$value_1 = strtoupper(str_replace('%', '*', $rule['value']));
							$value_2 = strtolower($value_1);
							
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$value_1.')';
								$t[] = '('.$field.'='.$value_2.')';
							}
							$return_t[] = '(!(|'.implode(' ',$t).'))';
						}
						break;

					case 'NOT LIKE':
						if (array_key_exists($rule['field'], $trans_table))
						{
							$value = str_replace('%', '*', $rule['value']);
							
							foreach($trans_table[$rule['field']] as $field)
							{
								$t[] = '('.$field.'='.$value.')';
							}
							$return_t[] = '(!(|'.implode(' ',$t).'))';
						}
						break;
				}
			}
			
			if (count($return_t))
			{
				$return = '('.$join_type;
				foreach ($return_t as $return_p)
				{
					$return .= $return_p;
				}
				$return .= ')';
			}
			else
			{
				$return = null;
			}
			return $return;
		}

		/*!
		
			@function get_restrictions_without_branch
			@abstract Returns an array containing the restrictions ignoring the
				branches
			@author Raphael Derosso Pereira
			
			@param array $restrictions The restrictions
		
		*/
		function get_restrictions_without_branch(&$restrictions)
		{
			if (!is_array($restrictions))
			{
				return null;
			}
			
			$fields = array();
			
			foreach ($restrictions as $restrict_data)
			{
				switch($restrict_data['type'])
				{
					case 'branch':
						$fields = array_merge($fields, $this->get_restrictions_without_branch($restrict_data['sub_branch']));
						break;
						
					case '=':
					case '!=':	
					case '<=':
					case '<':
					case '>':
					case '>=':
					case 'NULL':
					case 'IN':
					case 'LIKE':
					case 'iLIKE':
					case 'NOT NULL':
					case 'NOT IN':
					case 'NOT LIKE':
					case 'NOT iLIKE':
						array_push($fields, $restrict_data);
						break;
						
					default:
						exit('Error in '.__FILE__.' on '.__LINE__.'<br>The restriction type passed was: '.$restrict_data['type']);					
				}
			}
			
			return $fields;
		}
		
		
		/*********************************************************************\
		 *                        Methods to Get Data                        *
		\*********************************************************************/
		
	
		/*!
		
		 @function get_single_entry
		 @abstract Returns all information requested about one contact
		 @author Raphael Derosso Pereira
		     
		 @param integer $id_contact The contact ID
		 @param array $fields The array returned by get_fields with true
		 	on the fields to be taken.
		 	
		*/
		function get_single_entry ( $id_contact, $fields )
		{
			if (!is_array($fields)) 
			{
				if (is_object($GLOBALS['phpgw']->log)) 
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'F-BadcontactcenterParam, wrong get_single_entry parameters type.',
						'line' => __LINE__,
						'file' => __FILE__));
					
					$GLOBALS['phpgw']->log->commit();
				}
				else 
				{
					exit('Argument Error on: <br>File:'.__FILE__.'<br>Line:'.__LINE__.'<br>');
				}
			}
			
			$ldap = $GLOBALS['phpgw']->common->ldapConnect($this->src_info['host'], $this->src_info['acc'], $this->src_info['pw']);
			
			if (!$ldap)
			{
				return false;
			}
			
			$resource = ldap_read($ldap, $id_contact, 'objectClass='.$this->src_info['obj']);
			$n_entries = ldap_count_entries($ldap, $resource);
			if ( $n_entries > 1 or $n_entries < 1)
			{
				return false;
			}
			
			$contact = ldap_get_attributes($ldap,ldap_first_entry($ldap, $resource));

//			print_r($contact);
			
		//	$contact_data = $this->fields;
			
			foreach($fields as $field => $trueness)
			{
				if (!$trueness)
				{
					//unset($contact_data[$field]);
					continue;
				}
				
				switch ($field)
				{
					case 'companies':
						unset($l_fields);
						$l_fields['company_name']  = $this->trans_table['contact.company.company_name'];
						$l_fields['title']         = $this->trans_table['contact.business_info.title'];
						$l_fields['department']    = $this->trans_table['contact.business_info.department'];
						$l_fields['company_notes'] = $this->trans_table['contact.company.company_notes'];
						
						$contact_data['companies'] = array();
						foreach($l_fields as $l_field => $l_value)
						{
							if (!( $contact[$l_value[0]][0]))
							{
								continue;
							}
							
							$contact_data['companies']['company1'][$l_field] = $contact[$l_value[0]][0];
						}
						
						if (!(count($contact_data['companies'])))
						{
							unset($contact_data['companies']);
						}
						break;
					
					case 'relations':
						unset($l_fields);
						if (!$this->trans_table['contact.contact_related.names_ordered'])
						{
							unset($contact_data['relations']);
						}
						
						$contact_data['relations'] = array();
						if (!is_array($this->trans_table['contact.contact_related.names_ordered']))
						{
							if (!($trans = $this->trans_table[$this->trans_table['contact.contact_related.names_ordered']]))
							{
								continue;
							}
							
							$i = 1;
							foreach($trans as $l_type => $l_type_fields)
							{
								if (!($contact[$l_type_fields[0]][0]))
								{
									continue;
								}
								
								$contact_data['relations']['relation'.$i]['type'] = $l_type;
								$contact_data['relations']['relation'.$i]['names_ordered'] = $contact[$l_type_fields[0]][0];
								$i++;
							}
						}
						
						if (!(count($contact_data['relations'])))
						{
							unset($contact_data['relations']);
						}
						break;
					
					case 'addresses':
						unset($l_fields);
						$l_fields['address1'] = $this->trans_table['contact.address.address1'];
				 		$l_fields['address2'] = $this->trans_table['contact.address.address2'];
				 		$l_fields['complement'] = $this->trans_table['contact.address.complement'];
				 		$l_fields['address_other'] = $this->trans_table['contact.address.address_other'];
						$l_fields['postal_code'] = $this->trans_table['contact.address.postal_code'];
				 		$l_fields['po_box'] = $this->trans_table['contact.address.po_box'];
				 		$l_fields['id_city'] = $this->trans_table['contact.address.city.id_city'];
						$l_fields['city_name'] = $this->trans_table['contact.address.city.city_name'];
						$l_fields['city_timezone'] = $this->trans_table['contact.address.city.city_timezone'];
						$l_fields['city_geo_latitude'] = $this->trans_table['contact.address.city.city_geo_latitude'];
						$l_fields['city_geo_longitude'] = $this->trans_table['contact.address.city.city_geo_longitude'];
						$l_fields['city_geo_altitude'] = $this->trans_table['contact.address.city.city_geo_altitude'];
						$l_fields['id_state'] = $this->trans_table['contact.address.city.state.id_state'];
						$l_fields['state_name'] = $this->trans_table['contact.address.city.state.state_name'];
						$l_fields['state_symbol'] = $this->trans_table['contact.address.city.state.state_symbol'];
						$l_fields['id_country'] = $this->trans_table['contact.address.city.country.id_country'];
						$l_fields['country_name'] = $this->trans_table['contact.address.city.country.country_name'];
				 		$l_fields['address_is_default'] = $this->trans_table['contact.address.address_is_default'];

						$contact_data['addresses'] = array();
						foreach($l_fields as $l_field => $l_value)
						{
							if (!is_array($l_value))
							{
								if (!($trans = $this->trans_table[$l_value]))
								{
									continue;
								}
								
								$i = 1;
								foreach($trans as $l_type => $l_type_fields)
								{
									if (!($contact[$l_type_fields[0]][0]))
									{
										continue;
									}
									
									$contact_data['addresses']['address'.$i]['type'] = $l_type;
									$contact_data['addresses']['address'.$i][$l_field] = $contact[$l_type_fields[0]][0];
									$i++;
								}
							}
							else
							{
								$contact_data['addresses']['address1'][$l_field] = $contact[$l_value[0]][0];
							}
						}
						
						if (!(count($contact_data['addresses'])))
						{
							unset($contact_data['addresses']);
						}
						break;
					
					case 'connections':
	                    $conns_types = ExecMethod('phpgwapi.config.read_repository', 'contactcenter');

						if (!is_array($conns_types) and !$conns_types['cc_people_email'])
						{
							$GLOBALS['phpgw']->exit('Default Connections Types Not Configured. Call Administrator!');
						}
						
						unset($l_fields);
				 		$l_fields['connection_name'] = $this->trans_table['contact.connection.connection_name'];
				 		$l_fields['connection_value'] = $this->trans_table['contact.connection.connection_value'];

						$contact_data['connections'] = array();
						foreach($l_fields as $l_field => $l_value)
						{
							if (!is_array($l_value))
							{
								if (!($trans = $this->trans_table[$l_value]))
								{
									continue;
								}
								
								$i = 1;
								foreach($trans as $l_type => $l_type_fields)
								{
									if (!($contact[$l_type_fields[0]][0]))
									{
										continue;
									}
									
									switch ($l_type)
									{
										case 'email':
										$contact_data['connections']['connection'.$i]['id_type'] = $conns_types['cc_people_email'];
										break;

										default:
										$contact_data['connections']['connection'.$i]['id_type'] = $conns_types['cc_people_phone'];
									}
									$contact_data['connections']['connection'.$i]['type'] = $l_type;
									$contact_data['connections']['connection'.$i][$l_field] = $contact[$l_type_fields[0]][0];
									$i++;
								}
							}
							else
							{
								$contact_data['connections']['connection1'][$l_field] = $contact[$l_value[0]][0];
							}
						}
						
						if (!(count($contact_data['connections'])))
						{
							unset($contact_data['connections']);
						}
						break;
					
					case 'prefix':
						unset($l_fields);
						$l_fields = $this->trans_table['contact.prefixes.prefix'];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data['prefix']);
							continue;
						}
						
						$contact_data['prefix'] = $contact[$l_fields[0]][0];
						break;
						
					case 'suffix':
						unset($l_fields);
						$l_fields = $this->trans_table['contact.suffixes.suffix'];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data['suffix']);
							continue;
						}
						
						$contact_data['suffix'] = $contact[$l_fields[0]][0];
						break;
						
					case 'status':
						unset($l_fields);
						$l_fields = $this->trans_table['contact.status.status_name'];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data['status']);
							continue;
						}
						
						$contact_data['status'] = $contact[$l_fields[0]][0];
						break;
						
					default:
						unset($l_fields);
						$l_fields = $this->trans_table['contact.'.$field];
						if (!$l_fields or !$contact[$l_fields[0]][0])
						{
							unset($contact_data[$field]);
							continue;
						}
						
						$contact_data[$field] = $contact[$l_fields[0]][0];
						break;
				}
			}
			
			if (!is_array($contact_data))
			{
				return false;
			}
			
			return $contact_data;
		}
		
		function get_multiple_entries ( $id_contacts, $fields, $other_data = false )
		{
			if (!is_array($id_contacts) or !is_array($fields) or ($other_data != false and !is_array($other_data)))
			{
				if (is_object($GLOBALS['phpgw']->log)) 
				{
					$GLOBALS['phpgw']->log->message(array(
						'text' => 'F-BadcontactcenterParam, wrong get_multiple_entry parameter type.',
						'line' => __LINE__,
						'file' => __FILE__));
					
					$GLOBALS['phpgw']->log->commit();
				}
				else {
					exit('Argument Error on: <br>File:'.__FILE__.'<br>Line:'.__LINE__.'<br>');
				}
			}
			
			$contacts = array();
	
			if ($other_data)
			{
				//TODO
			}
	
			foreach ($id_contacts as $id)
			{
				$contacts[$id] = $this->get_single_entry($id,$fields);
			}
			
			return $contacts;
		}

		function get_all_entries_ids ()
		{
			$search_fields = array('contact.id_contact', 'contact.names_ordered');
			$search_rules  = array(
				0 => array(
					'field' => 'contact.names_ordered',
					'type'  => 'LIKE',
					'value' => '%'
				)
			);
			$search_other  = array('order' => 'contact.names_ordered');

			$result_i = $this->find($search_fields, $search_rules, $search_other);

			if (is_array($result_i) and count($result_i))
			{
				$result = array();
				foreach($result_i as $result_part)
				{
					$result[] = $result_part['id_contact'];
				}

				return $result;
			}

			return null;
		}
		
		function get_relations ($id_contact,$extra=false)
		{
		}
		
		function get_addresses ( $id_contact,$extra=false )
		{
		}
		
		function get_connections ( $id_contact,$extra=false )
		{
		}
		
		function get_companies ( $id_contact, $extra=false )
		{
		}
		
		function get_all_prefixes (  )
		{
		}
		
		function get_all_suffixes (  )
		{
		}
		
		function get_all_status (  )
		{
		}
		
		function get_all_relations_types (  )
		{
		}
		
		function get_all_addresses_types (  )
		{
		}
		
		function get_all_connections_types (  )
		{
		}
		
		function get_vcard ( $id_contact )
		{
		}
		
		
		
		
		function get_global_tree ( $root )
		{
		}
	
		function get_actual_brach (  )
		{
		}
	
		function set_actual_branch ( $branch )
		{
		}
	}
?>
