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

	class bo_contactcenter
	{

		/*!
			This var holds the actual catalog level.
		*/
		var $catalog_level;

		/*!
			This holds the instantiated catalog class;
		*/
		var $catalog;
		
		/*!
			The Security Manager
		*/
		var $security;
		

		function bo_contactcenter($catalog_level=false)
		{
			$this->tree = $GLOBALS['phpgw']->session->appsession('bo_contactcenter.tree','contactcenter');
			$this->catalog_level = $GLOBALS['phpgw']->session->appsession('bo_contactcenter.catalog_level','contactcenter');
			$this->security = CreateObject('contactcenter.bo_security_manager');
			
			if ($catalog_level)
			{
				$this->set_catalog($catalog_level);
			}
			else
			{
				if ($this->catalog_level[0])
				{
					$this->set_catalog($this->get_branch_by_level($this->catalog_level[0]));
				}
				else
				{
					$this->catalog_level = array('0.0');
					$this->get_catalog_tree();
					$this->set_catalog($this->catalog_level[0]);
				}
			}
		}
		
		/*!
		
			@function find
			@abstract Performs a search in the DB based on the parameters
			@author Raphael Derosso Pereira (algorithm and code)
			@author Vinicius Cubas Brand (algorithm)
			
			@param array $what The list of fields to be returned. The format is:
				$what = array(
					'contact.company.company_name',
					'contact.names_ordered'
				);
			
			@param array $rules The restrictions.
			
			The restrictions format is quite complicated, but is very complete.
			As defined here, there is the possibility to do almost any type of
			search (tell me if you can't do any). For example, imagine the
			following search:
					
						and(a,or(d,e,and(f,g)))
			
			That is represented by the folloowing tree:
				
                                   and
                                    |
                  .--------------------.
                  |                    |
                a = 5                 or
                                       |
                          .---------.------------.
                          |         |            |
                       d != 10  e LIKE %a       and
                                                 |
                                             .-------.
                                             |       |
                                           f = 5   g < 10


			The rules that should be passed to the find function for this tree
			is:
				
	 			$rules = array(
 					0 => array(
 						'field' => 'A',
 						'type'  => '=',
 						'value' => 5
 					),
 					1 => array (
	 					'type'	     => 'branch',
	 					'value'	     => 'OR',
	 					'sub_branch' => array(
 							0 => array(
 								'field' => 'D'
 								'type'  => '!=',
 								'value' => 10
 							),
 							1 => array(
 								'field' => 'E',
 								'type'  => 'LIKE',
 								'value' => '%a'
 							)
 							2 => array(
 								'type'       => 'branch',
 								'value'      => 'AND',
 								'sub_branch' => array(
 									0 => array(
 										'field' => 'F',
 										'type'  => '=',
 										'value' => 5
 									),
 									1 => array(
 										'field' => 'G'
 										'type'  => '<',
 										'value' => 10
 									)
 								)
 							)
 						)
 					)
	 			);

  
			The restriction type can be: =, !=, <=, <, >, >=, NULL, IN, LIKE, 
			NOT NULL, NOT IN, NOT LIKE
			Value of branch can be AND, OR, NOT
			
			@param array $other Other parameter to the search 
				$other = array(
					'offset'          => (int),
					'limit'           => (int),
					'sort'            => {ASC|DESC},
					'order'           => (string with field names separated by commas)
					'fields_modifier' => (COUNT|MAX)
				);

			@return array $array[<field_name>][<row_number>]
				
		*/
		function find($what, $rules=false, $other=false)
		{
			return $this->catalog->find($what, $rules, $other);
		}
		
		/*!
		
			@function get_catalog_tree
			@abstract Returns an array describing the available
				catalog-entity-view tree and their respective
				values and types
			@author Raphael Derosso Pereira

			@param (string)  $level The level to be taken
			@param (boolean) $recursive Retrive the whole tree from
				the level specified until the leaves
			
			@return The format of the return is:
				$return = array(
					0  => array(
						'name'       => '<branch_name>',
						'type'       => '<branch_type>',
						'class'      => '<branch_class>',
						'class_args' => '<branch_class_args>',
						'find_args'  => '<branch_find_args>',
						'sub_branch' => array(
							0  => array(
								'name'       => '<branch_name>',
								'type'       => '<branch_type>',
								'class'      => '<branch_class>',
								'class_args' => '<branch_class_args>',
								'find_args'  => '<branch_find_args>',
								'sub_branch' => array(...)
							),
							1  => array(...),...
						),
					),
					1  => array(...),...
				);
				
				<branch_type> can be 'catalog_group', 'catalog' or 'view';
				<branch_class> is the name of the class that is capable of 
					handling the information for this catalog/view
				<branch_class_args> is an array that holds the arguments to
					be passed to <branch_class> when it is instantiated
				<brach_find_args> is the string that should precede the search
					string
						
				If the branch is actually a leaf, than 'sub_branch' is false; 
		
		
			TODO: This method is hard-coded, but it should grab the tree
			from the DB using the View Manager...
		*/
		function get_catalog_tree($level = '0', $recursive = false)
		{
			if ($this->tree)
			{
				if ($level === '0')
				{
					return $this->tree['branches'];
				}
				
				$branch =& $this->get_branch_by_level($level);
				$info = $this->get_info_by_level($level);
				
				if ($branch['type'] === 'unknown')
				{
					if ($info['type'] === 'ldap')
					{
						$ldap = CreateObject('contactcenter.bo_ldap_manager');
						$new_branch = $ldap->get_ldap_tree($info['src'], $branch['value'], $recursive);
						
						if ($new_branch)
						{
							$branch = $new_branch;
						}
						else
						{
							return false;
						}
					}
					$GLOBALS['phpgw']->session->appsession('bo_contactcenter.tree','contactcenter',$this->tree);
					
					return $branch;
				}
				else if ($branch['type'])
				{
					return $branch;
				}
				
				return false;
			}
			
			if ($level !== '0')
			{
				return false;
			}
			
			$this->tree = array(
				0 => array(
					'type' => 'sql'
				),
				'branches' => array(
					0  => array(
						'name'       => lang('People'),
						'type'       => 'catalog',
						'class'      => 'bo_people_catalog',
						'icon'       => 'people-mini.png',
						'sub_branch' => false
					),/*
					1  => array(
						'name'       => lang('Companies'),
						'type'       => 'catalog',
						'class'      => 'bo_company_manager',
						'find_args'  => 'company',
						'icon'       => 'company-mini.png',
						'sub_branch' => false
					),*/
				)
			);
			
			$ldap = CreateObject('contactcenter.bo_ldap_manager');
			$ldap_srcs = $ldap->get_all_ldap_sources();
			
			if ($ldap_srcs)
			{
				$i = 1;
				reset($ldap_srcs);
				while (list($id,) = each($ldap_srcs))
				{
					if (($tree = $ldap->get_ldap_tree($id, $ldap_srcs[$id]['dn'], $recursive)))
					{
						$tree['name'] = $ldap_srcs[$id]['name'];
						array_push($this->tree['branches'], $tree);
						$this->tree[$i]['type'] = 'ldap';
						$this->tree[$i]['src'] = $id;
					}
					$i++;
				}
			}
			
			$GLOBALS['phpgw']->session->appsession('bo_contactcenter.tree','contactcenter',$this->tree);
			return $this->tree['branches'];
		}

		/*!
			
			@function get_branch_by_level
			@abstract Returns the branch and it's informations given the level
			@author Raphael Derosso Pereira
			
			@param (string) $level The level to be used
			
		*/
		function & get_branch_by_level($level)
		{
			$path = @explode('.',$level);
			$n_ways = count($path);
			
			if ($n_ways <= 1)
			{
				return false;
			}
			
			$code = '$branch =& $this->tree[\'branches\']';
			for ($i = 1; $i < $n_ways-1; $i++)
			{
				$code .= '['.$path[$i].'][\'sub_branch\']';
			}
			$code .= '['.$path[$i].'];';

			//echo 'Codigo: '.$code.'<br>';
			eval($code);
			
			return $branch;
		}

		/*!
		 
		 @function get_info_by_level
		 @abstract Returns the information about the catalog, given the level
		 @author Raphael Derosso Pereira

		 @param (string) $level The catalog level

		*/
		function get_info_by_level($level)
		{
			$path = @explode('.',$level);
			$n_ways = count($path);
			
			if ($n_ways <= 1)
			{
				return false;
			}
			
			$info = $this->tree[$path[1]];
			
			return $info;
		}

		/*!

			@function get_level_by_branch
			@abstract Returns the level of the given branch
			@author Raphael Derosso Pereira

			@param (array) $catalog The catalog
			@param (array) $branch  The reference to the branch to be searched

		*/
		function get_level_by_branch($catalog, &$branch, $branch_level = '0')
		{
//			echo '<br>';
			reset($branch);
			while(list($level, $bcatalog) = each($branch))
			{
//				echo 'Parent Level:    '.$branch_level.'<br>';
//				echo 'This node Level: '.$level.'<br>';
//				echo 'Catalog:         '.$bcatalog['name'].'<br>';

				$found = true;
				foreach ($catalog as $property => $value)
				{
					if ($property !== 'sub_branch' and $bcatalog[$property] !== $value)
					{
//						echo 'Property <b>'.$property.'</b> differs.<br>';
//						echo 'Expected: '.$value.'<br>';
//						echo 'Found: '.$bcatalog[$property].'<br>';
						$found = false;
					}

					if (!$found)
					{
						break;
					}
				}

				if ($found)
				{
//					echo '<b>FOUND</b><br>';
					return $branch_level.'.'.((string) $level);
				}
				else if ($bcatalog['sub_branch'])
				{
//					echo 'Not Found<br>';

					$search = $this->get_level_by_branch($catalog, $bcatalog['sub_branch'], (string) $level);
					
					if ($search !== false)
					{
//						echo 'Returning level: '.$branch_level.'.'.$search.'<br>';
//						echo 'Sholud it be '.$branch_level.'.'.$nlevel.' ?<br>';
//						echo 'Or '.$branch_level.'.'.((string)$search).' ?<br>';
						return $branch_level.'.'.$search;
					}
				}
			}

//			echo 'Not Found in this Branch<br>';
			return false;
		}
		
		/*!
		
			@function get_actual_catalog
			@abstract Returns the information about the Catalog that is 
				instantiated

			@author Raphael Derosso Pereira
		
		*/
		function get_actual_catalog()
		{
			$catalog = $this->get_branch_by_level($this->catalog_level[0]);
			return $catalog;
		}

		/*!

			@function get_actual_level
			@abstract Returns the level of the actual instantiated catalog
			@author Raphael Derosso Pereira

		*/
		function get_actual_level()
		{
			return $this->catalog_level[0];
		}
		
		/*!
		
			@function set_catalog
			@abstract Sets the actual catalog
			@author Raphael Derosso Pereira
			
			@param array $to_catalog The catalog in the format returned by
				get_available_tree or the level
		
		*/
		function set_catalog( $to_catalog )
		{
			if(!is_array($to_catalog))
			{
				if (is_string($to_catalog))
				{
					if (!($t =& $this->get_branch_by_level($to_catalog)))
					{
						return false;
					}
					$level = $to_catalog;
					$catalog =& $t;
				}
				else
				{
					return false;
				}
			}
			else
			{
				$catalog =& $to_catalog;
				//echo "Daqui!<br>";
				$level = $this->get_level_by_branch($to_catalog, $this->tree['branches']);
			}
			
			switch($catalog['type'])
			{
				case 'unknown':
					$level = $this->get_level_by_branch($catalog, $this->tree['branches']);
					$catalog =& $this->get_catalog_tree($level);

				case 'catalog':
				case 'catalog_group':
				case 'mixed_catalog_group':
					$this->catalog_level = array($level);
					$GLOBALS['phpgw']->session->appsession('bo_contactcenter.catalog_level','contactcenter', $this->catalog_level);
					$call = '$this->catalog = CreateObject('.'\'contactcenter.'.$catalog['class'].'\'';
					if ($catalog['class_args'])
					{
						foreach($catalog['class_args'] as $arg)
						{
							$args[] = is_string($arg) ? ($arg{0} != '$' ? "'".$arg."'" : $arg) : $arg;
							//$args[] = is_string($arg) ? "'".$arg."'" : $arg;
						}
						$call .= ','.implode(',',$args);
					}
					
					$call .= ');';
					
//					print_r($catalog);
//					echo '<br><br><b>Setando Catalogo '.$catalog['name'].': </b>'.$call.'<br>';

					eval($call);
								
					return $catalog;
					
				default: return false;
			}
		}
		


		/*********************************************************************\
		 *                Methods to set general fields                      *
		\*********************************************************************/
		
		/*!
		
			@function add_vcard
			@abstract Insert a VCard to the squema
			@author Raphael Derosso Pereira
			@param string $uploaded_file The path to the file that were
				uploaded.
		
		*/
		function add_vcard ( $uploaded_file )
		{
		}


		
		/*********************************************************************\
		 *                Methods to get general data                        *
		\*********************************************************************/
		
	}
?>
