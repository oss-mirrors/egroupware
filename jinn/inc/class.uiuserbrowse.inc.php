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


	class uiuserbrowse
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
		var $message;
		var $debug=False;	


		function uiuserbrowse()
		{
			$GLOBALS['phpgw']->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$this->bo = CreateObject('jinn.bojinn');
			$this->template = $GLOBALS['phpgw']->template;
			$this->message = $this->bo->message;
		}

		function render_list()
		{

			//echo 'test';

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
			$this->template->set_var('menu_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.browse_objects'));
			$this->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
			$this->template->set_var('start_at',lang('start at record'));
			$this->template->set_var('stop_at',lang('stop at record'));
			$this->template->set_var('search_for',lang('search for string'));
			$this->template->set_var('show',lang('show'));
			$this->template->set_var('search',lang('search'));
			$this->template->set_var('action_config_table',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.config_table'));
			$this->template->set_var('lang_config_this_tableview',lang('Configure this tableview'));
			$this->template->set_var('search_string',$search_string);
			$this->template->set_var('show_all_cols',$show_all_cols);
			$this->template->set_var('edit',lang('edit'));
			$this->template->set_var('delete',lang('delete'));
			$this->template->set_var('copy',lang('copy'));
			$this->template->set_var('show_all_cols',$show_all_cols);
			$this->template->pparse('out','browse_menu');

			$LIMIT="LIMIT $limit[start],$limit[stop]";

			// remove
			$fieldproperties = $this->bo->get_site_fieldproperties($this->bo->site_id,$this->bo->site_object[table_name]);

			// keep
			$columns = $this->bo->get_site_fieldproperties($this->bo->site_id,$this->bo->site_object[table_name]);

			// remove
			$fieldnames=$fieldproperties;


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
					$col_list=array_slice($fieldproperties,0,4);
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
					<a href="'.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uijinn.browse_objects&order=$order_link&
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

					$where_condition=$fieldnames[0][name]."='$recordvalues[0]'";
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
					<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uijinn.add_edit_object&where_condition=$where_condition")."\">".lang('edit')."</a></td>
					<td bgcolor=$bgclr align=\"left\"><a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uijinn.del_object&where_condition=$where_condition")."\"  onClick=\"return window.confirm('".lang('Are you sure?')."');\">".lang('delete')."</a></td>
					<td bgcolor=$bgclr align=\"left\">
					<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uijinn.copy_object&where_condition=$where_condition")."\" onClick=\"return window.confirm('".lang('Are you sure?')."');\"  >".lang('copy')."</a></td>
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
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.add_edit_object') .
			'" method="post"><input type="submit" name="action" value="'.lang('Add new').'"></form></td>';

			$button_browse='<td><form name=form2 action="'.
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.browse_objects') .
			'" method="post"><input type="submit" name="action" value="'.lang('Browse').'"></form></td>';

			$button_show_all_cols='<td><form name=form2 action="'.
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.browse_objects&show_all_cols=True') .
			'" method="post"><input type="submit" name="action" value="'.lang('Show all columns').'"></form></td>';

			$button_config='<td><form name=form2 action="'.
			$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.config_objects') .
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





























		function render_list_addressbook()
		{
			//$GLOBALS['phpgw']->common->phpgw_header();
			//echo parse_navbar();

			$this->template->set_file(array('browse_list_t' => 'browse_list.tpl'));
			$this->template->set_block('browse_list_t','list_header','list_header');
			$this->template->set_block('browse_list_t','column','column');
			$this->template->set_block('browse_list_t','row','row');
			$this->template->set_block('browse_list_t','list_footer','list_footer');



			// read sort,limit and search vars

			/*$custom = $this->fields->read_custom_fields();
			$customfields = array();
			while(list($x,$y) = @each($custom))
			{
				$customfields[$y['name']] = $y['name'];
				$namedfields[$y['name']] = $y['title'];
			}

			if (!isset($this->cat_id))
			{
				$this->cat_id = $this->prefs['default_category'];
			} 
			if ($this->prefs['autosave_category'])
			{
				$GLOBALS['phpgw']->preferences->read_repository();
				$GLOBALS['phpgw']->preferences->delete('addressbook','default_category');
				$GLOBALS['phpgw']->preferences->add('addressbook','default_category',$this->cat_id);
				$GLOBALS['phpgw']->preferences->save_repository();
			}
			*/


			/* create column list and the top row of the table based on user prefs */

			// read fields to display else show first 4 ...
			// get prefs for this user for this site for this table
			// get all fields from table
			// if table prefs exist and exist in table show these columns
			// else show thirst 4 columns and the possibility to set the prefs

			// while there are no prefs show fisrt 4

			$pref_columns=False; // function not implemented yet
			$all_columns=$this->bo->get_object_column_names($this->bo->site_id,$this->bo->site_object[table_name]);

			if ($pref_columns)
			{
				foreach($pref_columns as $pref_col)
				{
					if (in_array($pref_col,$all_columns))
					{
						$valid_pref_columns[]=$pref_col;
					}
				}

			}

			if ($display_all)
			{
				$col_list=$all_columns;
			}
			elseif($valid_pref_columns)
			{
				$col_list=array_slice($all_columns,0,4);
			}

			else
			{
				$col_list=array_slice($all_columns,0,4);
			}




			while($column = each($col_list))
			{
				//$showcol = $this->display_name($column);
				$cols .= '  <td height="21">' . "\n";
				$cols .= '    <font size="-1" face="Arial, Helvetica, sans-serif">';
				$cols .= $GLOBALS['phpgw']->nextmatchs->show_sort_order($this->sort,
				$column,$this->order,'/index.php',$showcol,'&menuaction=addressbook.uiaddressbook.index');
				$cols .= "</font>\n  </td>";
				$cols .= "\n";

				/* To be used when displaying the rows */
				//$columns_to_display[$column] = True;
			}




			/* Check if prefs were set, if not, create some defaults */
			if(!$columns_to_display)
			{
				//$columns_to_display = array(
					//			'n_given'  => 'n_given',
					//				'n_family' => 'n_family',
					//					'org_name' => 'org_name'
					//					);
					//				$columns_to_display = $columns_to_display + $customfields;
					/* No prefs,. so cols above may have been set to '' or a bunch of <td></td> */
					$cols='';
					while ($column = each($col_list))
					{
						//$showcol = $this->display_name($column[0]);
						//if (!$showcol) { $showcol = $column[1]; }
						$cols .= '  <td height="21">' . "\n";
						$cols .= '    <font size="-1" face="Arial, Helvetica, sans-serif">';
						$cols .= $GLOBALS['phpgw']->nextmatchs->show_sort_order($this->sort,
						$column,$this->order,"/index.php",$column,'&menuaction=jinn.uijinn.browse&cat_id='.$this->cat_id);
						$cols .= "</font>\n  </td>";
						$cols .= "\n";
					}
					$noprefs=lang('Please set your preferences for this application');
				}

				if(!$this->start)
				{
					$this->start = 0;
				}

				if($GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'] &&
				$GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'] > 0)
				{
					$this->limit = $GLOBALS['phpgw_info']['user']['preferences']['common']['maxmatchs'];
				}
				else
				{
					$this->limit = 30;
				}

				/*global $filter; */
				if(empty($this->filter) || !isset($this->filter))
				{
					if($this->prefs['default_filter'])
					{
						$this->filter = $this->prefs['default_filter'];
						$this->query = '';
					}
					else
					{
						$this->filter = 'none';
					}
				}

				/*
				Set qfilter to display entries where tid=n (normal contact entry),
				else they may be accounts, etc.
				*/
				$qfilter = 'tid=n';
				switch ($this->filter)
				{
					case 'blank':
					$nosearch = True;
					break;
					case 'none':
					break;
					case 'private':
					$qfilter .= ',access=private'; /* fall through */
					case 'yours':
					$qfilter .= ',owner=' . $GLOBALS['phpgw_info']['user']['account_id'];
					break;
					default:
					$qfilter .= ',owner=' . $this->filter;
				}
				if ($this->cat_id)
				{
					$qfilter .= ',cat_id='.$this->cat_id;
				}

				if (!$userid)
				{
					$userid = $GLOBALS['phpgw_info']['user']['account_id'];
				}

				if ($nosearch && !$this->query)
				{
					$entries = array();
					$total_records = 0;
				}
				elseif(false)
				{
					/* read the entry list */
					$entries = $this->bo->read_entries(array(
						'start'  => $this->start,
						'limit'  => $this->limit,
						'fields' => $columns_to_display,
						'filter' => $qfilter,
						'query'  => $this->query,
						'sort'   => $this->sort,
						'order'  => $this->order
					));
					$total_records = $this->bo->total;
				}

				/* global here so nextmatchs accepts our setting of $query and $filter */
				$GLOBALS['query']  = $this->query;
				$GLOBALS['filter'] = $this->filter;

				$search_filter = $GLOBALS['phpgw']->nextmatchs->show_tpl('/index.php',
				$this->start, $total_records,'&menuaction=addressbook.uiaddressbook.index&fcat_id='.$this->cat_id,'75%',
				$GLOBALS['phpgw_info']['theme']['th_bg'],1,1,1,1,$this->cat_id);
				$query = $filter = '';

				$lang_showing = $GLOBALS['phpgw']->nextmatchs->show_hits($total_records,$this->start);

				/* set basic vars and parse the header */
				$this->template->set_var('font',$GLOBALS['phpgw_info']['theme']['font']);
				$this->template->set_var('lang_view',lang('View'));
				$this->template->set_var('lang_vcard',lang('VCard'));
				$this->template->set_var('lang_edit',lang('Edit'));
				$this->template->set_var('lang_owner',lang('Owner'));

				$this->template->set_var('searchreturn',$noprefs . ' ' . $searchreturn);
				$this->template->set_var('lang_showing',$lang_showing);
				$this->template->set_var('search_filter',$search_filter);
				$this->template->set_var('cats',lang('Category'));
				$this->template->set_var('cats_url',$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiaddressbook.index'));
				/* $this->template->set_var('cats_link',$this->cat_option($this->cat_id)); */
				$this->template->set_var('lang_cats',lang('Select'));
				//$this->template->set_var('lang_addressbook',lang('Address book'));
				$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
				$this->template->set_var('th_font',$GLOBALS['phpgw_info']['theme']['font']);
				$this->template->set_var('th_text',$GLOBALS['phpgw_info']['theme']['th_text']);
				$this->template->set_var('lang_add',lang('Add'));
				$this->template->set_var('add_url',$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiaddressbook.add'));
				$this->template->set_var('lang_addvcard',lang('AddVCard'));
				$this->template->set_var('vcard_url',$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uivcard.in'));
				$this->template->set_var('lang_import',lang('Import Contacts'));
				$this->template->set_var('import_url',$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiXport.import'));
				$this->template->set_var('lang_import_alt',lang('Alt. CSV Import'));
				$this->template->set_var('import_alt_url',$GLOBALS['phpgw']->link('/addressbook/csv_import.php'));
				$this->template->set_var('lang_export',lang('Export Contacts'));
				$this->template->set_var('export_url',$GLOBALS['phpgw']->link('/index.php','menuaction=addressbook.uiXport.export'));

				$this->template->set_var('start',$this->start);
				$this->template->set_var('sort',$this->sort);
				$this->template->set_var('order',$this->order);
				$this->template->set_var('filter',$this->filter);
				$this->template->set_var('query',$this->query);
				$this->template->set_var('cat_id',$this->cat_id);

				$this->template->set_var('qfield',$qfield);
				$this->template->set_var('cols',$cols);

				$this->template->pparse('out','list_header');

				/* Show the entries */
				/* each entry */
				for ($i=0;$i<count($entries);$i++)
				{
					$this->template->set_var('columns','');
					$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
					$this->template->set_var('row_tr_color',$tr_color);
					$myid    = $entries[$i]['id'];
					$myowner = $entries[$i]['owner'];

					/* each entry column */
					@reset($columns_to_display);
					while ($column = @each($columns_to_display))
					{
						$ref = $data='';
						$coldata = $entries[$i][$column[0]];
						/* echo '<br>coldata="' . $coldata . '"'; */
						/* Some fields require special formatting. */
						if ($column[0] == 'url')
						{
							if ( !empty($coldata) && (substr($coldata,0,7) != 'http://') ) { $coldata = 'http://' . $coldata; }
							$ref='<a href="'.$coldata.'" target="_new">';
							$data=$coldata.'</a>';
						}
						elseif ( ($column[0] == 'email') || ($column[0] == 'email_home') )
						{
							if ($GLOBALS['phpgw_info']['user']['apps']['email'])
							{
								$ref='<a href="'.$GLOBALS['phpgw']->link("/email/compose.php","to=" . urlencode($coldata)).'" target="_new">';
							}
							else
							{
								$ref='<a href="mailto:'.$coldata.'">';
							}
							$data=$coldata . '</a>';
						}
						else /* But these do not */
						{
							$ref = ''; $data = $coldata;
						}
						$this->template->set_var('col_data',$ref.$data);
						$this->template->parse('columns','column',True);
					}

					if (1)
					{
						$this->template->set_var('row_view_link',$GLOBALS['phpgw']->link('/index.php',
						'menuaction=addressbook.uiaddressbook.view&ab_id='.$entries[$i]['id']));
					}
					else
					{
						$this->template->set_var('row_view_link','');
						$this->template->set_var('lang_view',lang('Private'));
					}

					$this->template->set_var('row_vcard_link',$GLOBALS['phpgw']->link('/index.php',
					'menuaction=addressbook.uivcard.out&ab_id='.$entries[$i]['id']));
					/* echo '<br>: ' . $contacts->grants[$myowner] . ' - ' . $myowner; */
					if ($this->contacts->check_perms($this->contacts->grants[$myowner],PHPGW_ACL_EDIT) || $myowner == $GLOBALS['phpgw_info']['user']['account_id'])
					{
						$this->template->set_var('row_edit','<a href="' . $GLOBALS['phpgw']->link('/index.php',
						'menuaction=addressbook.uiaddressbook.edit&ab_id='.$entries[$i]['id']) . '">' . lang('Edit') . '</a>');
					}
					else
					{
						$this->template->set_var('row_edit','&nbsp;');
					}

					$this->template->set_var('row_owner',$GLOBALS['phpgw']->accounts->id2name($myowner));

					$this->template->parse('rows','row',True);
					$this->template->pparse('out','row');
					reset($columns_to_display);
				}

				$this->template->pparse('out','list_footer');
				//$this->save_sessiondata();
				/* $GLOBALS['phpgw']->common->phpgw_footer(); */
			}

		}

	?>
