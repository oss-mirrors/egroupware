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

	$debug = True;

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
     
		if (! $name)
		{
			return 0;
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
			}
			else
			{
				$phpgw->categories->add($name,0);
				$cat[$name] = $phpgw->categories->name2id($name);
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
		print ("<p><b>DEBUG OUTPUT:</b>\n");
		print ("<br>file: " . $bkfile . "\n");
		print ("<br>file_name: " . $bkfile_name . "\n");
		print ("<br>file_size: " . $bkfile_size . "\n");
		print ("<br>file_type: " . $bkfile_type . "\n<p><b>URLs:</b>\n");

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
						$cid  = 0;
						$scid = 0;
						$i    = 0;
						$keyw = '';
						while ($i <= $folder_index)
						{
							if ($i == 0)
							{
								$cid = getCategory($folder_name_stack[$i]);
							}
							elseif ($i == 1)
							{
								$scid = getSubCategory($folder_name_stack[$i]);
							}

							$keyw .= ' ' . $folder_name_stack[$i];
							$i++;
						}
						$values['category'] = sprintf('%s|%s',$cid,$scid);
   					$values['url']      = $match[1];
						$bid = -1;
						if (! $phpgw->bookmarks->add(&$bid, $values))
						{
							print("<br>" . $error_msg . "\n");
							$all_errors .= $error_msg;
						}

						printf("<br>%s,%s,%s,%s,<i>%s</i>\n",$cid,$scid,$match[2],$match[1],$bid);
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

	}
	else
	{
		$error_msg .= "<br>Unable to open temp file " . $bkfile . " for import.";
	}

	unset($msg);
	$msg .= sprintf("<br>%s bookmarks imported from %s successfully.", $inserts, $bkfile_name);
	if (! $debug)
	{
		print ("\n-->\n");
	}
	$error_msg = $all_errors;
//	break;

 }


  $phpgw->template->set_var('FORM_ACTION',$phpgw->link('/bookmarks/import.php'));
  $phpgw->common->phpgw_footer();
?>
