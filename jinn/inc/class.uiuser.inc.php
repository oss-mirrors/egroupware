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

	class uiuser 
	{
		var $public_functions = Array
		(
			'index'				=> True,
			'add_edit_object'		=> True,
			'browse_objects'		=> True,
			'config_objects'		=> True,
			'save_object_config'	=> True
		);

		var $bo;
		var $ui;
		var $template;

		function uiuser()
		{
			$this->bo = CreateObject('jinn.bouser');

			$this->template = $GLOBALS['phpgw']->template;

			$this->ui = CreateObject('jinn.uicommon');
			$this->ui->app_title=lang('Moderator Mode');

		}

		/********************************
		*  create the default index page                                                          
		*/
		function index()
		{
			//var_dump($this->bo);


			if ($this->bo->site_object_id && $this->bo->site_object['parent_site_id']==$this->bo->site_id )
			{
				$this->bo->save_sessiondata();
				$this->bo->common->exit_and_open_screen('jinn.uiuser.browse_objects');
			}
			else
			{

				if (!$this->bo->site_id)
				{
					$this->bo->message['info']=lang('Select site to moderate');
				}
				else //if(!$this->bo->site_object_id)
				{
					$this->bo->message['info']=lang('Select site-object to moderate');
				}

				unset($GLOBALS['phpgw_info']['flags']['noheader']);
				unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
				unset($GLOBALS['phpgw_info']['flags']['noappheader']);
				unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

				$this->ui->header('Index');
				$this->ui->msg_box($this->bo->message);

				$this->main_menu();
				$this->bo->save_sessiondata();
			}
		}

		/****************************************************************************\
		* create main menu                                                           *
		\****************************************************************************/

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

				// set theme_colors
				$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
				$this->template->set_var('th_text',$GLOBALS['phpgw_info']['theme']['th_text']);
				$this->template->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
				$this->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);

				// set menu
				$this->template->set_var('site_objects',$object_options);
				$this->template->set_var('site_options',$site_options);

				$this->template->set_var('main_form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.index" name="jinn'));
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



			/**********************************
			* 	create form to new objectrecord                                          
			*/ 
			function add_edit_object()
			{

				$this->ui->header('add or edit objects');
				$this->ui->msg_box($this->bo->message);
				$this->main_menu();	

				$this->main = CreateObject('jinn.uiuseraddedit',$this->bo);
				$this->main->render_form();

				$this->bo->save_sessiondata();
			}



			/****************************************************************************\
			* 	Browse through site_objects                                              *
			\****************************************************************************/

			function browse_objects()
			{

				if(!$this->bo->so->test_JSO_table($this->bo->site_object))
				{
					unset($this->bo->site_object_id);
					$this->bo->message['error']=lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']);

					$this->bo->save_sessiondata();
					$this->bo->common->exit_and_open_screen('jinn.uiuser.index');

				}

				$this->ui->header('browse through objects');
				$this->ui->msg_box($this->bo->message);
				$this->main_menu();	

				$this->template->set_file(array(
					'browse_menu' => 'browse_menu.tpl',
					'browse' => 'browse.tpl'
				));

				$pref_columns_str=$this->bo->read_preferences('show_fields'); 
				$default_order=$this->bo->read_preferences('default_order');

				if ($GLOBALS['HTTP_POST_VARS']['offset']) $offset=$GLOBALS['HTTP_POST_VARS']['offset'];
				elseif($GLOBALS['offset']) $offset=$GLOBALS['offset'];
				else $offset = $this->bo->browse_settings['offset'];

				if ($GLOBALS['HTTP_POST_VARS']['asc']) $asc=$GLOBALS['HTTP_POST_VARS']['asc'];
				elseif($GLOBALS['asc']) $asc=$GLOBALS['asc'];
				else $asc = $this->bo->browse_settings['asc'];

				if ($GLOBALS['HTTP_POST_VARS']['order']) $order=$GLOBALS['HTTP_POST_VARS']['order'];
				elseif($GLOBALS['order']) $order=$GLOBALS['order'];
				elseif($this->bo->browse_settings['order']) $order = $this->bo->browse_settings['order'];
				else $order = $default_order;


				if ($GLOBALS['HTTP_POST_VARS']['filter']) $filter=$GLOBALS['HTTP_POST_VARS']['filter'];
				elseif($GLOBALS['filter']) $filter=$GLOBALS['filter'];
				else $filter = $this->bo->browse_settings['filter'];

				$this->bo->browse_settings = array
				(
					'offset'=>$offset,
					'range'=>$range,
					'$navdir'=>$navdir,
					'order'=>$order,
					'filter'=>$filter
				);

				if ($GLOBALS['HTTP_POST_VARS']['limit_start']) $limit_start=$GLOBALS['HTTP_POST_VARS']['limit_start'];
				else $limit_start=$GLOBALS['limit_start'];

				if ($GLOBALS['HTTP_POST_VARS']['limit_stop']) $limit_stop = $GLOBALS['HTTP_POST_VARS']['limit_stop'];
				else $limit_stop = $GLOBALS['limit_stop'];


				if ($GLOBALS['HTTP_POST_VARS']['direction']) $direction = $GLOBALS['HTTP_POST_VARS']['direction'];
				else $direction = $GLOBALS['direction'];

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

				/* get one with many relations */
				$relation1_array=$this->bo->extract_1w1_relations($this->bo->site_object['relations']);
				if (count($relation1_array)>0)
				{
					foreach($relation1_array as $relation1)
					{
						$fields_with_relation1[]=$relation1[field_org];
					}

				}

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




					if ($pref_columns_str)
					{
						$all_prefs_show_hide=explode('|',$pref_columns_str);
						foreach($all_prefs_show_hide as $pref_show_hide)
						{

							$pref_show_hide_arr=explode(',',$pref_show_hide);
							if($pref_show_hide_arr[0]==$this->bo->site_object_id)
							{
								$pref_columns=array_slice($pref_show_hide_arr,1);
								//is this necessary?	
								foreach($pref_columns as $pref_col)
								{
									//if (in_array($pref_col,$columns))
									//{
										$valid_pref_columns[]=array('name'=>$pref_col);
										//}
									}

								}


							}

						}

						//create more simple col_list
						foreach ($columns as $single_col)
						{
							$col_names_list[]=$single_col[name];
						}						


						/*
						check if orderfield exist else drop it
						*/
						if(!in_array(trim(substr($order,0,(strlen($order)-4))),$col_names_list)) unset($order);
						unset($col_names_list);

						// which/how many column to show, all, the prefered, or the default thirst 4
						if ($show_all_cols=='True')
						{
							$col_list=$columns;
						}
						elseif($pref_columns)
						{
							$col_list=$valid_pref_columns;
						}
						else
						{
							$col_list=array_slice($columns,0,4);
						}

						// make columnheaders
						foreach ($col_list as $col)
						{
							$col_names_list[]=$col[name];
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

							$col_headers_t.='<td bgcolor="'.$GLOBALS['phpgw_info']['theme']['th_bg'].'" align=\"center\">
							<a href="'.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiuser.browse_objects&order=$order_link&
							search=$search_string&limit_start=$limit_start&limit_stop=$limit_stop&show_all_cols=$show_all_cols
							").'">
							'.$col[name].'&nbsp;'.$order_image.'</a></td>';
						}
					}

					$records=$this->bo->get_records($this->bo->site_object[table_name],$where_condition,$limit[start],$limit[stop],'name',$order,implode(',',$col_names_list));

					if (count($records)>0)
					{

						foreach($records as $recordvalues)
						{
							// THIS WHERE_CONDITION HAS TO CONTAIN ALL FIELDS TO BE 'ID' independant
							$where_condition=$columns[0][name]."='$recordvalues[0]'";
							$where_condition=$columns[0][name].'=\''.$recordvalues[$columns[0][name]]."'";


							if ($bgclr==$GLOBALS['phpgw_info']['theme']['row_off'])
							{
								$bgclr=$GLOBALS['phpgw_info']['theme']['row_on'];
							}
							else
							{
								$bgclr=$GLOBALS['phpgw_info']['theme']['row_off'];
							}


							if(count($recordvalues)>0)
							{
								$table_rows.='<tr valign="top">';
								$table_rows.="<td bgcolor=$bgclr align=\"left\">
								<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiuser.add_edit_object&where_condition=$where_condition")."\">".lang('edit')."</a>
								</td>
								<td bgcolor=$bgclr align=\"left\"><a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.bouser.del_object&where_condition=$where_condition")."\"  onClick=\"return window.confirm('".lang('Are you sure?')."');\">".lang('delete')."</a>
								</td>
								<td bgcolor=$bgclr align=\"left\">
								<a href=\"".$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.bouser.copy_object&where_condition=$where_condition")."\" onClick=\"return window.confirm('".lang('Are you sure?')."');\"  >".lang('copy')."</a>
								</td>
								";
//								var_dump($recordvalues[0]);
//								die();
 								$records_keys=array_keys($recordvalues);
								$records_values=array_values($recordvalues);

								for($i=0;$i<count($recordvalues);$i++)
								{
									
									$recordvalue=$records_values[$i];
									if (empty($recordvalue))
									{
										$table_rows.="<td bgcolor=\"$bgclr\">&nbsp;</td>";
									}
									else
									{
										
										//parse one with many relations not functional / FIXME
										if (false && is_array($fields_with_relation1) 
											&& in_array($records_keys[$i],$fields_with_relation1))
										{
											$related_fields=$this->bo->get_related_field($relation1_array[$records_keys[$i]]);
											$recordvalue= $related_fields[$recordvalue][name].' ('.$recordvalue.')';
											
										}
										else
										{	
											$recordvalue=$this->bo->get_plugin_bv($records_keys[$i],$recordvalue);
										}

										$display_value=$recordvalue;
										$table_rows.="<td bgcolor=\"$bgclr\" valign=\"top\">".$display_value."</td>";
									}

								}
								
								$table_rows.='</tr>';


							}


						}
					}


					$button_add='<td><form name=form1 action="'	.
					$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.add_edit_object') .
					'" method="post"><input type="submit" name="action" value="'.lang('Add new').'"></form></td>';

					/*					$button_browse='<td><form name=form2 action="'.
					$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.browse_objects') .
					'" method="post"><input type="submit" name="action" value="'.lang('Browse').'"></form></td>';
					*/
					/*
					show all fields button
					*/
					/*					if($show_all_cols=='False')
					{
						$button_show_all_cols='<td><form name=form2 action="'.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.browse_objects&show_all_cols=True') .'" method="post"><input type="submit" name="action" value="'.lang('Show all columns').'"></form></td>';
					}
					else
					{
						$button_show_all_cols='<td><form name=form2 action="'.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.browse_objects&show_all_cols=False') . '" method="post"><input type="submit" name="action" value="'.lang('Normal View').'"></form></td>';

					}
					*/

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
					$this->template->set_var('table_row',$table_rows);
					$this->template->pparse('out','browse');

					unset($this->message);

					unset($this->bo->message);
					$this->bo->save_sessiondata();
				}

				/****************************************************************************\
				* 	Config site_objects                                              *
				\****************************************************************************/

				function config_objects()
				{
					$this->ui->header(lang('configure browse view'));
					$this->ui->msg_box($this->bo->message);
					$this->main_menu();	

					$main = CreateObject('jinn.uiconfig',$this->bo);
					$main->show_fields();

					$this->bo->save_sessiondata();
				}





			}
			?>
