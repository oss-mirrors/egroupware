<?php
  /**************************************************************************\
  * phpGroupWare - Bookmarks                                                 *
  * http://www.phpgroupware.org                                              *
  * Based on Bookmarker Copyright (C) 1998  Padraic Renaghan                 *
  *                     http://www.renaghan.com/bookmarker                   *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

	/* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'               => 'bookmarks',
		'enable_categories_class' => True
	);
	include('../header.inc.php');
	$GLOBALS['phpgw']->bookmarks = createobject('bookmarks.bookmarks');

	// Uncomment the echo line to return debugging info
	function _debug($s)
	{
		//echo $s;
	}

	// possible enhancements:
	//  give option, that if url already exists, update existing row
	//  give option, to load from csv file
	//  give option, to load all urls into unassigned unassigned
	//  give option, to delete bookmarks,cat,subcat before import

	// find existing category matching name, or
	// create a new one. return id.
	function getCategory($name)
	{
		global $cat_cache, $catNext;

		$db = $GLOBALS['phpgw']->db;

		_debug('<br>Testing for category: ' . $name);
     
		if (! $name)
		{
			$name = 'No category';
		}

		if ($cat_cache[$name] && $cat_cache[$name] != 0)
		{
			_debug(' - ' . $name . ' is already cached');
			return $cat_cache[$name];
		}
		else
		{
			if ($GLOBALS['phpgw']->categories->exists('mains',$name))
			{
				$cat_cache[$name] = $GLOBALS['phpgw']->categories->name2id($name);
				_debug(' - ' . $name . ' already exists - id: ' . $cat_cache[$name]);
			}
			else
			{
				$GLOBALS['phpgw']->categories->add(array(
					'name'   => $name,
					'descr'  => '',
					'parent' => 0,
					'access' => '',
					'data'   => ''
				));
				$cat_cache[$name] = $GLOBALS['phpgw']->categories->name2id($name);
				_debug(' - ' . $name . ' does not exist - new id: ' . $cat_cache[$name]);
			}

			return $cat_cache[$name];
		}
	}

	# find existing subcategory matching name, or
	# create a new one. return id.
	function getSubCategory ($name)
	{
		/*     global $subcat,$subcatNext,$default_subcategory;

		$db = $GLOBALS['phpgw']->db;
		$upperName = strtoupper($name);

		if (! $name) {
			$subcat[$upperName] = $default_subcategory;
			return $default_subcategory;
		}

		if (isset($subcat[$upperName])) {
			return $subcat[$upperName];
		} else {
			$q  = "INSERT INTO bookmarks_subcategory (name, username) ";
			$q .= "VALUES ('" . addslashes($name) . "', '" . $GLOBALS['phpgw_info']["user"]["account_id"] . "') ";

			$db->query($q,__LINE__,__FILE__);
			if ($db->Errno != 0) {
				$error_msg .= "<br>Error adding subcategory ".$name." - ".$subcatNext;
				return -1;
			}

			$db->query("select id from bookmarks_subcategory where name='" . addslashes($name) . "' and username='"
			. $GLOBALS['phpgw_info']["user"]["account_id"] . "'",__LINE__,__FILE__);
			$db->next_record();

			$subcat[$upperName] = $db->f("id");
			$subcatNext++;
			return $db->f("id");
		} */
	}

	$GLOBALS['phpgw']->template->set_file(array(
		'common'   => 'common.tpl',
		'body'     => 'import.body.tpl'
	));
	set_standard("import", &$GLOBALS['phpgw']->template);

	if ($import)
	{
		_debug('<p><b>DEBUG OUTPUT:</b>');
		_debug('<br>file: ' . $bkfile);
		_debug('<br>file_name: ' . $bkfile_name);
		_debug('<br>file_size: ' . $bkfile_size);
		_debug('<br>file_type: ' . $bkfile_type . '<p><b>URLs:</b>');
		_debug('<table border="1" width="100%">');
		_debug('<tr><td>cat id</td> <td>sub id</td> <td>name</td> <td>url</td> <td>add date</td> <td>change date</td> <td>vist date</td></tr>');

		if (empty($bkfile) || $bkfile == "none")
		{
			$error_msg .= '<br>'.lang('Netscape bookmark filename is required!');
			break;
		}
		$default_rating = 0;

		$fd = @fopen($bkfile,'r');
		if ($fd)
		{
			$inserts = 0;
			$folder_index = -1;
			$cat_index = -1;
			$scat_index = -1;
			$bookmarker->url_format_check = 0;
			$bookmarker->url_responds_check = false;

			$utf8flag = False;
   
			while ($line = @fgets($fd, 2048))
			{
			 	if ((strcmp('<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">', rtrim($line)) == 0) && function_exists('iconv'))
            {
             $utf8flag = True;
            }
				// URLs are recognized by A HREF tags in the NS file.
				elseif (eregi('<A HREF="([^"]*)[^>]*>(.*)</A>', $line, $match))
 				{
					$url_parts = @parse_url($match[1]);
					if ($url_parts[scheme] == 'http' || $url_parts[scheme] == 'https' || $url_parts[scheme] == 'ftp' || $url_parts[scheme] == 'news')
					{
						reset($folder_stack);
						unset($error_msg);
						$cid  = $GLOBALS['phpgw']->categories->name2id('No category');
						$scid = 0;
						$i    = 0;
						$keyw = '';

//						echo '<br>test: ' . $folder_index;

						while ($i <= $folder_index)
						{
							if ($i == 0)
							{
								$cid = getCategory($folder_name_stack[$i]);
								$cid = ($cid?$cid:0);
							}
							elseif ($i == 1)
							{
								$scid = getSubCategory($folder_name_stack[$i]);
								$scid = ($scid?$scid:0);
							}

							$keyw .= ' ' . $folder_name_stack[$i];
							$i++;
						}
						$values['category'] = sprintf('%s|%s',$cid,$scid);
						$values['url']      = $match[1];

						//if iconv fails, fall back to undecoded string
						$name_iconv = ($utf8flag ? iconv('UTF-8','ISO-8859-1',$match[2]) : False);
						$values['name']     = ($name_iconv ? $name_iconv : $match[2]);
						$values['rating']   = 0;

						eregi('ADD_DATE="([^"]*)"',$line,$add_info);
						eregi('LAST_VISIT="([^"]*)"',$line,$vist_info);
						eregi('LAST_MODIFIED="([^"]*)"',$line,$change_info);

						$values['timestamps'] = sprintf('%s,%s,%s',$add_info[1],$vist_info[1],$change_info[1]);

						$bid = -1;
						if (! $GLOBALS['phpgw']->bookmarks->add(&$bid, $values, True))
						{
							print("<br>" . $error_msg . "\n");
							$all_errors .= $error_msg;
						}
						else
						{
							$inserts++;
						}

						_debug(sprintf("<tr><td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> </tr>",$cid,$scid,$match[2],$match[1],$add_info[1],$change_info[1],$vist_info[1]));
					}
				}

				// folders start with the folder name inside an <H3> tag,
				// and end with the close </DL> tag.
				// we use a stack to keep track of where we are in the
				// folder hierarchy.
				elseif (eregi('<H3[^>]*>(.*)</H3>', $line, $match))
				{
					$folder_index ++;
					$id = -1;

					//if iconv fails, fall back to undecoded string
					$folder_name_iconv = ($utf8flag ? iconv('UTF-8','ISO-8859-1',$match[1]) : False);
					$folder_name = ($folder_name_iconv ? $folder_name_iconv : $match[1]);

					if ($folder_index == 0)
					{
						$cat_index ++;
						$cat_array[$cat_index] = $folder_name;
						$id = $cat_index + $cat_start;
					}
					elseif ($folder_index == 1)
					{
						$scat_index ++;
						$scat_array[$scat_index] = $folder_name;
						$id = $scat_index + $scat_start;
					}
					$folder_stack[$folder_index] = $id;
					$folder_name_stack[$folder_index] = $folder_name;
				}
				elseif (eregi('</DL>', $line))
				{
					$folder_index-- ;
				}
			}
			@fclose($fd);
			_debug('</table>');
		}
		else
		{
			$error_msg .= '<br>'.lang('Unable to open temp file %1 for import.',$bkfile);
		}

		unset($msg);
		$msg .= '<br>'.lang("%1 bookmarks imported from %2 successfully.", $inserts, $bkfile_name);
		$error_msg = $all_errors;
//		break;
	}

	$GLOBALS['phpgw']->template->set_var('FORM_ACTION',$GLOBALS['phpgw']->link('/bookmarks/import.php'));
	$GLOBALS['phpgw']->template->set_var('lang_name',lang('Enter the name of the Netscape bookmark file<br>that you want imported into bookmarker below.'));
	$GLOBALS['phpgw']->template->set_var('lang_file',lang('Netscape Bookmark File'));
	$GLOBALS['phpgw']->template->set_var('lang_import_button',lang('Import Bookmarks'));
	$GLOBALS['phpgw']->template->set_var('lang_note',lang('<b>Note:</b> This currently works with netscape bookmarks only'));
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
