<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

  $d1 = strtolower(substr($phpgw_info["server"]["app_inc"],0,3));
  if($d1 == "htt" || $d1 == "ftp" ) {
    echo "Failed attempt to break in via an old Security Hole!<br>\n";
    $phpgw->common->phpgw_exit();
  } unset($d1);

  if(floor(phpversion()) == 4)
  {
  	global $phpgw, $phpgw_info, $PHP_SELF;  // This was a problem for me.
  }

	$phpgw_info['user']['preferences'] = $phpgw->common->create_emailpreferences($phpgw_info['user']['preferences']);

  $phpgw->msg = CreateObject("email.msg");
  $phpgw->msg->msg_common_();

  /*Set some defults*/
  if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "UWash" &&
      $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" && !$folder) {
// Changed by skeeter 04 Jan 01
// This was changed to give me access back to my folders.
// Not sure what it would break if the user has a default folder preference set,
// but will allow access to other folders now.
//      $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") {
      $phpgw_info["user"]["preferences"]["email"]["folder"] = (!$phpgw_info["user"]["preferences"]["email"]["folder"] ? "INBOX" : $phpgw_info["user"]["preferences"]["email"]["folder"]);
//backward compatibility
      $folder = $phpgw_info["user"]["preferences"]["email"]["folder"];
  }

  if(!$folder) $folder="INBOX";
  
      //echo "<b>TEST:</b> ".$phpgw_info["user"]["preferences"]["email"]["folder"];

  // Its better then them using a ton of PHP errors.
  // Changed by Milosch on 3-26-2001 - This check was not working, and the code progressed to giving stream pointer errors
  // From the msg_imap class.  I tried to clean it up here so I could see what was happening.
  if (!$PHP_SELF) global $PHP_SELF;  // This was a problem for me.
  $attop   = ereg($phpgw_info['server']['webserver_url'] . '/index.php',$PHP_SELF);
  $inprefs = ereg("preferences",$PHP_SELF);

  if (!$inprefs) $mailbox = $phpgw->msg->login($folder); // Changed this to not try connection in prefs

  if (!$mailbox && !($attop || $inprefs)) {
     echo "<p><center><b>" . lang("There was an error trying to connect to your mail server.<br>Please, check your username and password, or contact your admin.")
        . "</b></center>";
     $phpgw->common->phpgw_exit(True);
  }

  function decode_header_string($string) {
    global $phpgw;

    if($string) {
      $pos = strpos($string,"=?");
      if(!is_int($pos)) { return $string; }
      $preceding = substr($string,0,$pos); // save any preceding text
      $end = strlen($string);
      $search = substr($string,$pos+2,$end - $pos - 2 ); // the mime header spec says this is the longest a single encoded word can be
      $d1 = strpos($search,"?");
      if(!is_int($d1)) { return $string; }
      $charset = strtolower(substr($string,$pos+2,$d1));
      $search = substr($search,$d1+1);
      $d2 = strpos($search,"?");
      if(!is_int($d2)) { return $string; }
      $encoding = substr($search,0,$d2);
      $search = substr($search,$d2+1);
      $end = strpos($search,"?=");
      if(!is_int($end)) { return $string; }
      $encoded_text = substr($search,0,$end);
      $rest = substr($string,(strlen($preceding.$charset.$encoding.$encoded_text)+6));
      if(strtoupper($encoding) == "Q") {
	      $decoded = $phpgw->msg->qprint(str_replace("_"," ",$encoded_text));
      }
      if (strtoupper($encoding) == "B") {
        $decoded = urldecode(base64_decode($encoded_text));
      }
      return $preceding . $decoded . decode_header_string($rest);
    } else return $string;
  }

 /* * * * * * * * * * *
  *  list_folders: new param:  $echo_out
  * $echo_out  = True   means the function will echo its output
  * $echo_out  = False  means the function will return a string instead
  * defaults to True to avoid breaking any calling code which expects echoed output
  *  However, returning a string was necessary for templating index.php
  * * * * * * *  * * * */
  function list_folders($mailbox,$folder="",$echo_out=True)
  {
	global $phpgw, $phpgw_info;
	// UWash patched for Maildir style: $Maildir.Junque
	// Cyrus style: INBOX.Junque
	// UWash style: ./aeromail/Junque

	$outstr = '';
	if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"])
	{
		while($pref = each($phpgw_info["user"]["preferences"]["nntp"]))
		{
			$phpgw->db->query("SELECT name FROM newsgroups WHERE con=".$pref[0]);
			while($phpgw->db->next_record())
			{
				$outstr = $outstr .'<option value="' . urlencode($phpgw->db->f("name")) . '">' . $phpgw->db->f("name")
				  . '</option>';
			}
		}
	}
	else
	{
		if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "UW-Maildir")
		{
			$stdoffset = 1;  // Used below to setup $nm
			if ( isset($phpgw_info["user"]["preferences"]["email"]["mail_folder"]) )
			{
				if ( empty($phpgw_info["user"]["preferences"]["email"]["mail_folder"]) )
				{
					$filter = "";
				}
				else
				{
					$filter = $phpgw_info["user"]["preferences"]["email"]["mail_folder"];
				}
			}
		}
		elseif ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus")
		{
			$filter = "INBOX";
			$stdoffset = 1;
		}
		else
		{
			$filter = "mail/";
			$stdoffset = 1;
		}

		if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap")
		{  /* Normal IMAP: */
			$mailboxes = $phpgw->msg->listmailbox($mailbox,"{".$phpgw_info["user"]["preferences"]["email"]["mail_server"]
			.":".$phpgw_info["user"]["preferences"]["email"]["mail_port"]."}",$filter."*");
		}
		elseif ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "pop3s")
		{  /* POP3 over SSL: */
			$mailboxes = $phpgw->msg->listmailbox($mailbox,"{".$phpgw_info["user"]["preferences"]["email"]["mail_server"]
			."/pop3/ssl/novalidate-cert:995}",$filter."*");
		}
		else
		{  /* IMAP over SSL: */
			$mailboxes = $phpgw->msg->listmailbox($mailbox,"{".$phpgw_info["user"]["preferences"]["email"]["mail_server"]
			."/ssl/novalidate-cert:993}",$filter."*");
		}

		if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] != "pop3")
		if (gettype($mailboxes) == "array")
		{
			sort($mailboxes); // added sort for folder names 
		}
		if($mailboxes)
		{
			$num_boxes = count($mailboxes);
			if ($filter != "INBOX")
			{
				$outstr = $outstr .'<option value="INBOX">INBOX</option>'; 
	        	}
			for ($index = 0; $index < $num_boxes; $index++)
			{
				$nm = substr($mailboxes[$index], strrpos($mailboxes[$index], "}") + $stdoffset, strlen($mailboxes[$index]));
				$outstr = $outstr .'<option value="';
				if ($nm != "INBOX")
				{
					$foldername = $phpgw->msg->deconstruct_folder_str($nm);
				}
				else
				{
					$foldername = "INBOX";
				}
				if ($foldername == $folder)
				{
					$sel = " selected";
				}
				else
				{
					$sel = "";
				}
				$outstr = $outstr .urlencode($foldername) . '"'.$sel.'>' . $foldername . '</option>';
				$outstr = $outstr ."\n";
			}
		}
		else
		{
			$outstr = $outstr .'<option value="INBOX">INBOX</option>';
		}
	}
	// do you echo out or return a string
	if ($echo_out)
	{
		echo $outstr;
	}
	else
	{
		return $outstr;
	}
  }

  function get_mime_type($de_part) {
    $mime_type = "unknown";
    if (isset($de_part->type) && $de_part->type) {
      switch ($de_part->type) {
	      case TYPETEXT:		$mime_type = "text"; break;
	      case TYPEMESSAGE:	$mime_type = "message"; break;
      	case TYPEAPPLICATION:	$mime_type = "application"; break;
      	case TYPEAUDIO:		$mime_type = "audio"; break;
      	case TYPEIMAGE:		$mime_type = "image"; break;
      	case TYPEVIDEO:		$mime_type = "video"; break;
      	case TYPEMODEL:		$mime_type = "model"; break;
      	default:		$mime_type = "unknown";
      }
    }
    return $mime_type;
  }

  function get_mime_encoding($de_part) {
    $mime_encoding = "other";
    if (isset($de_part->encoding) && $de_part->encoding) {
      switch ($de_part->encoding) {
      	case ENCBASE64:			$mime_encoding = "base64"; break;
      	case ENCQUOTEDPRINTABLE:	$mime_encoding = "qprint"; break;
      	case ENCOTHER:			$mime_encoding = "other";  break;
      	default:			$mime_encoding = "other";
      }
    }
    return $mime_encoding;
  }

  function get_att_name($de_part)
  {
    $att_name = "Unknown";
    if ($de_part->ifparameters) {
      for ($i = 0; $i < count($de_part->parameters); $i++) 
      {
        $param = $de_part->parameters[$i];
        if (strtoupper($param->attribute) == "NAME") {
          $att_name = $param->value;
        }
      }
    }
    return $att_name;
  }

  function attach_display($de_part, $part_no)
  {
    global $msgnum, $phpgw, $phpgw_info, $folder;
    $mime_type = get_mime_type($de_part);  
    $mime_encoding = get_mime_encoding($de_part);

    $att_name = "unknown";

    for ($i = 0; $i < count($de_part->parameters); $i++)
    {
      $param = $de_part->parameters[$i];
      if (strtoupper($param->attribute) == "NAME")
      {
        $att_name = $param->value;
	      $url_att_name = urlencode($att_name);
	      $att_name = decode_header_string($att_name);
      }
    }

//    $jnk = "<a href=\"".$phpgw->link("get_attach.php","folder=".$phpgw_info["user"]["preferences"]["email"]["folder"]
    $jnk = "<a href=\"".$phpgw->link("/".$phpgw_info['flags']['currentapp']."/get_attach.php","folder=".$folder
		       ."&msgnum=$msgnum&part_no=$part_no&type=$mime_type"
		       ."&subtype=".$de_part->subtype."&name=$url_att_name"
		       ."&encoding=$mime_encoding")."\">$att_name</a>";
    return $jnk;
  }

  function inline_display($de_part, $part_no)
  {
    global $mailbox, $msgnum, $phpgw, $phpgw_info;
    $mime_type = get_mime_type($de_part);
    $mime_encoding = get_mime_encoding($de_part);

    $dsp = $phpgw->msg->fetchbody($mailbox, $msgnum, $part_no);

    $tag = "pre";
    $jnk = $de_part->ifdisposition ? $de_part->disposition : "unknown";
    if ($mime_encoding == "qprint")
    {
      $dsp = $phpgw->msg->qprint($dsp);
      $tag = "tt";
    }

    // Thanks to Omer Uner Guclu <oquclu@superonline.com> for figuring out
    // a better way to do message wrapping

    if (strtoupper($de_part->subtype) == "PLAIN")
    {
      // nlbr and htmlentities functions are strip latin5 characters
      if (strtoupper(lang("charset")) <> "BIG5")
         $dsp = $phpgw->strip_html($dsp);
      $dsp = ereg_replace( "^","<p>",$dsp);
      $dsp = ereg_replace( "\n","<br>",$dsp);
      $dsp = ereg_replace( "$","</p>", $dsp);
      $dsp = make_clickable($dsp);
      echo "<table border=\"0\" align=\"left\" cellpadding=\"10\" width=\"80%\">"
           ."<tr><td>$dsp</td></tr></table>";
    } else if (strtoupper($de_part->subtype) == "HTML") {
      output_bound(lang("section").":" , "$mime_type/".strtolower($de_part->subtype));
      echo $dsp;
    } else {
      output_bound(lang("section").":" , "$mime_type/".strtolower($de_part->subtype));
      echo "<$tag>$dsp</$tag>\n";
    }
  }

  function output_bound($title, $str)
  {
    global $phpgw_info;
    echo "</td></tr></table>\n"
      . "<table border=\"0\" cellpadding=\"4\" cellspacing=\"3\" "
      . "width=\"700\">\n<tr><td bgcolor\"" . $phpgw_info["theme"]["th_bg"] . "\" " 
      . "valign=\"top\"><font size=\"2\" face=\"" . $phpgw_info["theme"]["font"] . "\">"
      . "<b>$title</b></td>\n<td bgcolor=\"" . $phpgw_info["theme"]["row_on"] . "\" "
      . "width=\"570\"><font size=\"2\" face=\"" . $phpgw_info["theme"]["font"] . "\">"
      . "$str</td></tr></table>\n<p>\n<table border=\"0\" cellpadding=\"2\" "
      . "cellspacing=\"0\" width=\"100%\"><tr><td>";
  }

  function image_display($folder, $msgnum, $de_part, $part_no, $att_name)  {
    global $phpgw;
    global $phpgw_info;

    output_bound(lang("image").":" , $att_name);
    $extra_parms = "folder=".urlencode($folder)."&m=".$msgnum
		 . "&p=".$part_no."&s=".strtolower($de_part->subtype)."&n=".$att_name;
    if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"]) 
      $extra_parms .= "&newsmode=on";
    $view_link = $phpgw->link("/".$phpgw_info['flags']['currentapp']."/view_image.php",$extra_parms);
    echo "\n<img src=\"".$view_link."\">\n<p>\n";
  }

  // function make_clickable taken from text_to_links() in the SourceForge Snipplet Library
  // http://sourceforge.net/snippet/detail.php?type=snippet&id=100004
  // modified to make mailto: addresses compose in phpGW
  function make_clickable($data)
  {
    global $phpgw,$phpgw_info;

    if(empty($data))
    {
      return $data;
    }

    $lines = split("\n",$data);

    while ( list ($key,$line) = each ($lines))
    {

      $line = eregi_replace("([ \t]|^)www\."," http://www.",$line);
      $line = eregi_replace("([ \t]|^)ftp\."," ftp://ftp.",$line);
      $line = eregi_replace("(http://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
      $line = eregi_replace("(https://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
      $line = eregi_replace("(ftp://[^ )\r\n]+)","<A href=\"\\1\" target=\"_new\">\\1</A>",$line);
      $line = eregi_replace("([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+))",
               "<a href=\"".$phpgw->link("/".$phpgw_info['flags']['currentapp']."/compose.php","folder=".urlencode($phpgw_info["user"]["preferences"]["email"]["folder"]))
               ."&to=\\1\">\\1</a>", $line);

      $newText .= $line . "\n";

    }

    return $newText;
  }
?>
