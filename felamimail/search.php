<?php

   /* $Id$ */

	$MAILBOX = $mailbox;
	$phpgw_info['flags'] = array(
		'currentapp'              => 'felamimail',
		'enable_network_class'    => True,
		'enable_nextmatchs_class' => True
	);
	include('../header.inc.php');
	$mailbox = $MAILBOX;

	if (!isset($strings_php))
	{
		include(PHPGW_APP_ROOT  . '/inc/strings.php');
	}

	if (!isset($i18n_php))
	{
		include(PHPGW_APP_ROOT  . '/inc/i18n.php');
	}

	if (!isset($config_php))
	{
		include(PHPGW_APP_ROOT  . '/config/config.php');
	}

	if (!isset($page_header_php))
	{
		include(PHPGW_APP_ROOT  . '/inc/page_header.php');
	}

	if (!isset($imap_php))
	{
		include(PHPGW_APP_ROOT  . '/inc/imap.php');
	}

	if (!isset($imap_search_php))
	{
		include(PHPGW_APP_ROOT  . '/inc/imap_search.php');
	}

	if (!isset($array_php))
	{
		include(PHPGW_APP_ROOT  . '/inc/array.php');
	}

   include(PHPGW_APP_ROOT  . '/src/load_prefs.php');

//mkorff@vpoint.com.br: first parm has been supressed and global parm boxes is needed
   //displayPageHeader($imapConnection, $color, $mailbox);
   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   $boxes = sqimap_mailbox_list($imapConnection);  
   displayPageHeader($color, $mailbox);

   do_hook("search_before_form");
   echo "<br>\n";
   echo "      <table width=95% align=center cellpadding=2 cellspacing=0 border=0>\n";
   echo "      <tr><td bgcolor=\"$color[0]\">\n";
   echo "          <center><b>".lang("Search")."</b></center>\n";
   echo "      </td></tr>\n";
   echo "      <tr><td align=center>";

   echo "<FORM ACTION=\"" . $phpgw->link('/felamimail/search.php') . "\" NAME=s method=\"POST\">\n";
   echo "   <TABLE WIDTH=75%>\n";
   echo "     <TR>\n";
   echo "       <TD WIDTH=33%>\n";
   echo "         <TT><SMALL><SELECT NAME=\"mailbox\">";

//mkorff@vpoint.com.br: already needed above in displayPageHeader
   //$boxes = sqimap_mailbox_list($imapConnection);
   for ($i = 0; $i < count($boxes); $i++) {
	  if (!in_array("noselect", $boxes[$i]["flags"])) {
         $box = $boxes[$i]["unformatted"];
         $box2 = replace_spaces($boxes[$i]["formatted"]);
         if ($mailbox == $box)
            echo "         <OPTION VALUE=\"$box\" SELECTED>$box2\n";
         else
            echo "         <OPTION VALUE=\"$box\">$box2\n";
      }
   }
   echo "         </SELECT></SMALL></TT>";
   echo "       </TD>\n";
   echo "        <TD ALIGN=\"CENTER\" WIDTH=33%>\n";
   if (!isset($what))
       $what = "";
   $what_disp = ereg_replace(",", " ", $what);
   $what_disp = str_replace("\\\\", "\\", $what_disp);
   $what_disp = str_replace("\\\"", "\"", $what_disp);
   $what_disp = str_replace("\"", "&quot;", $what_disp);
   echo "          <INPUT TYPE=\"TEXT\" SIZE=\"20\" NAME=\"what\" VALUE=\"$what_disp\">\n";
   echo "        </TD>";
   echo "       <TD ALIGN=\"RIGHT\" WIDTH=33%>\n";
   echo "         <SELECT NAME=\"where\">";
   
   if (isset($where) && $where == "BODY") echo "           <OPTION VALUE=\"BODY\" SELECTED>".lang("Body")."\n";
   else echo "           <OPTION VALUE=\"BODY\">".lang("Body")."\n";
   
   if (isset($where) && $where == "TEXT") echo "           <OPTION VALUE=\"TEXT\" SELECTED>".lang("Everywhere")."\n";
   else echo "           <OPTION VALUE=\"TEXT\">".lang("Everywhere")."\n";
   
   if (isset($where) && $where == "SUBJECT") echo "           <OPTION VALUE=\"SUBJECT\" SELECTED>".lang("Subject")."\n";
   else echo "           <OPTION VALUE=\"SUBJECT\">".lang("Subject")."\n";
   
   if (isset($where) && $where == "FROM") echo "           <OPTION VALUE=\"FROM\" SELECTED>".lang("From")."\n";
   else echo "           <OPTION VALUE=\"FROM\">".lang("From")."\n";
   
   if (isset($where) && $where == "CC") echo "           <OPTION VALUE=\"Cc\" SELECTED>".lang("Cc")."\n";
   else echo "           <OPTION VALUE=\"CC\">".lang("Cc")."\n";
   
   if (isset($where) && $where == "TO") echo "           <OPTION VALUE=\"TO\" SELECTED>".lang("To")."\n";
   else echo "           <OPTION VALUE=\"TO\">".lang("To")."\n";
   
   echo "         </SELECT>\n";
   echo "        </TD>\n";
   echo "       <TD COLSPAN=\"3\" ALIGN=\"CENTER\">\n";
   echo "         <INPUT TYPE=\"submit\" VALUE=\"".lang("Search")."\">\n";
   echo "       </TD>\n";
   echo "     </TR>\n";
   echo "   </TABLE>\n"; 
   echo "</FORM>";
   echo "</td></tr></table>";
   do_hook("search_after_form");
   if (isset($where) && $where && isset($what) && $what) {   
      sqimap_mailbox_select($imapConnection, $mailbox);
      sqimap_search($imapConnection, $where, $what, $mailbox, $color);
   }
   do_hook("search_bottom");
   sqimap_logout ($imapConnection);

	$phpgw->common->phpgw_footer();
?>
</body></html>
