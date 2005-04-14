<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2004 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.eGroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; either version 2 of the License, or (at your
   option) any later version.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   // FIXME do we need to extend?
   class uia_edit_object extends uiadmin
   {
	  //FIXME move all pub functions from uiadmin to this file
	  var $xxxpublic_functions = Array(
		 'render_form'=>True
	  ); 
	  
	  var $where_key;
	  var $where_value;
	  var $parent_site_id;
	  var $bool_edit_record=false;
	  var $valid_table_name;
	  var $object_values;
	  var $table_array;
	  var $available_tables;

	  /**
	  * @function uia_edit_object
	  * @abstract class constructor
	  * @note FIXME Can't we get the bo from somewhere else?
	  */
	  function uia_edit_object($bo)
	  {
		 $this->bo = $bo;
		 $this->template = $GLOBALS['phpgw']->template;
		 $this->ui = CreateObject('jinn.uicommon',$this->bo);
   
		  }

	  function render_form($where_key, $where_value)
	  {
		 $this->where_key=$where_key;
		 $this->where_value=$where_value;

		 $this->template->set_file(array
		 (
			'form_site' => 'frm_edit_object.tpl',
		 ));

		 if ($this->where_key && $this->where_value)
		 {
			$this->bool_edit_record=true;
			$this->object_values=$this->bo->so->get_object_values($this->where_value);
			$this->parent_site_id=$this->object_values[parent_site_id];
		 }
		 else
		 {
			$this->parent_site_id=$_GET[parent_site_id];
		 }

		 $this->available_tables=$this->bo->so->site_tables_names($this->parent_site_id);

		 $this->template->set_block('form_site','header','header');
		 $this->template->set_block('form_site','rows','rows');

		 $this->template->set_block('form_site','plugins_header','plugins_header');
		 $this->template->set_block('form_site','plugins_row','plugins_row');
		 $this->template->set_block('form_site','plugins_footer','plugins_footer');

		 $this->template->set_block('form_site','relations_header','relations_header');
		 $this->template->set_block('form_site','relations1','relations1');
		 $this->template->set_block('form_site','relations2','relations2');
		 $this->template->set_block('form_site','relations3','relations3');
		 $this->template->set_block('form_site','relation_defined1','relation_defined1');
		 $this->template->set_block('form_site','relation_defined2','relation_defined2');
		 $this->template->set_block('form_site','relation_defined3','relation_defined3');
		 $this->template->set_block('form_site','relations_footer','relations_footer');

		 $this->template->set_block('form_site','footer','form_footer');

		 $this->render_header();
		 $this->render_body();
		 $this->render_footer();

		 $this->template->pparse('out','header');
		 $this->template->pparse('out','row');

		 if($this->bool_edit_record && $this->valid_table_name)
		 {
			$this->render_plugins();
			$this->render_relations();

			$this->template->pparse('out','plugins_header');
			$this->template->pparse('out','plugins_rows');
			$this->template->pparse('out','plugins_footer');
			$this->template->pparse('out','relations_header');
			if($this->type1_num) $this->template->pparse('out','relations_defined1');
			$this->template->pparse('out','relations1');
			if($this->type2_num) $this->template->pparse('out','relations_defined2');
			$this->template->pparse('out','relations2');
			if($this->type3_num)$this->template->pparse('out','relations_defined3');
			$this->template->pparse('out','relations3');
			$this->template->pparse('out','relations_footer');
		 }
		 $this->template->pparse('out','footer');
	  }

	  function render_header()
	  {
		 if ($this->bool_edit_record)
		 {
			$form_action = $GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.boadmin.update_egw_jinn_object");
			$action=lang('edit '. 'egw_jinn_objects');
			$where_key_form='<input type="hidden" name="where_key" value="'.$this->where_key.'">';
			$where_value_form='<input type="hidden" name="where_value" value="'.$this->where_value.'">';

		 }
		 else
		 {
			$form_action = $GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.boadmin.insert_egw_jinn_object");
			$action=lang('add '. 'egw_jinn_objects' );
		 }

		 $this->template->set_var('form_action',$form_action);
		 $this->template->set_var('where_key_form',$where_key_form);
		 $this->template->set_var('where_value_form',$where_value_form);
		 $this->template->parse('out','header');
	  }

	  function render_body()
	  {
		 $fields=$this->bo->so->phpgw_table_metadata('egw_jinn_objects');

		 if($this->bool_edit_record)
		 {
			// fixme DON'T WE HAVE THESE ALLREADY
			$values_object= $this->bo->get_phpgw_records('egw_jinn_objects',$this->where_key,$this->where_value,'','','name');
			if($values_object[0][plugins]!='')
			{
			   $this->bo->upgrade_plugins($values_object[0][object_id],true);
			   $this->object_values[plugins]='';
			   $values_object[0][plugins]='';
			}

		 }

		 foreach ($fields as $testone => $fieldproperties)
		 {
			$edit_value=$values_object[0][$fieldproperties[name]];

			$input_name='FLD'.$fieldproperties[name];
			$display_name = lang(ucfirst(strtolower(ereg_replace("_", " ", $fieldproperties[name]))));
			$input_max_length=' maxlength="'. $fieldproperties[len].'"';
			$input_length=$fieldproperties[len];
			$value=$values_object[0][$fieldproperties[name]];

			if ($input_length>40)
			{
			   $input_length=40;
			}

			if ($fieldproperties[name]=='object_id')
			{
			   if (!$value)
			   {
				  $display_value=lang('automatic');
			   }
			   else
			   {
				  $display_value=$value;
			   }

			   $input='<input type="hidden" name="'.$input_name.'" value="'.$value.'">'.$display_value;
			}

			elseif ($fieldproperties[name]=='parent_site_id')
			{
			   if($value) // when we are editing
			   {
				  $parent_site_name=$this->bo->so->get_site_name($value);
				  $this->parent_site_id=$value; //id for further use in formgeneration
				  $input="<input type=hidden name=\"$input_name\" value=\"$value\">";
				  $input.=$parent_site_name;
			   }
			   elseif($this->parent_site_id) //when we are adding
			   {
				  $parent_site_name=$this->bo->so->get_site_name($this->parent_site_id);
				  $input='<input type=hidden name="'.$input_name.'" value="'.$this->parent_site_id.'">';
				  $input.=$parent_site_name;
			   }
			}
			elseif ($fieldproperties[name]=='table_name')
			{
			   $table_name=$value;
			   $tables=$this->available_tables;

			   if(!is_array($tables[0]))
			   {
				  $error_msg='<font color=red>'.lang('Could not find any tables! Check your database name, database username or database password or create one or more  tables in the database.').'</font><br>';

				  $input=$error_msg;
			   }
			   else
			   {
				  foreach($tables as $table)
				  {
					 $tables_check_arr[]=$table[table_name];
					 $this->table_array[]=array
					 (
						'name'=> $table[table_name],
						'value'=> $table[table_name]
					 );
				  }

				  if($this->bool_edit_record && in_array($table_name,$tables_check_arr))
				  {
					 $this->valid_table_name=true;
				  }

				  elseif(!$this->bool_edit_record && !$value)
				  {
					 $this->valid_table_name=true;
				  }
				  else
				  {
					 $error_msg='<font color=red>'.lang('Tablename <i>%1</i> is not correct. Probably the tablename has changed or or the table is deleted. Please select a new table or delete this object',$table_name).'</font><br>';
				  }

				  $input=$error_msg.'<select name="'.$input_name.'">';

					 $input.=$this->ui->select_options($this->table_array,$value,false);
					 $input.='</select>';
			   }
			}
			elseif ($fieldproperties[name]=='upload_path')
			{
			   $input='<input type="text" name="'.$input_name.'" size="'.$input_length.'" $input_max_length" value="'.$value.'">';
			}
			elseif($fieldproperties[name]=='unique_id') 				
			{
				if($value == '')
				{
					$uid = $this->bo->so->generate_unique_object_id();
				}
				else
				{
					$uid = $value;
				}
				$input='<input readonly type="text" name="'.$input_name.'" value="'.$uid.'"/>';
			}
			elseif ($fieldproperties[type]=='varchar' || $fieldproperties[type]=='string')
			{
			   $input='<input type="text" name="'.$input_name.'" size="'.$input_length.'" input_max_length" value="'.$value.'">';
			}
			elseif ($fieldproperties[name]=='max_records')
			{
			   unset($selected);
			   if($value==1) $selected='selected';
			   $input='<select name="'.$input_name.'"><option value="">'.lang('unlimited').'</option><option '.$selected.' value="1">'.lang('only one').'</option></select>';

			}
			elseif ($fieldproperties[name]=='hide_from_menu')
			{
			   unset($selected);
			   if($value==1) $selected='selected';
			   $input='<select name="'.$input_name.'"><option value="">'.lang('No').'</option><option '.$selected.' value="1">'.lang('Yes, hide from menu').'</option></select>';
			}


			elseif ($fieldproperties[name]=='serialnumber')
			{
			   $input='<input type="hidden" name="'.$input_name.'" value="'.time().'">'.$value;
			}
			elseif ($fieldproperties[type]=='int')
			{
			   $input='<input type="text" name="'.$input_name.'" size="10" value="'.$value.'">';
			}
			elseif ($fieldproperties[name]=='help_information')
			{
			   continue;
			}
			elseif ($fieldproperties[name]=='relations')
			{
			   continue;
			}

			elseif($fieldproperties[name]=='plugins') 				
			{
			   continue;
			}

			elseif($fieldproperties[name]=='events_config') 				
			{
			   $onclick='parent.window.open(\''.$GLOBALS['phpgw']->link('/jinn/plgconfwrapper.php','screen=objeventsconf').'&object_id='.$this->object_values['object_id'].'\', \'pop\', \'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no\')';
			   $input='<input type="button" value="'.lang('Object Events Configuration').'" onClick="'.$onclick.'"/>';
			}

			// when it doesn't fit anywhere
			else
			{
			   $value = ereg_replace ("(<br />|<br/>)","",$value);
			   $input='<textarea name="'.$input_name.'" cols="60" rows="2">'.$value.'</textarea>';
			}

			if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on'])
			{
			   $row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
			}
			else
			{
			   $row_color=$GLOBALS['phpgw_info']['theme']['row_on'];
			}
			$this->template->set_var('row_color',$row_color);
			$this->template->set_var('input',$input);
			$this->template->set_var('fieldname',$display_name);

			$this->template->parse('row','rows',true);
		 }

	  }

	  function render_plugins()
	  {
		 if($this->object_values[plugins])
		 {
			$value=$this->object_values[plugins];
			if(!$value) $value='TRUE';
			$hidden_value='<input type="hidden" name="FLDplugins" value="'.$value.'">';
			$plugin_settings_old=explode('|',$value);
		 }

		 $table_name=$this->object_values[table_name];

		 if ($this->bool_edit_record && $this->valid_table_name)
		 {
			if ($fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table_name))
			{
			   $this->template->set_var('lang_fields',lang('field'));
			   $this->template->set_var('hidden_value',$hidden_value);
			   $this->template->set_var('lang_field_plugin',lang('Field Plugin'));
			   $this->template->set_var('lang_field_plugins',lang('Field Plugins'));
			   $this->template->parse('out','plugins_header');


			   foreach($fields as $field)
			   {

				  unset($sets);
				  unset($plg_name);
				  unset($plg_conf);
				  if(is_array($plugin_settings_old))
				  {
					 foreach($plugin_settings_old as $setting)
					 {
						$sets=explode(':',$setting);
						if ($sets[0]==$field['name'])
						{
						   $plg_name=$sets[1];
						   $plg_conf=$sets[3];
						}

					 }

				  }
				  else
				  {
					 $plugin_conf_arr=$this->bo->so->get_field_values($this->object_values[object_id],$field['name']);
					 
					 if($plugin_conf_arr[field_plugins])
					 {
						$plugin_settings=unserialize(base64_decode($plugin_conf_arr[field_plugins]));

						if (is_array($plugin_settings))
						{
						   $plg_name=$plugin_settings[name];
						}

						 $this->template->set_var('mandatory',(($plugin_conf_arr[field_mandatory]==1) ? ' checked' : ''));
						 $this->template->set_var('default',(($plugin_conf_arr[field_show_default]==1) ? ' checked' : ''));
						 $this->template->set_var('position',$plugin_conf_arr[field_position]);
					}
					else
					{
							//default values:
						 $this->template->set_var('mandatory', '');
						 $this->template->set_var('default', ' checked');
						 $this->template->set_var('position', '');
					}
				  }

				  $this->template->set_var('field_name',$field['name']);

				  $jinn_fieldtype=$this->bo->db_ftypes->complete_resolve($field);
				  $plugin_default=$this->bo->plug->get_default_plugin($jinn_fieldtype);
				  $plugin_hooks=$this->bo->plug->plugin_hooks($jinn_fieldtype);

				  $plugin_hooks=array_merge($plugin_default,$plugin_hooks);
					$doublecheck = array();
					foreach($plugin_hooks as $key => $plugin_hook)
					{
						if(array_key_exists($plugin_hook[value], $doublecheck))
						{
							unset($plugin_hooks[$key]);
						}
						else
						{
							$doublecheck[$plugin_hook[value]] = $key;
						}
					}
					$plugin_hooks = array_values($plugin_hooks); //reorder the array
					if(!array_key_exists($plg_name, $doublecheck))
					{
						if(array_key_exists($plg_name, $this->bo->plug->registry->aliases))
						{
							$alias = $this->bo->plug->registry->aliases[$plg_name];
							$aliasname = $this->bo->plug->registry->plugins[$alias]['title'];
							$plugin_hooks[] = array('value' => $plg_name, 'name' => $plg_name.' (alias:'.$aliasname.')');
						}
						else
						{
							$plugin_hooks[] = array('value' => $plg_name, 'name' => $plg_name.' (unknown)');
						}
						
					}
					
				  $options=$this->ui->select_options($plugin_hooks,$plg_name,false);

				  if ($options) 
				  {
					 $popup_onclick_plug='parent.window.open(\''.$GLOBALS['phpgw']->link('/jinn/plgconfwrapper.php','screen=plugconf').'&plug_orig='.$plg_name.'&plug_name=\'+document.frm.FIELD_'.$field['name'].'_PLG.value+\'&hidden_name=FIELD_'.$field['name'].'_PLC&field_name='.$field['name'].'&object_id='.$this->object_values['object_id'].'&hidden_val='.rawurlencode($plg_conf).'\', \'pop'.$field['name'].'\', \'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no\')';

					 $popup_onclick_name_and_help='parent.window.open(\''.$GLOBALS['phpgw']->link('/jinn/plgconfwrapper.php','screen=helpconf').'&plug_orig='.$plg_name.'&plug_name=\'+document.frm.FIELD_'.$field['name'].'_PLG.value+\'&hidden_name=FIELD_'.$field['name'].'_PLC&field_name='.$field['name'].'&object_id='.$this->object_values['object_id'].'&hidden_val='.rawurlencode($plg_conf).'\', \'pop'.$field['name'].'\', \'width=500,height=400,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no\')';

					 $this->template->set_var('plg_options',$options);

					 $this->template->set_var('plg_conf',$plg_conf);
					 $this->template->set_var('lang_field_configuration',lang('Field configuration'));
					 $this->template->set_var('lang_afc',lang('information'));
					 $this->template->set_var('lang_plugin_conf',lang('configure field plugin'));
					 $this->template->set_var('lang_name_and_help',lang('name and help info'));
					 $this->template->set_var('lang_mandatory',lang('mandatory'));
					 $this->template->set_var('lang_show_by_default_listview',lang('show by default_listview'));
					 $this->template->set_var('lang_position',lang('field position'));
					 $this->template->set_var('popup_onclick_plug',$popup_onclick_plug);
					 $this->template->set_var('popup_onclick_name_and_help',$popup_onclick_name_and_help);
					 $this->template->parse('plugins_rows','plugins_row',true);
				  }
			   }
			}
		 }

		 if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on'])
		 {
			$row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
		 }
		 else
		 {
			$row_color=$GLOBALS['phpgw_info']['theme']['row_on'];
		 }

		 $this->template->set_var('prow_color',$row_color);
		 $this->template->parse('out','plugins_footer');
	  }

	  function render_relations()
	  {
		 // FIXME re-use options vars to speed up rendering

		 /* 
		 relation type 1 = one to many relation
		 relation type 2 = many to many relation
		 relation type 3 = one to one relation
		 */
		 
		 $relations_field=$this->object_values[relations];

		$relations_arr = unserialize(base64_decode($relations_field));
			//if relations field contains old format data, now is the time to convert it.
			/*
				old format test data:
			1:i:null:x2.id:x2.v1|2:tussentabel1.t1_id:tussentabel1.t2_id:t2.id:t2.v1|3:t1.id:null:t2.id:84
			*/
		if(!is_array($relations_arr))
		{
			$old_fmt_relations=explode('|',$relations_field);
			$relations_arr = array();
			foreach($old_fmt_relations as $old_fmt_relation)
			{
			   $old_fmt_relation_parts=explode(':',$old_fmt_relation);
				if($old_fmt_relation_parts[0]==1)
				{
					unset($new_fmt_relation);
					$new_fmt_relation[type] = $old_fmt_relation_parts[0];
					$new_fmt_relation[org_field] = $old_fmt_relation_parts[1];
					$new_fmt_relation[related_with] = $old_fmt_relation_parts[3];
					$new_fmt_relation[display_field] = $old_fmt_relation_parts[4];
				}
				else if($old_fmt_relation_parts[0]==2)
				{
					unset($new_fmt_relation);
					$new_fmt_relation[type] = $old_fmt_relation_parts[0];
					$new_fmt_relation[via_primary_key] = $old_fmt_relation_parts[1];
					$new_fmt_relation[via_foreign_key] = $old_fmt_relation_parts[2];
					$new_fmt_relation[foreign_key] = $old_fmt_relation_parts[3];
					$new_fmt_relation[display_field] = $old_fmt_relation_parts[4];
				}
				else if($old_fmt_relation_parts[0]==3)
				{
					unset($new_fmt_relation);
					$new_fmt_relation[type] = $old_fmt_relation_parts[0];
					$new_fmt_relation[org_field] = $old_fmt_relation_parts[1];
					$new_fmt_relation[related_with] = $old_fmt_relation_parts[3];
					$new_fmt_relation[object_conf] = $old_fmt_relation_parts[4];
				}
				$relations_arr[] = $new_fmt_relation;
			}
		}
		 $hidden_value='<input type="hidden" name="FLDrelations" value="'.base64_encode(serialize($relations_arr)).'">';

		 $table_name=$this->object_values[table_name];


		 if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on'])
		 {
			$row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
		 }
		 else
		 {
			$row_color=$GLOBALS['phpgw_info']['theme']['row_on'];
		 }
		 $this->template->set_var('lang_relations',lang('relations'));
		 $this->template->set_var('row_color',$row_color);
		 $this->template->set_var('hidden_value',$hidden_value);
		 $this->template->parse('out','relations_header');

		 $i=1;
 		 if ($relations_arr)
		 {
			$this->type1_num=0;
			$this->type2_num=0;
			$this->type3_num=0;

			foreach($relations_arr as $relation)
			{
			   if ($relation[type]==1)
			   {
						//check if more than one field is displayed:
					$display_fields=$relation[display_field];
					if($relation[display_field_2]!='') $display_fields.=', '.$relation[display_field_2];
					if($relation[display_field_3]!='') $display_fields.=', '.$relation[display_field_3];
					
					if($relation[default_value]!='')
					{
						$r1txt=lang('%1 has a one-to-many with %2 (default:%4) showing %3',$relation[org_field],$relation[related_with],$display_fields, $relation[default_value]);
					}
					else 
					{
						$r1txt=lang('%1 has a one-to-many with %2 showing %3',$relation[org_field],$relation[related_with],$display_fields);
					}
				  $this->type1_num++;
				  $this->template->set_var('total_num',$i);
				  $this->template->set_var('lang_delete',lang('delete'));
				  $this->template->set_var('type1_num',$this->type1_num);
				  $this->template->set_var('relation',$i-1);
				  $this->template->set_var('r1txt',$r1txt);

				  $this->template->parse('relations_defined1','relation_defined1',true);
			   }
			   elseif ($relation[type]==2)
			   {
						//check if more than one field is displayed:
					$display_fields=$relation[display_field];
					if($relation[display_field_2]!='') $display_fields.=', '.$relation[display_field_2];
					if($relation[display_field_3]!='') $display_fields.=', '.$relation[display_field_3];
					
				  $r2txt=lang('The identifierfield of this table, %1, represented by %2 has a many-to-many with %3 represented by %4 showing %5',$table_name,$relation[via_primary_key],$relation[foreign_key],$relation[via_foreign_key],$display_fields);
				  $this->type2_num++;
				  $this->template->set_var('total_num',$i);
				  $this->template->set_var('lang_delete',lang('delete'));
				  $this->template->set_var('type2_num',$this->type2_num);
				  $this->template->set_var('relation',$i-1);
				  $this->template->set_var('r2txt',$r2txt);

				  $this->template->parse('relations_defined2','relation_defined2',true);
			   }
			   elseif ($relation[type]==3)
			   {
				  $r3txt=lang('%1 has a one-to-one relation with %2 using the configuration of object %3',$relation[org_field],$relation[related_with],$relation[object_conf]);
				  $this->type3_num++;
				  $this->template->set_var('total_num',$i);
				  $this->template->set_var('lang_delete',lang('delete'));
				  $this->template->set_var('type3_num',$this->type3_num);
				  $this->template->set_var('relation',$i-1);
				  $this->template->set_var('r3txt',$r3txt);

				  $this->template->parse('relations_defined3','relation_defined3',true);
			   }
   			   $i++;
			}
		 }


		 /*********************************
		 * ADD NEW ONE TO MANY RELATION *
		 *********************************/
		 if($fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table_name))
		 {

			$this->template->set_var('lang_new_rel1',lang('Add new ONE TO MANY'));

			foreach($fields as $field)
			{
			   $fields_array[]=array
			   (
				  'name'=> $field[name],
				  'value'=> $field[name]
			   );
			}

			$rel1_options1=$this->ui->select_options($fields_array,$value,true);
			$this->template->set_var('rel1_options1',$rel1_options1);


			$this->template->set_var('lang_field',lang('field'));
			$this->template->set_var('lang_has_1rel',lang('has a ONE TO MANY relation with'));
			$this->template->set_var('lang_default',lang('default value'));

			foreach($this->table_array as $table)
			{
			   $fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table[name]);
			   foreach($fields as $field)
			   {
				  $related_fields_array[]=array
				  (
					 'name'=> $table[name].'.'.$field[name],
					 'value'=> $table[name].'.'.$field[name]
				  );
			   }
			}
			$rel1_options2=$this->ui->select_options($related_fields_array,'',true);
			$this->template->set_var('rel1_options2',$rel1_options2);

			$this->template->set_var('lang_displaying',lang('field to display'));

			foreach($this->table_array as $table)
			{
			   $fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table[name]);
			   foreach($fields as $field)
			   {
				  $display_fields_array[]=array
				  (
					 'name'=> $table[name].'.'.$field[name],
					 'value'=> $table[name].'.'.$field[name]
				  );
			   }
			}
			$rel1_options3=$this->ui->select_options($display_fields_array,'',true);
			$this->template->set_var('rel1_options3',$rel1_options3);

			$this->template->parse('out','relations1');
		 }

		 /**********************************
		 * ADD NEW MANY TO MANY RELATION *
		 **********************************/

		 if (is_array($this->table_array))
		 {


			$this->template->set_var('lang_new_rel2',lang('Add new MANY TO MANY relation'));
			$this->template->set_var('lang_the_id_of',lang('The identifyer from this table (%1.id) represented by',$table_name));


			foreach($this->table_array as $table)
			{
			   $fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table[name]);
			   foreach($fields as $field)
			   {
				  $fields_array[]=array
				  (
					 'name'=> $table[name].'.'.$field[name],
					 'value'=> $table[name].'.'.$field[name]
				  );
			   }
			}
			$rel2_options1=$this->ui->select_options($fields_array,$value,true);
			$this->template->set_var('rel2_options1',$rel2_options1);

			$this->template->set_var('lang_has_rel2_with',lang('has a MANY TO MANY relation with'));

			foreach($this->table_array as $table)
			{
			   $fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table[name]);
			   foreach($fields as $field)
			   {
				  $related_fields_array[]=array
				  (
					 'name'=> $table[name].'.'.$field[name],
					 'value'=> $table[name].'.'.$field[name]
				  );
			   }
			}
			$rel2_options2=$this->ui->select_options($related_fields_array,'',true);
			$this->template->set_var('rel2_options2',$rel2_options2);

			$this->template->set_var('lang_represented_by',lang('represented by:'));

			foreach($this->table_array as $table)
			{
			   $fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table[name]);
			   foreach($fields as $field)
			   {
				  $display_fields_array[]=array
				  (
					 'name'=> $table[name].'.'.$field[name],
					 'value'=> $table[name].'.'.$field[name]
				  );
			   }
			}
			$rel2_options3=$this->ui->select_options($display_fields_array,'',true);
			$this->template->set_var('rel2_options3',$rel2_options3);

			$this->template->set_var('lang_showing',lang('showing'));

			foreach($this->table_array as $table)
			{
			   $fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table[name]);
			   foreach($fields as $field)
			   {
				  $related_fields_array[]=array
				  (
					 'name'=> $table[name].'.'.$field[name],
					 'value'=> $table[name].'.'.$field[name]
				  );
			   }
			}
			$rel2_options4=$this->ui->select_options($related_fields_array,'',true);
			$this->template->set_var('rel2_options4',$rel2_options4);

			$this->template->parse('out','relations2');
		 }


		 /************************************
		 * ADD NEW ONE TO ONE RELATION (3)  *
		 ************************************/
		 if($fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table_name))
		 {
			$this->template->set_var('lang_new_rel3',lang('Add new one-to-one relation'));

			$this->template->set_var('lang_field',lang('field'));
			unset($fields_array);
			foreach($fields as $field)
			{
			   $fields_array[]=array
			   (
				  'name'=> $field[name],
				  'value'=> $field[name]
			   );
			}

			$rel3_options1=$this->ui->select_options($fields_array,$value,true);
			$this->template->set_var('rel3_options1',$rel3_options1);

			$this->template->set_var('lang_has_3rel',lang('has a ONE-TO-ONE relation with'));

			$rel3_options2=$this->ui->select_options($related_fields_array,'',true);
			$this->template->set_var('rel3_options2',$rel3_options2);

			$this->template->set_var('lang_object_conf',lang('Using object configuration'));

			$objects_array=$this->bo->get_phpgw_records('egw_jinn_objects','parent_site_id',$this->parent_site_id,$limit[start],$limit[stop],'name');

			foreach($objects_array as $object)
			{
			   $objects[]=array
			   (
				  'name'=> $object[name],
				  'value'=> $object[object_id]
			   );
			}
			$rel3_options3=$this->ui->select_options($objects,'',true);
			$this->template->set_var('rel3_options3',$rel3_options3);


			$this->template->parse('out','relations3');
		 }



		 $this->template->parse('out','relations_footer');
	  }

	  function render_footer()
	  {
		 $cancel_link=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.add_edit_site&cancel=true&where_key=site_id&where_value='.$this->parent_site_id);

		 $delete_link=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boadmin.del_egw_jinn_object&where_key=object_id&where_value='.$this->where_value);

		 $this->template->set_var('confirm_del',lang('Are you sure?'));
		 $this->template->set_var('save_button',lang('save and finish'));
		 $this->template->set_var('save_and_continue_button',lang('save and contiue'));
		 $this->template->set_var('reset_form',lang('reset form'));
		 $this->template->set_var('lang_delete',lang('delete'));
		 $this->template->set_var('link_delete',$delete_link);
		 $this->template->set_var('cancel_link',$cancel_link);
		 $this->template->set_var('cancel_text',lang('cancel'));
		 $this->template->parse('out','footer');
	  }
   }
?>
