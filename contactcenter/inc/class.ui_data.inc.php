<?php
  /***************************************************************************\
  * eGroupWare - Contacts Center                                              *
  * http://www.egroupware.org                                                 *
  * Written by:                                                               *
  *  - Raphael Derosso Pereira <raphael@think-e.com.br>                       *
  *  - Vinicius Cubas <vinicius@think-e.com.br>                               *
  *  sponsored by Think.e - http://www.think-e.com.br                         *
  * ------------------------------------------------------------------------- *
  *  This program is free software; you can redistribute it and/or modify it  *
  *  under the terms of the GNU General Public License as published by the    *
  *  Free Software Foundation; either version 2 of the License, or (at your   *
  *  option) any later version.                                               *
  \***************************************************************************/

	
	class ui_data
	{
		var $public_functions = array(
			'data_manager' => true,
		);
		
		var $bo;
		
		var $page_info = array(
			'n_cards'          => false,
			'n_pages'          => false,
			'actual_letter'    => 'A',
			'actual_page'      => 1,
			'actual_entries'   => false,
			'changed'          => false,
			'catalogs'         => false,
			'actual_catalog'   => false
		);
		
		/*!
		
			@function ui_data
			@abstract The constructor. Sets the initial parameters and loads
				the data saved in the session
			@author Raphael Derosso Pereira
			
		*/
		function ui_data()
		{
			$temp = $GLOBALS['phpgw']->session->appsession('ui_data.page_info','contactcenter');
			$temp2 = $GLOBALS['phpgw']->session->appsession('ui_data.all_entries','contactcenter');
			
			$this->bo = CreateObject('contactcenter.bo_contactcenter');
			
			if ($temp)
			{
				$this->page_info = $temp;
			}

			if ($temp2)
			{
				$this->all_entries = $temp2;
			}
			
			if (!$this->page_info['actual_catalog'])
			{
				$catalogs = $this->bo->get_catalog_tree();
				$this->page_info['actual_catalog'] = $catalogs[0];
			}
			
			$this->page_info['actual_catalog'] =& $this->bo->set_catalog($this->page_info['actual_catalog']);
		}

		/*!
		
			@function index
			@abstract Builds the Main Page
			@author Raphael Derosso Pereira
			@author Jonas Goes
			
		*/		
		function index()
		{	
			if(!@is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			$GLOBALS['phpgw']->js->validate_file('venus','table');
			$GLOBALS['phpgw']->js->validate_file('venus','shapes');
			$GLOBALS['phpgw']->js->validate_file('venus','jsStructUtil');
			$GLOBALS['phpgw']->js->validate_file('venus','cssUtil');
			
//			$GLOBALS['phpgw']->js->set_onload('setTimeout(\'updateCards()\',1000)');
			$GLOBALS['phpgw']->common->phpgw_header();
			
			$GLOBALS['phpgw']->template->set_file(array('index' => 'index.tpl'));
			$GLOBALS['phpgw']->template->set_var('cc_root_dir', $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/');
			
			/* Quick Add */
			$GLOBALS['phpgw']->template->set_var('cc_qa_alias',lang('Alias').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_given_names',lang('Given Names').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_family_names',lang('Family Names').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_phone',lang('Phone').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_email',lang('Email').':');
			$GLOBALS['phpgw']->template->set_var('cc_qa_save',lang('Save'));
			$GLOBALS['phpgw']->template->set_var('cc_qa_clear',lang('Clear'));
			/* End Quick Add */
			
			$cc_css_file = $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/styles/cc.css';
			$cc_card_image_file = $GLOBALS['phpgw_info']['server']['webserver_url'].'/contactcenter/templates/default/images/card.png';
			$GLOBALS['phpgw']->template->set_var('cc_css',$cc_css_file);
			$GLOBALS['phpgw']->template->set_var('cc_dtree_css', $cc_dtree_file);
			$GLOBALS['phpgw']->template->set_var('cc_card_image',$cc_card_image_file);
			
			$GLOBALS['phpgw']->template->set_var('cc_personal',lang('Personal'));
			
			$GLOBALS['phpgw']->template->set_var('cc_full_add',lang('Full Add'));
			$GLOBALS['phpgw']->template->set_var('cc_reset',lang('Reset'));
			
			$GLOBALS['phpgw']->template->set_var('cc_personal_data',lang('Personal Data'));
			$GLOBALS['phpgw']->template->set_var('cc_addresses',lang('Addresses'));
			$GLOBALS['phpgw']->template->set_var('cc_connections',lang('Connections'));
			$GLOBALS['phpgw']->template->set_var('cc_relations',lang('Relations'));

			$GLOBALS['phpgw']->template->set_var('cc_quick_add',lang('Quick Add'));
			$GLOBALS['phpgw']->template->set_var('cc_catalogs',lang('Catalogues'));
			
			/* Panel */
			$GLOBALS['phpgw']->template->set_var('cc_panel_new',lang('New').'...');
			$GLOBALS['phpgw']->template->set_var('cc_panel_search',lang('Search').'...');
			$GLOBALS['phpgw']->template->set_var('cc_panel_table',lang('Table View'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_cards',lang('Cards View'));
			
			$GLOBALS['phpgw']->template->set_var('cc_panel_search_found',lang('Showing found entries'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_first_page',lang('First Page'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_previous_page',lang('Previous Page'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_next_page',lang('Next Page'));
			$GLOBALS['phpgw']->template->set_var('cc_panel_last_page',lang('Last Page'));
			$GLOBALS['phpgw']->template->set_var('cc_all',lang('all'));
			/* End Panel */
			
			/* Messages */
			$GLOBALS['phpgw']->template->set_var('cc_msg_no_cards',lang('No Cards'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_err_no_room',lang('No Room for Cards! Increase your browser area.'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_card_new',lang('New from same Company'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_card_edit',lang('Edit Contact'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_card_remove',lang('Remove Contact'));
			$GLOBALS['phpgw']->template->set_var('cc_msg_card_remove_confirm',lang('Confirm Removal of this Contact?'));
			/* End Messages */
			
			$GLOBALS['phpgw']->template->set_var('cc_results',lang('Results'));
			$GLOBALS['phpgw']->template->set_var('cc_is_my',lang('Is My'));
			$GLOBALS['phpgw']->template->set_var('cc_btn_search',lang('Search'));
			$GLOBALS['phpgw']->template->set_var('cc_add_relation',lang('Add Relation'));
			$GLOBALS['phpgw']->template->set_var('cc_del_relation',lang('Remove Selected Relations'));
			
			$GLOBALS['phpgw']->template->set_var('cc_contact_title',lang('Contact Center').' - '.lang('Contacts'));
			$GLOBALS['phpgw']->template->set_var('cc_window_views_title',lang('Contact Center').' - '.lang('Views'));
			$GLOBALS['phpgw']->template->set_var('phpgw_img_dir', $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/images');
			
			$GLOBALS['phpgw']->template->parse('out','index');
			
			$api = CreateObject('contactcenter.ui_api');
			$main = $api->get_people_full_add();
			$main .= $api->get_search_obj();
			$main .= $api->get_quick_add_plugin();
			$main .= $GLOBALS['phpgw']->template->get_var('out');

			echo $main;
		}

		
		/*!
		
			@function data_manager
			@abstract Calls the right method and passes to it the right 
				parameters
			@author Raphael Derosso Pereira
		
		*/
		function data_manager()
		{
			switch($_GET['method'])
			{
				/* Cards Methods */
				case 'set_n_cards':
					return $this->set_n_cards((int)$_GET['ncards']);
					
				case 'get_cards_data':
					return $this->get_cards_data($_POST['letter'], $_POST['page'], unserialize(str_replace('\\"','"',$_POST['ids'])));
				case 'get_cards_data_get':
					return $this->get_cards_data($_GET['letter'], $_GET['page'], unserialize(str_replace('\\"','"',$_GET['ids'])));


				case 'get_photo':
					return $this->get_photo($_GET['id']);

					
				/* Catalog Methods */
				case 'set_catalog':
					return $this->set_catalog($_GET['catalog']);
					
				case 'get_catalog_tree':
					echo serialize($this->get_catalog_tree($_GET['level']));
					return;

				case 'get_actual_catalog':
					echo serialize($this->get_actual_catalog());
					return;
					
					
				/* Full Add Methods */
				case 'get_full_data':
					return $this->get_full_data($_GET['id']);
					
				case 'get_contact_full_add_const':
					return $this->get_contact_full_add_const();

				case 'post_full_add':
					return $this->post_full_add();

				case 'post_photo':
					return $this->post_photo((int) $_GET['id'] ? (int) $_GET['id'] : '_new_');

				case 'get_states':
					return $this->get_states($_GET['country']);
					
				case 'get_cities':
					return $this->get_cities($_GET['country'], $_GET['state'] ? $_GET['state'] : null);
					
					
				/* Other Methods */
				case 'quick_add':															
					return $this->quick_add($_POST['add']);
					
				case 'remove_entry':
					return $this->remove_entry((int)$_GET['remove']);

				case 'search':
					return $this->search(str_replace('\\"', '"', $_GET['data']));

				case 'email_win':
					$GLOBALS['phpgw']->common->phpgw_header();
					$api = CreateObject('contactcenter.ui_api');
					$win = $api->get_email_win();
					$win .= $api->get_quick_add_plugin();
					$win .= '<input id="QAbutton" type="button" value="QuickAdd" />'
						.'<br><input type="button" value="EmailWin" onclick="ccEmailWin.open()" />'
						.'<script type="text/javascript">'
						.'	ccQuickAdd.associateAsButton(Element("QAbutton"));'
						.'</script>';
					echo $win;
					return;

				/* Information Gathering */
				case 'get_multiple_entries':
					echo serialize($this->get_multiple_entries(str_replace('\\"','"',$_POST['data'])));
					return;

				case 'get_all_entries':
					echo serialize($this->get_all_entries(str_replace('\\"','"',$_POST['data'])));
					return;
			}
		}

		/*!
		
			@function set_n_cards
			@abstract Informs the class the number of cards the page can show
			@author Raphael Derosso Pereira
			
			@param integer $n_cards The number of cards
			
		*/
		function set_n_cards($n_cards)
		{
			if (is_int($n_cards))
			{
				$this->page_info['n_cards'] = $n_cards;
				echo 1;
			}
			
			$this->save_session();
		}
				
		/*!
		
			@function set_catalog
			@abstract Sets the current catalog selected by the user
			@author Raphael Derosso Pereira
			
			@param string $id_catalog The sequence of IDs to reach the catalog
				separated by commas
		
		*/
		function set_catalog($id_catalog)
		{
			$id_catalog = str_replace('\\"', '"', $id_catalog);
			$temp =& $this->bo->set_catalog($id_catalog);
			
			if ($temp)
			{
				$this->page_info['changed'] = true;
				$this->page_info['actual_entries'] = false;
				$this->page_info['actual_catalog'] =& $temp;
				$this->save_session();

				$catalog_info = $this->bo->get_branch_by_level($this->bo->catalog_level[0]);
				
				if ($catalog_info['class'] === 'bo_global_ldap_catalog' ||
				    $catalog_info['class'] === 'bo_catalog_group_catalog')
				{
					$perms = 1;
				}
				else
				{
					$perms = 15;
				}
				
				echo serialize(array(
					'status' => 'ok',
					'perms'  => $perms
				));

				return;
			}
			
			echo serialize(array(
				'status' => 'ok',
				'perms'  => 0
			));
		}
		
		
		/*!
		
			@function get_catalog_tree
			@abstract Returns the JS serialized array to used as the tree
				level
			@author Raphael Derosso Pereira
			
			@param (string) $level The level to be taken 
		
		*/
		function get_catalog_tree($level)
		{
			if ($level === '0')
			{
				$folderImageDir = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/dftree/images/';

				$parent = '0';
				
				if (!($tree = $this->bo->get_catalog_tree($level)))
				{
					return array(
						'msg'    => lang('Couldn\'t get the Catalogue Tree. Please contact the Administrator.'),
						'status' => 'fatal'
					);
				}
			}
			else
			{
				$last_dot = strrpos($level,'.');
				$parent = substr($level, 0, $last_dot);
				$child = substr($level, $last_dot+1, strlen($level));
			
				if (!($tree[$child] = $this->bo->get_catalog_tree($level)))
				{
					return array(
						'msg'    => lang('Couldn\'t get the Catalogue Tree. Please contact the Administrator.'),
						'status' => 'fatal'
					);
				}
			}
			
			$folderImageDir = $GLOBALS['phpgw']->common->image('contactcenter','globalcatalog-mini.png');
			$folderImageDir = substr($folderImageDir, 0, strpos($folderImageDir, 'globalcatalog-mini.png'));
			
			$tree_js = $this->convert_tree($tree, $folderImageDir, $parent);
			
			return array(
				'data' => $tree_js,
				'msg'  => lang('Catalog Tree Successfully taken!'),
				'status' => 'ok'
			);
		}
		
/*		function get_catalog_tree($level, $name = 'tree')
		{
			if ($level === '0')
			{
				$folderImageDir = $GLOBALS['phpgw_info']['server']['webserver_url'] . '/phpgwapi/dftree/images/';
				$tree_js =  $name." = new dFTree({'name': '".$name."'});\n";

				$parent = '0';
				$child  = '0';
				
				if (!($tree = $this->bo->get_catalog_tree($level)))
				{
					return '0';
				}
			}
			else
			{
				$last_dot = strrpos($level,'.');
				$parent = substr($level, 0, $last_dot);
				$child = substr($level, $last_dot+1, strlen($level));
			
				$tree_js = '';
				
				if (!($tree[$child] = $this->bo->get_catalog_tree($level)))
				{
					return '0';
				}
			}
			
			$folderImageDir = $GLOBALS['phpgw']->common->image('contactcenter','globalcatalog-mini.png');
			$folderImageDir = substr($folderImageDir, 0, strpos($folderImageDir, 'globalcatalog-mini.png'));
			
			$tree_js .= $this->convert_tree($tree, $name, $folderImageDir, $parent);
			
			return $tree_js;
		}
*/		
		
		/*!
		
			@function get_actual_catalog
			@abstract Returns the actual selected Catalog
			@author Raphael Derosso Pereira

		*/
		function get_actual_catalog()
		{
			$level = $this->bo->get_level_by_branch($this->bo->get_actual_catalog(), $this->bo->tree['branches'], '0');

			if ($level)
			{
				return array(
					'status' => 'ok',
					'data'   => $level
				);
			}

			return array(
				'status' => 'fatal',
				'msg'    => lang('Couldn\'t get the actual catalog.'),
			);
		}
		
		/*!
		
			@function get_cards_data
			@abstract Returns the information that is placed on the cards
			@author Raphael Derosso Pereira
			
			@param string $letter The first letter to be searched
			@param (int)  $page The page to be taken 
			@param (str)  $ids The ids to be taken in case of search

			TODO: This function is not well done. It must be rewritten
				using the new array 'msg','status','data' schema.
		*/
		function get_cards_data($letter, $page, $ids)
		{
			//echo $page."\n";
			if ($letter !== 'search' and ($letter != $this->page_info['actual_letter'] or
			    ($letter == $this->page_info['actual_letter'] and $page == $this->page_info['actual_page']) or 
			    $this->page_info['changed']))
			{
				unset($ids);
				$this->page_info['changed'] = false;
				switch ($this->page_info['actual_catalog']['class'])
				{
					case 'bo_people_catalog':
						$field_name = 'id_contact';

						if ($letter !== 'number')
						{
							$find_restric[0] = array(
								0 => array(
									'field' => 'contact.names_ordered',
									'type'  => 'iLIKE',
									'value' => $letter !== 'all' ? $letter.'%' : '%'
								),
								1 => array(
									'field' => 'contact.id_owner',
									'type'  => '=',
									'value' => $GLOBALS['phpgw_info']['user']['account_id']
								)
							);
						}
						else
						{
							$find_restric[0] = array(
								0 => array(
									'type'  => 'branch',
									'value' => 'OR',
									'sub_branch' => array(
										0 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '0%'
										),
										1 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '1%'
										),
										2 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '2%'
										),
										3 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '3%'
										),
										4 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '4%'
										),
										5 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '5%'
										),
										6 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '6%'
										),
										7 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '7%'
										),
										8 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '8%'
										),
										9 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '9%'
										),
									),
								),
								1 => array(
									'field' => 'contact.id_owner',
									'type'  => '=',
									'value' => $GLOBALS['phpgw_info']['user']['account_id']
								),
							);
						}
						
						$find_field[0] = array('contact.id_contact','contact.names_ordered');
						
						$find_other[0] = array(
							//'offset' => (($page-1)*$this->page_info['n_cards']),
							//'limit'  => $this->page_info['n_cards'],
							'order'  => 'contact.names_ordered'
						);
						
						break;
					
					case 'bo_global_ldap_catalog':
						$field_name = 'id_contact';

						if ($letter !== 'number')
						{
							$find_restric[0] = array(
								0 => array(
									'field' => 'contact.names_ordered',
									'type'  => 'iLIKE',
									'value' => $letter !== 'all' ? $letter.'%' : '%'
								)
							);
						}
						else
						{
							$find_restric[0] = array(
								0 => array(
									'type'  => 'branch',
									'value' => 'OR',
									'sub_branch' => array(
										0 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '0%'
										),
										1 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '1%'
										),
										2 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '2%'
										),
										3 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '3%'
										),
										4 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '4%'
										),
										5 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '5%'
										),
										6 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '6%'
										),
										7 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '7%'
										),
										8 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '8%'
										),
										9 => array(
											'field' => 'contact.names_ordered',
											'type'  => 'LIKE',
											'value' => '9%'
										),
									),
								),
							);
						}
						
						$find_field[0] = array('contact.id_contact','contact.names_ordered');
						
						$find_other[0] = array(
							//'offset' => (($page-1)*$this->page_info['n_cards']),
							//'limit'  => $this->page_info['n_cards'],
							'order'  => 'contact.names_ordered'
						);
						
						break;
					
					case 'bo_company_manager':
						$field_name = 'id_company';
	
						$find_field[0] = array('company.id_company','company.company_name');
						
						$find_other[0] = array(
							//'offset' => (($page-1)*$this->page_info['n_cards']),
							//'limit'  => $this->page_info['n_cards'],
							'order'  => 'company.company_name'
						);
	
						$find_restric[0] = array(
							0 => array(
								'field' => 'company.company_name',
								'type'  => 'iLIKE',
								'value' => $letter !== 'all' ? $letter.'%' : '%'
							)
						);
							
						break;

					case 'bo_catalog_group_catalog':
						$this->page_info['actual_entries'] = false;
						
						$this->page_info['actual_letter'] = $letter;
						$this->page_info['actual_page'] = 0;
						
						$this->save_session();
							
						echo 0;
						return;

				}
				
				$result = $this->bo->find($find_field[0],$find_restric[0],$find_other[0]);
				$n_entries = count($result);
				
				if ($n_entries)
				{
					//echo 'N_entries: '.$n_entries.'<br>';
					$this->page_info['n_pages'] = ceil($n_entries/($this->page_info['n_cards'] ? $this->page_info['n_cards'] : 1));
				}
				else
				{
					$this->page_info['n_pages'] = 0;
				}

				if (!$result)
				{
					$this->page_info['actual_entries'] = false;
					
					$this->page_info['actual_letter'] = $letter;
					$this->page_info['actual_page'] = 0;
					
					$this->save_session();
						
					echo 0;
					return;
				}
				else
				{
					unset($this->page_info['actual_entries']);
					foreach ($result as $id => $value)
					{
						$this->page_info['actual_entries'][] = $value[$field_name];
					} 
					//print_r($this->page_info['actual_entries']);
				}
			}
			else if ($letter === 'search')
			{
				if (!$ids and $this->page_info['actual_letter'] !== 'search')
				{
					$this->page_info['actual_entries'] = false;
					
					$this->page_info['actual_letter'] = $letter;
					$this->page_info['actual_page'] = 0;
					
					$this->save_session();
						
					echo 0;
					return;
				}
				else if ($ids)
				{				
					$this->page_info['actual_letter']  = $letter;
					$this->page_info['actual_entries'] = $ids;
					$this->page_info['n_pages'] = ceil(count($ids)/($this->page_info['n_cards'] ? $this->page_info['n_cards'] : 1));
				}
			}
			else
			{
				unset($ids);
			}

			if ($this->page_info['actual_entries'])
			{
				if ($page >= $this->page_info['n_pages'])
				{
					$page = $this->page_info['n_pages'];
				}
				
				$final = array(
					0 => (int)$this->page_info['n_pages'],
					1 => (int)$page,
					2 => array(
						0 => 'cc_company',
						1 => 'cc_name',
						2 => 'cc_title',
						3 => 'cc_phone',
						4 => 'cc_mail',
						5 => 'cc_alias',
						6 => 'cc_id',
					)
				);
				
				//echo 'Page: '.$page.'<br>';
				$id_i = (($page-1)*$this->page_info['n_cards']);
				$id_f = $id_i + $this->page_info['n_cards'];
				$n_entries = count($this->page_info['actual_entries']);
				
				//echo 'ID_I: '.$id_i.'<br>';
				//echo 'ID_F: '.$id_f.'<br>';
				$ids = array();
				for($i = $id_i; $i < $id_f and $i < $n_entries; $i++)
				{
					$ids[] = $this->page_info['actual_entries'][$i];
				}
				
				$fields = $this->bo->catalog->get_fields(false);
				$fields['photo'] = true;
				$fields['names_ordered'] = true;
				$fields['alias'] = true;
				$fields['companies'] = 'default';
				$fields['connections'] = 'default';
				$contacts =& $this->bo->catalog->get_multiple_entries($ids,$fields);
				
				if (!is_array($contacts) or !count($contacts))
				{
					echo 0;
					return;
				}
				
				/* Select the correct Email and Telephone to be shown */
				$conns_types = ExecMethod('phpgwapi.config.read_repository', 'contactcenter');

				if (!is_array($conns_types) and !$conns_types['cc_people_email'])
				{
					$GLOBALS['phpgw']->exit('Default Connections Types Not Configured. Call Administrator!');
				}
			
				$i = 0;
				foreach($contacts as $contact)
				{
					$final[3][$i][0] = $contact['companies']['company1']['company_name'] ? $contact['companies']['company1']['company_name']:'none';
					$final[3][$i][1] = $contact['names_ordered'] ? $contact['names_ordered'] : 'none';
					$final[3][$i][2] = $contact['companies']['company1']['title']?$contact['companies']['company1']['title']:'none';

					if ($contact['connections'])
					{
						$default_email_found = false;
						$default_phone_found = false;
						foreach($contact['connections'] as $conn_info)
						{
							if ($conn_info['id_type'] == $conns_types['cc_people_email'] and !$default_email_found)
							{
								if ($conn_info['connection_is_default'])
								{
									$default_email_found = true;
								}
								$final[3][$i][4] = $conn_info['connection_value'] ? $conn_info['connection_value'] : 'none';
							}
							else if ($conn_info['id_type'] == $conns_types['cc_people_phone'] and !$default_phone_found)
							{
								if ($conn_info['connection_is_default'])
								{
									$default_phone_found = true;
								}
								$final[3][$i][3] = $conn_info['connection_value'] ? $conn_info['connection_value'] : 'none';
							}
						}
					}
					
					if (!$final[3][$i][3])
					{
						$final[3][$i][3] = 'none';
					}
					
					if (!$final[3][$i][4])
					{
						$final[3][$i][4] = 'none';
					}

					$final[3][$i][5] = $contact['alias']?$contact['alias']:'none';
					$final[3][$i][6] = $ids[$i];
					$final[4][$i] = $contact['photo'] ? 1 : 0;
					$i++;
				}
				
				$this->page_info['actual_letter'] = $letter;
				$this->page_info['actual_page'] = $page;
				
				$this->save_session();

//				print_r($final);
				echo serialize($final);
				return;
			}
			
			$this->page_info['actual_letter'] = $letter;
			$this->page_info['actual_page'] = $page;
			
			$this->save_session();
			
			echo 0;
		}
		
		/*!
		
			@function get_full_data
			@abstract Returns all the information of a given Entry
			@author Raphael Derosso Pereira
			
			@param (integer) $id The id to get information
		
		*/
		function get_full_data($id)
		{
			$dateformat = $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat'];
		
			$fields = $this->bo->catalog->get_fields(true);
			$fields['photo'] = false;
			$entry = $this->bo->catalog->get_single_entry($id,$fields);

			if (is_bool($entry['given_names']))
			{
				$data['result'] = 'false';
				echo serialize($data);
				return;
			}

			$date = explode('-', $entry['birthdate']);
			$j = 0;
			for ($i = 0; $i < 5; $i+=2)
			{
				switch($dateformat{$i})
				{
					case 'Y':
						$birthdate[$j] = $date[0];
						break;

					case 'm':
					case 'M':
						$birthdate[$j] = $date[1];
						break;

					case 'd':
						$birthdate[$j] = $date[2];
				}
				$j++;
			}
			$datecount = 0;
			
			$data['result'] = 'ok';
			$data['cc_full_add_contact_id'] = $id;

			/* Personal Data */
			$data['personal']['cc_pd_photo'] = '../index.php?menuaction=contactcenter.ui_data.data_manager&method=get_photo&id='.$id;
			$data['personal']['cc_pd_alias'] = $entry['alias'];
			$data['personal']['cc_pd_given_names'] = $entry['given_names'];
			$data['personal']['cc_pd_family_names'] = $entry['family_names'];
			$data['personal']['cc_pd_full_name'] = $entry['names_ordered'];
			$data['personal']['cc_pd_suffix'] = $entry['id_suffix'];
			$data['personal']['cc_pd_birthdate_0'] = $birthdate[0];
			$data['personal']['cc_pd_birthdate_1'] = $birthdate[1];
			$data['personal']['cc_pd_birthdate_2'] = $birthdate[2];
			$data['personal']['cc_pd_sex'] = $entry['sex'] === 'M' ? 1 : ($entry['sex'] === 'F' ? 2 : 0);
			$data['personal']['cc_pd_prefix'] = $entry['id_prefix'];
			$data['personal']['cc_pd_gpg_finger_print'] = $entry['pgp_key'];
			$data['personal']['cc_pd_notes'] = $entry['notes'];

			/* Addresses */
			if (is_array($entry['addresses']))
			{
				$data['addresses'] = $entry['addresses'];
			}

			/* Connections */
			if (is_array($entry['connections']))
			{
				$data['connections'] = array();
				foreach ($entry['connections'] as $connection)
				{
					$type = $connection['id_type'];
					$i = count($data['connections'][$type]);
					$data['connections'][$type][$i]['id'] = $connection['id_connection'];
					$data['connections'][$type][$i]['name'] = $connection['connection_name'];
					$data['connections'][$type][$i]['value'] = $connection['connection_value'];
					//TODO: defaultness
				}
			}
//			print_r($data);

			/* Relations */
			
			echo serialize($data);
		}

		/*!

			@function get_contact_full_add_const
			@abstract Returns all the constant fields in Contact Full Add Window to the JS
			@author Raphael Derosso Pereira
		*/
		function get_contact_full_add_const()
		{
			$data = array();
			$predata[] = $this->bo->catalog->get_all_prefixes();
			$predata[] = $this->bo->catalog->get_all_suffixes();
			$predata[] = $this->bo->catalog->get_all_addresses_types();
			$predata[] = $this->bo->catalog->get_all_countries();
			$predata[] = $this->bo->catalog->get_all_connections_types();
			//$predata[] = $this->bo->catalog->get_all_relations_types();

			$i = 0;
			foreach($predata as $data_)
			{
				if ($data_)
				{
					$data[$i] = $data_;
				}

				$i++;
			}

			if (count($data))
			{
				echo serialize($data);
				return;
			}
			
			echo 0;
		}
		
		/*!
		
			@function quick_add
			@abstract Adds a new Contact using the Quick Add interface
			@author Raphael Derosso Pereira
			
			@param string $sdata Serialized data
		*/
		function quick_add($sdata)
		{
			$sdata = str_replace('\\"', '"', $sdata);
			$tdata = unserialize($sdata);
			
			if (!$tdata)
			{
				echo serialize(array(
					'msg'    => lang('Problems on adding your Contact. Invalid Data came from client. No Contact added!'),
					'status' => 'abort'
				));
				
				return;
			}
			
			$data['alias'] = $tdata[0];
			$data['given_names'] = $tdata[1];
			$data['family_names'] = $tdata[2];
//			$data['connections']['default_phone']['id_typeof_connection'] = 1;
			$data['connections']['default_phone']['connection_name'] = lang('Main');
			$data['connections']['default_phone']['connection_value'] = $tdata[3];
//			$data['connections']['default_email']['id_typeof_connection'] = 2;
			$data['connections']['default_email']['connection_name'] = lang('Main');
			$data['connections']['default_email']['connection_value'] = $tdata[4];
			
			$id = $this->bo->catalog->quick_add($data);
			
			if ($id)
			{
				$this->page_info['changed'] = true;
				
				echo serialize(array(
					'msg'    => lang('Entry added with success!'),
					'status' => 'ok'
				));
			}
			else
			{
				echo serialize(array(
					'msg'    => lang('Problems on adding your Contact. No Contact added!'),
					'status' => 'error'
				));
			}

			$this->save_session();
		}
		
		/*!
		
			@function remove_entry
			@abstract Removes an entry if the user has the right to do it
			@author Raphael Derosso Pereira
			
			@param (integer) $id The id to be removed
				
		*/
		function remove_entry ($id)
		{
			if (!is_int($id))
			{
				echo lang('Couldn\'t remove entry! Problem passing data to the server. Please inform admin!');
				return;
			}
			
			$this->page_info['changed'] = true;
			$result = $this->bo->catalog->remove_single_entry($id);
			
			if ($result)
			{
				if ($pos = array_search($id, $this->page_info['actual_entries']))
				{
					unset($this->page_info['actual_entries'][$pos]);
				}
				
				$temp = false;
				reset($this->page_info['actual_entries']);
				foreach($this->page_info['actual_entries'] as $t)
				{
					$temp[] = $t;
				}
				
				$this->page_info['actual_entries'] = $temp;

				echo serialize(array(
					'msg'    => lang('Removed Entry ID '.$id.'!'),
					'status' => 'ok'
				));
			}
			else
			{
				echo serialize(array(
					'msg'    => lang('Couldn\'t remove this entry. Inform the Site Admin!'),
					'status' => 'fail'
				));
			}
			
			$this->save_session();
		}

		
		/*!
		
			@function post_full_add
			@abstract Saves all the information altered/entered in the Full Add
				window
			@author Raphael Derosso Pereira

		*/
		function post_full_add()
		{
			$data = unserialize(str_replace('\\"', '"', $_POST['data']));

//			echo str_replace('\\"', '"', $_POST['data']);
			
			if (!is_array($data))
			{
				echo serialize(array(
					'msg' => lang('<p>Some problem receiving data from browser. This is probably a bug in ContactCenter<br>'.
				                  'Please go to eGroupWare Bug Reporting page and report this bug.<br>'.
						          'Sorry for the inconvenient!<br><br>'.
						          '<b><i>ContactCenter Developer Team</i></b></p>'),
					'status' => 'fatal'
				));
				return;
			}
//			print_r($data);
//			echo '<br><br>';

			$replacer = $data['commercialAnd'];
			unset($data['commercialAnd']);
			if (!is_string($replacer) or strpos($replacer, "'") or strpos($replacer, '"'))
			{
				echo serialize(array(
					'msg' => lang('Invalid \'&\' replacer! This may be an attempt to bypass Security! Action aborted!'),
					'status' => 'fatal'
				));
				
				return;
			}

			if ($data['id_contact'])
			{
				$id = $data['id_contact'];
				$id_photo = $id;
				unset($data['id_contact']);
			}
			else
			{
				$id_photo = '_new_';
			}
			
			/* 
			 * Process Photo, if available 
			 */
			$sleep_count = 0;
			$photo_ok = $GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter');
			while($photo_ok[0]{0} !== 'o' and $photo_ok[1]{0} === 'y')
			{
				sleep(1);
				$photo_ok = $GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter');
				$sleep_count++;

				if ($sleep_count > 35)
				{
					// TODO
					return;
				}
			}
			$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('wait', 'n'));
			
			if (isset($this->page_info['photos'][$id_photo]))
			{
				if (array_search($this->page_info['photos'][$id_photo]['status'], array('changed', 'sync')) === false)
				{
					echo serialize(array(
						'msg' => $this->page_info['photos'][$id_photo]['msg'],
						'status' => $this->page_info['photos'][$id_photo]['status']
					));

					return;
				}

				$data['photo'] = $this->page_info['photos'][$id_photo]['content'];
				unset($this->page_info['photos'][$id_photo]);
				$this->save_session();
			}
			
			/*
			 * Arrange Date so it gets inserted correctly
			 */
			 
			$dateformat = $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat'];
		
			$j = 0;
			for ($i = 0; $i < 5; $i+=2)
			{
				switch($dateformat{$i})
				{
					case 'Y':
						$date[$j]['size'] = 4;
						$date[$j]['digit'] = 'Y';
						break;

					case 'm':
					case 'M':
						$date[$j]['size'] = 2;
						$date[$j]['digit'] = 'M';
						break;

					case 'd':
						$date[$j]['size'] = 2;
						$date[$j]['digit'] = 'D';
				}
				$j++;
			}
			$datecount = 0;

			/* Verify Data and performs insertion/update */
			foreach($data as $field => $value)
			{
				if ($value == '' or is_null($value))
				{
					unset($data[$field]);
					continue;
				}
				
				switch($field)
				{
					case 'alias':
					case 'given_names':
					case 'family_names':
					case 'names_ordered':
					case 'pgp_key':
					case 'notes':
					case 'photo':
						/* Do Nothing. This is just to make sure no invalid field is passed */
						break;
					
					case 'id_status':
					case 'id_prefix':
					case 'id_suffix':
						if ($data[$field] == 0)
						{
							unset($data[$field]);
						}
						break;
					
					case 'birthdate_0':
					case 'birthdate_1':
					case 'birthdate_2':
					
						switch($date[$datecount]['digit'])
						{
							case 'Y':
								$date['value'][2] = (int) $data[$field];
								break;

							case 'M':
								$date['value'][0] = (int) $data[$field];
								break;

							case 'D':
								$date['value'][1] = (int) $data[$field];
								break;
						}
						unset($data[$field]);
						$datecount++;

						if ($datecount != 3)
						{
							break;
						}
						
						if (!checkdate($date['value'][0], $date['value'][1], $date['value'][2]))
						{
							echo serialize(array(
								'msg' => lang('Invalid Date'),
								'status' => 'invalid_data'
							));
							return;
						}
						
						$data['birthdate'] = $date['value'][2].'-'.$date['value'][0].'-'.$date['value'][1];
						break;

					case 'sex':
						if ($data[$field] !== 'M' and $data[$field] !== 'F')
						{
							echo serialize(array(
								'msg' => lang('Invalid Sex'),
								'status' => 'invalid_data'
							));
							return;
						}
						break;


					case 'addresses':
						/* Insert new cities/states */
						if (isset($value['new_states']))
						{
							foreach($value['new_states'] as $type => $state_info)
							{
								$index = 'address'.$type;
								
								$id_state = $this->bo->catalog->add_state($state_info);
								$data['addresses'][$index]['id_state'] = $id_state;

								if ($value['new_cities'][$type])
								{
									$value['new_cities'][$type]['id_state'] = $id_state;
								}
							}

							unset($data['addresses']['new_states']);
						}

						if (isset($value['new_cities']))
						{
							foreach($value['new_cities'] as $type => $city_info)
							{
								$index = 'address'.$type;
								
								$id_city = $this->bo->catalog->add_city($city_info);
								$data['addresses'][$index]['id_city'] = $id_city;
							}

							unset($data['addresses']['new_cities']);
						}

					break;

					case 'connections':
						/* Does nothing... */
						break;

					default:
						echo serialize(array(
							'msg' => lang('Invalid field: ').$field,
							'status' => 'invalid_data'
						));
						return;
				}
			}

			$code = '$id = $this->bo->catalog->';

			if (!is_null($id) and $id !== '')
			{
				$code .= $code.'update_single_info($id, $data);';
				$result = array(
					'msg' => lang('Updated Successfully!'),
					'status' => 'ok'
				);
			}
			else
			{
				$code .= 'add_single_entry($data);';
				$result = array(
					'msg' => lang('Entry Added Successfully!'),
					'status' => 'ok'
				);
			}
			
			eval($code);

			if (!($id))
			{
				$result = array(
					'msg' => lang('Some problem occured when trying to insert/update contact information.<br>'.
				                   'Report the problem to the Administrator.'),
					'status' => 'fail'
				);
			}

			echo serialize($result);
		}

		/*!
		
			@function post_photo
			@abstract Wrapper to post a photo without reload a page.
			@author Raphael Derosso Pereira

		*/
		function post_photo($id)
		{
			//print_r($_FILES);
			$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('wait', 'y'));
			
			if (!is_array($_FILES) and is_array(!$_FILES['cc_pd_photo']))
			{
				$this->page_info['photos'][$id]['status'] = 'no_upload';
				$this->page_info['photos'][$id]['msg'] = lang('No Photos uploaded to Server.');
				
				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}

			if (!function_exists('imagecreate'))
			{
				$this->page_info['photos'][$id]['status'] = 'no_GD_lib';
				$this->page_info['photos'][$id]['msg'] = lang('Cannot manipulate Image. No Image added. Please, if you want to use images, ask the Administrator to install GD library.');
				
				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}

			// TODO: Get Max Size from preferences!
			if ($_FILES['cc_pd_photo']['size'] > 1000000)
			{
				$this->page_info['photos'][$id]['status'] = 'too_large';
				$this->page_info['photos'][$id]['msg'] = lang('Image too large! ContactCenter limits the image size to 1 Mb');

				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}

			if ($_FILES['cc_pd_photo']['error'])
			{
				$this->page_info['photos'][$id]['status'] = 'error';
				$this->page_info['photos'][$id]['msg'] = lang('Some Error occured while processed the Image. Contact the Administrator. The error code was: ').$_FILES['cc_pd_photo']['error'];

				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}
			
			switch($_FILES['cc_pd_photo']['type'])
			{
				case 'image/jpeg':
				case 'image/pjpeg':
					$src_img = imagecreatefromjpeg($_FILES['cc_pd_photo']['tmp_name']);
					if ($src_img == '')
					{
						$bogus = true;
					}
					break;

				case 'image/png':
				case 'image/x-png':
					$src_img = imagecreatefrompng($_FILES['cc_pd_photo']['tmp_name']);
					if ($src_img == '')
					{
						$bogus = true;
					}
					break;

				case 'image/gif':
					$src_img = imagecreatefromgif($_FILES['cc_pd_photo']['tmp_name']);
					if ($src_img == '')
					{
						$bogus = true;
					}
					break;

				default:
					
					$this->page_info['photos'][$id]['status'] = 'invalid_image';
					$this->page_info['photos'][$id]['msg'] = lang('The file must be an JPEG, PNG or GIF Image.');

					$this->save_session();
					$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
					return;
			}

			if ($bogus)
			{
					$this->page_info['photos'][$id]['status'] = 'invalid_file';
					$this->page_info['photos'][$id]['msg'] = lang('Couldn\'t open Image. It may be corrupted or internal library doesn\'t support this format.');
					
					$this->save_session();
					$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
					return;
			}
			
			$img_size = getimagesize($_FILES['cc_pd_photo']['tmp_name']);
			$dst_img = imagecreatetruecolor(60, 80);
			
			if (!imagecopyresized($dst_img, $src_img, 0, 0, 0, 0, 60, 80, $img_size[0], $img_size[1]))
			{
				$this->page_info['photos'][$id]['status'] = 'invalid_file';
				$this->page_info['photos'][$id]['msg'] = lang('Couldn\'t open Image. It may be corrupted or internal library doesn\'t support this format.');
				
				$this->save_session();
				$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));
				return;
			}
			
			ob_start();
			imagepng($dst_img);
			$this->page_info['photos'][$id]['content'] = ob_get_contents();
			ob_end_clean();

			$this->page_info['photos'][$id]['status'] = 'changed';
			$this->page_info['photos'][$id]['msg'] = lang('Photo Successfully Updated!');

			$this->save_session();
		
			$GLOBALS['phpgw']->session->appsession('ui_data.photo','contactcenter', array('ok', 'y'));

			imagedestroy($src_img);
			imagedestroy($dst_img);
			echo 'ok';
			return;
		}


		/*!

			@function get_photo
			@abstract Returns the photo to the browser
			@author Raphael Derosso Pereira

		*/
		function get_photo($id)
		{
			$fields = $this->bo->catalog->get_fields(false);
			$fields['photo'] = true;
			
			$contact = $this->bo->catalog->get_single_entry($id, $fields);

			if (!$contact['photo'])
			{
				header('Content-type: image/png');
				echo file_get_contents(PHPGW_INCLUDE_ROOT.'/contactcenter/templates/default/images/photo.png');
				return;
			}

			header('Content-type: image/png');
			echo $contact['photo'];
			return;
		}

		
		/*!
		
			@function get_states
			@abstract Echos a serialized array containing all the states for the given country
			@author Raphael Derosso Pereira

			@params $id_country The ID of the Country that contains the requested states

		*/
		function get_states($id_country)
		{
			$states = $this->bo->catalog->get_all_states($id_country);

			if (!$states)
			{
				$result = array(
					'msg'    => lang('No States found for this Country.'),
					'status' => 'empty'
				);

				echo serialize($result);
				return;
			}
			
			$result = array(
				'msg'    => lang('States Successfully retrieved!'),
				'status' => 'ok'
			);
			
			foreach ($states as $state_info)
			{
				$result['data'][$state_info['id_state']] = $state_info['name'];

				if ($state_info['symbol'])
				{
					$result['data'][$state_info['id_state']] .= ', '.$state_info['symbol'];
				}
			}

			echo serialize($result);
		}

		/*!

			@function get_cities
			@abstract Echos a serialized array containing all the cities of a given state
			@author Raphael Derosso Pereira

			@param $id_country The ID of the Country that has the specified Cities (in case the
				Country doesn't have any States)
			@param $id_state The ID of the State that has the Cities requested

		*/
		function get_cities($id_country, $id_state=false)
		{
			$cities = $this->bo->catalog->get_all_cities($id_country, $id_state);

			if (!$cities)
			{
				$result = array(
					'msg'    => lang('No Cities found for this State.'),
					'status' => 'empty'
				);

				echo serialize($result);
				return;
			}
			
			$result = array(
				'msg'    => lang('Cities Successfully retrieved!'),
				'status' => 'ok'
			);
			
			foreach ($cities as $city_info)
			{
				$result['data'][$city_info['id_city']] = $city_info['name'];
			}
			
			echo serialize($result);
		}


		/*!
		
			@function search
			@abstract Echos a serialized array containing the IDs
				of the entries that matches the search argument
			@author Raphael Derosso Pereira

			@param string $str_data A serialized array with two informations:
				$data = array(
					'search_for' => (string),
					'recursive'  => (boolean),
				);

		*/
		function search($str_data)
		{
			$data = unserialize($str_data);

			if (!is_array($data) || !$data['search_for'] || !is_array($data['fields']))
			{
				echo serialize(array(
					'msg'    => lang('Invalid parameters'),
					'status' => 'abort'
				));

				return;
			}

			$rules  = array();

			if ($data['search_for'] === '*')
			{
				$rules = array(
					0 => array(
						'field' => $data['fields']['search'],
						'type'  => 'LIKE',
						'value' => '%'
					)
				);
			}
			else
			{
				$names = explode(' ', $data['search_for']);

				if (!is_array($names))
				{
					echo serialize(array(
						'msg'    => lang('Invalid Search Parameter'),
						'status' => 'abort'
					));
					
					return;
				}
				
				foreach ($names as $name)
				{
					if ($name != '')
					{
						array_push($rules, array(
							'field' => $data['fields']['search'],
							'type'  => 'iLIKE',
							'value' => '%'.$name.'%'
						));
					}
				}
			}
			
			/*
			$catalog = $this->bo->get_branch_by_level($this->bo->catalog_level[0]);
			
			if ($catalog['class'] === 'bo_people_catalog')
			{
				array_push($rules, array(
					'field' => 'contact.id_owner',
					'type'  => '=',
					'value' => $GLOBALS['phpgw_info']['user']['account_id']
				));
			}
			*/
			
			$ids = $this->bo->find(array($data['fields']['id'], $data['fields']['search']), $rules, array('order' => $data['fields']['search'], 'sort' => 'ASC'));

			if (!is_array($ids) || !count($ids))
			{
				echo serialize(array(
					'msg'    => lang('No Entries Found!'),
					'status' => 'empty'
				));

				return;
			}

			$id_field = substr($data['fields']['id'], strrpos($data['fields']['id'], '.')+1);

			$ids_f = array();
			foreach ($ids as $e_info)
			{
				$ids_f[] = $e_info[$id_field];
			}
			
			echo serialize(array(
				'data'   => $ids_f,
				'msg'    => lang('Found %1 Entries', count($ids)),
				'status' => 'ok'
			));
		}

		/*!

			@function get_multiple_entries
			@abstract Returns an array containing the specifiend data in the default
				CC UI format
			@author Raphael Derosso Pereira

			@param array str_data A serialized array containing the ID's of the entries 
				to be taken, the fields to be taken and the rules to be used on the
				retrieval:
				$data = array(
					'ids'    => array(...),
					'fields' => array(...),
					'rules'  => array(...)
				);

		*/
		function get_multiple_entries($str_data)
		{
			$data = unserialize($str_data);
			
			if (!is_array($data) or !count($data) or !count($data['fields']) or !count($data['ids']))
			{
				return array(
					'msg'    => lang('Invalid Parameters'),
					'status' => 'abort'
				);
			}

			$entries = $this->bo->catalog->get_multiple_entries($data['ids'], $data['fields']);
			
			if (!is_array($entries) or !count($entries))
			{
				return array(
					'msg'    => lang('No Entries Found!'),
					'status' => 'empty'
				);
			}

			return array(
				'msg'    => lang('Found %1 Entries!', count($entries)),
				'status' => 'ok',
				'data'   => $entries
			);
		}

		/*

			@function get_all_entries
			@abstract Returns the specified fields for all catalog's entries 
				in the default CC UI format
			@author Raphael Derosso Pereira

			@params array str_data A serialized array containing the fields to 
				be grabbed, the maximum number of entries to be returned and a
				boolean specifying if the calls refers to a new grab or to an
				unfinished one.

		*/
		function get_all_entries($str_data)
		{
			$data = unserialize($str_data);
			
			if (!is_array($data) or 
			    !count($data) or 
				!count($data['fields']) or 
				!$data['maxlength'] or
				(!$data['new'] and !$data['offset']))
			{
				return array(
					'msg'    => lang('Invalid Parameters'),
					'status' => 'abort'
				);
			}

			if ($data['new'])
			{
				$this->all_entries = $this->bo->catalog->get_all_entries_ids();

				$this->save_session();

				if (!is_array($this->all_entries) or !count($this->all_entries))
				{
					return array(
						'msg'    => lang('No Entries Found!'),
						'status' => 'empty'
					);
				}

				$data['offset'] = 0;
			}
			
			if ($data['maxlength'] != -1)
			{
				$result = $this->bo->catalog->get_multiple_entries(array_slice($this->all_entries, $data['offset'], $data['maxlength']), $data['fields']);
			}
			else
			{
				$result = $this->bo->catalog->get_multiple_entries($this->all_entries, $data['fields']);
			}

			/* Select the correct Email and Telephone to be shown */
			$conns_types = ExecMethod('phpgwapi.config.read_repository', 'contactcenter');

			if (!is_array($conns_types) and !$conns_types['cc_people_email'])
			{
				$GLOBALS['phpgw']->exit('Default Connections Types Not Configured. Call Administrator!');
			}
			
			$jsCode = array();
			$count = 0;
			foreach ($result as $each)
			{
				if (!is_array($each))
				{
					continue;
				}

				foreach ($each as $field => $value)
				{
					if ($field === 'names_ordered')
					{
						$name = '\\"'.$value.'\\"';
					}
					else if ($field === 'connections')
					{
						foreach ($value as $connection)
						{
							if ($connection['id_type'] == $conns_types['cc_people_email'])
							{
								$jsCode[] = '_this.entries.options[_this.entries.options.length] = new Option("'.$name.' <'.$connection['connection_value'].'>", "'.$count.'");';
								$count++;
							}
						}
					}
				}
			}

			$jsCodeFinal = implode("\n", $jsCode);
			
			$nEntries = count($result);
			
			if (!$nEntries)
			{
				return array(
					'msg'    => lang('Error while getting user information...'),
					'status' => 'abort'
				);
			}

			return array(
				'msg'      => lang('Found %1 Entries!', $nEntries),
				'status'   => 'ok',
				'final'    => $nEntries + $data['offset'] < count($this->all_entries) ? false : true,
				'offset'   => $data['offset'] + $nEntries,
				'data'     => $jsCodeFinal
			);
		}
		
		/*********************************************************************\
		 *                      Auxiliar Methods                             *
		\*********************************************************************/

		/*!
		
			@function save_session
			@abstract Saves the data on the session
			@author Raphael Derosso Pereira
		
		*/
		function save_session()
		{
			$GLOBALS['phpgw']->session->appsession('ui_data.page_info','contactcenter',$this->page_info);
			$GLOBALS['phpgw']->session->appsession('ui_data.all_entries','contactcenter',$this->all_entries);
		}

		/*!
		
			@function convert_tree
			@abstract Converts the tree array in the BO format to a JS tree array compatible
				with the one available in eGW
			@author Raphael Derosso Pereira
		
			@param (array)  $tree    The tree in the BO format
			@param (string) $name    The tree name
			@param (string) $iconDir The dir where the icons are
			@param (string) $parent  The parent
		*/

		function convert_tree($tree, &$iconDir, $parent='0')
		{
//			echo "Entrou<br>\tPai: $parent <br>";
			$rtree = array();

			if ($parent === '0')
			{
//				echo 'Root!<br>';
				$rtree['0'] = array(
					'type'       => 'catalog_group',
					'id'         => '0',
					'pid'        => 'none',
					'caption'    => lang('Catalogues'),
					'class'      => 'bo_catalog_group_catalog',
					'class_args' => array('_ROOT_', '$this', '$this->get_branch_by_level($this->catalog_level[0])')
				);
			}

			foreach($tree as $id => $value)
			{
//				echo 'ID: '.$id.'<br>';
				$rtree[$parent.'.'.$id] = array(
					'type'    => $value['type'],
					'id'      => $parent.'.'.$id,
					'pid'     => $parent,
					'caption' => $value['name']
				);
				
				switch($value['type'])
				{
					case 'catalog_group':
					case 'mixed_catalog_group':
						$rtree = $rtree + $this->convert_tree($value['sub_branch'],$iconDir,$parent.'.'.$id);
						break;
				}
			}

			if (count($rtree))
			{
				return $rtree;
			}
		}
		
	
