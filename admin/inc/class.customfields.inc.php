<?php
	/**************************************************************************\
	* eGroupWare - admin: Custom fields                                        *
	* http://www.egroupware.org                                                *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	/**
	* Custiomfields class -  manages customfield definitions in egw_config table
	*
	* The repository name (config_name) is 'customfields'.
	*
	* @license GPL
	* @author Ralf Becker <ralfbecker-AT-outdoor-training.de>
	* @author Cornelius Weiss <nelius-AT-von-und-zu-weiss.de>
	* @package admin
	*/
	class customfields
	{
	
		/**
		* appname of app which want to add / edit its customfields
		* 
		* @var string
		*/
		var $appname;
		
		/**
		* Allowd types of customfields
		* 
		* The additionally allowed app-names from the link-class, will be add by the edit-method only,
		* as the link-class has to be called, which can NOT be instanciated by the constructor, as 
		* we get a loop in the instanciation.
		* 
		* @var array
		*/
		var $cf_types = array(
			'text'     => 'Text',
			'label'    => 'Label',
			'select'   => 'Selectbox',
			'radio'    => 'Radiobutton',
			'checkbox' => 'Checkbox',
			'link-entry' => 'Select entry',
		);
		/**
		* userdefiened types e.g. type of infolog
		* 
		* @var array
		*/
		var $types2 = array();
		var $content_types,$fields;
		
		var $public_functions = array(
			'edit' => True
		);

		function customfields($appname='')
		{
// 			$this->tmpl =& CreateObject('etemplate.etemplate');
			$this->config =& CreateObject('phpgwapi.config',$this->appname=$appname);
			if ($appname)
			{
				$this->fields = $this->get_customfields();
				$this->content_types = $this->get_content_types();
			}
		}

		/**
		 * Edit/Create Custom fields with type
		 *
		 * @author Ralf Becker <ralfbecker-AT-outdoor-training.de>
		 * @param array $content Content from the eTemplate Exec
		 */
		function edit($content = null)
		{
			$GLOBALS['egw']->translation->add_app('infolog');       // til we move the translations

			// determine appname
			$this->appname = $_GET['appname'] ? $_GET['appname'] : ($content['appname'] ? $content['appname'] : false);
			if(!$this->appname) die(lang('Error! No appname found'));
			
			$GLOBALS['egw']->translation->add_app('infolog');	// til we move the translations
			$this->tmpl =& CreateObject('etemplate.etemplate');
			$this->config =& CreateObject('phpgwapi.config',$this->appname);
			// do we manage content-types?
			if($this->tmpl->read($this->appname.'.admin.types')) $this->manage_content_types = true;
			
			$this->fields = $this->get_customfields();
			$this->tmpl->read('admin.customfields');
			
			if($this->manage_content_types) $this->content_types = $this->get_content_types();
			else
			{
				$this->tmpl->children[0]['data'][2]['A']['disabled'] = true;
				$this->tmpl->children[0]['data'][3]['A']['disabled'] = true;
			}
			
			if (is_array($content))
			{
				//echo '<pre style="text-align: left;">'; print_r($content); echo "</pre>\n";
				if($this->manage_content_types)
				{
					$this->content_type = $content['content_types']['types'];
				}

				if($content['content_types']['delete']) $this->delete_content_type($content);
				elseif($content['content_types']['create']) $this->create_content_type($content);
				elseif($content['fields']['delete']) $this->delete_field($content);
				elseif($content['fields']['create']) $this->create_field($content);
				else
				{
					list($action) = @each($content['button']);
					switch($action)
					{
						default:
							if (!$content['fields']['create'] && !$content['fields']['delete'])
							{
								break;	// type change
							}
						case 'save':
						case 'apply':
							$this->update($content);
							if ($action != 'save')
							{
								break;
							}
						case 'cancel':
							$GLOBALS['egw']->redirect_link($content['referer'] ? $content['referer'] : '/admin/index.php');
							exit;
					}
				}
				$referer = $content['referer'];
			}
			else
			{
				if($this->manage_content_types)
				{
					$content_types = array_keys($this->content_types);
					$this->content_type = $content_types[0];
				}

				$referer = $GLOBALS['egw']->common->get_referer();
			}
			$GLOBALS['egw_info']['flags']['app_header'] = $GLOBALS['egw_info']['apps'][$this->appname]['title'].' - '.lang('Custom fields');
			$readonlys = array();
			
			if($this->manage_content_types)
			{
				$content['content_types']['app-name'] = $this->appname;
				foreach($this->content_types as $type => $entry)
				{
					$this->types2[$type] = $entry['name'];
				}
				$content['content_types']['options-types'] = $this->types2;
				$this->tmpl->children[0]['data'][3]['A']['name'] = $this->appname.'.admin.types';
				$this->tmpl->children[0]['data'][3]['A']['size'] = 'content_type_options';
				$content['content_type_options'] = $this->content_types[$this->content_type]['options'];
				$content['content_type_options']['type'] = $this->content_types[$this->content_type]['name'];
				if ($this->content_types[$this->content_type]['non_deletable'])
				{
					$content['content_types']['non_deletable'] = true;
				}
			}
			
			//echo 'customfields=<pre style="text-align: left;">'; print_r($this->fields); echo "</pre>\n";
			$content['fields'] = array();
			$n = 0;
			foreach($this->fields as $name => $data)
			{
				if(!is_array($data))
				{
					$data = array();
					$data['label'] = $name;
					$data['order'] = ($n+1) * 10;
				}
				if (is_array($data['values']))
				{
					$values = '';
					foreach($data['values'] as $var => $value)
					{
						$values .= (!empty($values) ? "\n" : '').$var.'='.$value;
					}
					$data['values'] = $values;
				}
				$content['fields'][++$n] = (array)$data + array(
					'name'   => $name
				);
				$preserv_fields[$n]['old_name'] = $name;
				$readonlys['fields']["create$name"] = True;
			}
			$content['fields'][++$n] = array('name'=>'','order' => 10 * $n);	// new line for create
			if($this->manage_content_types) $content['fields']['type2'] = 'enable';
			$readonlys['fields']["delete[]"] = True;
			//echo '<p>uicustomfields.edit(content = <pre style="text-align: left;">'; print_r($content); echo "</pre>\n";
			//echo 'readonlys = <pre style="text-align: left;">'; print_r($readonlys); echo "</pre>\n";
			$sel_options = array(
				'type2' => $this->types2 + array('tmpl' => 'template'),
			);
			$GLOBALS['egw']->translation->add_app('etemplate');
			foreach($this->cf_types as $name => $label) $sel_options['type'][$name] = lang($label);
			$link_types = ExecMethod('phpgwapi.bolink.app_list','');
			ksort($link_types);
			foreach($link_types as $name => $label) $sel_options['type'][$name] = '- '.$label;

			$this->tmpl->exec('admin.customfields.edit',$content,$sel_options,$readonlys,array(
				'fields' => $preserv_fields,
				'appname' => $this->appname,
				'referer' => $referer,
			));
		}

		function update_fields(&$content)
		{
			foreach($content['fields'] as $field)
			{
				$name = trim($field['name']);
				$old_name = $field['old_name'];

				if (!empty($delete) && $delete == $old_name)
				{
					unset($this->fields[$old_name]);
					continue;
				}
				if (isset($field['old_name']))
				{
					if (empty($name))	// empty name not allowed
					{
						$content['error_msg'] = lang('Name must not be empty !!!');
						$name = $old_name;
					}
					if (!empty($name) && $old_name != $name)	// renamed
					{
						unset($this->fields[$old_name]);
					}
				}
				elseif (empty($name))		// new item and empty ==> ignore it
				{
					continue;
				}
				$values = array();
				if (!empty($field['values']))
				{
					foreach(explode("\n",$field['values']) as $line)
					{
						list($var,$value) = split('=',trim($line),2);
						$var = trim($var);
						$values[$var] = empty($value) ? $var : $value;
					}
				}
				$this->fields[$name] = array(
					'type'  => $field['type'],
					'type2'	=> $field['type2'],
					'label' => empty($field['label']) ? $name : $field['label'],
					'help'  => $field['help'],
					'values'=> $values,
					'len'   => $field['len'],
					'rows'  => intval($field['rows']),
					'order' => intval($field['order'])
				);
				if(!$this->fields[$name]['type2'] && $this->manage_content_types)
				{
					$this->fields[$name]['type2'] = (string)0;
				}
			}
			if (!function_exists('sort_by_order'))
			{
				function sort_by_order($arr1,$arr2)
				{
					return $arr1['order'] - $arr2['order'];
				}
			}
			uasort($this->fields,sort_by_order);

			$n = 0;
			foreach($this->fields as $name => $data)
			{
				$this->fields[$name]['order'] = ($n += 10);
			}
		}


		function update(&$content)
		{
			$this->update_fields($content);
			$this->content_types[$this->content_type]['options'] = $content['content_type_options'];
			// save changes to repository
			$this->save_repository();
		}

		/**
		* deletes custom field from customfield definitions
		*/
		function delete_field(&$content)
		{
			unset($this->fields[key($content['fields']['delete'])]);
			// save changes to repository
			$this->save_repository();
		}
		
		function delete_content_type(&$content)
		{
			unset($this->content_types[$content['content_types']['types']]);
			// save changes to repository
			$this->save_repository();
		}
		
		/**
		* create a new custom field
		*/
		function create_field(&$content)
		{
			$new_name = trim($content['fields'][count($content['fields'])-1]['name']);
			if (empty($new_name) || isset($this->fields[$new_name]))
			{
				$content['error_msg'] .= empty($new_name) ?
					lang('You have to enter a name, to create a new field!!!') :
					lang("Field '%1' already exists !!!",$new_name);
			}
			else
			{
				$this->fields[$new_name] = $content['fields'][count($content['fields'])-1];
				if(!$this->fields[$new_name]['label']) $this->fields[$new_name]['label'] = $this->fields[$new_name]['name'];
				$this->save_repository();
			}
		}
		
		function create_content_type(&$content)
		{
			$new_name = trim($content['content_types']['name']);
			if (empty($new_name) || isset($this->fields[$new_name]))
			{
				$content['error_msg'] .= empty($new_name) ?
					lang('You have to enter a name, to create a new type!!!') :
					lang("type '%1' already exists !!!",$new_name);
			}
			else
			{
				// search free type character
				for($i=97;$i<=122;$i++)
				{
					if(!$this->content_types[chr($i)])
					{
						$new_type = chr($i);
						break;
					}
				}
				$this->content_types[$new_type] = array('name' => $new_name);
				$this->save_repository();
			}
		}

		/**
		* save changes to repository
		*/
		function save_repository()
		{
			//echo '<p>uicustomfields::save_repository() \$this->fields=<pre style="text-aling: left;">'; print_r($this->fields); echo "</pre>\n";
			$this->config->value('customfields',$this->fields);
			$this->config->value('types',$this->content_types);
			$this->config->save_repository();
		}
		
		/**
		* get customfields of using application
		*
		* @author Cornelius Weiss
		* @return array with customfields
		*/
		function get_customfields()
		{
			$config = $this->config->read_repository();
			//merge old config_name in phpgw_config table
			$config_name = isset($config['customfields']) ? 'customfields' : 'custom_fields';
			
			return is_array($config[$config_name]) ? $config[$config_name] : array();
		}
		
		/**
		* get_content_types of using application
		*
		* @author Cornelius Weiss
		* @return array with content-types
		*/
		function get_content_types()
		{
			$config = $this->config->read_repository();

			return is_array($config['types']) ? $config['types'] : array();
		}

	}
