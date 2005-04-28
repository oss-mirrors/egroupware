<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
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

   /* $Id$ */

   /**
   @package jinn_users_classes
   */
   class uiu_edit_record // extends uiuser
   {
	  var $public_functions = Array
	  (
		 'display_form'				=> True,
		 'multiple_entries'			=> True,
		 'view_multiple_records'	=> True,
		 'view_record'				=> True
	  );
	  var $bo;
	  var $template;
	  var $ui;

	  var $mult_records;	
	  var $mult_index;

	  var $o2o_index;

	  var $record_id_key;
	  var $record_id_val;

	  var $submit_javascript;
	  var $jstips;
	  var $jsmandatory;	//stores javascript calls that set specific fields to be checked if filled in
	  var $hiddenfields; //stores hidden inputs for rendering outside the form tables
	  
	  var $db_ftypes;

	  var $relation1_array; # one-2-many info array

	  /**
	  @function uiu_edit_record
	  @abstract class contructor that set header and inits bo
	  */
	  function uiu_edit_record()
	  {
		 $this->bo = CreateObject('jinn.bouser');
//_debug_array($this->bo->session['mult_where_array']);		 
		 $this->template = $GLOBALS['phpgw']->template;
		 $this->ui = CreateObject('jinn.uicommon',$this->bo);
		 if($this->bo->so->config[server_type]=='dev')
		 {
			$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
		 }
		 $this->ui->app_title=$dev_title_string;//.lang('Moderator Mode');

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');

	  }

	  function incl_js_validation_script()
	  {
		 $this->submit_javascript .= '
		 
		 var valid = true;
/*
		 _console = window.open("","console", "width=600,height=600,resizable");
		 _console.document.open("text/plain");
		 _console.document.writeln("checking mandatory fields:<br>");
*/
		 for(var i = 0; i < document.frm.length; i++)
		 {
			var element = document.frm.elements[i];
			//_console.document.writeln(element.name + " > " + element.value + "<br>");
			if(element.mandatory)
			{
				//_console.document.writeln("mandatory field. checking value: <br>");
				//_console.document.writeln("field type: " + element.type + "<br>");
				if(element.value == \'\' && element.type != "option")
				{
					//_console.document.writeln("error... element is empty!<br>");
					valid=false;
					element.style.backgroundColor="#FFAAAA";
				}
				else
				{
					element.style.backgroundColor="";
				}
			}
		 }

		//_console.document.close();
		 
		 if(!valid)
		 {
			alert("'.lang('please fill in all mandatory fields').'");
			return false;
		 }
		 ';
	  }
	  
	  /**
	  @function display_form 
	  @abstract main public function to create the complete record editing form for a single record
	  */
	  function display_form()
	  {
		 if(!$this->bo->so->test_JSO_table($this->bo->site_object))
		 {
			unset($this->bo->session['site_object_id']);
			$this->bo->session['message']['error']=lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']);

			$this->bo->sessionmanager->save();
			$this->bo->common->exit_and_open_screen('jinn.uiuser.index');
		 }				

		 $this->template->set_file(array(
			'frm_edit_record' => 'frm_edit_record.tpl'
		 ));

		 $this->template->set_block('frm_edit_record','form_header','');
		 $this->template->set_block('frm_edit_record','table_header','');
		 $this->template->set_block('frm_edit_record','rows','rows');
		 $this->template->set_block('frm_edit_record','js','js');
		 $this->template->set_block('frm_edit_record','many_to_many','many_to_many');


		 $this->render_header();
		 $this->render_table_header();
		 $this->render_fields();
		 $this->render_one_to_one_input();
		 $this->render_many_to_many_input();
		 $this->render_buttons();
		 $this->render_footer();

		 //		 unset($this->bo->session['message']);

		 #FIXME does this belong here?
		 if (!is_object($GLOBALS['phpgw']->js))
		 {
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
		 }
		 if (!strstr($GLOBALS['phpgw_info']['flags']['java_script'],'jinn'))
		 {
			$GLOBALS['phpgw']->js->validate_file('jinn','display_func','jinn');
		 }

		 if ($this->bo->where_string)
		 {
			$this->ui->header('edit record');
		 }
		 else
		 {
			$this->ui->header('add new record');
			$this->template->set_var('btn_delete','hidden'); // do not show the delete button when adding a new record
		 }

		 $this->incl_js_validation_script();
		 
		 $this->ui->msg_box($this->bo->session['message']);
		 unset($this->bo->session['message']);

		 $this->ui->main_menu();	

		 $popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');

		 $this->template->set_var('popuplink',$popuplink);

		 
		 $this->template->pparse('out','form_header');
		 $this->template->pparse('out','form_buttons');
		 $this->template->pparse('out','table_header');
		 $this->template->set_var('jstips',$this->jstips);
		 $this->template->set_var('submit_script',$this->submit_javascript);
		 $this->template->parse('js','js');
		 $this->template->pparse('out','js');
		 $this->template->pparse('out','row');
		 $this->template->pparse('out','form_buttons');
	     $this->template->set_var('hiddenfields',$this->hiddenfields);
	     $this->template->set_var('jsmandatory',$this->jsmandatory);
		 $this->template->pparse('out','form_footer');
		 $this->bo->sessionmanager->save();

	  }

	  function multiple_entries()// new
	  {

		$this->incl_js_validation_script();

		 if (!is_object($GLOBALS['phpgw']->js))
		 {
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
		 }
		 if (!strstr($GLOBALS['phpgw_info']['flags']['java_script'],'jinn'))
		 {
			$GLOBALS['phpgw']->js->validate_file('jinn','display_func','jinn');
		 }


		 if (!$_GET['insert'] && is_array($this->bo->session['mult_where_array']))
		 {
			$this->ui->header('edit records');
			$mult_where_array=$this->bo->session['mult_where_array']; // get local en unset bo
			$this->bo->where_string=true;
			$this->mult_records=count($mult_where_array);
		 }
		 else
		 {
			$this->ui->header('add new records');
			if(!$this->bo->session['mult_records_amount'] || !is_numeric($this->bo->session['mult_records_amount']))
			{
			   $this->mult_records=3;// FIXME get from user
			}
			elseif(intval($this->mult_records)>99)
			{
			   $this->bo->session['message'][error]=lang('Can\'t edit more then 99 record at once (error code 108)');
			   $this->mult_records=3;// FIXME get from user
			}
			else
			{
			   $this->mult_records=$this->bo->session['mult_records_amount'];// FIXME get from user
			}
		 }

		 if(!$this->bo->so->test_JSO_table($this->bo->site_object))
		 {
			unset($this->bo->session['site_object_id']);
			$this->bo->session['message']['error']=lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']);

			$this->bo->sessionmanager->save();
			$this->bo->common->exit_and_open_screen('jinn.uiuser.index');
		 }				

		 $this->template->set_file(array(
			'frm_edit_record' => 'frm_edit_multiple_records.tpl'
		 ));


		 // move to function?
		 $this->template->set_block('frm_edit_record','form_header','');
		 $this->template->set_block('frm_edit_record','change_num','');

		 $this->template->set_var('mult_records',$this->mult_records);
		 $this->template->set_block('frm_edit_record','table_header','');
		 $this->template->set_block('frm_edit_record','rows','rows');
		 $this->template->set_block('frm_edit_record','table_footer','');
		 $this->template->set_block('frm_edit_record','js','js');
		 $this->template->set_block('frm_edit_record','many_to_many','many_to_many');


		 $this->render_header();
		 $this->render_buttons();

		 $this->ui->msg_box($this->bo->session['message']);

		 $this->ui->main_menu();	

		 if (!is_array($mult_where_array))
		 {
			$this->template->set_var('form_action_change_amount',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.mult_change_num_records'));
			$this->template->set_var('num_records',$this->mult_records);
			$this->template->set_var('lang_change_num_records',lang('change number of records'));
			$this->template->parse('change_num','change_num');
			$this->template->pparse('out','change_num');
		 }

		 $popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');
		 $this->template->set_var('popuplink',$popuplink);
		 $this->template->pparse('out','form_header');
		 $this->template->pparse('out','form_buttons');

		 unset($this->bo->session['message']);


		 if($mult_where_array)
		 {
			$i=0;
			$setwhere=true;
			foreach($mult_where_array as $where_string)	
			{
			   $this->bo->where_string=$where_string;
			   $this->mult_index=sprintf("%02d",$i);


			   $this->render_mult_table_header($setwhere);
			   $i++;

			   $this->render_fields();
			   $this->render_many_to_many_input();

			   $this->template->pparse('out','row');
			   $this->render_mult_table_footer();
			}
		 }
		 else
		 {
			for($i=0;$i<$this->mult_records;$i++)
			{
			   $this->mult_index=sprintf("%02d",$i);
			   $this->render_mult_table_header();

			   $this->render_fields();
			   $this->render_many_to_many_input();

			   $this->template->pparse('out','row');
			   $this->render_mult_table_footer();
			}
		 }

		 $this->render_footer();

		 $this->template->set_var('submit_script',$this->submit_javascript);
		 $this->template->set_var('jstips',$this->jstips);
		 $this->template->parse('js','js');
		 $this->template->pparse('out','js');
		 $this->template->set_var('colfield_lang_confirm_delete_multiple',lang('Are you sure you want to delete these multiple records?'));

		 $this->template->pparse('out','form_buttons');
	     $this->template->set_var('hiddenfields',$this->hiddenfields);
	     $this->template->set_var('jsmandatory',$this->jsmandatory);
		 $this->template->pparse('out','form_footer');

		 $this->bo->sessionmanager->save();

	  }

	  function render_mult_table_header($setwhere=false) 
	  {
		 if($setwhere)
		 {
			$where_string_record='<input type="hidden" name="MLTWHR'.$this->mult_index.'" value="'.base64_encode($this->bo->where_string).'">';
		 }

		 $this->template->set_var('where_string_record',$where_string_record);

		 //$this->template->parse('table_header','table_header');
		 $this->template->parse('row','table_header');
	  }

	  function render_mult_table_footer() 
	  {
		 $this->template->parse('table_footer','table_footer');
		 $this->template->pparse('row','table_footer');
	  }


	  function render_header()
	  {		

		 if ($this->bo->where_string && !$this->mult_records)
		 {
			$form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.record_update');
			$where_string_form='<input type="hidden" name="where_string" value="'.base64_encode($this->bo->where_string).'">';
		 }
		 elseif(!$this->bo->where_string && !$this->mult_records)
		 {
			$form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.record_insert');
		 }
		 elseif($this->bo->where_string && $this->mult_records)
		 {
			$form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.multiple_records_update');
		 }
		 elseif(!$this->bo->where_string && $this->mult_records)
		 {
			$form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.multiple_records_insert');
		 }


		 $form_attributes='onSubmit="return onSubmitForm()"';

		 $this->template->set_var('form_attributes',$form_attributes);
		 $this->template->set_var('form_action',$form_action);
		 $this->template->set_var('where_string_form',$where_string_form);
		 $this->template->parse('form_header','form_header');
	  }

	  function render_table_header()
	  {
		 $this->template->parse('table_header','table_header');
	  }

	  
	  function render_fields($alt_values_object=false,$alt_object_arr=false)
	  {

		 if($this->mult_records>1 && is_numeric($this->mult_index)) 
		 {
			$input_prefix='MLTX'.$this->mult_index; //becoming like MLTFLD02name_field
		 }
		 elseif($this->o2o_index)
		 {
			$input_prefix='O2OX'.$this->o2o_index;
		 }
		 else
		 {
			$input_prefix='FLDXXX';
		 }

		 // when this function is called by o2o-relations
		 if(is_array($alt_object_arr))
		 {
			$object_arr=$alt_object_arr;
		 }
		 else
		 {
			$object_arr=$this->bo->site_object;
		 }

		 /* get and set one to many relations */
		 $this->relation1_array = $this->bo->extract_O2M_relations($object_arr[relations]);

		 if($this->bo->where_string && !$alt_object_arr)
		 {
			$this->values_object= $this->bo->so->get_record_values($this->bo->session['site_id'],$object_arr[table_name],'','','','','name','','*',$this->bo->where_string);
		 }
		 
		 /* get all fieldproperties (name, type, etc...) */
		 $fields = $this->bo->so->site_table_metadata($this->bo->session['site_id'],$object_arr[table_name]);

		 /* The main loop to create all rows with input fields start here */ 
		 foreach ( $fields as $fprops )
		 {
			unset($input);
			unset($ftype);

			if(is_array($alt_values_object) && $alt_object_arr) 
			{
			   $value=$alt_values_object[0][$fprops[name]];	/* get value from o2o-relation */
			}
			elseif(is_array($this->values_object) && !$alt_object_arr)
			{
			   $value=$this->values_object[0][$fprops[name]];	/* get value */
			}

			/* add FLD so we can identify the real input HTTP_POST_VARS */
			$input_name=$input_prefix.$fprops[name];	

			unset($field_conf_arr);
			$field_conf_arr=$this->bo->so->get_field_values($object_arr[object_id],$fprops[name]);
			if($field_conf_arr[field_alt_name])
			{
			   $display_name=$field_conf_arr[field_alt_name];
			}
			else
			{
			   $display_name = ucfirst(strtolower(ereg_replace("_", " ", $fprops[name]))); 
			}



			unset($tipmouseover);
			if(trim($field_conf_arr[field_help_info]))
			{
			   //$tooltip=str_replace("'", "\'", $field_conf_arr[field_help_info]);
			   $tooltip=$field_conf_arr[field_help_info];
			   //$tipmouseover='<img onMouseover="tooltip(\''.$tooltip.'\')" onMouseout="hidetooltip()" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="" />'; 
			  if (!is_object($GLOBALS['phpgw']->html))
			  {
				 $GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
			  }
			  $options = array('width' => 'auto');
			   $tipmouseover='<img '.$GLOBALS[phpgw]->html->tooltip($tooltip, True, $options).' src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="" />'; 
			}


			/* ---------------------- start fields -------------------------------- */

			// auto
			if (eregi("auto_increment", $fprops[flags]) || eregi("nextval",$fprops['default']))
			{
			   $ftype='auto';

			   $this->record_id_key=$input_name;
			   $record_identifier[name]=$input_name;

			   $this->record_id_val=$value;
			   $record_identifier[value]=$value;
			}

			/* string */
			elseif($this->db_ftypes->complete_resolve($fprops)=='string')
			{
			   /* If this field has a relation, get that options */
			   if (is_array($this->relation1_array) && is_array($this->relation1_array[$fprops[name]]))
			   {
				  $input=$this->render_one2many_input($fprops,$input_name,$value);
			   }
			   else
			   {
				  if($fprops[len] && $fprops[len]!=-1)
				  {
					 $attr_arr=array(
						'max_size'=>$fprops[len],
					 );
				  }
			   }
			}
			// int
			elseif ($this->db_ftypes->complete_resolve($fprops)=='int')
			{
			   /* If this integer has a relation get that options */
			   if (is_array($this->relation1_array) && is_array($this->relation1_array[$fprops[name]]))
			   {
				  $input=$this->render_one2many_input($fprops,$input_name,$value);
			   }
			}

			/* if input is not set above do it the standard way below */
			if(!$input)
			{
			   if(!$ftype) $ftype=$this->db_ftypes->complete_resolve($fprops);
			   if(!$ftype) $ftype='string';

			   if(!$object_arr[plugins])
			   {
				  $input = $this->bo->plug->call_plugin_fi($input_name,$value,$ftype,$field_conf_arr, $attr_arr);
			   }
			   else
			   {
				  $input = $this->bo->get_plugin_fi($input_name,$value,$ftype, $attr_arr,$object_arr[plugins]);
			   }
			   
			   //some plugins return an array containing extra info to be considered:
			   if(is_array($input))
			   {
					if($input[__hidden__])	//render this field as a hidden parameter
					{
						$this->hiddenfields .= $input[html];
						$input='__disabled__';
					}
					else
					{
						$input=$input[html];
					}
			   }
			}

			// check if this field is mandatory. If yes, add a javascript warning.
			if($field_conf_arr[field_mandatory]==1)
			{
				$this->jsmandatory .= '<script language="JavaScript">document.frm.' . $input_name . '.mandatory=true;</script>';
			}

			/* if there is something to render to this */
			if($input!='__disabled__')
			{
			   if($this->bo->read_preferences('table_debugging_info')=='yes')
			   {
				  $keys=array_keys($fprops);
				  $input.='<br/>';
				  foreach($keys as $key)
				  {
					 if(!$fprops[$key]) continue;
					 $input.= $key.'='.$fprops[$key].' ';

				  }
			   }

			   /* set the row colors */
			   $GLOBALS['phpgw_info']['theme']['row_off']='#eeeeee';
			   if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on']) $row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
			   else $row_color=$GLOBALS['phpgw_info']['theme']['row_on'];

			   $this->template->set_var('row_color',$row_color);
			   $this->template->set_var('input',$input);
			   $this->template->set_var('tipmouseover',$tipmouseover);
			   $this->template->set_var('fieldname',$display_name);
			   
			   $this->template->parse('row','rows',true);
			}
		 }
	  }

	  function render_buttons()
	  {
		 $this->template->set_block('frm_edit_record','form_buttons','form_buttons');

		 if($this->bo->site_object[max_records]==1)
		 {
			$this->template->set_var('save_and_add_new_button_submit','button');
			$this->template->set_var('save_and_add_new_button_onclick','onclick="alert(\''.lang("This object can only have one record.").'\')"');
		 }
		 else
		 {
			$this->template->set_var('save_and_add_new_button_submit','submit');
		 }

		 $save_button=lang('save');
		 $save_and_return_button=lang('save and return to list');

		 $cancel_button='<input type=button onClick="location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_list_records.display').'\'" value="'.lang('cancel').'">';

		 $save_and_add_new_button=lang('save and add new record');
		 $save_and_add_new_multi_button=lang('save and add new records');

		 $this->template->set_var('save_and_add_new_button',$save_and_add_new_button);
		 $this->template->set_var('save_and_add_new_multi_button',$save_and_add_new_multi_button);
		 $this->template->set_var('save_button',$save_button);
		 $this->template->set_var('save_and_return_button',$save_and_return_button);
		 $this->template->set_var('delete',lang('delete'));
		 $this->template->set_var('cancel',$cancel_button);
		 $this->template->set_var('repeat_buttons',$repeat_buttons);
		 $this->template->parse('form_buttons','form_buttons');
	  }


	  function render_footer()
	  {
		 $this->template->set_block('frm_edit_record','form_footer','form_footer');
		 $this->template->parse('form_footer','form_footer');
	  }

	  function render_one2many_input($fprops,$input_name,$value)
	  {
		 $related_fields=$this->bo->get_related_field($this->relation1_array[$fprops[name]]);
		 
		 if(is_array($related_fields))
		 {
			foreach ($related_fields as $rel_field)
			{
			   $related_fields_keyed[$rel_field[value]]=$rel_field[name];
			}
		 }

		 $input.= '<sel'.'ect name="'.$input_name.'" onchange="document.frm.O2MXXX'.$fprops[name].'.value=document.frm.'.$input_name.'.options[document.frm.'.$input_name.'.selectedIndex].text">';
		 if($value!='')
		 {
			$input.= $this->ui->select_options($related_fields,$value,true);
			$input.= '</sel'.'ect> ('.lang('real value').': '.$value.')';
			$input.= '<input type="hidden" name="O2MXXX'.$fprops[name].'" value="'.$related_fields_keyed[$value].'" />';
		 }
		 else
		 {
			$input.= $this->ui->select_options($related_fields,$this->relation1_array[$fprops[name]][default_value],true);
			$input.= '</sel'.'ect> ('.lang('real value').': '.$value.')';
			$input.= '<input type="hidden" name="O2MXXX'.$fprops[name].'" value="'.$this->relation1_array[$fprops[name]][default_value].'" />';
		 }
		 
		 return $input;

	  }


	  function render_many_to_many_ro($template_block)
	  {
		 $relation2_array=$this->bo->extract_M2M_relations($this->bo->site_object[relations]);
		 if (count($relation2_array)>0)
		 {
			$rel_i=0;
			foreach($relation2_array as $relation2)
			{
			   $rel_i++;


			   $display_name=lang('relation %1',$rel_i);

			   if($this->record_id_val)
			   {
				  $record_id=$this->record_id_val;
				  $options_arr= $this->bo->so->get_1wX_record_values($this->bo->session['site_id'],$record_id,$relation2,'stored');

				  if(@count($options_arr))
				  {
					 $input=lang('Related entries from table %1', $relation2[display_table]);
					 foreach($options_arr as $option)  
					 {
						$input.='<br/>- <i>'.$option[name].'</i>';
					 }

					 $this->template->set_var('row_color',$row_color);
					 $this->template->set_var('tipmouseover',$tipmouseover);
					 $this->template->set_var('input',$input);
					 $this->template->set_var('fieldname',$display_name);

					 $this->template->parse($template_block,'rows',true);
				  }
				  else
				  {
					 $this->template->set_var('row_color',$row_color);
					 $this->template->set_var('tipmouseover',$tipmouseover);
					 $this->template->set_var('input',lang('empty'));
					 $this->template->set_var('fieldname',$display_name);
			
					 $this->template->parse($template_block,'rows',true);
				  }
			   }

			}
		 }
	  }

	  function render_one_to_one_input()
	  {
		 $O2O_arr=$this->bo->extract_O2O_relations($this->bo->site_object[relations]);
		 if (count($O2O_arr)>0)
		 {
			if(!$this->bo->where_string && !$this->bo->session['mult_where_array'])
			{
			   $this->template->set_var('input',lang('Come back in edit mode to enter one-to-one fields for this record.'));
			   $this->template->set_var('row_color','');
			   $this->template->set_var('fieldname','');
			   $this->template->parse('row','rows',true);
			   return;
			}

			$i=1;

			foreach($O2O_arr as $O2O_rule_arr)
			{
			   $O2O_where_key=$O2O_rule_arr[related_with];
			   $tmp_arr=explode('.',$O2O_rule_arr[related_with]);

			   $O2O_related_key=$tmp_arr[1];
			   $O2O_where_value=$this->values_object[0][$O2O_rule_arr[org_field]];
			   $O2O_where_string="($O2O_where_key='$O2O_where_value')";

			   $O2O_object_arr=$this->bo->so->get_object_values($O2O_rule_arr[object_conf]);

			   //fixme we do nee hide field
			   /*			   // add hidefield fi-plugin for related key
			   if($O2O_object_arr[plugins])
			   {
				  $O2O_object_arr[plugins].='|';
			   }
			   $O2O_object_arr[plugins].=$O2O_related_key.':hidefield::';
			   */			   

			   $O2O_values_object = $this->bo->so->get_record_values($this->bo->session['site_id'],$O2O_object_arr[table_name],'','','','','name','','*',$O2O_where_string);

			   $this->o2o_index=sprintf("%02d",$i);

			   if($O2O_values_object)
			   {
				  $input.='<input type="hidden" name="O2OW'.$this->o2o_index.'" value="'.$O2O_where_string.'"/>';
			   }

			   $input.='<input type="hidden" name="O2OT'.$this->o2o_index.'" value="'.$O2O_object_arr[table_name].'"/>';
			   $input.='<input type="hidden" name="O2OO'.$this->o2o_index.'" value="'.$O2O_rule_arr[object_conf].'"/>';

			   $this->template->set_var('input',$input);
			   $this->template->set_var('row_color','');
			   $this->template->set_var('fieldname','');

			   $this->template->parse('row','rows',true);

			   $this->render_fields($O2O_values_object,$O2O_object_arr);

			   $this->template->set_var('input','<input type="hidden" name="O2OX'.$this->o2o_index.$O2O_related_key.'" value="'.$O2O_where_value.'"/>');
			   $this->template->set_var('row_color','');
			   $this->template->set_var('fieldname','');

			   $this->template->parse('row','rows',true);

			   $i++;
			}
		 }
	  }


	  function render_many_to_many_input()
	  {

		 if($this->mult_records>1 && is_numeric($this->mult_index)) 
		 {
			$prefix1='M2MX'.$this->mult_index; 
			$prefix2='M2MA'.$this->mult_index;
			$prefix3='M2MO'.$this->mult_index;
			$prefix4='M2MR'.$this->mult_index;
			//			$input_prefix='MLTX'.$this->mult_index; //becoming like MLTFLD02name_field
		 }
		 else
		 {
			$prefix1='M2MXXX';
			$prefix2='M2MAXX';
			$prefix3='M2MOXX';
			$prefix4='M2MRXX';
		 }

		 $relation2_array=$this->bo->extract_M2M_relations($this->bo->site_object[relations]);
		 if (count($relation2_array)>0)
		 {
			$rel_i=0;
			foreach($relation2_array as $relation2)
			{
			   $related_table=$relation2[display_table];
			   $rel_i++;

			   $display_name=lang('relation %1',$rel_i);
			   $sel1_all_from=lang('all from').' '.$related_table;
			   $on_dbl_click1='SelectPlace(\''.$prefix1.$rel_i.'\',\''.$prefix2.$rel_i.'\')';
			   $on_dbl_click2='DeSelectPlace(\''.$prefix1.$rel_i.'\')';

			   $sel1_name=''.$prefix2.$rel_i;
			   $sel2_name=''.$prefix1.$rel_i;

			   $lang_add_remove=lang('add or remove');

			   $options_arr= $this->bo->so->get_1wX_record_values($this->bo->session['site_id'],'',$relation2,'all');
			   $sel1_options = $this->ui->select_options($options_arr,'',false);
			   $lang_related=lang('related').' '.$related_table;

			   $this->submit_javascript.='saveOptions(\''.$prefix1.$rel_i.'\',\''.$prefix3.$rel_i.'\');'."\n";

			   if($this->record_id_val)
			   {
				  $record_id=$this->record_id_val;
				  $options_arr= $this->bo->so->get_1wX_record_values($this->bo->session['site_id'],$record_id,$relation2,'stored');
				  $sel2_options= $this->ui->select_options($options_arr,'',false);
			   }
			   elseif(!$this->record_id_key)
			   {
				  $sel2_options= '<option>'.lang('This table has not unique identifier field').'</option>';
				  $sel2_options.= '<option>'.lang('Many 2 Many relations will not work').'</option>';
			   }

			   $m2m_rel_string_name=''.$prefix4.$rel_i;
			   $m2m_rel_string_val=$relation2[via_primary_key].'|'.$relation2[via_foreign_key];
			   $m2m_opt_string_name=''.$prefix3.$rel_i;

			   $this->template->set_var('sel1_all_from',$sel1_all_from);
			   $this->template->set_var('on_dbl_click1',$on_dbl_click1);
			   $this->template->set_var('on_dbl_click2',$on_dbl_click2);
			   $this->template->set_var('tipmouseover',$tipmouseover);
			   $this->template->set_var('sel1_name',$sel1_name);
			   $this->template->set_var('sel2_name',$sel2_name);
			   $this->template->set_var('lang_add_remove',$lang_add_remove);
			   $this->template->set_var('sel1_options',$sel1_options);
			   $this->template->set_var('lang_related',$lang_related);
			   $this->template->set_var('sel2_options',$sel2_options);
			   $this->template->set_var('m2m_rel_string_name',$m2m_rel_string_name);
			   $this->template->set_var('m2m_rel_string_val',$m2m_rel_string_val);
			   $this->template->set_var('m2m_opt_string_name',$m2m_opt_string_name);

			   $this->template->set_var('m2mrow_color',$row_color);
			   $this->template->set_var('m2mfieldname',$display_name);

			   $this->template->parse('row','many_to_many',true);
			}
		 }
	  }

  	  function view_multiple_records()
	{
		 $this->ui->header('View multiple records');
		 $this->ui->msg_box($this->bo->session['message']);

		 $this->ui->main_menu();	

		 $this->template->set_file(array(
			'view_record' => 'view_multiple_records.tpl'
		 ));

		 $popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');

		 $this->template->set_var('popuplink',$popuplink);
		 $this->template->set_block('view_record','header','');
		 $this->template->set_block('view_record','rows','rows');
		 $this->template->set_block('view_record','recordheader','recordheader');
		 $this->template->set_block('view_record','recordfooter','recordfooter');
		 $this->template->set_block('view_record','back_button','back_button');
		 $this->template->set_block('view_record','footer','footer');

		 $this->template->pparse('out','header');
		 
		 if (is_array($this->bo->session['mult_where_array']))
		 {
			$mult_where_array=$this->bo->session['mult_where_array']; // get local en unset bo
			$this->bo->where_string=true;
			$this->mult_records=count($mult_where_array);
			//$i=0;
			//$setwhere=true;
			foreach($mult_where_array as $where_string)	
			{
				 $this->template->parse('record','recordheader',true);

				 $this->values_object= $this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object[table_name],'','','','','name','','*',$where_string);
				 $fields = $this->bo->so->site_table_metadata($this->bo->session['site_id'],$this->bo->site_object[table_name]);
		
				 /* The main loop to create all rows with input fields start here */ 
				 foreach ( $fields as $fprops )
				 {
					unset($input);
					unset($ftype);
		
					$value=$this->values_object[0][$fprops[name]];
					$input_name=$fprops[name];	
		
					unset($field_conf_arr);
					$field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object[object_id],$fprops[name]);
		
					if($field_conf_arr[field_alt_name])
					{
					   $display_name=$field_conf_arr[field_alt_name];
					}
					else
					{
					   $display_name = ucfirst(strtolower(ereg_replace("_", " ", $fprops[name]))); 
					}
		
					unset($tipmouseover);
					if(trim($field_conf_arr[field_help_info]))
					{
					   //$tooltip=str_replace("'", "\'", $field_conf_arr[field_help_info]);
					   $tooltip=$field_conf_arr[field_help_info];
					   //$tipmouseover='<img onMouseover="tooltip(\''.$tooltip.'\')" onMouseout="hidetooltip()" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="" />'; 
					  if (!is_object($GLOBALS['phpgw']->html))
					  {
						 $GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
					  }
					  $options = array('width' => 'auto');
					   $tipmouseover='<img '.$GLOBALS[phpgw]->html->tooltip($tooltip, True, $options).' src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="" />'; 
					}
		
					// auto
					if (eregi("auto_increment", $fprops[flags]) || eregi("nextval",$fprops['default']))
					{
					   $this->record_id_val=$value;
					   $input='<b>'.$value.'</b>';
					}
		
					if(!$input)
					{
					   if(!$ftype) $ftype=$this->db_ftypes->complete_resolve($fprops);
					   if(!$ftype) $ftype='string';
		
					   if(!$this->bo->site_object[plugins])
					   {
						  $input=$this->bo->plug->call_plugin_ro($value,$field_conf_arr);
					   }
					   else
					   {
						  $input=$this->bo->get_plugin_ro($input_name,$value,$this->db_ftypes->complete_resolve($fprops),'');
					   }
		
					}
		
					/* if there is something to render to this */
					if($input!='__disabled__')
					{
					   if($this->bo->read_preferences('table_debugging_info')=='yes')
					   {
						  $keys=array_keys($fprops);
						  $input.='<br/>';
						  foreach($keys as $key)
						  {
							 if(!$fprops[$key]) continue;
							 $input.= $key.'='.$fprops[$key].' ';
		
						  }
					   }
		
					   /* set the row colors */
					   $GLOBALS['phpgw_info']['theme']['row_off']='#eeeeee';
					   if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on']) $row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
					   else $row_color=$GLOBALS['phpgw_info']['theme']['row_on'];
		
					   $this->template->set_var('row_color',$row_color);
					   $this->template->set_var('tipmouseover',$tipmouseover);
					   $this->template->set_var('input',$input);
					   $this->template->set_var('fieldname',$display_name);
		
					   $this->template->parse('record','rows',true);
					}
				 }
				 $this->render_many_to_many_ro('record');
				$this->template->parse('record','recordfooter',true);
			}
			$this->template->pparse('out','record');

		}
		 
		 
		 
		 
		 
		 
		 
		 

		 if($this->bo->site_object[max_records]!=1)
		 {
			$back_onclick='location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_list_records.display').'\'';
			$this->template->set_var('back_onclick',$back_onclick);

			$this->template->parse('extra_back_button','back_button');
		 }
		 else
		 {
			$this->template->set_var('extra_back_button','');
		 }
		 
		 //$this->template->set_var('lang_edit',lang('edit this record'));
		 $this->template->set_var('lang_back',lang('back to record list'));
		 //$edit_onclick='location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_edit_record.display_form&where_string='.base64_encode($where_string)).'\'';
		 //$this->template->set_var('edit_onclick',$edit_onclick);
		 
		 $this->template->pparse('out','footer');
		 
	  }
	  
	  function view_record()
	  {
		 $this->ui->header('View record');
		 $this->ui->msg_box($this->bo->session['message']);

		 $this->ui->main_menu();	

		 $this->template->set_file(array(
			'view_record' => 'view_record.tpl'
		 ));

		 $popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');

		 $this->template->set_var('popuplink',$popuplink);
		 $this->template->set_block('view_record','header','');
		 $this->template->set_block('view_record','rows','rows');
		 $this->template->set_block('view_record','back_button','back_button');
		 $this->template->set_block('view_record','footer','footer');

		 $where_string=$this->bo->where_string;

		 $this->values_object= $this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object[table_name],'','','','','name','','*',$where_string);
		 $fields = $this->bo->so->site_table_metadata($this->bo->session['site_id'],$this->bo->site_object[table_name]);

		 /* The main loop to create all rows with input fields start here */ 
		 foreach ( $fields as $fprops )
		 {
			unset($input);
			unset($ftype);

			$value=$this->values_object[0][$fprops[name]];
			$input_name=$fprops[name];	

			unset($field_conf_arr);
			$field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object[object_id],$fprops[name]);

			if($field_conf_arr[field_alt_name])
			{
			   $display_name=$field_conf_arr[field_alt_name];
			}
			else
			{
			   $display_name = ucfirst(strtolower(ereg_replace("_", " ", $fprops[name]))); 
			}


			unset($tipmouseover);
			if(trim($field_conf_arr[field_help_info]))
			{
			   //$tooltip=str_replace("'", "\'", $field_conf_arr[field_help_info]);
			   $tooltip=$field_conf_arr[field_help_info];
			   //$tipmouseover='<img onMouseover="tooltip(\''.$tooltip.'\')" onMouseout="hidetooltip()" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="" />'; 
			  if (!is_object($GLOBALS['phpgw']->html))
			  {
				 $GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
			  }
			  $options = array('width' => 'auto');
			   $tipmouseover='<img '.$GLOBALS[phpgw]->html->tooltip($tooltip, True, $options).' src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="" />'; 
			}

			// auto
			if (eregi("auto_increment", $fprops[flags]) || eregi("nextval",$fprops['default']))
			{
			   $this->record_id_val=$value;
			   $input='<b>'.$value.'</b>';
			}

			if(!$input)
			{
			   if(!$ftype) $ftype=$this->db_ftypes->complete_resolve($fprops);
			   if(!$ftype) $ftype='string';

			   if(!$this->bo->site_object[plugins])
			   {
				  $input=$this->bo->plug->call_plugin_ro($value,$field_conf_arr);
			   }
			   else
			   {
				  $input=$this->bo->get_plugin_ro($input_name,$value,$this->db_ftypes->complete_resolve($fprops),'');
			   }

			}

			/* if there is something to render to this */
			if($input!='__disabled__')
			{
			   if($this->bo->read_preferences('table_debugging_info')=='yes')
			   {
				  $keys=array_keys($fprops);
				  $input.='<br/>';
				  foreach($keys as $key)
				  {
					 if(!$fprops[$key]) continue;
					 $input.= $key.'='.$fprops[$key].' ';

				  }
			   }

			   /* set the row colors */
			   $GLOBALS['phpgw_info']['theme']['row_off']='#eeeeee';
			   if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on']) $row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
			   else $row_color=$GLOBALS['phpgw_info']['theme']['row_on'];

			   $this->template->set_var('row_color',$row_color);
			   $this->template->set_var('tipmouseover',$tipmouseover);
			   $this->template->set_var('input',$input);
			   $this->template->set_var('fieldname',$display_name);

			   $this->template->parse('row','rows',true);
			}
		 }


		 $this->render_many_to_many_ro('row');



		 if($this->bo->site_object[max_records]!=1)
		 {
			$back_onclick='location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_list_records.display').'\'';
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

   }

?>
