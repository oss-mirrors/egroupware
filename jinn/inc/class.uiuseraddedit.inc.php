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


	class uiuseraddedit extends uijinn
	{

		var $relations;

		function uiuseraddedit($bo)
		{
			$this->bo=$bo;
			$this->relations = CreateObject('jinn.borelations.inc.php');
			$this->template = $GLOBALS['phpgw']->template;
		}

		/****************************************************************************\
		* create form to new objectrecord                                            *
		\****************************************************************************/

		function render_form()
		{
			$this->template->set_file(array(
				'form_header' => 'form_header.tpl',
				'object_field' => 'object_field.tpl',
				'javascript' => 'javascript.tpl',
				'form_footer' => 'form_footer.tpl'
			));

			$where_condition=$GLOBALS[where_condition];
			if ($where_condition)
			{
				
				/* set vars for edit form */
				$form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.object_update');
				$where_condition_form="<input type=\"hidden\" name=\"where_condition\" value=\"$where_condition\">";
				$values_object= $this->bo->get_records($this->bo->site_object[table_name],$where_condition,'','','name');
				$add_edit_button=lang('edit');

			}
			else
			{
				/* vars for new record form */
				$form_action = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.object_insert');
				$add_edit_button='voeg toe';
				$action=lang('add object');
			}

			$form_attributes='onSubmit="return onSubmitForm()"';

			$this->template->set_var('form_attributes',$form_attributes);
			$this->template->set_var('form_action',$form_action);
			$this->template->set_var('where_condition_form',$where_condition_form);
			$this->template->pparse('out','form_header');
			

			/* get one with many relations */
			$relation1_array=$this->relations->get_fields_with_1_relation($this->bo->site_object[relations]);

			if (count($relation1_array)>0)
			{
				foreach($relation1_array as $relation1)
				{
					$fields_with_relation1[]=$relation1[field_org];
				}

			}

			/* get all fieldproperties (name, type, etc...) */
			$fields = $this->bo->get_site_fieldproperties($this->bo->site_id,$this->bo->site_object[table_name]);

			/* The main loop to create all rows with input fields start here */ 
			foreach ( $fields as $fieldproperties )
			{

				/* set the row colors */
				if ($row_color==$GLOBALS['phpgw_info']['theme']['row_on']) $row_color=$GLOBALS['phpgw_info']['theme']['row_off'];
				else $row_color=$GLOBALS['phpgw_info']['theme']['row_on'];

				$value=$values_object[0][$fieldproperties[name]];	/* get value */
				$input_name='FLD'.$fieldproperties[name];	/* add FLD so we can identify the real input HTTP_POST_VARS */
				$display_name = ucfirst(strtolower(ereg_replace("_", " ", $fieldproperties[name]))); /* replace _ for a space */

				/* set max length for inputfield */
				$input_max_length=' maxlength="'. $fieldproperties[len].'"';
				$input_length=$fieldproperties[len];
				if ($input_length>40) $input_length=40;

				/* ---------------------- start fields -------------------------------- */
				
				/* Its an identifier field */
				if (eregi("auto_increment", $fieldproperties[flags]))
				{
					$input='<input type="hidden" name="'.$input_name.'" value="'.$value.'">'.$display_value;
					$record_identifier=$value;
				}

				// deze velden handelen plaatjes, hier moet meer mee gebeuren incl uploaden
				// move to standard plugins
				elseif ($fieldproperties[name]=='image_path'||$fieldproperties[name]=='img_path')
				{
					$input=$this->bo->plug->get_plugin($input_name,$value,'text');

				}
				elseif ($fieldproperties[name]=='thumb_path')
				{
					unset($input);
					if($value)
					{
						$input='<input type="hidden" name="thumb_path_org" value="'.$value.'">';

						$values=explode(';',$value);
						if (is_array($values))
						{

							$i=0;
							foreach($values as $img_path)
							{
								$i++;
								$input.=$i.'.<b> '.$img_path.'</b> <input type="checkbox" value="'.$img_path.'" name="TMBDEL'.$i.'">'.lang('remove').'<br>';
							}
						}
						else
						{
							$input=$img_path.'<input type="checkbox" value="'.$img_path.'" name="TMBDEL'.$img_path.'"> '.lang('remove').'<br>';
						}

					}
					$input.='<input type="hidden" name="'.$fieldproperties[name].'" value="True">'.lang('automatic');

				}

				/*************************************
				* start attachments
				*************************************/

				elseif ($fieldproperties[name]=='attachment_path')
				{
					unset($input);
					if($value)
					{
						$input='<input type="hidden" name="attachment_path_org" value="'.$value.'">';

						$value=explode(';',$value);
						if (is_array($value))
						{

							$i=0;
							foreach($value as $attachment_path)
							{
								$i++;
								$input.=$i.'.<b> <a href="'.$this->bo->site_object[image_dir_url].'/'
								.$attachment_path.'" >'.$attachment_path
								.'<a href="'.$this->bo->site_object[image_dir_url].'/'.$attachment_path
								.'"></a> </b> <input type="checkbox" value="'.$attachment_path
								.'" name="ATTDEL'.$i.'">'.lang('remove').' <br>';
							}
						}
						else
						{
							$input=$attachment_path.'<input type="checkbox" value="'.$attachment_path.'" name="ATTDEL'.$attachment_path.'"> '.lang('remove').'<br>';
						}



					}
					$input.=lang('add attachent').' <input type="file" name="'.$fieldproperties[name].'">';

				}

				/*************************************
				* end attachments
				*************************************/

				elseif ($fieldproperties[type]=='string')
				{
					$input=$this->bo->plug->get_plugin($input_name,$value,'string');
				}

				// int int int int int int int int int
				elseif ($fieldproperties[type]=='int' || $fieldproperties[type]=='real')
				{
					/* If this integer has a relation get that options */
					if (is_array($fields_with_relation1) && in_array($fieldproperties[name],$fields_with_relation1))
					{
						//get related field vals en displays
						$related_fields=$this->relations->get_related_field($relation1_array[$fieldproperties[name]]);

						$input= '<select name="'.$input_name.'">';
						$input.= $this->bo->make_options($related_fields,$value);
						$input.= '</select> ('.lang('real value').': '.$value.')';
					}
					else
					{	
						$input=$this->bo->plug->get_plugin($input_name,$value,'int');						
					}
				}

				// timestamp timestamp timestamp timestamp timestamp
				elseif ($fieldproperties[type]=='timestamp')
				{
					if ($value)
					{
						$input=$this->bo->plug->get_plugin($input_name,$value,'timestamp');
					}
					else
					{
						$input = lang('automatic');
					}
				}

				elseif ($fieldproperties[type]=='blob' && ereg('binary',$fieldproperties[flags]))
				{
					$input = lang('binary');
				}

				elseif ($fieldproperties[type]=='blob') //then it is a textblob
				{
					$input=$this->bo->plug->get_plugin($input_name,$value,'text');
				}

				$this->template->set_var('row_color',$row_color);
				$this->template->set_var('input',$input);
				$this->template->set_var('fieldname',$display_name);

				$this->template->pparse('out','object_field');
			}


			/***********************************************
			* MANY WITH MANY RELATION SECTION OF FORM      *
			***********************************************/

			/*	check for many with many relations. 
			if so make double selectionbox		*/

			$relation2_array=$this->relations->get_fields_with_2_relation($this->bo->site_object[relations]);
			if (count($relation2_array)>0)
			{

				$rel_i=0;
				foreach($relation2_array as $relation2)
				{

					$related_table=$relation2[display_table];
					$rel_i++;

					$display_name=lang($rel_i.'e relatie');

					$input= '<table cellspacing=0 cellpadding=3 border=1>';
					$input.= '<tr>';
					$input.= '<td valign=top>'.lang('all from').' '.$related_table .'<br>';
					$input.= '<select onDblClick=SelectPlace(\'M2M'.$rel_i.'\',\'all_related'.$rel_i.'\') multiple size=5 name="all_related'.$rel_i.'">';

					// make all possible options
					$options=$this->relations->get_m2m_options($relation2,"all",'');
					$input.= $this->bo->make_options_non_empty($options,'');

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

					if($where_condition) $record_id=$record_identifier;
					$options=$this->relations->get_m2m_options($relation2,"stored",$record_id);
					$input.= $this->bo->make_options_non_empty($options,'');
					$input.= '</select>';
					$input.= '<input type=hidden name=MANY_REL_STR_'.$rel_i.' value='.$relation2[via_primary_key].'|'.$relation2[via_foreign_key].'>';
					$input.= '<input type=hidden name=MANY_OPT_STR_'.$rel_i.'>';
					$input.= '</td>';
					$input.= '</tr>';
					$input.= '</table>';

					$this->template->set_var('row_color',$row_color);
					$this->template->set_var('input',$input);
					$this->template->set_var('fieldname',$display_name);

					$this->template->pparse('out','object_field');

				}


			}

			$cancel_button='<form action="'.
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.browse_objects').
			'" method=post><input type=submit value="'.lang('cancel').'"></form>';

			$this->template->set_var('submit_script',$submit_javascript);
			$this->template->pparse('out','javascript');

			$this->template->set_var('add_edit_button',$add_edit_button);
			$this->template->set_var('reset_form',lang('reset form'));
			$this->template->set_var('delete',lang('delete'));
			$this->template->set_var('cancel',$cancel_button);
			$this->template->set_var('extra_buttons',$extra_buttons);
			$this->template->pparse('out','form_footer');

			unset($this->bo->message);


		}





	}

?>
