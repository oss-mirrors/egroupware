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
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 $this->ui = CreateObject('jinn.uicommon',$this->bo);
		 $this->boreport = CreateObject('jinn.boreport');
		  }
 	  
	  function render_form($where_key, $where_value)
	  {
		 $this->where_key=$where_key;
		 $this->where_value=$where_value;
		 $this->tplsav2->assign('where_key',$this->where_key);
		 $this->tplsav2->assign('where_value',$this->where_value);
		 
		 if($this->where_key && $this->where_value)
		 {
			$this->bool_edit_record=true;
			$this->object_values=$this->bo->so->get_object_values($this->where_value);
			$this->parent_site_id=$this->object_values[parent_site_id];
			$table_name = $this->object_values['table_name'];
		 }
		 else
		 {
			$this->parent_site_id=$_GET[parent_site_id];
		 }
		 $this->available_tables=$this->bo->so->site_tables_names($this->parent_site_id);

		 $tables=$this->available_tables;

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
		echo($error_msg);
		if(!$this->object_values[parent_site_id])
		{
		   $this->object_values[parent_site_id]=$_GET[parent_site_id];
		}
		$this->tplsav2->assign('rapport_list',$this->boreport->get_report_list($this->object_values[unique_id],2));
		 $this->tplsav2->assign('tables',$this->available_tables);	
		 $this->tplsav2->assign('global_values',$this->object_values);	
		 if($this->bool_edit_record && $this->valid_table_name)
		 {
			$this->render_plugins();
			$this->render_relations();

	 }
		$this->tplsav2->display('frm_edit_object.tpl.php');

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
									
						$mandatory[]=($plugin_conf_arr[field_mandatory]==1) ? ' checked' : '';
						$default[]=($plugin_conf_arr[field_show_default]==1) ? ' checked' : '';
						$show_frm[]=($plugin_conf_arr[field_form_visible]==1) ? ' checked' : '0';
						$position[]=$plugin_conf_arr[field_position];
					}
					else
					{
					   //default values:
					   	$mandatory[]='';
						$default[]=' checked';
						$show_frm[]=' checked';
						$position[]= '';
					}
				  }


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
						elseif($plg_name != '')
						{
							$plugin_hooks[] = array('value' => $plg_name, 'name' => $plg_name.' (unknown)');
						}
					}
					
					$options=$this->ui->select_options($plugin_hooks,$plg_name,false);
					$plugins[] =$options;
				  if ($options) 
				  {
					 $popup_onclick_plug[]='parent.window.open(\''.$GLOBALS['phpgw']->link('/jinn/plgconfwrapper.php','screen=plugconf').'&plug_orig='.$plg_name.'&plug_name=\'+document.frm.FIELD_'.$field['name'].'_PLG.value+\'&hidden_name=FIELD_'.$field['name'].'_PLC&field_name='.$field['name'].'&object_id='.$this->object_values['object_id'].'&hidden_val='.rawurlencode($plg_conf).'\', \'pop'.$field['name'].'\', \'width=600,height=500,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no\')';

					 $popup_onclick_name_and_help[]='parent.window.open(\''.$GLOBALS['phpgw']->link('/jinn/plgconfwrapper.php','screen=helpconf').'&plug_orig='.$plg_name.'&plug_name=\'+document.frm.FIELD_'.$field['name'].'_PLG.value+\'&hidden_name=FIELD_'.$field['name'].'_PLC&field_name='.$field['name'].'&object_id='.$this->object_values['object_id'].'&hidden_val='.rawurlencode($plg_conf).'\', \'pop'.$field['name'].'\', \'width=500,height=400,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no\')';

		 }
				else
				{
				   $popup_onclick_plug[]="";
				   $popup_onclick_name_and_help[]="";
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
			   $this->tplsav2->assign('hidden_value',$hidden_value);
			   $this->tplsav2->assign('fields',$fields);
			   $this->tplsav2->assign('plugins',$plugins);
			   $this->tplsav2->assign('pop_plug',$popup_onclick_plug);
			   $this->tplsav2->assign('pop_name',$popup_onclick_name_and_help);
			   $this->tplsav2->assign('mandatory',$mandatory);
			   $this->tplsav2->assign('default',$default);
			   $this->tplsav2->assign('show_frm',$show_frm);
			   $this->tplsav2->assign('position',$position);

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
		 $this->tplsav2->assign('code',$hidden_value);	 
		 $i=1;
		 if ($relations_arr)
		 {
			$this->type1_num=0;
			$this->type2_num=0;
			$this->type3_num=0;
			$arr_rel_format;
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
				  $arr_rel_format[1][]= $r1txt;
			   }
			   elseif ($relation[type]==2)
			   {
						//check if more than one field is displayed:
					$display_fields=$relation[display_field];
					if($relation[display_field_2]!='') $display_fields.=', '.$relation[display_field_2];
					if($relation[display_field_3]!='') $display_fields.=', '.$relation[display_field_3];
					
				  $r2txt=lang('The identifierfield of this table, %1, represented by %2 has a many-to-many with %3 represented by %4 showing %5',$table_name,$relation[via_primary_key],$relation[foreign_key],$relation[via_foreign_key],$display_fields);
				  $this->type2_num++;
				  $arr_rel_format[2][]= $r2txt;

			   }
			   elseif ($relation[type]==3)
			   {
				  $r3txt=lang('%1 has a one-to-one relation with %2 using the configuration of object %3',$relation[org_field],$relation[related_with],$relation[object_conf]);
				  $this->type3_num++;
				 $arr_rel_format[3][]= $r3txt;

			   }
   			   $i++;
			}
		 }
		 $this->tplsav2->assign('relations',$arr_rel_format);
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
			$this->tplsav2->assign('rel1_options1',$rel1_options1);
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
			$this->tplsav2->assign('rel1_options2',$rel1_options2);
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
			$this->tplsav2->assign('rel1_options3',$rel1_options3);

		//	$this->template->parse('out','relations1');
		 }

		 /**********************************
		 * ADD NEW MANY TO MANY RELATION *
		 **********************************/

		 if (is_array($this->table_array))
		 {



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
			$this->tplsav2->assign('rel2_options1',$rel2_options1);
			//	$this->template->set_var('rel2_options1',$rel2_options1);


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
			$this->tplsav2->assign('rel2_options2',$rel2_options2);

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
			$this->tplsav2->assign('rel2_options3',$rel2_options3);

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
			$this->tplsav2->assign('rel2_options4',$rel2_options4);
	 }


		 /************************************
		 * ADD NEW ONE TO ONE RELATION (3)  *
		 ************************************/
		 if($fields=$this->bo->so->site_table_metadata($this->parent_site_id,$table_name))
		 {
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
			$this->tplsav2->assign('rel3_options1',$rel3_options1);

			$rel3_options2=$this->ui->select_options($related_fields_array,'',true);
			$this->tplsav2->assign('rel3_options2',$rel3_options2);

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
			$this->tplsav2->assign('rel3_options3',$rel3_options3);
		 }



	  }
   }
?>
