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



	class uiconfig
	{
		var $public_functions = Array
		(
			'index' => True,
			'add_edit_object' => True,
			'object_update' => True,
			'object_insert' => True,
			'del_object' => True,
			'browse_objects' => True
		);

		var $app_title='jinn';
		var $bo;
		var $template;
		var $plugins;
		var $relations;
		var $debug=False;

		function uiconfig()
		{

			$this->bo = CreateObject('jinn.bojinn');
			$this->plugins = CreateObject('jinn.boplugins.inc.php');
			$this->relations = CreateObject('jinn.borelations.inc.php');
			$this->template = $GLOBALS['phpgw']->template;
		}

		function show_fields()
		{

			//echo 'test';

			$this->template->set_file(array(
				//				'browse_menu' => 'browse_menu.tpl',
				'config' => 'config_browse_view.tpl'
			));

			$columns = $this->bo->get_object_column_names($this->bo->site_id,$this->bo->site_object[table_name]);

			if (count($columns)>0)
			{


				// get the prefered columns, if they exist
				$prefs=$this->bo->read_preferences('show_fields'); //False; // function not implemented yet
				// "1:id,name,place|2:id,name,city" // example

				$prefs_objects=explode('|',$prefs);
				foreach ($prefs_objects as $prefs_obj)
				{
					list($object,$fields)=explode(':',$prefs_obj);





					// which/how many column to show, all, the prefered, or the default thirst 4
					if($pref_columns)
					{
						$show_cols=$pref_columns;
					}
					else
					{
						$show_cols=array_slice($columns,0,4);
					}

					foreach ($columns as $col)
					{
						unset($checked);
						if(in_array($col,$show_cols)) $checked='CHECKED';
						if ($bgclr==$GLOBALS['phpgw_info']['theme']['row_off'])
						{
							$bgclr=$GLOBALS['phpgw_info']['theme']['row_on'];
						}
						else
						{
							$bgclr=$GLOBALS['phpgw_info']['theme']['row_off'];
						}
						$rows.='<tr>';				
						$rows.='<td bgcolor='.$bgclr.' align="left">'.$col.'</td>';
						$rows.='<td bgcolor='.$bgclr.' align="left"><input name="'.$col.'" type=checkbox '.$checked.'></td>';
						$rows.='</tr>';
					}
				}

				$form_action=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.save_object_config');

				$button_save='<td><input type="submit" name="action" value="'.lang('save').'"></td>';

				$button_cancel='<td><form name=form2 action="'.
				$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.browse_objects') .
				'" method="post"><input type="submit" name="action" value="'.lang('cancel').'"></form></td>';

				$this->template->set_var('form_action',$form_action);
				$this->template->set_var('button_save',$button_save);
				$this->template->set_var('button_cancel',$button_cancel);
				$this->template->set_var('lang_config_table',lang('Configure view of').' '.$this->bo->site_object[name]);
				$this->template->set_var('rows',$rows);
				$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
				$this->template->set_var('lang_column_name',lang('column name'));
				$this->template->set_var('lang_show_column',lang('show colomn'));

				$this->template->pparse('out','config');

				unset($this->message);
			}


		}


	}
?>
