<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	// store the value of $mailbox, because it will overwriten
        $MAILBOX = $mailbox;
        
	$phpgw_info["flags"] = array("currentapp" => "felamimail","noheader" => True, "nonavbar" => True,
		"enable_nextmatchs_class" => True, "enable_network_class" => True);
		
	include("../header.inc.php");
	
	$mailbox = $MAILBOX;
	
	$phpgw->common->phpgw_header();
	echo parse_navbar();

	if ($totalerrors) 
	{
		echo "<p><center>" . $phpgw->common->error_list($errors) . "</center>";
	}

	include "src/load_prefs.php";

	$available = array
		(
			'1'	=> lang("Checkbox"),
			'2'	=> lang("From"),
			'3'	=> lang("Date"),
			'4'	=> lang("Subject"),
			'5'	=> lang("Flags"),
			'6'	=> lang("Size")
		);
	
	if (! isset($method)) { $method = ""; }
	
	if ($method == "up" && $num > 1) 
	{
		$prev = $num-1;
		$tmp = $index_order[$prev];
		$index_order[$prev] = $index_order[$num];
		$index_order[$num] = $tmp;
	} 
	else if ($method == "down" && $num < count($index_order)) 
	{
		$next = $num++;
		$tmp = $index_order[$next];
		$index_order[$next] = $index_order[$num];
		$index_order[$num] = $tmp;
	} 
	else if ($method == "remove" && $num) 
	{
		for ($i=1; $i < 8; $i++) 
		{
			$phpgw->preferences->delete("felamimail","order$i");
		}
		$phpgw->preferences->save_repository();
		for ($j=1,$i=1; $i <= count($index_order); $i++) 
		{
			if ($i != $num) 
			{
				$new_ary[$j] = $index_order[$i];
				$j++;
			}
		}
		$index_order = array();
		$index_order = $new_ary;
		if (count($index_order) < 1) 
		{
			include "src/load_prefs.php";
		}
	} 
	else if ($method == "add" && $add) 
	{
		$index_order[count($index_order)+1] = $add;
	}
	
	if ($method) 
	{
		for ($i=1; $i <= count($index_order); $i++) 
		{
			$phpgw->preferences->add("felamimail","order$i", $index_order[$i]);
		}
		$phpgw->preferences->save_repository();
	}
	
	$colors = Array
	(
		'0'	=> $phpgw_info['theme']['row_on'],
		'1'	=> $phpgw_info['theme']['row_off']
	);

	
	if (count($index_order))
	{
		for ($i=1; $i <= count($index_order); $i++) 
		{
			$tmp = $index_order[$i];
			$tableRows .= sprintf("<tr bgcolor=\"%s\">",$colors[$i%2]);
			$link = $phpgw->link('/felamimail/preferences_index_order.php',"method=up&num=$i");
			$tableRows .= "<td><small><a href=\"$link\">". lang("up") ."</a></small></td>\n";
			$tableRows .= "<td><small>&nbsp;|&nbsp;</small></td>\n";
			$link = $phpgw->link('/felamimail/preferences_index_order.php',"method=down&num=$i");
			$tableRows .= "<td><small><a href=\"$link\">". lang("down") . "</a></small></td>\n";
			$tableRows .= "<td><small>&nbsp;|&nbsp;</small></td>\n";
			$tableRows .= "<td>";
			// Always show the subject
			if ($tmp != 4)
			{
				$link = $phpgw->link('/felamimail/preferences_index_order.php',"method=remove&num=$i");
				$tableRows .= "<small><a href=\"$link\">" . lang("remove") . "</a></small>";
			}
			$tableRows .= "</td>\n";
			$tableRows .= "<td><small>&nbsp;-&nbsp;</small></td>\n";
			$tableRows .= "<td>" . $available[$tmp] . "</td>\n";
			$tableRows .= "</tr>\n";
		}
	}
	
	if (count($index_order) != count($available)) 
	{
		$options = "<select name=add>\n";
		for ($i=1; $i <= count($available); $i++) 
		{
			$found = false;
			for ($j=1; $j <= count($index_order); $j++) 
			{
				if ($index_order[$j] == $i) 
				{
					$found = true;
				}
			}
			if (!$found) 
			{
				$options .= "<option value=$i>$available[$i]</option>\n";
			}
		}
		$options .= "</select>\n";
		$options .= "<input type=hidden value=add name=method>\n";
		$options .= sprintf("<input type=submit value=\"%s\" name=\"submit\">\n",lang('add'));
	}
	
	$tmpl = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
	#$tmpl->set_unknowns('remove');

	$tmpl->set_file(array('body' => 'preferences_index_order.tpl'));
	
	$var = Array
	(
		'th_bg'			=> $phpgw_info["theme"]["th_bg"],
		'tr_color1'		=> $phpgw_info['theme']['row_on'],
		'tr_color2'		=> $phpgw_info['theme']['row_off'],
		'link'			=> $phpgw->link('/felamimail/preferences_index_order.php'),
		'link_back'		=> $phpgw->link('/preferences/index.php'),
		'tablerows'		=> $tableRows,
		'options'		=> $options
	);
	
	$tmpl->set_var($var);
	
	$translations = Array
	(
		'lang_return'		=> lang('Return to options page'),
		'lang_index_order'	=> lang('Index Order'),
		'lang_description'	=> lang("index_what_is")
	);
	$tmpl->set_var($translations);

	$tmpl->pparse('out','body');
	
	$phpgw->common->phpgw_footer(); 
?>
