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

class uiuserbrowse extends uiuser 
{

	function uiuserbrowse($bo)
	{
		$this->bo=$bo;
		
		$GLOBALS['phpgw']->nextmatchs = CreateObject('phpgwapi.nextmatchs');
		
		$this->template = $GLOBALS['phpgw']->template;
	}

	function render_list()
	{
		
		$this->template->set_file(array(
					'browse_menu' => 'browse_menu.tpl',
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

		$num_rows=$this->bo->so->num_rows_table($this->bo->site_id,$this->bo->site_object['table_name']);

		$limit=$this->bo->set_limits($limit_start,$limit_stop,$direction,$num_rows);

		$this->template->set_var('limit_start',$limit['start']);
		$this->template->set_var('limit_stop',$limit['stop']);
		$this->template->set_var('order',$order);
		$this->template->set_var('menu_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.browse_objects'));
		$this->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
		$this->template->set_var('start_at',lang('start at record'));
		$this->template->set_var('stop_at',lang('stop at record'));
		$this->template->set_var('search_for',lang('search for string'));
		$this->template->set_var('show',lang('show'));
		$this->template->set_var('search',lang('search'));
		$this->template->set_var('action_config_table',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.config_table'));
		$this->template->set_var('lang_config_this_tableview',lang('Configure this tableview'));
		$this->template->set_var('search_string',$search_string);
		$this->template->set_var('show_all_cols',$show_all_cols);
		$this->template->set_var('edit',lang('edit'));
		$this->template->set_var('delete',lang('delete'));
		$this->template->set_var('copy',lang('copy'));
		$this->template->set_var('show_all_cols',$show_all_cols);
		$this->template->pparse('out','browse_menu');

		$LIMIT="LIMIT $limit[start],$limit[stop]";

		$columns=$this->bo->so->site_table_metadata($this->bo->site_id, $this->bo->site_object['table_name']);

		if (count($columns)>0)
		{
			foreach ($columns as $col)
			{

				if ($search_string)
				{
					if ($where_condition)
					{
						$where_condition.= " OR $col[name] LIKE '%$search_string%'";
						$limit="";
					}
					else
					{
						$where_condition = " $col[name] LIKE '%$search_string%'";
					}
				}
			}


			/* create column list and the top row of the table based on user prefs */

			// read fields to display else show first 4 ...
			// get prefs for this user for this site for this table
			// get all fields from table
			// if table prefs exist and exist in table show these columns
			// else show thirst 4 columns and the possibility to set the prefs

			// while there are no prefs show fisrt 4


			// get the prefered columns, if they exist
			$pref_columns=False; // function not implemented yet

			if ($pref_columns)
			{
				foreach($pref_columns as $pref_col)
				{
					if (in_array($pref_col,$columns))
					{
						$valid_pref_columns[]=$pref_col;
					}
				}

			}

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
				$col_list=array_slice($columns,0,4);
			}


			//$order = 'producten DESC';
			// make columnheaders

			foreach ($col_list as $col)
			{
				unset($order_link);
				unset($order_image);
				if ($col[name] == trim(substr($order,0,(strlen($order)-4))))
				{
					if (substr($order,-4)== 'DESC')
					{
						$order_link = $col[name].' ASC';
						$order_image = '<img src="'. $GLOBALS['phpgw']->common->image('jinn','desc.png').'" border="0">';
					}
					else 
					{
						$order_link = $col[name].' DESC';
						$order_image = '<img src="'. $GLOBALS['phpgw']->common->image('jinn','asc.png').'" border="0">';
					}
				}
				else
				{
					$order_link = $col[name].' ASC';
				}

				//die ($order);

				$col_headers_t.='<td bgcolor="'.$GLOBALS['phpgw_info']['theme']['th_bg'].'" align=\"center\">
					<a href="'.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiuser.browse_objects&order=$order_link&
					search=$search_string&limit_start=$limit_start&limit_stop=$limit_stop&show_all_cols=$show_all_cols
					").'">
					'.$col[name].'&nbsp;'.$order_image.'</a></td>';
			}
		}

		$records=$this->bo->get_records_2($this->bo->site_object[table_name],$where_condition,$limit[start],$limit[stop],'num',$order);

		if (count($records)>0)
		{

			foreach($records as $recordvalues)
			{

				$table_row.='<tr valign="top">';

				$where_condition=$columns[0][name]."='$recordvalues[0]'";
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
					<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiuser.add_edit_object&where_condition=$where_condition")."\">".lang('edit')."</a>
					</td>
					<td bgcolor=$bgclr align=\"left\"><a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.bouser.del_object&where_condition=$where_condition")."\"  onClick=\"return window.confirm('".lang('Are you sure?')."');\">".lang('delete')."</a>
					</td>
					<td bgcolor=$bgclr align=\"left\">
					<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.bouser.copy_object&where_condition=$where_condition")."\" onClick=\"return window.confirm('".lang('Are you sure?')."');\"  >".lang('copy')."</a>
					</td>
					";




				if(count($recordvalues)>0)
				{

					// first 4, all, or an selection
					if ($show_all_cols)
					{	
					}
					elseif ($valid_pref_columns)
					{

					}
					else
					{
						$recordvalues=array_slice($recordvalues,0,4);
					}


					foreach($recordvalues as $recordvalue)
					{

						if (empty($recordvalue))
						{
							$table_row.="<td bgcolor=\"$bgclr\">&nbsp;</td>";
						}
						else
						{
							if(strlen($recordvalue)>15)
							{
								$display_value = substr($recordvalue,0,15). ' ...';
							}
							else
							{
								$display_value=$recordvalue;
							}
							$table_row.="<td bgcolor=\"$bgclr\" valign=\"top\">".htmlentities($display_value)."</td>";
						}
					}


				}


				$table_row.='</tr>';

			}
		}

		$button_add='<td><form name=form1 action="'	.
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.add_edit_object') .
			'" method="post"><input type="submit" name="action" value="'.lang('Add new').'"></form></td>';

		$button_browse='<td><form name=form2 action="'.
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.browse_objects') .
			'" method="post"><input type="submit" name="action" value="'.lang('Browse').'"></form></td>';

		$button_show_all_cols='<td><form name=form2 action="'.
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.browse_objects&show_all_cols=True') .
			'" method="post"><input type="submit" name="action" value="'.lang('Show all columns').'"></form></td>';

		$button_config='<td><form name=form2 action="'.
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.config_objects') .
			'" method="post"><input type="submit" name="action" value="'.lang('Configure this View').'"></form></td>';

		$this->template->set_var('button_add',$button_add);
		$this->template->set_var('button_browse',$button_browse);
		$this->template->set_var('button_show_all_cols',$button_show_all_cols);
		$this->template->set_var('button_config',$button_config);
		$this->template->set_var('table_title',$this->bo->site_object[name]);
		$this->template->set_var('record_info',lang('record').' '.$limit[start].' '.lang('t/m').' '.$limit[stop]);
		$this->template->set_var('fieldnames',$col_headers_t);
		$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
		$this->template->set_var('fieldnames',$col_headers_t);
		$this->template->set_var('table_row',$table_row);
		$this->template->pparse('out','browse');

		unset($this->message);
	}


}

?>
