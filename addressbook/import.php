<?php
  /**************************************************************************\
  * phpGroupWare - addressbook                                               *
  * http://www.phpgroupware.org                                              *
  * Written by Joseph Engo <jengo@phpgroupware.org>                          *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$phpgw_info['flags'] = array(
		'currentapp' => 'addressbook',
		'enable_contacts_class' => True,
		'enable_browser_class'  => True
	);
	include('../header.inc.php');

	if (!$convert)
	{
		$t = new Template(PHPGW_APP_TPL);
		$t->set_file(array('import' => 'import.tpl'));

		$dir_handle=opendir(PHPGW_APP_ROOT . SEP . 'import');
		$i=0; $myfilearray='';
		while ($file = readdir($dir_handle))
		{
			//echo "<!-- ".is_file($phpgw_info["server"]["app_root"].$sep."import".$sep.$file)." -->";
			if ((substr($file, 0, 1) != '.') && is_file(PHPGW_APP_ROOT . SEP . 'import' . SEP . $file) )
			{
				$myfilearray[$i] = $file;
				$i++;
			}
		}
		closedir($dir_handle);
		sort($myfilearray);
		for ($i=0;$i<count($myfilearray);$i++)
		{
			$fname = ereg_replace('_',' ',$myfilearray[$i]);
			$conv .= '<OPTION VALUE="' . $myfilearray[$i].'">' . $fname . '</OPTION>';
		}

		$t->set_var('lang_cancel',lang('Cancel'));
		$t->set_var('lang_cat',lang('Select Category'));
		$t->set_var('cancel_url',$phpgw->link('/addressbook/index.php'));
		$t->set_var('navbar_bg',$phpgw_info['theme']['navbar_bg']);
		$t->set_var('navbar_text',$phpgw_info['theme']['navbar_text']);
		$t->set_var('import_text',lang('Import from LDIF, CSV, or VCard'));
		$t->set_var('action_url',$phpgw->link('/addressbook/import.php'));
		$t->set_var('cat_link',cat_option($cat_id,True,False));
		$t->set_var('tsvfilename','');
		$t->set_var('conv',$conv);
		$t->set_var('debug',lang('Debug output in browser'));
		$t->set_var('filetype',lang('LDIF'));
		$t->set_var('download',lang('Submit'));
		$t->set_var('start',$start);
		$t->set_var('sort',$sort);
		$t->set_var('order',$order);
		$t->set_var('filter',$filter);
		$t->set_var('query',$query);
		$t->set_var('cat_id',$cat_id);
		$t->pparse('out','import');
		$phpgw->common->phpgw_footer();
	}
	else
	{
		include (PHPGW_APP_ROOT . SEP. 'import' . SEP . $conv_type);

		if ($private == '') { $private = 'public'; }
		$row=0;
		$buffer=array();
		$contacts = new import_conv;
		$buffer = $contacts->import_start_file($buffer);
		$fp=fopen($tsvfile,'r');
		if ($contacts->type == 'csv')
		{
			while ($data = fgetcsv($fp,8000,','))
			{
				$num = count($data);
				$row++;
				if ($row == 1)
				{
					// Changed here to ignore the header, set to our array
					while(list($lhs,$rhs) = each($contacts->import))
					{
						$header[] = $lhs;
					}
				}
				else
				{
					$buffer = $contacts->import_start_record($buffer);
					for ($c=0; $c<$num; $c++ )
					{
						//Send name/value pairs along with the buffer
						if ($contacts->import[$header[$c]] != '' && $data[$c] != '')
						{
							$buffer = $contacts->import_new_attrib($buffer, $contacts->import[$header[$c]],$data[$c]);
						}
					}
					$buffer = $contacts->import_end_record($buffer,$private);
				}
			}
		}
		elseif ($contacts->type == 'ldif')
		{
			while ($data = fgets($fp,8000))
			{
				$url = "";
				list($name,$value,$extra) = split(':', $data);
				if (substr($name,0,2) == 'dn')
				{
					$buffer = $contacts->import_start_record($buffer);
				}
				
				$test = trim($value);
				if ($name && !empty($test) && $extra)
				{
					// Probable url string
					$url = $test;
					$value = $extra;
				}
				elseif ($name && empty($test) && $extra)
				{
					// Probable multiline encoding
					$newval = base64_decode(trim($extra));
					$value = $newval;
					//echo $name.':'.$value;
				}
				
				if ($name && $value)
				{
					$test = split(',mail=',$value);
					if ($test[1])
					{
						$name = "mail";
						$value = $test[1];
					}
					if ($url)
					{
						$name = "homeurl";
						$value = $url. ':' . $value;
					}
					//echo '<br>'.$j.': '.$name.' => '.$value;
					if ($contacts->import[$name] != '' && $value != '')
					{
						$buffer = $contacts->import_new_attrib($buffer, $contacts->import[$name],$value);
					}
				}
				else
				{
					$buffer = $contacts->import_end_record($buffer,$private);
				}
			}
		}
		else
		{
			while ($data = fgets($fp,8000))
			{
				$data = trim($data);											// RB 2001/05/07 added for Lotus Organizer
				while (substr($data,-1) == '=') {						// '=' at end-of-line --> line to be continued with next line
					$data = substr($data,0,-1) . trim(fgets($fp,8000));
				}
				if (strstr($data,';ENCODING=QUOTED-PRINTABLE')) {	// RB 2001/05/07 added for Lotus Organizer
					$data = quoted_printable_decode(str_replace(';ENCODING=QUOTED-PRINTABLE','',$data));
				}								
				list($name,$value) = explode(':', $data,2); 			// RB 2001/05/09 to allow ':' in Values (not only in URL's)

				if (strtolower(substr($name,0,5)) == 'begin')
				{
					$buffer = $contacts->import_start_record($buffer);
				}
				if ($name && $value)
				{
					reset($contacts->import);
					while ( list($fname,$fvalue) = each($contacts->import) )
					{
						if ( strstr(strtolower($name), $contacts->import[$fname]) )
						{
							$buffer = $contacts->import_new_attrib($buffer,$name,$value);
						}
					}
				}
				else
				{
					$buffer = $contacts->import_end_record($buffer);
				}
			}
		}

		fclose($fp);
		$buffer = $contacts->import_end_file($buffer,$private,$cat_id);

		if ($download == '')
		{
			if($conv_type == 'Debug LDAP' || $conv_type == 'Debug SQL' )
			{
				// filename, default application/octet-stream, length of file, default nocache True
				$phpgw->browser->content_header($tsvfilename,'',strlen($buffer));
				echo $buffer;
			}
			else
			{
				echo "<pre>$buffer</pre>";
				echo '<a href="'.$phpgw->link('/addressbook/index.php',
					"sort=$sort&order=$order&filter=$filter&start=$start&query=$query&cat_id=$cat_id")
					. '">'.lang("OK").'</a>';
				$phpgw->common->phpgw_footer();
			}
		}
		else
		{
			echo "<pre>$buffer</pre>";
			echo '<a href="'.$phpgw->link('/addressbook/index.php',
				"sort=$sort&order=$order&filter=$filter&start=$start&query=$query&cat_id=$cat_id")
				. '">'.lang("OK").'</a>';
			$phpgw->common->phpgw_footer();
		}
	}
?>
