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

		// FIXME Can't we get the bo from somewhere else?
		function uia_edit_object($bo)
		{
			$this->bo = $bo;
			$this->template = $GLOBALS['phpgw']->template;

			$this->ui = CreateObject('jinn.uicommon');
		}

		function render_form($where_key, $where_value)
		{

			$this->template->set_file(array
			(
				'form_header' => 'form_header.tpl',
				'object_field' => 'object_field.tpl',
				'form_footer' => 'form_object_footer.tpl'
			));


			if ($where_key && $where_value)
			{

				$form_action = $GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.boadmin.update_phpgw_jinn_site_objects");
				$where_key_form="<input type=\"hidden\" name=\"where_key\" value=\"$where_key\">";
				$where_value_form="<input type=\"hidden\" name=\"where_value\" value=\"$where_value\">";
				$values_object= $this->bo->get_phpgw_records('phpgw_jinn_site_objects',$where_key,$where_value,'','','name');
//				$add_edit_button=lang('save');
				$action=lang('edit '. 'phpgw_jinn_site_objects');
			}
			else
			{
				$form_action = $GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.boadmin.insert_phpgw_jinn_site_objects");
//				$add_edit_button=lang('add');
				$action=lang('add '. 'phpgw_jinn_site_objects' );
				$parent_site_id=$_GET['parent_site_id'];
			}

			$this->template->set_var('form_action',$form_action);
			$this->template->set_var('where_key_form',$where_key_form);
			$this->template->set_var('where_value_form',$where_value_form);
			$this->template->pparse('out','form_header');


			$fields=$this->bo->so->phpgw_table_metadata('phpgw_jinn_site_objects');

//			_debug_array($fields);
   
            //sort array and remove non-functional elements
/*            $slice0=array_slice($fields,0,6);
            $slice1=array_slice($fields,9,2);
            $slice2=array_slice($fields,7,1);
            $slice3=array_slice($fields,6,1);
            unset($fields);
            $fields=array_merge($slice0,$slice1,$slice2,$slice3);
*/


			foreach ($fields as $testone => $fieldproperties)
			{
				$edit_value=$values_object[0][$fieldproperties[name]];

				if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on'])
				{
					$row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
				}
				else
				{
					$row_color=$GLOBALS['phpgw_info']['theme']['row_on'];
				}

				$input_name='FLD'.$fieldproperties[name];
				$display_name = lang(ucfirst(strtolower(ereg_replace("_", " ", $fieldproperties[name]))));
				$input_max_length=' maxlength="'. $fieldproperties[len].'"';
				$input_length=$fieldproperties[len];
				$value=$values_object[0][$fieldproperties[name]];

				if ($input_length>40)
				{
					$input_length=40;
				}

				if (eregi("auto_increment", $fieldproperties[flags]) || $fieldproperties['default']=="nextval('seq_phpgw_jinn_site_objects'::text)")
//				if (eregi("auto_increment", $fieldproperties[flags]))
				{
					if (!$value)
					{
						$display_value=lang('automatic');
					}
					else
					{
//					   $display_value=lang('automatic') . ' <strong>'.$value.'</strong>';
						$display_value=$value;
					}

					$input='<input type="hidden" name="'.$input_name.'" value="'.$value.'">'.$display_value;
				}

				elseif ($fieldproperties[name]=='parent_site_id')
				{
					if($value) // when we are editing
					{
						$parent_site_name=$this->bo->so->get_site_name($value);
						$parent_site_id=$value; //id for further use in formgeneration
						$input="<input type=hidden name=\"$input_name\" value=\"$value\">";
						$input.=$parent_site_name;
					}
					elseif($parent_site_id) //when we are adding
					{
						$parent_site_name=$this->bo->so->get_site_name($parent_site_id);
						$input="<input type=hidden name=\"$input_name\" value=\"$parent_site_id\">";
						$input.=$parent_site_name;
					}
					else // when we are adding without parent_site_id; this must
					{
						$input.=lang('Something went wrong');
					}
				}

				elseif ($fieldproperties[name]=='table_name')
				{
					$table_name=$value;
					$tables=$this->bo->so->site_tables_names($parent_site_id);

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
							$table_array[]=array
							(
								'name'=> $table[table_name],
								'value'=> $table[table_name]
							);
						}

						if($where_key && $where_value && in_array($table_name,$tables_check_arr))
						{
							$valid_table_name=true;

						}
						elseif(!$where_key && !$where_value && !$value)
						{
							$valid_table_name=true;
						}
						else
						{
							$error_msg='<font color=red>'.lang('Tablename <i>%1</i> is not correct. Probably the tablename has changed or or the table is deleted. Please select a new table or delete this object',$table_name).'</font><br>';
						}

						$input=$error_msg.'<select name="'.$input_name.'">';

						$input.=$this->ui->select_options($table_array,$value,false);
						$input.='</select>';


					}



				}
				elseif ($fieldproperties[name]=='upload_path')
				{
					$input='<input type="text" name="'.$input_name.'" size="'.$input_length.'" $input_max_length" value="'.$value.'">';
				}
				elseif ($fieldproperties[type]=='varchar' || $fieldproperties[type]=='string')
				{
					$input='<input type="text" name="'.$input_name.'" size="'.$input_length.'" input_max_length" value="'.$value.'">';
				}
				elseif ($fieldproperties[type]=='int')
				{
					$input='<input type="text" name="'.$input_name.'" size="10" value="'.$value.'">';
				}
				elseif ($fieldproperties[type]=='timestamp')
				{
					if ($value)
					{
						$input=$this->bo->format_date($value);
					}
					else
					{
						$input = 'automatisch';
					}
				}

				/*************************************************
				* RELATION SECTION                               *
				*************************************************/
				// this is a long one: relations
				// html has to move to a template
				elseif ($fieldproperties[name]=='relations')
				{

					unset($input);
					if ($where_key && $where_value && $valid_table_name)
					{
						$i=1;
						if ($value)
						{
							$input.='<b>'.lang('Relations for this object').'</b><br><input type="hidden" name="FLDrelations" value="'.$value.'">';
							$relations=explode('|',$value);
							foreach($relations as $relation)
							{
								$relation_parts=explode(':',$relation);
								if ($relation_parts[0]==1)
								{
									$relation_type='ONE TO MANY';
									$input.=$i.'.: <u>'.$relation_parts[1].'</u> has a <u>'.$relation_type.'</u> relation with <u>'.$relation_parts[3].'</u> showing <u>'.$relation_parts[4].'</u><input type=checkbox name="DELrelation'.$i.'" value="'.$relation.'">delete<br><br>';

								}
								elseif ($relation_parts[0]==2)
								{
									$relation_type='MANY TO MANY';
									//This table, 'tablename',identifyerfield represented by ..... has a MANY TO MANY relation with ..... represented by ......... showing ........
									$input.="$i: The identifierfield of this table, <u>$table_name.id</u>, represented by <u>$relation_parts[1]</u> has a <u>$relation_type</u> relation with <u>$relation_parts[3]</u> represented by <u>$relation_parts[2]</u> showing <u>$relation_parts[4]</u><input type=checkbox name=\"DELrelation$i\" value=\"$relation\">delete<br><br>";
								}

								$i++;
							}
						}
						// ADD NEW ONE WITH MANY RELATION
						//die($parent_site_id);

						if($fields=$this->bo->so->site_table_metadata($parent_site_id,$table_name))
						{

							$input.='<b>'.lang('Add new ONE TO MANY').'</b> relation<b><br><table><tr><td colspan=2>field:<br>';
							$input.='<select name="1_relation_org_field">';


							foreach($fields as $field)
							{
								$fields_array[]=array
								(
									'name'=> $field[name],
									'value'=> $field[name]
								);
							}

							$input.=$this->ui->select_options($fields_array,$value,true);
							$input.='</select></td></tr>';

							// related table and field
							$input.='<tr><td>has a ONE TO MANY relation with:<br>';
							$input.='<select name="1_relation_table_field">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->so->site_table_metadata($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$related_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->ui->select_options($related_fields_array,'',true);
							$input.='</select></td></tr>';

							// displaying
							$input.='<tr><td colspan=2>displaying field:<br>';
							$input.='<select name="1_display_field">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->so->site_table_metadata($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$display_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->ui->select_options($display_fields_array,'',true);
							$input.='</select></td></tr></table><br>';
						}


						// ADD NEW MANY WITH MANY RELATION
						if (is_array($table_array))
						{

							$input.='<b>'.lang('Add new MANY TO MANY relation')."<b><br><table><tr><td colspan=2>The identifyer from this table ('$table_name.id') represented by:<br>";
							$input.='<select name="2_relation_via_primary_key">';

							foreach($table_array as $table)
							{
								$fields=$this->bo->so->site_table_metadata($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->ui->select_options($fields_array,$value,true);
							$input.='</select></td></tr>';

							// related table and field
							$input.='<tr><td>has a MANY TO MANY relation with:<br>';
							$input.='<select name="2_relation_foreign_key">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->so->site_table_metadata($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$related_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->ui->select_options($related_fields_array,'',true);
							$input.='</select></td></tr>';

							// represented by ....
							$input.='<tr><td colspan=2>represented by:<br>';
							$input.='<select name="2_relation-via-foreign-key">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->so->site_table_metadata($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$display_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->ui->select_options($display_fields_array,'',true);
							$input.='</select></td></tr>';

							// related table and field
							$input.='<tr><td>showing:<br>';
							$input.='<select name="2_display_field">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->so->site_table_metadata($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$related_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->ui->select_options($related_fields_array,'',true);
							$input.='</select></td></tr>';

							// end table
							$input.='</table>';

						}

					}
					elseif(!$where_key && !$where_value)
					{
						$input.='come back in edit-mode to add relations';
					}
					else
					{
						$input.='come back after new valid tablename is saved to add relations';
					}



					// end relations
				}

				/*************************************************
				* FORM PLUGIN SECTION                            *
				*************************************************/
				// this will become general field level configuration
				elseif($fieldproperties[name]=='plugins') 				
				{
					unset($input);
					if ($where_key && $where_value && $valid_table_name)
					{

						if(!$value) $value='TRUE';

						$input.='<input type="hidden" name="FLDplugins" value="'.$value.'">';

						if ($fields=$this->bo->so->site_table_metadata($parent_site_id,$table_name))
						{

						    //_debug_array($fields);
							$input.='<table border="1" ><tr><td>'.lang('fields').'</td>';
								  $input.='<td>'.lang('form input plugin').'</td><td>&nbsp;</td><td>'.lang('extra info').'</td></tr>';

							$plugin_settings=explode('|',$value);

							foreach($fields as $field)
							{

								unset($sets);
								unset($plg_name);
								unset($plg_conf);
								if (is_array($plugin_settings))
								{
									foreach($plugin_settings as $setting)
									{
										$sets=explode(':',$setting);
										if ($sets[0]==$field['name'])
										{
											$plg_name=$sets[1];
											$plg_conf=$sets[3];
										}

									}
								}
								$input.='<tr><td>';
								$input.=$field['name'] . '</td><td>';

								if ($field['name']!='id')
								{

									// remove

									$plugin_hooks=$this->bo->plugin_hooks($field['type']);
									$options=$this->ui->select_options($plugin_hooks,$plg_name,true);


									if ($options)
									{
										$input.='<select name="PLG'.$field['name'].'">';
										$input.=$options;
										$input.='</select></td>';

										/*
										$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.plug_config&
										plug_orig='.$plg_name.'&
										plug_name=document.frm.PLG'.$field['name'].'.value\'&
										hidden_name=CFG_PLG'.$field['name'].'&
										hidden_val='.$plg_conf)
										*/

										/************************************
										* here comes the plugin conf button *
										************************************/
										$input.='<td>
										<input type="hidden" name="CFG_PLG'.$field['name'].'" value="'.$plg_conf.'">

										<input type="button" onClick="parent.window.open(\''.$GLOBALS['phpgw']->link('/jinn/plgconfwrapper.php','foo=bar').'&plug_orig='.$plg_name.'&plug_name=\'+document.frm.PLG'.$field['name'].'.value+\'&hidden_name=CFG_PLG'.$field['name'].'&hidden_val='.rawurlencode($plg_conf).'\', \'pop'.$field['name'].'\', \'width=400,height=400,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no\')" value="'.lang('configure').'">
										</td>';
									}
								}
								else
								{
									$input.='<td>&nbsp;</td>';
								}


								$input.='</tr>';
							}
							$input.='</table>';
						}
					}
					elseif(!$where_key && !$where_value)
					{
						$input.=lang('come back in edit mode to configure plugins');
					}
					else
					{
						$input.=lang('come back after new valid tablename is saved to configure plugins');
					}
				}
				else
				{
					$value = ereg_replace ("(<br />|<br/>)","",$value);
					$input='<textarea name="'.$input_name.'" cols="60" rows="15">'.$value.'</textarea>';
				}

				$this->template->set_var('row_color',$row_color);
				$this->template->set_var('input',$input);
				$this->template->set_var('fieldname',$display_name);

				$this->template->pparse('out','object_field');

			}

			$cancel_button='<input type="button" onClick="location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.add_edit_site&cancel=true&where_key=site_id&where_value='.$parent_site_id).'\'" value="'.lang('cancel').'">';

			$delete_button='<input type="button" onClick="location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.boadmin.del_phpgw_jinn_site_objects&where_key=object_id&where_value='.$where_value).'\'" value="'.lang('delete').'">';

			$this->template->set_var('save_button',lang('save and finish'));
			$this->template->set_var('save_and_continue_button',lang('save and contiue'));
			$this->template->set_var('reset_form',lang('reset form'));
			$this->template->set_var('delete',$delete_button);
			$this->template->set_var('cancel',$cancel_button);
			$this->template->set_var('test_access',$test_access);
			$this->template->set_var('extra_buttons',$extra_buttons);
			$this->template->pparse('out','form_footer');

		}

	}


	?>
