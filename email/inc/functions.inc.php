<?php

  /* $Id$ */

  if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "UWash" &&
      $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap") {
      $folder = (!$folder ? "INBOX" : $folder);
  }
      //echo "<b>TEST:</b> $folder";

  // Its better then them using a ton of PHP errors.
  $mailbox = $phpgw->msg->login($folder);
  if (!$mailbox && !ereg("preferences",$PHP_SELF)) {
     echo "<p><center><b>" . lang("There was an error trying to connect to your mail server.<br>Please, check your username and password, or contact your admin.")
        . "</b></center>";
     exit;
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
        $decoded = base64_decode($encoded_text);
      }
      return $preceding . $decoded . decode_header_string($rest);
    } else return $string;
  }

  function list_folders($mailbox)
  {
    global $phpgw, $phpgw_info;
    // Cyrus style: INBOX.Junque
    // UWash style: ./aeromail/Junque

    if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"]) {
      while($pref = each($phpgw_info["user"]["preferences"]["nntp"])) {
	$phpgw->db->query("SELECT name FROM newsgroups WHERE con=".$pref[0]);
	while($phpgw->db->next_record()) {
	  echo "<option>".$phpgw->db->f("name");
        }
      }
    } else {
      if ($phpgw_info["user"]["preferences"]["email"]["imap_server_type"] == "Cyrus") {
	$filter = "INBOX";
      } else {
	$filter = "mail/";
      }

      $mailboxes = $phpgw->msg->listmailbox($mailbox,"{".$phpgw_info["user"]["preferences"]["email"]["mail_server"].":".$phpgw_info["server"]["mail_port"]."}",$filter."*");  
      if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] != "pop3")
         if (gettype($mailboxes) == "array") {
 	    sort($mailboxes); // added sort for folder names 
         }
      if($mailboxes) {
	$num_boxes = count($mailboxes);
	if ($filter != "INBOX") { 
	  echo "<option>INBOX"; 
	}
	for ($index = 0; $index < $num_boxes; $index++) {
	  $nm = substr($mailboxes[$index], strrpos($mailboxes[$index], "}") + 1, strlen($mailboxes[$index]));
	  echo "<option>";
	  if ($nm != "INBOX") {
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
	      $att_name = decode_header_string($att_name);
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
      if(strtoupper(lang("charset")) <> "BIG5")
        $dsp = htmlentities($dsp);
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
    $view_link = $phpgw->link("viewimage.php",$extra_parms);
    echo "\n<img src=\"".$view_link."\">\n<p>\n";
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
