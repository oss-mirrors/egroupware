<?php

  /* $Id$ */

  if ($phpgw_info["server"]["imap_server_type"] == "UWash" &&
      $phpgw_info["server"]["mail_server_type"] == "imap") {
     $folder = (!$folder ? "INBOX" : $folder);
  }
  $mailbox = $phpgw->msg->login($folder);
  
  function decode_header_string($hed_str)
  {
    global $phpgw;
    $output = "Empty String";
    if($hed_str)
    {
      if(substr($hed_str, 0, 2) == "=?")
      {
	      $start_pos = strpos($hed_str, "?", 2);
	      $type = substr($hed_str, $start_pos + 1, 1);
	      $newstr = substr($hed_str, $start_pos + 3, strlen($hed_str) - ($start_pos + 5));
      	if (strtoupper($type) == "Q")
	      {
	        $output = str_replace("_", " " , $phpgw->msg->qprint($newstr));
	      }
	      if (strtoupper($type) == "B")
	      {
	        $output = base64_decode($newstr);
	      }
      }	else {
	      $output = $hed_str;
      }
    }
    return $output;
  }

  function list_folders($mailbox)
  {
    global $phpgw, $phpgw_info, $msgtype;
    // Cyrus style: INBOX.Junque
    // UWash style: ./aeromail/Junque

    if ($phpgw_info["server"]["imap_server_type"] == "Cyrus") {
      $filter = "INBOX";
    } else {
      $filter = "mail/";
    }

    $mailboxes = $phpgw->msg->listmailbox($mailbox,"{".$phpgw_info["server"]["mail_server"].":".$phpgw_info["server"]["mail_port"]."}",$filter."*");  
    if ($phpgw_info["server"]["mail_server_type"] != "pop3")
      sort($mailboxes); // added sort for folder names 
    if($mailboxes)
    {
      $num_boxes = count($mailboxes);
      if ($filter != "INBOX") 
      { 
        echo "<option>INBOX"; 
      }
      for ($index = 0; $index < $num_boxes; $index++)
      {
	      $nm = substr($mailboxes[$index], strrpos($mailboxes[$index], "}") + 1, strlen($mailboxes[$index]));
	      echo "<option>";
	      if ($nm != "INBOX")
	      {
	        echo $phpgw->msg->deconstruct_folder_str($nm);
	      } else {
	        echo "INBOX";
	      }
	      echo "\n";
      }
    } else {
      echo "<option>INBOX";
    }
  }

  function get_mime_type($de_part)
  {
    switch ($de_part->type)
    {
      case TYPETEXT:		$mime_type = "text"; break;
      case TYPEMESSAGE:		$mime_type = "message"; break;
      case TYPEAPPLICATION:	$mime_type = "application"; break;
      case TYPEAUDIO:		$mime_type = "audio"; break;
      case TYPEIMAGE:		$mime_type = "image"; break;
      case TYPEVIDEO:		$mime_type = "video"; break;
      case TYPEMODEL:		$mime_type = "model"; break;
      default:			$mime_type = "unknown";
    }
    return $mime_type;
  }

  function get_mime_encoding($de_part)
  {
    switch ($de_part->encoding)
    {
      case ENCBASE64:		$mime_encoding = "base64"; break;
      case ENCQUOTEDPRINTABLE:	$mime_encoding = "qprint"; break;
      case ENCOTHER:		$mime_encoding = "other";  break;
      default:			$mime_encoding = "other";
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
    global $folder, $msgnum, $phpgw;
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
      }
    }

    $jnk = "<a href=\"".$phpgw->link("get_attach.php","folder=$folder"
		       ."&msgnum=$msgnum&part_no=$part_no&type=$mime_type"
		       ."&subtype=" . $de_part->subtype . "&name=$url_att_name"
		       ."&encoding=$mime_encoding")."\">$att_name</a>";
    return $jnk;
  }

  function inline_display($de_part, $part_no)
  {
    global $mailbox, $folder, $msgnum, $phpgw, $phpgw_info;
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
      $dsp = htmlentities($dsp);
      $dsp = ereg_replace( "^","<p>",$dsp);
      $dsp = ereg_replace( "\n","<br>",$dsp);
      $dsp = ereg_replace( "$","</p>", $dsp);
      $dsp = make_clickable($dsp);
      echo "<table border=\"0\" align=\"left\" cellpadding=\"10\" width=\"80%\">"
           ."<tr><td>$dsp</td></tr></table>";
    } else if (strtoupper($de_part->subtype) == "HTML") {
      output_bound(lang("section").":" , "$mime_type/$de_part->subtype");
      echo $dsp;
    } else {
      output_bound(lang("section").":" , "$mime_type/$de_part->subtype");
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

    output_bound(lang("image").":" , $att_name);
    echo "\n<img src=\"".$phpgw->link("view_image.php",
				 "folder=".urlencode($folder)."&msgnum=$msgnum"
				."&part_no=$part_no&type=".$de_part->subtype
				."&name=$att_name")."\">\n<p>\n";
  }

  // function make_clickable ripped off from PHPWizard.net
  // http://www.phpwizard.net/phpMisc/
  // modified to make mailto: addresses compose in AeroMail
  function make_clickable($text)
  {
    global $folder, $phpgw;
    $ret = eregi_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",
	    "<a href=\"\\1://\\2\\3\" target=\"_new\">\\1://\\2\\3</a>", $text);
    $ret = eregi_replace("(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
	    "<a href=\"".$phpgw->link("compose.php","folder=".urlencode($folder))
	    ."&to=\\1\">\\1</a>", $ret);
    return($ret);
  }

?>
