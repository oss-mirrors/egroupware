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



	class uiadminbrowse extends uiadmin
	{
/*
		var $public_functions = Array
		(
			'index' => True,
			'add_edit_phpgw_jinn_sites' => True,
			'add_edit_phpgw_jinn_site_objects' => True,
			'browse_phpgw_jinn_sites' => True,
			'browse_phpgw_jinn_site_objects' => True,
			'del_phpgw_jinn_sites'=> True,
			'del_phpgw_jinn_site_objects' => True,
			'insert_phpgw_jinn_sites'=> True,
			'insert_phpgw_jinn_site_objects'=> True,
			'update_phpgw_jinn_sites'=> True,
			'update_phpgw_jinn_site_objects' => True,
			'access_rights'=> True,
			'set_access_rights_site_objects'=> True,
			'set_access_rights_sites'=> True,
			'save_access_rights_object'=> True,
			'save_access_rights_site'=> True
		);


		var $app_title='jinn';
		var $bo;
		var $template;
		var $debug=False;
*/
		function uiadminbrowse($bo)
		{

			if(!$GLOBALS['phpgw_info']['user']['apps']['admin'])
			{
				Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.index'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			$this->bo = $bo; //CreateObject('jinn.bojinn');
			$this->template = $GLOBALS['phpgw']->template;

		}

		function render_list($table,$where_condition)
		{
			$this->template->set_file(array(

				'browse' => 'browse.tpl'
			));

			if ($GLOBALS['HTTP_POST_VARS']['limit_start']) $limit_start=$GLOBALS['HTTP_POST_VARS']['limit_start'];
			else $limit_start=$GLOBALS['limit_start'];

			if ($GLOBALS['HTTP_POST_VARS']['limit_stop']) $limit_stop = $GLOBALS['HTTP_POST_VARS']['limit_stop'];
			else $limit_stop = $GLOBALS['limit_stop'];


			if ($GLOBALS['HTTP_POST_VARS']['direction']) $direction = $GLOBALS['HTTP_POST_VARS']['direction'];
			else $direction = $GLOBALS['direction'];


			if ($GLOBALS['HTTP_POST_VARS']['order']) $order = $GLOBALS['HTTP_POST_VARS']['order'];
			else $order = $GLOBALS['order'];

			if ($GLOBALS['HTTP_POST_VARS']['show_all_cols']) $show_all_cols = $GLOBALS['HTTP_POST_VARS']['show_all_cols'];
			else $show_all_cols = $GLOBALS['show_all_cols'];

			if ($GLOBALS['HTTP_POST_VARS']['search']) $search_string=$GLOBALS['HTTP_POST_VARS']['search'];
			else $search_string=$GLOBALS['search'];


			$fieldnames = $this->bo->get_phpgw_fieldnames($table);

			// which/how many column to show, all, the prefered, or the default thirst 4
			if ($show_all_cols)
			{
				$col_list=$columns;
			}
			elseif($valid_pref_columns)
			{
				$col_list=$valid_pref_columns;
			}
			else
			{
				$col_list=array_slice($fieldnames,0,4);
			}

			foreach ( $col_list as $field ) {

				$display_name = ucfirst(strtolower(ereg_replace("_", " ", $field)));
				$column_header.='<td bgcolor="'.$GLOBALS['phpgw_info']['theme']['th_bg'].'" valign="top"><font color="'.$GLOBALS['phpgw_info']['theme']['th_text'] .'">'.lang($display_name).'</font></td>';
			}

			$records=$this->bo->get_phpgw_records($table,$where_condition,$limit[start],$limit[stop],'num');





			if (count($records)>0)
			{
				foreach($records as $recordvalues)
				{

					$table_row.='<tr valign="top">';

					$where_condition=$fieldnames[0]."='$recordvalues[0]'";
					if ($bgclr==$GLOBALS['phpgw_info']['theme']['row_off'])
					{
						$bgclr=$GLOBALS['phpgw_info']['theme']['row_on'];
					}
					else
					{
						$bgclr=$GLOBALS['phpgw_info']['theme']['row_off'];
					}

					$table_row.=
					"<td bgcolor=$bgclr align=\"left\">
					<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.add_edit_$table&where_condition=$where_condition")."\">".lang('edit')."</a></td>
					<td bgcolor=$bgclr align=\"left\">
					<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.del_$table&where_condition=$where_condition")."\" onClick=\"return window.confirm('".lang('Are you sure?')."');\"  >".lang('delete')."</a></td>
					<td bgcolor=$bgclr align=\"left\">

					<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.copy_$table&where_condition=$where_condition")."\"  onClick=\"return window.confirm('".lang('Are you sure?')."');\"   >".lang('copy')."</a></td>
					";

					if(count($recordvalues)>0)
					{

						// which/how many column to show, all, the prefered, or the default thirst 4
						if ($show_all_cols)
						{
							$col_list=$columns;
						}
						elseif($valid_pref_columns)
						{
							$col_list=$valid_pref_columns;
						}
						else
						{
							$record_list=array_slice($recordvalues,0,4);
						}


						foreach($record_list as $recordvalue)
						{

							if (empty($recordvalue))
							{
								$table_row.="<td bgcolor=\"$bgclr\">&nbsp;</td>";
							}
							else
							{
								$table_row.="<td bgcolor=\"$bgclr\" valign=\"top\">$recordvalue</td>";
							}
						}
					}

					$table_row.='</tr>';

				}
			}


			if ($table=='phpgw_jinn_site_objects')
			{                 
				//$button_browse='<td><form method=post action="'.
				//$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.add_edit_phpgw_jinn_sites&where_condition=site_id=".$parent_site_id).'"><input type=submit value="'.lang('browse').'"></form></td>';

				$button_add='<td><form method=post action="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.add_edit_phpgw_jinn_site_objects').'"><input type=submit value="'.lang('add site-object').'"><input type=hidden name=parent_site_id value='.substr($GLOBALS[where_condition],8).'></form></td>';
				$table_title=lang('Site-objects');



			}
			elseif($table=='phpgw_jinn_sites')
			{
				$button_browse='<td><form method=post action="index.php?menuaction=jinn.uiadmin.browse_phpgw_jinn_sites&where_condition=site_id='.
				$this->bo->site_id.'"><input type=submit value="'.
				lang('browse').'"></form></td>';


				$button_add='<td><form method=post action="index.php?menuaction=jinn.uiadmin.add_edit_phpgw_jinn_sites'.
				$parent_site_id.'"><input type=submit value="'.
				lang('add site').'"></form></td>';
				$table_title=lang('Sites');

			}



			$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$this->template->set_var('fieldnames',$column_header);
			$this->template->set_var('button_add',$button_add);
			$this->template->set_var('button_browse',$button_browse);
			$this->template->set_var('button_show_all_cols','');
			$this->template->set_var('button_config','');
			$this->template->set_var('table_title',$table_title);
			$this->template->set_var('record_info','');
			$this->template->set_var('table_row',$table_row);
			$this->template->set_var('edit',lang('edit'));
			$this->template->set_var('delete',lang('delete'));
			$this->template->set_var('copy',lang('copy'));
			$this->template->pparse('out','browse');


		}

	}


?>
