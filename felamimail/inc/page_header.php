<?php
   /**
    **  page_header.php
    **
    **  Prints the page header (duh)
    **
    **  $Id$
    **/

	$page_header_php = true;

	if (!isset($prefs_php))
	{
		include(PHPGW_APP_ROOT . '/inc/prefs.php');
	}

	if (!isset($i18n_php))
	{
		include(PHPGW_APP_ROOT . '/inc/i18n.php');
	}

	if (!isset($plugin_php))
	{
		include(PHPGW_APP_ROOT . '/inc/plugin.php');
	}

	// Check to see if gettext is installed
	set_up_language(getPref($data_dir, $username, "language"));
	// This is done to ensure that the character set is correct.
	
	function displayHtmlHeader ($title="SquirrelMail") 
	{
		global $theme_css;

		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
		echo "\n\n";
		echo "<HTML>\n";
		echo "<HEAD>\n";
		
		if ($theme_css != "") 
		{
			printf ('<LINK REL="stylesheet" TYPE="text/css" HREF="%s">',$theme_css);
			echo "\n";
		}
		
		do_hook ("generic_header");
		
		echo "<TITLE>$title</TITLE>\n";
		echo "</HEAD>\n\n";
	}

	function displayInternalLink ($path, $extra_vars, $text, $target="") 
	{
		global $phpgw, $phpgw_info;
		
		if ($target != "")
			$target = " target=\"$target\"";
			
		echo '<a href="'. $phpgw->link('/felamimail/' . $path,$extra_vars) . '"'.$target.'>'.$text.'</a>';
	}
	
	function displayPageHeader($color, $mailbox) 
	{
		global $phpgw, $boxes;
		global $imapConnection;

		#displayHtmlHeader ();

		#printf('<BODY TEXT="%s" BGCOLOR="%s" LINK="%s" VLINK="%s" ALINK="%s">',
		#	$color[8], $color[4], $color[7], $color[7], $color[7]);
		#echo "\n\n";
	
		/** Here is the header and wrapping table **/
		$shortBoxName = readShortMailboxName($mailbox, ".");
		$shortBoxName = sqStripSlashes($shortBoxName);
	
		echo "<A NAME=pagetop></A>\n";
		echo "<TABLE BGCOLOR=\"$color[4]\" BORDER=0 WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2>\n";
		echo "   <TR BGCOLOR=\"$color[9]\">\n";
		echo "      <TD ALIGN=left WIDTH=\"30%\">\n";
	
		$urlMailbox = urlencode($mailbox);
		#displayInternalLink ("compose.php","mailbox=$urlMailbox", lang("Compose"));
		#print "(old)";
		$linkData = array
		(
			'menuaction'	=> 'felamimail.uicompose.compose',
			'mailbox'	=> $urlMailbox
		);
		printf("&nbsp;<a href=\"%s\">%s</a>",$phpgw->link('/index.php',$linkData),lang("Compose"));
		
		echo "&nbsp;&nbsp;\n";
		//mkorff@vpoint.com.br: enabled with changes in move_messages
		displayInternalLink ("search.php","mailbox=$urlMailbox", lang("Search"));
		echo "&nbsp;&nbsp;\n";
#		displayInternalLink ("src/help.php","", lang("Help"));
		echo "&nbsp;&nbsp;\n";
		echo "      </b></TD>\n";
	
		// Folder list
		echo "\n\n\n<FORM name=folderList method=post action=\"".$phpgw->link("/felamimail/index.php")."\">\n";
		echo "      <TD WIDTH=40% ALIGN=RIGHT VALIGN=CENTER>\n";
		echo '         <TT><SELECT NAME="mailbox" onChange="document.folderList.submit()">'."\n";
	
//mkorff@vpoint.com.br: there are cases where boxes is not an array
		if (!is_array($boxes)) {
			if (!isset($imapConnection))  {
				if (!isset($config_php))
				{
					include(PHPGW_APP_ROOT . '/config/config.php');
				}
				$key      = $phpgw_info['user']['preferences']['email']['passwd'];
				$username = $phpgw_info['user']['preferences']['email']['userid'];

				$imapConnection = sqimap_login($username, $key, 
					$imapServerAddress, $imapPort, 0);
			}
			$boxes = sqimap_mailbox_list($imapConnection);
			$phpgw->session->register("boxes");
		}
		reset($boxes);
		for ($i = 0; $i < count($boxes); $i++) 
		{
			if (!in_array("noselect", $boxes[$i]["flags"])) 
			{
				$box = $boxes[$i]['unformatted'];
				$box2 = replace_spaces($boxes[$i]['unformatted-disp']);
				echo "         <OPTION VALUE=\"$box\"";
				if($box==$mailbox) 
				{ 
					echo "selected"; 
				}
				echo ">$box2</option>\n";
			}
		}
		echo '         </SELECT></TT>'."\n";
		echo '         <INPUT TYPE=HIDDEN NAME="startMessage" VALUE="1">'."\n";
#		echo '         <INPUT TYPE=HIDDEN NAME="newsort" VALUE="0">'."\n";
		echo '         <noscript>';
		echo '         <SMALL><INPUT TYPE=SUBMIT NAME="GoButton" VALUE="'. lang("Select") ."\"></SMALL></NOBR>\n";
		echo '         </noscript>';
		echo "      </TD>\n";
		echo "</FORM>\n";
		
		echo "   </TR>\n";
		echo "</TABLE>\n\n";

#		echo "<TABLE BGCOLOR=\"$color[4]\" BORDER=0 WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2>\n";
#		echo "   <TR>\n";
#		echo "      <TD ALIGN=left WIDTH=\"99%\">\n";
#
#		do_hook("menuline");
#
#		echo "      </TD><TD ALIGN=right nowrap WIDTH=\"1%\">\n";
#		echo "         <A HREF=\"http://www.felamimail.org/\" TARGET=\"_top\">SquirrelMail</A>\n";
#		echo "      </TD>\n";
#		echo "   </TR>\n";
#		echo "</TABLE>\n\n";
	}
?>
