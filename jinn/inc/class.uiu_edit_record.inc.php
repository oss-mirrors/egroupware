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

   /* $id$ */

   /**
   @package jinn_users_classes
   */
   class uiu_edit_record // extends uiuser
   {
	  var $public_functions = Array
	  (
		 'display_form'		=> True,
		 'multiple_entries'		=> True,
		 'view_record'		=> True
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
	  	
	  var $db_ftypes;

	  /**
	  @function uiu_edit_record
	  @abstract class contructor that set header and inits bo
	  */
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

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');
	  
	  }

	  /**
	  @function display_form 
	  @abstract main public function to create the complete record editing form for a single record
	  */
	  function display_form()
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
		 $this->template->set_block('frm_edit_record','rows','rows');
		 $this->template->set_block('frm_edit_record','js','js');
		 $this->template->set_block('frm_edit_record','many_to_many','many_to_many');


		 $this->render_header();
		 $this->render_fields();
		 $this->render_one_to_one_input();
		 $this->render_many_to_many_input();
		 $this->render_footer();

//		 unset($this->bo->message);

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
		 }

		 $this->ui->msg_box($this->bo->message);
		 unset($this->bo->message);

		 $this->main_menu();	

		 $popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');

		 $this->template->set_var('popuplink',$popuplink);

		 $this->template->pparse('out','form_header');
		 $this->template->set_var('jstips',$this->jstips);
		 $this->template->set_var('submit_script',$this->submit_javascript);
		 $this->template->parse('js','js');
		 $this->template->pparse('out','js');
		 $this->template->pparse('out','row');
		 $this->template->pparse('out','form_footer');
		 $this->bo->save_sessiondata();

	  }

	  function multiple_entries()// new
	  {
		 if (!is_object($GLOBALS['phpgw']->js))
		 {
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
		 }
		 if (!strstr($GLOBALS['phpgw_info']['flags']['java_script'],'jinn'))
		 {
			$GLOBALS['phpgw']->js->validate_file('jinn','display_func','jinn');
		 }
		 
		 
		 if (is_array($this->bo->mult_where_array))
		 {
			$this->ui->header('edit records');
			$mult_where_array=$this->bo->mult_where_array; // get local en unset bo
			$this->bo->where_string=true;
			$this->mult_records=count($mult_where_array);
		 }
		 else
		 {
			$this->ui->header('add new records');
			if(!$this->bo->mult_records_amount || !is_numeric($this->bo->mult_records_amount))
			{
			   $this->mult_records=3;// FIXME get from user
			}
			elseif(intval($this->mult_records)>99)
			{
			   $this->message[error]=lang('Can\'t edit more then 99 record at once (error code 108)');
			   $this->mult_records=3;// FIXME get from user
			}
			else
			{
			   $this->mult_records=$this->bo->mult_records_amount;// FIXME get from user
			}
		 }
		 
		 if(!$this->bo->so->test_JSO_table($this->bo->site_object))
		 {
			unset($this->bo->site_object_id);
			$this->bo->message['error']=lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']);

			$this->bo->save_sessiondata();
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
		 
		 $this->ui->msg_box($this->bo->message);

		 $this->main_menu();	

		 if (!is_array($mult_where_array))
		 {
			$this->template->set_var('form_action_change_amount',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.mult_change_num_records'));
			$this->template->set_var('num_records',$this->mult_records);
			$this->template->set_var('lang_change_num_records',lang('change number of records'));
			$this->template->parse('change_num','change_num');
			$this->template->pparse('out','change_num');
		 }

		 $this->template->pparse('out','form_header');


		 unset($this->bo->message);


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

			   $popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');
			   $this->template->set_var('popuplink',$popuplink);

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

			   $popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');
			   $this->template->set_var('popuplink',$popuplink);

			   $this->template->pparse('out','row');
			   $this->render_mult_table_footer();
			}
		 }
		 
		 
		 $this->render_footer();
		
		 $this->template->set_var('submit_script',$this->submit_javascript);
		 $this->template->set_var('jstips',$this->jstips);
		 $this->template->parse('js','js');
		 $this->template->pparse('out','js');
		 
		 $this->template->pparse('out','form_footer');

		 $this->bo->save_sessiondata();

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

		 if($this->bo->where_string && !$alt_object_arr)
		 {
			$this->values_object= $this->bo->so->get_record_values($this->bo->site_id,$object_arr[table_name],'','','','','name','','*',$this->bo->where_string);
		 
		 }

		 /* get one with many relations */
		 $relation1_array=$this->bo->extract_O2M_relations($object_arr[relations]);

		 if (count($relation1_array)>0)
		 {
			foreach($relation1_array as $relation1)
			{
			   $fields_with_relation1[]=$relation1[field_org];
			}
		 }

		 /* get all fieldproperties (name, type, etc...) */
		 $fields = $this->bo->so->site_table_metadata($this->bo->site_id,$object_arr[table_name]);
 
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
			   $tooltip=str_replace("'", "\'", $field_conf_arr[field_help_info]);
			   $tipmouseover='<img onMouseover="tooltip(\''.$tooltip.'\')" onMouseout="hidetooltip()" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="'.lang('info').'"/>'; 
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
			   if (is_array($fields_with_relation1) && in_array($fprops[name],$fields_with_relation1))
			   {
				  $related_fields=$this->bo->get_related_field($relation1_array[$fprops[name]]);

				  $input= '<sel'.'ect name="'.$input_name.'">';
				  $input.= $this->ui->select_options($related_fields,$value,true);
				  $input.= '</sel'.'ect> ('.lang('real value').': '.$value.')';
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
			   if (is_array($fields_with_relation1) && in_array($fprops[name],$fields_with_relation1))
			   {
				  $related_fields=$this->bo->get_related_field($relation1_array[$fprops[name]]);
				  $input= '<sel'.'ect name="'.$input_name.'">';
				  $input.= $this->ui->select_options($related_fields,$value,true);
				  $input.= '</sel'.'ect> ('.lang('real value').': '.$value.')';
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
			}
			
			/* if there is something to render to this */
			if($input!='__hide__')
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

	  function render_footer()
	  {
		 $this->template->set_block('frm_edit_record','form_footer','form_footer');

		 if(!$this->bo->where_string)
		 {
			if($this->repeat_input=='true') $REPEAT_INPUT_CHECKED='CHECKED';

			$repeat_buttons='<input type="checkbox" '.$REPEAT_INPUT_CHECKED.' name="repeat_input" value="true" /> '.lang('insert another record after saving');

		 }

		 $add_edit_button_continue=lang('save and continue');
		 $add_edit_button=lang('save and finish');

		 $cancel_button='<input type=button onClick="location=\''.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.browse_objects').'\'" value="'.lang('cancel').'">';

		 $this->template->set_var('add_edit_button_continue',$add_edit_button_continue);
		 $this->template->set_var('add_edit_button',$add_edit_button);
		 $this->template->set_var('reset_form',lang('reset form'));
		 $this->template->set_var('delete',lang('delete'));
		 $this->template->set_var('cancel',$cancel_button);
		 $this->template->set_var('repeat_buttons',$repeat_buttons);
		 $this->template->parse('form_footer','form_footer');
	  }

	  function render_many_to_many_ro()
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
				  $options_arr= $this->bo->so->get_1wX_record_values($this->bo->site_id,$record_id,$relation2,'stored');

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

					 $this->template->parse('row','rows',true);
				  }

			   }

			}
		 }
	  }

	  function render_one_to_one_input()
	  {
		 $O2O_arr=$this->bo->extract_O2O_relations($this->bo->site_object[relations]);

		 if(!$this->bo->where_string && !$this->bo->mult_where_array)
		 {
			$this->template->set_var('input',lang('Come back in edit mode to enter one-to-one fields for this record.'));
			$this->template->set_var('row_color','');
			$this->template->set_var('fieldname','');
			$this->template->parse('row','rows',true);
			return;
		 }
		 
		 if (count($O2O_arr)>0)
		 {
	        $i=1;
			
			foreach($O2O_arr as $O2O_rule_arr)
			{
			   $O2O_where_key=$O2O_rule_arr[related_with];
			   $tmp_arr=explode('.',$O2O_rule_arr[related_with]);

			   $O2O_related_key=$tmp_arr[1];
			   $O2O_where_value=$this->values_object[0][$O2O_rule_arr[field_org]];
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

			   $O2O_values_object = $this->bo->so->get_record_values($this->bo->site_id,$O2O_object_arr[table_name],'','','','','name','','*',$O2O_where_string);

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

			   $options_arr= $this->bo->so->get_1wX_record_values($this->bo->site_id,'',$relation2,'all');
			   $sel1_options = $this->ui->select_options($options_arr,'',false);
			   $lang_related=lang('related').' '.$related_table;

			   $this->submit_javascript.='saveOptions(\''.$prefix1.$rel_i.'\',\''.$prefix3.$rel_i.'\');'."\n";

			   if($this->record_id_val)
			   {
				  $record_id=$this->record_id_val;
				  $options_arr= $this->bo->so->get_1wX_record_values($this->bo->site_id,$record_id,$relation2,'stored');
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

	  function view_record()
	  {
		 $this->ui->header('View record');
		 $this->ui->msg_box($this->bo->message);

		 $this->main_menu();	

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

		 $this->values_object= $this->bo->so->get_record_values($this->bo->site_id,$this->bo->site_object[table_name],'','','','','name','','*',$where_string);
		 $fields = $this->bo->so->site_table_metadata($this->bo->site_id,$this->bo->site_object[table_name]);
		 
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
			   $tooltip=str_replace("'", "\'", $field_conf_arr[field_help_info]);
			   $tipmouseover='<img onMouseover="tooltip(\''.$tooltip.'\')" onMouseout="hidetooltip()" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="'.lang('info').'"/>'; 
			}

			// auto
			if (eregi("auto_increment", $fprops[flags]) || eregi("nextval",$fprops['default']))
			{
			   $this->record_id_val=$value;
			   $input='<b>'.$value.'</b>';
			}
			// string
			elseif($this->db_ftypes->complete_resolve($fprops)=='string')
			{
			   if (is_array($fields_with_relation1) && in_array($fprops[name],$fields_with_relation1))
			   {
				  $related_fields=$this->bo->get_related_field($relation1_array[$fprops[name]]);
				  $input= '<sel'.'ect name="'.$input_name.'">';
				  $input.= $this->ui->select_options($related_fields,$value,true);
				  $input.= '</sel'.'ect> ('.lang('real value').': '.$value.')';
			   }
			}
			// int
			elseif ($this->db_ftypes->complete_resolve($fprops)=='int')
			{
			   if (is_array($fields_with_relation1) && in_array($fprops[name],$fields_with_relation1))
			   {
				  //get related field vals en displays
				  $related_fields=$this->bo->get_related_field($relation1_array[$fprops[name]]);

				  $input= '<sel'.'ect name="'.$input_name.'">';
				  $input.= $this->ui->select_options($related_fields,$value,true);
				  $input.= '</se'.'lect> ('.lang('real value').': '.$value.')';
			   }
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
			if($input!='__hide__')
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


		 $this->render_many_to_many_ro();



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
