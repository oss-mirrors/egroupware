<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
	Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

	phpGroupWare - http://www.phpgroupware.org

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

	class uiadminaddedit extends uiadmin
	{

		function uiadminaddedit($bo)
		{
			$this->bo = $bo; 
			$this->template = $GLOBALS['phpgw']->template;
		}

		function render_form($table)
		{

			$this->template->set_file(array
			(
				'form_header' => 'form_header.tpl',
				'object_field' => 'object_field.tpl',
				'form_footer' => 'form_footer.tpl'
			));

			$phpgw_table=$table;
			$where_condition=$GLOBALS[where_condition];

			if ($where_condition)
			{
				$form_action = $GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.uiadmin.update_$table");
				$where_condition_form="<input type=\"hidden\" name=\"where_condition\" value=\"$where_condition\">";
				$values_object= $this->bo->get_phpgw_records($table,$where_condition,'','','name');
				$add_edit_button=lang('edit');
				$action=lang('edit '. $table);
			}
			else
			{
				$form_action = $GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.uiadmin.insert_$table");
				$add_edit_button=lang('add');
				$action=lang('add '. $table );
				$parent_site_id=$GLOBALS['HTTP_POST_VARS']['parent_site_id'];

			}

			$this->template->set_var('form_action',$form_action);
			$this->template->set_var('where_condition_form',$where_condition_form);
			$this->template->pparse('out','form_header');

			$fields = $this->bo->get_phpgw_fieldproperties($table);

			foreach ( $fields as $fieldproperties )
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

				if (eregi("auto_increment", $fieldproperties[flags]))
				{
					if (!$value)
					{
						$display_value=lang('automatic');
					}
					else
					{
						$display_value=lang('automatic');
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
						die('Something went wrong, contact the uberadministrator');
					}
				}

				elseif ($fieldproperties[name]=='table_name')
				{

					//set vars for further generation
					$table_name=$value;

					// on change submit
					$input='<select name="'.$input_name.'">';
					$tables=$this->bo->get_site_tables($parent_site_id);

					foreach($tables as $table)
					{
						$table_array[]=array
						(
							'name'=> $table[table_name],
							'value'=> $table[table_name]
						);
					}

					$input.=$this->bo->make_options($table_array,$value);
					$input.='</select>';
				}
				elseif ($fieldproperties[name]=='upload_path')
				{
					$input='<input type="text" name="'.$input_name.'" size="'.$input_length.'" $input_max_length" value="'.$value.'"><input type=button onClick=\'PcjsOpenExplorer("jinn/inc/pcsexplorer.php", "forms.frm.'.$input_name.'.value", "type=dir", "calling_dir=", "start_dir=")\' value="'.lang('select directory').'">';
				}
				elseif ($fieldproperties[type]=='string')
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
					if ($where_condition)
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
									$relation_type='ONE WITH MANY';
									$input.=$i.'.: <u>'.$relation_parts[1].'</u> has a <u>'.$relation_type.'</u> relation with <u>'.$relation_parts[3].'</u> showing <u>'.$relation_parts[4].'</u><input type=checkbox name="DELrelation'.$i.'" value="'.$relation.'">delete<br><br>';

								}
								elseif ($relation_parts[0]==2)
								{
									$relation_type='MANY WITH MANY';
									//This table, 'tablename',identifyerfield represented by ..... has a MANY WITH MANY relation with ..... represented by ......... showing ........
									$input.="$i: The identifierfield of this table, <u>$table_name.id</u>, represented by <u>$relation_parts[1]</u> has a <u>$relation_type</u> relation with <u>$relation_parts[3]</u> represented by <u>$relation_parts[2]</u> showing <u>$relation_parts[4]</u><input type=checkbox name=\"DELrelation$i\" value=\"$relation\">delete<br><br>";
								}

								$i++;
							}
						}
						// ADD NEW ONE WITH MANY RELATION
						if($fields=$this->bo->get_site_fieldproperties($parent_site_id,$table_name))
						{

							$input.='<b>'.lang('Add new ONE WITH MANY').'</b> relation<b><br><table><tr><td colspan=2>field:<br>';
							$input.='<select name="1_relation_org_field">';


							foreach($fields as $field)
							{
								$fields_array[]=array
								(
									'name'=> $field[name],
									'value'=> $field[name]
								);
							}

							$input.=$this->bo->make_options($fields_array,$value);
							$input.='</select></td></tr>';

							// related table and field
							$input.='<tr><td>has a ONE WITH MANY relation with:<br>';
							$input.='<select name="1_relation_table_field">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->get_site_fieldproperties($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$related_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->bo->make_options($related_fields_array,'');
							$input.='</select></td></tr>';

							// displaying
							$input.='<tr><td colspan=2>displaying field:<br>';
							$input.='<select name="1_display_field">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->get_site_fieldproperties($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$display_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->bo->make_options($display_fields_array,'');
							$input.='</select></td></tr></table><br>';
						}


						// ADD NEW MANY WITH MANY RELATION
						if (is_array($table_array))
						{

							$input.='<b>'.lang('Add new MANY WITH MANY relation')."<b><br><table><tr><td colspan=2>The identifyer from this table ('$table_name.id') represented by:<br>";
							$input.='<select name="2_relation_via_primary_key">';

							foreach($table_array as $table)
							{
								$fields=$this->bo->get_site_fieldproperties($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->bo->make_options($fields_array,$value);
							$input.='</select></td></tr>';

							// related table and field
							$input.='<tr><td>has a MANY WITH MANY relation with:<br>';
							$input.='<select name="2_relation_foreign_key">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->get_site_fieldproperties($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$related_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->bo->make_options($related_fields_array,'');
							$input.='</select></td></tr>';

							// represented by ....
							$input.='<tr><td colspan=2>represented by:<br>';
							$input.='<select name="2_relation-via-foreign-key">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->get_site_fieldproperties($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$display_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->bo->make_options($display_fields_array,'');
							$input.='</select></td></tr>';

							// related table and field
							$input.='<tr><td>showing:<br>';
							$input.='<select name="2_display_field">';
							foreach($table_array as $table)
							{
								$fields=$this->bo->get_site_fieldproperties($parent_site_id,$table[name]);
								foreach($fields as $field)
								{
									$related_fields_array[]=array
									(
										'name'=> $table[name].'.'.$field[name],
										'value'=> $table[name].'.'.$field[name]
									);
								}
							}
							$input.=$this->bo->make_options($related_fields_array,'');
							$input.='</select></td></tr>';

							// end table
							$input.='</table>';

						}

					}
					else
					{
						$input.='come back in edit-mode to add relations';
					}
					// end relations
				}



				/*************************************************
				* FORM PLUGIN SECTION                            *
				*************************************************/
				elseif($fieldproperties[name]=='plugins')
				{
					unset($input);
					if ($where_condition)
					{

						if(!$value) $value='TRUE';

						$input.='<input type="hidden" name="FLDplugins" value="'.$value.'">';

						if ($fields=$this->bo->get_site_fieldproperties($parent_site_id,$table_name))
						{

							$input.='<table border=1><tr><td>'.lang('fields').'</td>';
							$input.='<td>'.lang('form input plugin').'</td><td>&nbsp;</td></tr>';

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
									$options=$this->bo->plug->make_plugins_options($field['type'],$plg_name);
									if ($options)
									{
										$input.='<select name="PLG'.$field['name'].'">';
										$input.=$options;
										$input.='</select></td>';

										/************************************
										* here comes the plugin conf button *
										************************************/
										$input.='<td>
										<input type="hidden" name="CFG_PLG'.$field['name'].'" value="'.$plg_conf.'"><input type="button" onClick="parent.window.open(\''.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.plug_config&plug_orig='.$plg_name.'&plug_name=\'+document.frm.PLG'.$field['name'].'.value+\'&hidden_name=CFG_PLG'.$field['name'].'&hidden_val='.$plg_conf).'\', \'pop'.$field['name'].'\', \'width=400,height=300,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no\')" value="'.lang('configure').'"></td>';
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
					else 
					{
						$input.=lang('come back in edit mode for configuring plugins');
					}
				}
				else
				{
					die();
					$value = ereg_replace ("(<br />|<br/>)","",$value);
					$input='<textarea name="'.$input_name.'" cols="60" rows="15">'.$value.'</textarea>';
				}



				$this->template->set_var('row_color',$row_color);
				$this->template->set_var('input',$input);
				$this->template->set_var('fieldname',$display_name);

				$this->template->pparse('out','object_field');

			}

			if ($phpgw_table=='phpgw_jinn_site_objects')
			{                

				$cancel_button='<input type=button onClick="location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.add_edit_phpgw_jinn_sites&where_condition=site_id='.$parent_site_id).'\'" value="'.lang('cancel').'">';

			}
			elseif($phpgw_table=='phpgw_jinn_sites')
			{
					$cancel_button='<input type=button onClick="location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.browse_phpgw_jinn_sites&where_condition=site_id='.$this->bo->site_id).'\'" value="'.lang('cancel').'">';

				$extra_buttons='<td>
				<script>
				function testdbfield()
				{
					dbvals=document.frm.FLDsite_db_name.value+\':\'+document.frm.FLDsite_db_host.value+\':\'+document.frm.FLDsite_db_user.value+\':\'+document.frm.FLDsite_db_password.value+\':\'+document.frm.FLDsite_db_type.value;

					sessionlink=\''.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.test_db_access').'\';
					link=sessionlink+\'&dbvals=\'+dbvals;
					parent.window.open(link, \'width=400,height=300,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no\')
				}
				</script>
				
				<input type=hidden name=testdbvals>
				<input type="button" onClick="testdbfield()" value="'.lang('test database access').'">
							
				</td>

				<td><input type=button onClick="location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.export_site&where_condition=site_id='.$values_object[0][site_id]).'\'" value="'.lang('export this site').'"></td>';

			}
			$this->template->set_var('add_edit_button',$add_edit_button);
			$this->template->set_var('reset_form',lang('reset form'));
			$this->template->set_var('delete',lang('delete'));
			$this->template->set_var('cancel',$cancel_button);
			$this->template->set_var('test_access',$test_access);
			$this->template->set_var('extra_buttons',$extra_buttons);
			$this->template->pparse('out','form_footer');

		}

	}


?>
