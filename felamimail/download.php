<?php
   /**
    **  download.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Handles attachment downloads to the users computer.
    **  Also allows displaying of attachments when possible.
    **
    **  $Id$
    **/
#   header("Pragma: ");
#   header("Cache-Control: cache");

	// store the value of $mailbox, because it will overwriten
	$phpgw_info["flags"] = array(
		'noheader'    => 'True',
		'nonavbar'    => 'True',
		"currentapp" => "felamimail"
	);
	include("../header.inc.php");
	$mailbox = $GLOBALS['HTTP_GET_VARS']['mailbox'];
	$passed_id = $GLOBALS['HTTP_GET_VARS']['passed_id'];
	$passed_ent_id = $GLOBALS['HTTP_GET_VARS']['passed_ent_id'];

	$phpgw->session->restore();


   if (!isset($strings_php))
      include(PHPGW_APP_ROOT . '/inc/strings.php');
   if (!isset($config_php))
      include(PHPGW_APP_ROOT . '/config/config.php');
   if (!isset($imap_php))
      include("./inc/imap.php");
   if (!isset($mime_php))
      include("./inc/mime.php");
   if (!isset($date_php))
      include("./inc/date.php");
   if (!isset($i18n_php))
      include("./inc/i18n.php");


   include("./src/load_prefs.php");

   function viewText($color, $body, $id, $entid, $mailbox, $type1, $wrap_at) {
      global $where, $what, $charset, $phpgw;
      global $startMessage, $imapConnection;
      
      //displayPageHeader($imapConnection, $color, "None");
      displayPageHeader($color, "None");

      echo "<BR><TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=2 ALIGN=CENTER><TR><TD BGCOLOR=\"$color[0]\">";
      echo "<B><CENTER>";
      echo lang("Viewing a text attachment") . " - ";
      if ($where && $what) {
         // from a search
         echo "<a href=\"".$phpgw->link('/felamimail/read_body.php',"mailbox=".urlencode($mailbox)."&passed_id=$id&where=".urlencode($where)."&what=".urlencode($what))."\">". lang("View message") . "</a>";
      } else {   
         echo "<a href=\"".$phpgw->link('/felamimail/read_body.php',"mailbox=".urlencode($mailbox)."&passed_id=$id&startMessage=$startMessage&show_more=0")."\">". lang("View message") . "</a>";
      }   

      $urlmailbox = urlencode($mailbox);
      echo "</b></td><tr><tr><td><CENTER><A HREF=\"".$phpgw->link('/felamimail/download.php',"absolute_dl=true&passed_id=$id&passed_ent_id=$entid&mailbox=$urlmailbox")."\">";
      echo lang("Download this as a file");
      echo "</A></CENTER><BR>";
      echo "</CENTER></B>";
      echo "</TD></TR></TABLE>";

      echo "<TABLE WIDTH=98% BORDER=0 CELLSPACING=0 CELLPADDING=2 ALIGN=CENTER><TR><TD BGCOLOR=\"$color[0]\">";
      echo "<TR><TD BGCOLOR=\"$color[4]\"><TT>";

      if ($type1 != "html")
         translateText($body, $wrap_at, $charset);
      
      // this will help netscape linux
      $body = eregi_replace('base href','base hreff',$body);
	
      echo $body;

      echo "</TT></TD></TR></TABLE>";
   }

   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   sqimap_mailbox_select($imapConnection, $mailbox);

   // $message contains all information about the message
   // including header and body
   $message = sqimap_get_message($imapConnection, $passed_id, $mailbox);
   $top_header = $message->header;

   // lets redefine message as this particular entity that we wish to display.
   // it should hold only the header for this entity.  We need to fetch the body
   // yet before we can display anything.
   $message = getEntity($message, $passed_ent_id);

   $header = $message->header;

   $charset = $header->charset;
   $type0 = $header->type0;
   $type1 = $header->type1;
   $filename = decodeHeader($header->filename);

   if (strlen($filename) < 1) {
      if ($type1 == "plain" && $type0 == "text")                  $suffix = "txt";
      else if ($type1 == "richtext" && $type0 == "text")          $suffix = "rtf";
      else if ($type1 == "postscript" && $type0 == "application") $suffix = "ps";
      else if ($type1 == "message" && $type0 == "rfc822")         $suffix = "msg";
      else $suffix = $type1;

      $filename = "untitled$passed_ent_id.$suffix";
   }

   // Note:
   //    The following sections display the attachment in different
   //    ways depending on how they choose.  The first way will download
   //    under any circumstance.  This sets the Content-type to be
   //    applicatin/octet-stream, which should be interpreted by the
   //    browser as "download me".
   //      The second method (view) is used for images or other formats
   //    that should be able to be handled by the browser.  It will
   //    most likely display the attachment inline inside the browser.
   //      And finally, the third one will be used by default.  If it
   //    is displayable (text or html), it will load them up in a text
   //    viewer (built in to felamimail).  Otherwise, it sets the
   //    content-type as application/octet-stream
   
   if (isset($absolute_dl) && $absolute_dl == "true") {
      switch($type0) {
         case "text":
            $body = mime_fetch_body($imapConnection, $passed_id, $passed_ent_id);
            $body = decodeBody($body, $header->encoding);
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-type: application/octet-stream; name=\"$filename\"");
            set_up_language(getPref($data_dir, $username, "language"));
            if ($type1 == "plain") {
               echo lang("Subject") . ": " . decodeHeader(sqStripSlashes($top_header->subject)) . "\n";
               echo "   " . lang("From") . ": " . decodeHeader(sqStripSlashes($top_header->from)) . "\n";
               echo "     " . lang("To") . ": " . decodeHeader(sqStripSlashes(getLineOfAddrs($top_header->to))) . "\n";
               echo "   " . lang("Date") . ": " . getLongDateString($top_header->date) . "\n\n";
            }
            echo trim($body);
            break;
         default:
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-type: application/octet-stream; name=\"$filename\"");
            mime_print_body_lines ($imapConnection, $passed_id, $passed_ent_id, $header->encoding);
            break;
      }
   } else {
      switch ($type0) {
         case "text":
            if ($type1 == "plain" || $type1 == "html") {
		$phpgw->common->phpgw_header();
		echo parse_navbar();
                $body = mime_fetch_body($imapConnection, $passed_id, $passed_ent_id);
                $body = decodeBody($body, $header->encoding);
                include(PHPGW_APP_ROOT . "/inc/page_header.php");
                viewText($color, $body, $passed_id, $passed_ent_id, $mailbox, $type1, $wrap_at);
            } else {
                $body = mime_fetch_body($imapConnection, $passed_id, $passed_ent_id);
                $body = decodeBody($body, $header->encoding);
                header("Content-Type: $type0/$type1");
                header("Content-Disposition: filename=\"$filename\"");
                echo $body;
            }
            break;
         case "message":
            $phpgw->common->phpgw_header();
	    echo parse_navbar();
            $body = mime_fetch_body($imapConnection, $passed_id, $passed_ent_id);
            $body = decodeBody($body, $header->encoding);
            include(PHPGW_APP_ROOT . '/inc/page_header.php');
            viewText($color, $body, $passed_id, $passed_ent_id, $mailbox, $type1, $wrap_at);
            break;
         default:
            header("Content-Type: $type0/$type1");
            header("Content-Disposition: filename=\"$filename\"");
	    mime_print_body_lines ($imapConnection, $passed_id, $passed_ent_id, $header->encoding);
            break;
      }
   }    
    
   sqimap_logout($imapConnection);
	$phpgw->session->save();
	// vcard doesn't need a footer
	if ($type1 != 'x-vcard') {
//mkorff@vpoint.com.br: disabled; the typical application/octet-stream doesn't want this
		//$phpgw->common->phpgw_footer();
	}


?>
