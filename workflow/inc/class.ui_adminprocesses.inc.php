<?php

	require_once(dirname(__FILE__) . SEP . 'class.bo_workflow_forms.inc.php');

	class ui_adminprocesses extends bo_workflow_forms
	{

		var $public_functions = array(
			'form'	=> true,
		);

		var $process_manager;

		var $activity_manager;

		var $filter_active;
		
		var $process_config=array();

		function ui_adminprocesses()
		{
			parent::bo_workflow_forms('admin_processes');

		       //regis: acl check
			if ( !(($GLOBALS['phpgw']->acl->check('run',1,'admin')) || ($GLOBALS['phpgw']->acl->check('admin_workflow',1,'workflow'))) )
			{
				$GLOBALS['phpgw']->common->phpgw_header();
				echo parse_navbar();
				echo lang('access not permitted');
				$GLOBALS['phpgw']->log->message('F-Abort, Unauthorized access to workflow.ui_adminprocesses');
				$GLOBALS['phpgw']->log->commit();
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$this->process_manager	= CreateObject('workflow.workflow_processmanager');
			$this->activity_manager	= CreateObject('workflow.workflow_activitymanager');
			
		}

		/**
		* Shows and processes process form
		* Fields in the database are in the form 'wf_field', whereas in the form just 'field'
		*
		* @author	Alejandro Pedraza, Regis Leroy, Michael Bartz
		* @access	public
		*/
		function form()
		{
			//$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['workflow']['title'] . ' - ' . lang('Admin Processes');
			//$GLOBALS['phpgw']->common->phpgw_header();
			//echo parse_navbar();
			//$this->t->set_file('admin_processes', 'admin_processes.tpl');
			$this->t->set_block('admin_processes', 'block_items', 'items');

			$name			= get_var('name', 'POST', '');
			$description		= get_var('description', 'POST', '');
			$version		= get_var('version', 'POST', '');
			$is_active		= get_var('isActive', 'POST', '');
			$filter			= get_var('filter', 'any', '');
			$this->filter_active	= get_var('filter_active', 'any', '');
			$newminor		= get_var('newminor', 'GET', 0);
			$newmajor		= get_var('newmajor', 'GET', 0);
			//overwrite default sort order
			$this->order		= get_var('order', 'any', 'wf_last_modif');
			$this->sort		= get_var('sort', 'any', 'desc');
			$this->sort_mode	= $this->order . '__'. $this->sort;
			//retrieve config_values POSTed by the form

			// filtering options
			$where_str = '';
			$wheres = array();

			if ($this->filter_active)
			{
				$wheres[] = " wf_is_active='". $this->filter_active ."'";
			}
			$where_str = implode('and', $wheres);
			$this->link_data = array(
				'filter_active'	=> $this->filter_active,
				'search_str'	=> $this->search_str,
				'offset'	=> $this->offset,
				'start'		=> $this->start,
			);

			// we set an array with all config values and titles we know
			// this will serve to show config values and to save them
			$known_config_items= array(
						'Running activities options'		=> 'title',
						'display_please_wait_message'		=> 'yesno',
						'use_automatic_parsing'		 	=> 'yesno',
						'show_activity_title' 			=> 'yesno',
						'show_instance_name'			=> 'yesno',
						'show_instance_owner'			=> 'yesno',
						'show_multiple_submit_as_select'	=> 'yesno',
						'show_activity_info_zone'		=> 'yesno',
						'Graphic options'			=> 'title',
						'draw_roles'				=> 'yesno',
						'font_size'				=> 'text',
						'Actions Right Options '		=> 'title',
						'ownership_give_abort_right'            => 'yesno',
						'ownership_give_exception_right'        => 'yesno',
						'ownership_give_release_right'          => 'yesno',
						'role_give_abort_right'                 => 'yesno',
						'role_give_release_right'               => 'yesno',
						'role_give_exception_right'             => 'yesno',
			);

			if( isset($_POST['upload']))
			{
				if ($_FILES['userfile1']['size'] == 0)
				{ 
					die("Bad upload!"); 
				}
				
				if ($_FILES['userfile1']['type'] != "text/xml") { die("Invalid file format!"); } 				
				//if ($_FILES['userfile1']['type'] != "text/xml" && $_FILES['file']['type'] != "image/jpeg" && $_FILES['file']['type'] != "image/pjpeg") { die("Invalid file format!"); } 				
				$fh = fopen ($_FILES['userfile1']['tmp_name'], "r") or die("Could not open file");

				// read file
				$date = '';
				while (!feof($fh))
				{
				   $data .= fgets($fh);
				   //echo $data;
				}
				// close file
				fclose ($fh); 

				$process_data =& $this->process_manager->unserialize_process($data);
				//_debug_array($process_data);
				if ($this->process_manager->import_process($process_data))
				{
					$this->message[] = lang('Import successfull');
				}
				else
				{
					$this->message[] = lang('Import aborted');
				}
			}
			// delete processes
			if (isset($_POST['delete']))
			{
				$this->delete_processes(array_keys($_POST['process']));
				$this->message[] = lang('Deletion successful');
			}


			// save new process
			// or save modifs
			if (isset($_POST['save']))
			{
				//retrieve config_values POSTed by the form
				$config_yesno		=& get_var('config_yesno', 'POST', array());
				$config_value 		=& get_var('config_value', 'POST', array());
				$config_use_default 	=& get_var('config_use_default', 'POST', array());
				$global_config_array = array(
					'known_items' 	=> &$known_config_items,
					'yesno'		=> &$config_yesno,
					'value'		=> &$config_value,
					'default' 	=> &$config_use_default,
				);
				$this->wf_p_id = $this->save_process($name, $version, $description, $is_active,$global_config_array);
			}

			// new minor
			if ($newminor)
			{
				if (!($this->process_manager->new_process_version($newminor)))
				{
					$this->message[] = lang('something was wrong while creating the new minor version');
				}
				else
				{
					$this->message[] = lang('new minor version created');
				}
			}

			// new major
			if ($newmajor)
			{
				if (!($this->process_manager->new_process_version($newmajor, false)))
				{
					$this->message[] = lang('something was wrong while creating the new major version');
				}
				else
				{
					$this->message[] = lang('new major version created');
				}

			}

			// retrieve current process
			if ($this->wf_p_id)
			{
				$proc_info = $this->process_manager->get_process($this->wf_p_id);
				$this->t->set_var('proc_bar', $this->fill_proc_bar($proc_info));
				//retrieve config values
				$this->process_config =& $this->process_manager->getConfigValues($this->wf_p_id);
			}
			else
			{
				$proc_info = array(
					'wf_name'		=> '',
					'wf_description'	=> '',
					'wf_version'		=> '1.0',
					'wf_is_active'		=> 'n',
					'wf_p_id'		=> 0
				);
				$this->t->set_var('proc_bar', '');
				$this->process_config = array();
			}

			// show list of processes
			$items = &$this->process_manager->list_processes($this->start, $this->offset, $this->sort_mode, $search_str, $where_str);
			//echo "list of processes: <pre>";print_r($items);echo "</pre>";
			$this->show_list_processes($items['data'], $items['cant']);

			if ($this->wf_p_id)
			{
				// check process validity and show errors if necessary
				$proc_info['wf_is_valid'] = $this->show_errors($this->activity_manager, $error_str);
			}

			// show current process
			$this->t->set_var(array(
				'errors'		=> $error_str,
				'link_new'		=> $GLOBALS['phpgw']->link('/index.php', array_merge( array(
								'menuaction'	=> $this->form_action,
								'p_id'		=> 0,), $this->link_data)
								),
				'p_id'			=> $proc_info['wf_p_id'],
				'name'			=> $proc_info['wf_name'],
				'version'		=> $proc_info['wf_version'],
				'description'		=> $proc_info['wf_description'],
				'is_active'		=> ($proc_info['wf_is_active'] == 'y')? 'checked="checked"' : '',
				'btn_update_create'	=> ($this->wf_p_id)? lang('update') : lang('create'),
				'list_processes'	=> lang('List of processes'),
			));
			// show process config values
			$this->show_process_config($known_config_items);
			
			$this->fill_form_variables();
			$this->finish();
		}

		function delete_processes($process_ids)
		{
			foreach ($process_ids as $process_id)
			{
				$this->process_manager->remove_process($process_id);
			}
		}
		
		//! Show the list of process configuration options
		/*!
		Use the process_config array member which should be already setted. Show a table with a line for each config
		value containing [Yes-No/value | default] choices. The parameter is an array containing all known config items and is used
		to show all config items, as only the ones changed for this process are stored in process_config. You should give
		this function all config_names avaible at process level associated with his type which is 'yesno' or 'text'. 
		You can add titles by giving the title in the config_name and 'title' as type. 
		*/
		function show_process_config(&$known_config_items)
		{
			$siteconfiglink = '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=workflow')
				.'">'.lang('Workflow Site Configuration').'</a>';
			$this->t->set_var(array(
				'txt_consult_site_config_with_link' => lang ('Consult %1 to get the default values:',$siteconfiglink),
			));
			
		 	$this->t->set_block('admin_processes', 'block_config_table_empty', 'config_table_empty');
		 	$this->t->set_block('admin_processes', 'block_config_table_title', 'config_table_title');
			$this->t->set_block('admin_processes', 'block_config_table_yesno', 'config_table_yesno');
			$this->t->set_block('admin_processes', 'block_config_table_text', 'config_table_text');
			$this->translate_template('block_config_table_title');
			$this->translate_template('block_config_table_yesno');
			$this->translate_template('block_config_table_text');
			$this->translate_template('block_config_table_empty');
		
                
		        if (!(is_array($known_config_items)) || !count($known_config_items))
			{
				$this->t->set_var(array(
					'config_empty' => lang('There are no config value defined'),
					'config_table_title' => '',
					'config_table_yesno' => '',
					'config_table_text' => '',
				));
				
				$this->t->parse('config_table_empty', 'block_config_table_empty', true);
			}
			else
			{
				//we wont need the 'empty config' row
				$this->t->set_var(array('config_table_empty'=> ''));
				//we prepare the global table rows
				$this->t->set_block('admin_processes', 'block_config_table', 'config_table');
				$this->translate_template('block_config_table');
				// we parse the config items we knows
				foreach ($known_config_items as $config_name => $config_type)
				{
					// now rows can be of different types
					if ($config_type=='title')
					{
						$this->t->set_var(array(
                                	        	'config_name_trad'	=> lang($config_name),
	        	                                'color_line'		=> '#D3DCE3',
	        	                                'config_table_text' 	=> '',
	        	                                'config_table_yesno' 	=> '',
        	        	                ));
        	        	                $this->t->parse('config_table_title', 'block_config_table_title', false);
					}
					else
					{
						// if not title our row can be a text value or a Yes/No/Default value
						$this->t->set_var(array(
        		                                'config_name' 			=> $config_name,
        		                                'config_name_trad'		=> lang($config_name),
                        		                'color_line' 			=> $this->nextmatchs->alternate_row_color($tr_color),
                        		                'config_table_title' 		=> '',
						));
						unset($row_value);
						$row_value = $this->process_config[$config_name];
						if ($config_type=='text')
						{
							if (isset($row_value))
							{
								$this->t->set_var(array(
									'config_value' 			=> $row_value,
									'config_use_default_checked' 	=> '',
		                        		                'config_table_yesno' 		=> '',
								));
							}
							else
							{
								$this->t->set_var(array(
									'config_value' 			=> '',
									'config_use_default_checked' 	=> 'checked',
		                        		                'config_table_yesno' 		=> '',
								));
							}
							$this->t->parse('config_table_text', 'block_config_table_text', false);
						}
						elseif ($config_type=='yesno')
						{
							if (isset($row_value))
							{
								$this->t->set_var(array(
	                        		                	'config_table_text' 		=> '',
		                        		                'config_default_selected' 	=> '',
		                        		                'config_yes_selected'		=> ($row_value==1)? 'selected':'',
		                        		                'config_no_selected'		=> ($row_value==1)? '':'selected',
								));
							}
							else
							{
								$this->t->set_var(array(
	                        		                	'config_table_text' 		=> '',
		                        		                'config_default_selected' 	=> 'selected',
		                        		                'config_yes_selected'		=> '',
		                        		                'config_no_selected'		=> '',
								));
							}
							$this->t->parse('config_table_yesno', 'block_config_table_yesno', false);
						}
					}
					$this->t->parse('config_table','block_config_table',true);
				}
			}
		}

		function show_list_processes(&$items, $total_number)
		{
			$header_array = array(
				'procname'	=> lang('Process'),
				'wf_version'	=> lang('Version'),
				'wf_is_active'	=> lang('Active'),
				'wf_is_valid'	=> lang('Valid'),
			);
			$this->fill_nextmatchs($header_array,$total_number);
			
			// filter_active, "", y or n
			$this->t->set_var(array(
					'filter_active_selected_all'	=> ($this->filter_active=='')? 'selected':'',
					'filter_active_selected_y'	=> ($this->filter_active=='y')? 'selected':'',
					'filter_active_selected_n'	=> ($this->filter_active=='n')? 'selected':'',
			));
			$get_link = array(
				'menuaction' 	=> 'workflow.ui_'. $this->class_name .'.form',
				'search_str'	=> $this->search_str,
				'start'		=> $this->start,
				'sort'		=> $this->sort,
				'order'		=> $this->order,
			);
			$get_link = array_merge($get_link, $this->link_data);
			foreach ($items as $item)
			{
				if ($item['wf_is_valid'] == 'y')
				{
					$dot = 'green';
					$alt = lang('Valid Process');
				}
				else
				{
					$dot = 'red';
					$alt = lang('Invalid Process');
				}
				
				$myp_id = $item['wf_p_id'];
				$this->t->set_var(array(
					'item_wf_p_id'		=> $myp_id,
					'href_item_name'	=> $GLOBALS['phpgw']->link('/index.php', array_merge($get_link,array('p_id' => $myp_id))),
					'item_name'		=> $item['wf_name'],
					'item_version'		=> $item['wf_version'],
					'img_active'		=> ($item['wf_is_active'] == 'y')? '<img src="'. $GLOBALS['phpgw']->common->image('workflow', 'refresh2') .'" alt="'. lang('active') .'" title="'. lang('active') .'" />' : '',
					'img_valid'		=> '<img src="'. $GLOBALS['phpgw']->common->image('workflow', $dot.'_dot') .'" alt="'. $alt .'" title="'. $alt .'" />',
					'href_item_minor'	=> $GLOBALS['phpgw']->link('/index.php', array_merge($get_link,array('newminor'	=> $myp_id))),
					'img_new'		=> $GLOBALS['phpgw']->common->image('workflow', 'new'),
					'href_item_major'	=> $GLOBALS['phpgw']->link('/index.php', array_merge($get_link,array('newmajor' => $myp_id))),
					'href_item_activities'	=> $GLOBALS['phpgw']->link('/index.php', array(
									'menuaction'	=> 'workflow.ui_adminactivities.form',
									'p_id'		=> $myp_id)),
					'img_activities'	=> $GLOBALS['phpgw']->common->image('workflow', 'Activity'),
					'href_item_code'	=> $GLOBALS['phpgw']->link('/index.php', array(
									'menuaction'	=> 'workflow.ui_adminsource.form',
									'p_id'		=> $myp_id)),
					'img_code'		=> $GLOBALS['phpgw']->common->image('workflow', 'code'),
					'href_item_save'	=> $GLOBALS['phpgw']->link('/index.php', array(
									'menuaction'	=> 'workflow.workflow.export',
									'p_id'		=> $myp_id)),
					'img_save'		=> $GLOBALS['phpgw']->common->image('workflow', 'save'),
					'href_item_roles'	=> $GLOBALS['phpgw']->link('/index.php', array(
									'menuaction'	=> 'workflow.ui_adminroles.form',
									'p_id'		=> $myp_id)),
					'img_roles'		=> $GLOBALS['phpgw']->common->image('workflow', 'roles'),
					'color_line'		=> $this->nextmatchs->alternate_row_color($tr_color),
				));
				$this->t->parse('items', 'block_items', True);
			}
			if (!count($items)) $this->t->set_var('items', '<tr><td colspan="5" align="center">'. lang('There are no processes defined')  .'</td></tr>');
			$this->translate_template('block_items');
		}
		
		//! Save or update the current process
		function save_process($name, $version, $description, $is_active, &$config_data)
		{
			if ($this->process_manager->process_name_exists($name, $version) && $this->wf_p_id==0)
			{
				$this->message[] = lang('Process name already exists');
				return false;
			}
			else
			{
				$proc_info = array(
					'wf_name'		=> $name,
					'wf_description'	=> $description,
					'wf_version'		=> $version,
					'wf_is_active'		=> ($is_active == 'on')? 'y' : 'n'
				);
				$this->wf_p_id = $this->process_manager->replace_process($this->wf_p_id, $proc_info);
				$this->save_config($config_data);
				$valid = $this->activity_manager->validate_process_activities($this->wf_p_id);
				if (!$valid)
				{
					$this->process_manager->deactivate_process($this->wf_p_id);
				}
				$this->message[] = lang('Process saved');
				return $this->wf_p_id;
			}
		}
		
		//! Save the configuration values for the current process
		/*!
		This function use the list of knwon configuration items to parse POSTed config values
		Theses values are passed to the process->SetConfigValues which know well what to do with
		them.
		*/
		function save_config(&$global_config_data)
		{
			$known_config_items	= $global_config_data['known_items'];
			$config_yesno		= $global_config_data['yesno'];	
			$config_value		= $global_config_data['value'];
			$config_use_default	= $global_config_data['default'];
			$array_config = array();
			foreach ($known_config_items as $config_name => $config_type)
			{
				if (!($config_type=='title')) //we do not need titles
				{
					if ($config_type=='yesno')
					{
						if (isset($config_yesno[$config_name]))
						{
							$user_post_value = $config_yesno[$config_name];
							if ($user_post_value == 'default')
							{
								//user ask for default
								$array_config[$config_name]=array('int' => -1);
							}
							elseif ($user_post_value == 'yes')
							{
								$array_config[$config_name]=array('int' => 1);
							}
							else //no
							{
								$array_config[$config_name]=array('int' => 0);
							}
						}
					}
					else // text config type
					{
						if (isset($config_use_default[$config_name]))
						{
							//user ask for default
							$array_config[$config_name]=array('int' => -1);
						}
						elseif (isset($config_value[$config_name]))
						{
							$array_config[$config_name]=array('text' => $config_value[$config_name]);
						}
					}
				}
			
			} // end foreach
			$this->process_manager->setConfigValues($this->wf_p_id,$array_config);
		}// end function
	}
?>