/*
		function convert_tree($tree, $name, &$iconDir, $parent='0')
		{
			$new = null;
			$code = null;
			
			if ($parent === '0')
			{

				$code .= $name.".add(new dNode({id:'0', caption: '".lang('Catalogues')."'}),'none');\n";
			}
			
			foreach ($tree as $id => $value)
			{
				$title = $value['name'];

				switch ($value['type'])
				{					
					case 'unknown':
						$code .= $name.".add(new dNode({id: '{$parent}.{$id}', caption:'{$value['name']}', onFirstOpen: 'getCatalogTree(\\'{$parent}.{$id}\\');', onClick: 'getCatalogTree(\\'{$parent}.{$id}\\'); waitForTree(\\'{$parent}.{$id}\\', 0)'}),'$parent');\n"; 
						break;
					
					case 'catalog_group':
						$code .= $name.".add(new dNode({id: '{$parent}.{$id}', caption: '{$value['name']}'}),'$parent');\n"; 
						$code .= $this->convert_tree($value['sub_branch'],$name,$iconDir,$parent.'.'.$id);
						break;

					case 'mixed_catalog_group':
						$code .= $name.".add(new dNode({id: '{$parent}.{$id}', caption: '{$value['name']}', onClick: 'setCatalog(\\'{$parent}.{$id}\\')'}),'$parent');\n";

						$code .= $this->convert_tree($value['sub_branch'],$name,$iconDir,$parent.'.'.$id);
						break;
					
					case 'catalog':
						$code .= $name.".add(new dNode({id: '{$parent}.{$id}', caption: '{$value['name']}', onClick: 'setCatalog(\\'{$parent}.{$id}\\')'}),'$parent');\n";
						
				}
			}
			
			return $code;
		}
*/
	}

?>
