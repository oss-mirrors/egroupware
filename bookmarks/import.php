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

	$phpgw_info['flags'] = array(
		'currentapp'               => 'bookmarks',
		'enable_categories_class' => True
	);
	include('../header.inc.php');
	$phpgw->bookmarks = createobject('bookmarks.bookmarks');

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
		global $phpgw, $phpgw_info, $cat, $catNext;

		$db = $phpgw->db;

		_debug('<br>Testing for category: ' . $name);
     
		if (! $name)
		{
			$name = 'No category';
		}

		if ($cat[$name])
		{
			return $cat[$upperName];
		}
		else
		{
			if ($phpgw->categories->exists('mains',$name))
			{
				$cat[$name] = $phpgw->categories->name2id($name);
				_debug('<br>' . $name . ' already exists - id: ' . $cat[$name]);
			}
			else
			{
				$phpgw->categories->add($name,0,'','','',0);
				$cat[$name] = $phpgw->categories->name2id($name);
				_debug('<br>' . $name . ' does not exist - new id: ' . $cat[$name]);
			}

			return $cat[$name];
		}
	}

  # find existing subcategory matching name, or
  # create a new one. return id.
  function getSubCategory ($name)
  {
/*     global $phpgw,$phpgw_info,$subcat,$subcatNext,$default_subcategory;

     $db = $phpgw->db;
     $upperName = strtoupper($name);
     
     if (! $name) {
        $subcat[$upperName] = $default_subcategory;
        return $default_subcategory;
     }

     if (isset($subcat[$upperName])) {
        return $subcat[$upperName];
     } else {
        $q  = "INSERT INTO bookmarks_subcategory (name, username) ";
        $q .= "VALUES ('" . addslashes($name) . "', '" . $phpgw_info["user"]["account_id"] . "') ";

        $db->query($q,__LINE__,__FILE__);
        if ($db->Errno != 0) {
           $error_msg .= "<br>Error adding subcategory ".$name." - ".$subcatNext;
           return -1;
        }
        
        $db->query("select id from bookmarks_subcategory where name='" . addslashes($name) . "' and username='"
                 . $phpgw_info["user"]["account_id"] . "'",__LINE__,__FILE__);
        $db->next_record();
        
        $subcat[$upperName] = $db->f("id");
        $subcatNext++;
        return $db->f("id");
     } */
  }


	$phpgw->template->set_file(array(
		'common'   => 'common.tpl',
		'body'     => 'import.body.tpl'
	));
	set_standard("import", &$phpgw->template);

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
			$error_msg .= "<br>Netscape bookmark filename is required!";
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
   
			while ($line = @fgets($fd, 2048))
			{
				// URLs are recognized by A HREF tags in the NS file.
				if (eregi('<A HREF="([^"]*)[^>]*>(.*)</A>', $line, $match))
				{
					$url_parts = @parse_url($match[1]);
					if ($url_parts[scheme] == 'http' || $url_parts[scheme] == 'https' || $url_parts[scheme] == 'ftp' || $url_parts[scheme] == 'news')
					{
   
						reset($folder_stack);
						unset($error_msg);
						$cid  = $phpgw->categories->name2id('No Category');
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
						$values['name']     = $match[2];
						$values['rating']   = 0;

						eregi('ADD_DATE="([^"]*)"',$line,$add_info);
						eregi('LAST_VISIT="([^"]*)"',$line,$vist_info);
						eregi('LAST_MODIFIED="([^"]*)"',$line,$change_info);

						$values['timestamps'] = sprintf('%s,%s,%s',$add_info[1],$vist_info[1],$change_info[1]);

						$bid = -1;
						if (! $phpgw->bookmarks->add(&$bid, $values))
						{
							print("<br>" . $error_msg . "\n");
							$all_errors .= $error_msg;
						}

						_debug(sprintf("<tr><td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td> </tr>",$cid,$scid,$match[2],$match[1],$add_info[1],$change_info[1],$vist_info[1]));
						if (! $error_msg)
						{
							$inserts++;
						}
					}
				}

				// folders start with the folder name inside an <H3> tag,
				// and end with the close </DL> tag.
				// we use a stack to keep track of where we are in the
				// folder hierarchy.
				else if (eregi('<H3[^>]*>(.*)</H3>', $line, $match))
				{
					$folder_index ++;
					$id = -1;

					if ($folder_index == 0)
					{
						$cat_index ++;
						$cat_array[$cat_index] = $match[1];
						$id = $cat_index + $cat_start;

					}
					elseif ($folder_index == 1)
					{
						$scat_index ++;
						$scat_array[$scat_index] = $match[1];
						$id = $scat_index + $scat_start;
					}
					$folder_stack[$folder_index] = $id;
					$folder_name_stack[$folder_index] = $match[1];

				}
				else if (eregi('</DL>', $line))
				{
					$folder_index-- ;
				}
		}
		@fclose($fd);
		_debug('</table>');
	}
	else
	{
		$error_msg .= "<br>Unable to open temp file " . $bkfile . " for import.";
	}

	unset($msg);
	$msg .= sprintf("<br>%s bookmarks imported from %s successfully.", $inserts, $bkfile_name);
	$error_msg = $all_errors;
//	break;

 }


  $phpgw->template->set_var('FORM_ACTION',$phpgw->link('/bookmarks/import.php'));
  $phpgw->common->phpgw_footer();
?>
