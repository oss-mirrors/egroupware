<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
	Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

	eGroupWare - http://www.egroupware.org

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

	class uiu_edit_record // extends uiuser
	{
		var $public_functions = Array
		(
			'display_form'		=> True,
			'view_record'		=> True
		);
		var $bo;
		var $template;
		var $ui;
		
		function uiu_edit_record()
		{
			$this->bo = CreateObject('jinn.bouser');
			$this->template = $GLOBALS['phpgw']->template;
			$this->ui = CreateObject('jinn.uicommon');
			if($this->bo->so->config[server_type]=='dev')
			{
				$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
			}
			$this->ui->app_title=$dev_title_string;//.lang('Moderator Mode');
		}

		function display_form()
		{
			$this->render_header();
			$this->render_fields();
			$this->render_many_to_many();
			$this->render_one_to_one();
		}

		function view_record()
		{
		   $this->ui->header('View record');
		   $this->ui->msg_box($this->bo->message);

		   $this->main_menu();	
  
		   $this->template->set_file(array(
			  'view_record' => 'view_record.tpl'
		   ));

		   $this->template->set_block('view_record','header','');
		   $this->template->set_block('view_record','rows','rows');
		   $this->template->set_block('view_record','back_button','back_button');
		   $this->template->set_block('view_record','footer','footer');

		   $where_string=$this->bo->where_string;
		   $form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.object_update');
		   $where_string_form='<input type="hidden" name="where_string" value="'.base64_encode($where_string).'">';
		   $this->template->set_var('form_attributes',$form_attributes);
		   $this->template->set_var('form_action',$form_action);
		   $this->template->set_var('where_string_form',$where_string_form);

		   $values_object= $this->bo->so->get_record_values($this->bo->site_id,$this->bo->site_object[table_name],'','','','','name','','*',$where_string);
		
		   /* get all fieldproperties (name, type, etc...) */
		   $fields = $this->bo->so->site_table_metadata($this->bo->site_id,$this->bo->site_object[table_name]);

		   /* The main loop to create all rows with input fields start here */ 
		   foreach ( $fields as $fieldproperties )
		   {
			  $value=$values_object[0][$fieldproperties[name]];	/* get value */
			  $input_name='FLD'.$fieldproperties[name];	/* add FLD so we can identify the real input HTTP_POST_VARS */
			  $display_name = ucfirst(strtolower(ereg_replace("_", " ", $fieldproperties[name]))); /* replace _ for a space */
			  /* ---------------------- start fields -------------------------------- */

			  /* Its an identifier field */
			  if (eregi("auto_increment", $fieldproperties[flags]) || eregi("nextval",$fieldproperties['default']))
			  //				if (eregi("auto_increment", $fieldproperties[flags]))
			  {
				 if(!$value) $display_value=lang('automaticly incrementing');
				 $input='<b>'.$value.'</b><input type="hidden" name="'.$input_name.'" value="'.$value.'">'.$display_value;
				 $record_identifier[name]=$input_name;
				 $record_identifier[value]=$value;
			  }

			  elseif ($fieldproperties[type]=='varchar' || $fieldproperties[type]=='string' ||  $fieldproperties[type]=='char')
			  {
				 /* If this integer has a relation get that options */
				 if (is_array($fields_with_relation1) && in_array($fieldproperties[name],$fields_with_relation1))
				 {
					//get related field vals en displays
					$related_fields=$this->bo->get_related_field($relation1_array[$fieldproperties[name]]);

					$input= '<select name="'.$input_name.'">';
					   $input.= $this->ui->select_options($related_fields,$value,true);
					   $input.= '</select> ('.lang('real value').': '.$value.')';
				 }
				 else
				 {
					if($fieldproperties[len] && $fieldproperties[len]!=-1)
					{
					   $attr_arr=array(
						  'max_size'=>$fieldproperties[len],
					   );
					}
					$input=$this->bo->get_plugin_ro($input_name,$value,'string', $attr_arr);
				 }
			  }

			  elseif ($fieldproperties[type]=='int' || $fieldproperties[type]=='real' || $fieldproperties[type]=='smallint'|| $fieldproperties[type]=='tinyint' || $fieldproperties[type]=='int4' )
			  {
				 /* If this integer has a relation get that options */
				 if (is_array($fields_with_relation1) && in_array($fieldproperties[name],$fields_with_relation1))
				 {
					//get related field vals en displays
					$related_fields=$this->bo->get_related_field($relation1_array[$fieldproperties[name]]);


					$input= '<select name="'.$input_name.'">';
					   $input.= $this->ui->select_options($related_fields,$value,true);
					   $input.= '</select> ('.lang('real value').': '.$value.')';
				 }
				 else
				 {	
					$input=$this->bo->get_plugin_ro($input_name,$value,'int',$attr_arr);
				 }
			  }

			  elseif ($fieldproperties[type]=='timestamp')
			  {
				 if ($value)
				 {
					$input=$this->bo->get_plugin_ro($input_name,$value,'timestamp',$attr_arr);
				 }
				 else
				 {
					$input = lang('automatic');
				 }
			  }

			  elseif ($fieldproperties[type]=='blob' && ereg('binary',$fieldproperties[flags]))
			  {
				 // FIXME this is a quick hack make a standard routine
				 $tmpplugins=explode('|',str_replace('~','=',$this->bo->site_object['plugins']));
				 foreach ($tmpplugins as $tval)
				 {
					if (stristr($tval, $fieldproperties[name])) 
					{
					   $has_plugin=true;
					   break;
					}
				 }
				 if($has_plugin)
				 {
					$input=$this->bo->get_plugin_ro($input_name,$value,'blob',$attr_arr);
				 }
				 else
				 {
					$input = lang('binary');
				 }
			  }

			  elseif (ereg('text',$fieldproperties[type]) && ereg('binary',$fieldproperties[flags]))
			  {
				 $input = lang('binary');
			  }
			  elseif ($fieldproperties[type]=='blob' || ereg('text',$fieldproperties[type])) //then it is a textblob
			  {
				 $input=$this->bo->get_plugin_ro($input_name,$value,'blob',$attr_arr);
			  }
			  else
			  {
				 $input=$this->bo->get_plugin_ro($input_name,$value,'string',$attr_arr);
			  }

			  /* if there is something to render to this */
			  if($input!='hide')
			  {
				 if($this->bo->read_preferences('table_debugging_info')=='yes')
				 {
					$keys=array_keys($fieldproperties);
					$input.='<br/>';
					foreach($keys as $key)
					{
					   if(!$fieldproperties[$key]) continue;
					   $input.= $key.'='.$fieldproperties[$key].' ';

					}
				 }

				 /* set the row colors */
				 $GLOBALS['phpgw_info']['theme']['row_off']='#eeeeee';
				 if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on']) $row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
				 else $row_color=$GLOBALS['phpgw_info']['theme']['row_on'];

				 $this->template->set_var('row_color',$row_color);
				 $this->template->set_var('input',$input);
				 $this->template->set_var('fieldname',$display_name);

				 $this->template->parse('row','rows',true);
			  }
		   }

		   
		   if($this->bo->site_object[max_records]!=1)
		   {
			  $back_onclick='location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.browse_objects').'\'';
			  $this->template->set_var('back_onclick',$back_onclick);

			  $this->template->parse('extra_back_button','back_button');
		   }
		   else
		   {
			  $this->template->set_var('extra_back_button','');
		  }

		   
		   $edit_onclick='location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_edit_record.display_form&where_string='.base64_encode($where_string)).'\'';

		   $this->template->set_var('lang_edit',lang('edit this record'));
		   $this->template->set_var('lang_back',lang('back to record list'));
		   $this->template->set_var('edit_onclick',$edit_onclick);

		   $this->template->pparse('out','header');
		   $this->template->pparse('out','row');
		   $this->template->pparse('out','footer');
		
		}



		function render_header()
		{		
		if(!$this->bo->so->test_JSO_table($this->bo->site_object))
			{
				unset($this->bo->site_object_id);
				$this->bo->message['error']=lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']);

				$this->bo->save_sessiondata();
				$this->bo->common->exit_and_open_screen('jinn.uiuser.index');
			}				
			
			
			$this->template->set_file(array(
				'frm_edit_record' => 'frm_edit_record.tpl'
			));

			$this->template->set_block('frm_edit_record','form_header','');
			$this->template->set_block('frm_edit_record','js','js');
			$this->template->set_block('frm_edit_record','rows','rows');
			$this->template->set_block('frm_edit_record','form_footer','form_footer');
	
			$where_string=$this->bo->where_string;
			
			if ($where_string)
			{
			   /* set vars for edit form */
				$form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.object_update');
				$where_string_form='<input type="hidden" name="where_string" value="'.base64_encode($where_string).'">';

				$values_object= $this->bo->so->get_record_values($this->bo->site_id,$this->bo->site_object[table_name],'','','','','name','','*',$where_string);
			}
			else
			{
				/* vars for new record form */
				$form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.object_insert');
			}
			
			$add_edit_button_continue=lang('save and continue');
			$add_edit_button=lang('save and finish');

			$form_attributes='onSubmit="return onSubmitForm()"';

			$this->template->set_var('form_attributes',$form_attributes);
			$this->template->set_var('form_action',$form_action);
			$this->template->set_var('where_string_form',$where_string_form);
			$this->template->parse('form_header','form_header');

			/* get one with many relations */
			$relation1_array=$this->bo->extract_1w1_relations($this->bo->site_object[relations]);

			if (count($relation1_array)>0)
			{
				foreach($relation1_array as $relation1)
				{
					$fields_with_relation1[]=$relation1[field_org];
				}

			}
			
			/* get all fieldproperties (name, type, etc...) */
			$fields = $this->bo->so->site_table_metadata($this->bo->site_id,$this->bo->site_object[table_name]);
			
			/* The main loop to create all rows with input fields start here */ 
			foreach ( $fields as $fieldproperties )
			{
				$value=$values_object[0][$fieldproperties[name]];	/* get value */
				$input_name='FLD'.$fieldproperties[name];	/* add FLD so we can identify the real input HTTP_POST_VARS */
				$display_name = ucfirst(strtolower(ereg_replace("_", " ", $fieldproperties[name]))); /* replace _ for a space */


				/* ---------------------- start fields -------------------------------- */

				/* Its an identifier field */
				if (eregi("auto_increment", $fieldproperties[flags]) || eregi("nextval",$fieldproperties['default']))
//				if (eregi("auto_increment", $fieldproperties[flags]))
				{
					if(!$value) $display_value=lang('automaticly incrementing');
				   $input='<b>'.$value.'</b><input type="hidden" name="'.$input_name.'" value="'.$value.'">'.$display_value;
					$record_identifier[name]=$input_name;
					$record_identifier[value]=$value;
				}

				elseif ($fieldproperties[type]=='varchar' || $fieldproperties[type]=='string' ||  $fieldproperties[type]=='char')
				{

					/* If this integer has a relation get that options */
				   if (is_array($fields_with_relation1) && in_array($fieldproperties[name],$fields_with_relation1))
				   {

					  //get related field vals en displays
					   $related_fields=$this->bo->get_related_field($relation1_array[$fieldproperties[name]]);

					   $input= '<select name="'.$input_name.'">';
					   $input.= $this->ui->select_options($related_fields,$value,true);
					   $input.= '</select> ('.lang('real value').': '.$value.')';
				   }
				   else
				   {
					  if($fieldproperties[len] && $fieldproperties[len]!=-1)
					  {
						 $attr_arr=array(
							'max_size'=>$fieldproperties[len],
						 );
					  }
					  $input=$this->bo->get_plugin_fi($input_name,$value,'string', $attr_arr);
				   }
				}

				elseif ($fieldproperties[type]=='int' || $fieldproperties[type]=='real' || $fieldproperties[type]=='smallint'|| $fieldproperties[type]=='tinyint' || $fieldproperties[type]=='int4' )
				{
					/* If this integer has a relation get that options */
					if (is_array($fields_with_relation1) && in_array($fieldproperties[name],$fields_with_relation1))
					{
						//get related field vals en displays
						$related_fields=$this->bo->get_related_field($relation1_array[$fieldproperties[name]]);


						$input= '<select name="'.$input_name.'">';
						$input.= $this->ui->select_options($related_fields,$value,true);
						$input.= '</select> ('.lang('real value').': '.$value.')';
					}
					else
					{	
					   $input=$this->bo->get_plugin_fi($input_name,$value,'int',$attr_arr);
					}
				}

				elseif ($fieldproperties[type]=='timestamp')
				{
					if ($value)
					{
					   $input=$this->bo->get_plugin_fi($input_name,$value,'timestamp',$attr_arr);
					}
					else
					{
						$input = lang('automatic');
					}
				}

				elseif ($fieldproperties[type]=='blob' && ereg('binary',$fieldproperties[flags]))
				{
				   // FIXME this is a quick hack make a standard routine
				   $tmpplugins=explode('|',str_replace('~','=',$this->bo->site_object['plugins']));
				   foreach ($tmpplugins as $tval)
				   {
					  if (stristr($tval, $fieldproperties[name])) 
					  {
						 $has_plugin=true;
						 break;
					  }
				   }
				   if($has_plugin)
				   {
					  $input=$this->bo->get_plugin_fi($input_name,$value,'blob',$attr_arr);
				   }
				   else
				   {
					  $input = lang('binary');
				   }
				}

				elseif (ereg('text',$fieldproperties[type]) && ereg('binary',$fieldproperties[flags]))
				{
					$input = lang('binary');
				 }
				 elseif ($fieldproperties[type]=='blob' || ereg('text',$fieldproperties[type])) //then it is a textblob
				{
				   $input=$this->bo->get_plugin_fi($input_name,$value,'blob',$attr_arr);
				}
				else
				{
				   $input=$this->bo->get_plugin_fi($input_name,$value,'string',$attr_arr);
				}

				/* if there is something to render to this */
				if($input!='hide')
				{
				   if($this->bo->read_preferences('table_debugging_info')=='yes')
				   {
					  $keys=array_keys($fieldproperties);
					  $input.='<br/>';
					  foreach($keys as $key)
					  {
						 if(!$fieldproperties[$key]) continue;
						 $input.= $key.'='.$fieldproperties[$key].' ';

					  }
				   }
		   
				   
				   /* set the row colors */
					$GLOBALS['phpgw_info']['theme']['row_off']='#eeeeee';
					if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on']) $row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
					else $row_color=$GLOBALS['phpgw_info']['theme']['row_on'];

					$this->template->set_var('row_color',$row_color);
					$this->template->set_var('input',$input);
					$this->template->set_var('fieldname',$display_name);

					$this->template->parse('row','rows',true);
				}
			}
			
			/***********************************************
			* MANY WITH MANY RELATION SECTION OF FORM      *
			***********************************************/

			$relation2_array=$this->bo->extract_1wX_relations($this->bo->site_object[relations]);
			if (count($relation2_array)>0)
			{

				$rel_i=0;
				foreach($relation2_array as $relation2)
				{

					$related_table=$relation2[display_table];
					$rel_i++;

					$display_name=lang('relation %1',$rel_i);

					$input= '<table cellspacing=0 cellpadding=3 border=1>';
					$input.= '<tr>';
					$input.= '<td valign=top>'.lang('all from').' '.$related_table .'<br>';
					$input.= '<select onDblClick=SelectPlace(\'M2M'.$rel_i.'\',\'all_related'.$rel_i.'\') multiple size=5 name="all_related'.$rel_i.'">';

					// make all possible options
					$options_arr= $this->bo->so->get_1wX_record_values($this->bo->site_id,'',$relation2,'all');
					
					$input.= $this->ui->select_options($options_arr,'',false);

					$input.= '</select>';
					$input.= '</td>';
					$input.= '<td align=center valign=top>'.lang('add or remove').'<br><br>';
					$input.= '<input onClick=SelectPlace(\'M2M'.$rel_i.'\',\'all_related'.$rel_i.'\'); type=button value=" >> " name=add>';
					$input.= '<br>';
					$input.= '<input onClick=DeSelectPlace(\'M2M'.$rel_i.'\'); type=button value=" << " name=remove>';
					$input.= '</td>';
					$input.= '<td valign=top>'.lang('related').' '.$related_table.'<br>';
					$input.= '<select onDblClick="DeSelectPlace(\'M2M'.$rel_i.'\')"  multiple size=5 name="M2M'.$rel_i.'"><br>';
					$submit_javascript.='saveOptions(\'M2M'.$rel_i.'\',\'MANY_OPT_STR_'.$rel_i.'\');';

					if(is_array($record_identifier) && $record_identifier[value])
					{
					   $record_id=$record_identifier[value];
					   $options_arr= $this->bo->so->get_1wX_record_values($this->bo->site_id,$record_id,$relation2,'stored');
					   $input.= $this->ui->select_options($options_arr,'',false);
					}
					elseif(!is_array($record_identifier))
					{
					   $input.= '<option>'.lang('This table has not unique identifier field').'</option>';
					   $input.= '<option>'.lang('Many 2 Many relations will not work').'</option>';
					}
					
					$input.= '</select>';
					$input.= '<input type=hidden name=MANY_REL_STR_'.$rel_i.' value='.$relation2[via_primary_key].'|'.$relation2[via_foreign_key].'>';
					$input.= '<input type=hidden name=MANY_OPT_STR_'.$rel_i.'>';
					$input.= '</td>';
					$input.= '</tr>';
					$input.= '</table>';

					$this->template->set_var('row_color',$row_color);
					$this->template->set_var('input',$input);
					$this->template->set_var('fieldname',$display_name);

					$this->template->parse('row','rows',true);
				}
			}

			if(!$where_string)
			{
				if($this->repeat_input=='true') $REPEAT_INPUT_CHECKED='CHECKED';
				$extra_buttons='<input type="checkbox" '.$REPEAT_INPUT_CHECKED.' name="repeat_input" value="true" /> '.lang('insert another record after saving');
			}
			$cancel_button='<input type=button onClick="location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.browse_objects').'\'" value="'.lang('cancel').'">';

			$this->template->set_var('submit_script',$submit_javascript);
			$this->template->parse('js','js');

			$this->template->set_var('add_edit_button_continue',$add_edit_button_continue);
			$this->template->set_var('add_edit_button',$add_edit_button);
			$this->template->set_var('reset_form',lang('reset form'));
			$this->template->set_var('delete',lang('delete'));
			$this->template->set_var('cancel',$cancel_button);
			$this->template->set_var('extra_buttons',$extra_buttons);
			$this->template->parse('form_footer','form_footer');

			unset($this->bo->message);

			#FIXME does this belong here?
			if (!is_object($GLOBALS['phpgw']->js))
			{
				$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
			}
			if (!strstr($GLOBALS['phpgw_info']['flags']['java_script'],'jinn'))
			{
				$GLOBALS['phpgw']->js->validate_file('jinn','display_func','jinn');
			}
		
			// FIXME say add new record in blokken or say edit record in blokken
			$this->ui->header('add or edit objects');
			$this->ui->msg_box($this->bo->message);

			$this->main_menu();	
			
			$popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');
			
			$this->template->set_var('popuplink',$popuplink);
			
			$this->template->pparse('out','form_header');
			$this->template->pparse('out','js');
			$this->template->pparse('out','row');
			$this->template->pparse('out','form_footer');
			$this->bo->save_sessiondata();
		}

		function render_fields(){}

		function render_many_to_many(){}

		function render_one_to_one(){}
		
		function main_menu()
		{
			$this->template->set_file(array(
				'main_menu' => 'main_menu.tpl'));

				// get sites for user and group and make options
				$sites=$this->bo->common->get_sites_allowed($GLOBALS['phpgw_info']['user']['account_id']);

				if(is_array($sites))
				{
					foreach($sites as $site_id)
					{
						$site_arr[]=array(
							'value'=>$site_id,
							'name'=>$this->bo->so->get_site_name($site_id)
						);
					}
				}

				$site_options=$this->ui->select_options($site_arr,$this->bo->site_id,true);


				if ($this->bo->site_id)
				{
					$objects=$this->bo->common->get_objects_allowed($this->bo->site_id, $GLOBALS['phpgw_info']['user']['account_id']);

					if (is_array($objects))
					{
						foreach ( $objects as $object_id) 
						{
							$objects_arr[]=array(
								'value'=>$object_id,
								'name'=>$this->bo->so->get_object_name($object_id)
							);
						}
					}

					$object_options=$this->ui->select_options($objects_arr,$this->bo->site_object_id,true);

				}
				else
				{
					unset($this->bo->site_object_id);
				}

				$this->template->set_var('jinn_main_menu',lang('JiNN Main Menu'));

				// set menu
				$this->template->set_var('site_objects',$object_options);
				$this->template->set_var('site_options',$site_options);

				$this->template->set_var('main_form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.index'));
				$this->template->set_var('select_site',lang('select site'));
				$this->template->set_var('select_object',lang('select_object'));
				$this->template->set_var('go',lang('go'));

				/* set admin shortcuts */
				// if site if site admin
				if($this->bo->site_id && $userisadmin)
				{
					$admin_site_link='<br><a href="'.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadminaddedit.').'">'.
					lang('admin:: edit site').'</a>';
				}
				$this->template->set_var('admin_site_link',$admin_site_link);
				$this->template->set_var('admin_object_link',$admin_object_link);

				$this->template->pparse('out','main_menu');

			}
		}

		?>
