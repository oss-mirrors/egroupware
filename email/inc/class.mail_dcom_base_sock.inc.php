<?php
  /**************************************************************************\
  * phpGroupWare API - MAIL                                                  *
  * This file written by Mark Peters <skeeter@phpgroupware.org>              *
  * Handles general functionality for mail/mail structures                   *
  * Copyright (C) 2001 Mark Peters                                           *
  * -------------------------------------------------------------------------*
  * This library is part of the phpGroupWare API                             *
  * http://www.phpgroupware.org/api                                          * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  define('SA_MESSAGES',1);
  define('SA_RECENT',2);
  define('SA_UNSEEN',4);
  define('SA_UIDNEXT',8);
  define('SA_UIDVALIDITY',16);
  define('SA_ALL',31);

  define('SORTDATE',0);
  define('SORTARRIVAL',1);
  define('SORTFROM',2);
  define('SORTSUBJECT',3);
  define('SORTTO',4);
  define('SORTCC',5);
  define('SORTSIZE',6);

  define ('TYPETEXT',0);
  define ('TYPEMULTIPART',1);
  define ('TYPEMESSAGE',2);
  define ('TYPEAPPLICATION',3);
  define ('TYPEAUDIO',4);
  define ('TYPEIMAGE',5);
  define ('TYPEVIDEO',6);
  define ('TYPEOTHER',7);
  //  define ('TYPEMODEL',
  define ('ENC7BIT',0);
  define ('ENC8BIT',1);
  define ('ENCBINARY',2);
  define ('ENCBASE64',3);
  define ('ENCQUOTEDPRINTABLE',4);
  define ('ENCOTHER',5);
  define ('ENCUU',6);

  class mailbox_status
  {
	var $messages;
	var $recent;
	var $unseen;
	var $uidnext;
	var $uidvalidity;
	var $quota;
	var $quota_all;
  }

  class att_parameter
  {
	var $attribute;
	var $value;
  }

  class struct
  {
	var $encoding;
	var $type;
	var $subtype;
	var $ifsubtype;
	var $parameters;
	var $ifparameters;
	var $description;
	var $ifdescription;
	var $disposition;
	var $ifdisposition;
	var $id;
	var $ifid;
	var $lines;
	var $bytes;
  }

  class msg_mb_info
  {
	var $Date = '';
	var $Driver ='';
	var $Mailbox = '';
	var $Nmsgs = '';
	var $Recent = '';
	var $Unread = '';
	var $Size;
  }

  class address
  {
	var $personal;
	var $mailbox;
	var $host;
	var $adl;
  }

  class msg
  {
	var $from;
	var $fromaddress;
	var $to;
	var $toaddress;
	var $cc;
	var $ccaddress;
	var $bcc;
	var $bccaddress;
	var $reply_to;
	var $reply_toaddress;
	var $sender;
	var $senderaddress;
	var $return_path;
	var $return_pathaddress;
	var $udate;
	var $subject;
	var $lines;
  }

  class mail_dcom_base extends network
  {
	var $header=array();
	var $msg;
	var $struct;
	var $body;
	var $mailbox;
	var $numparts;

	var $sparts;
	var $hsub=array();
	var $bsub=array();
	
	function mail_dcom_base()
	{
		global $phpgw_info;

		$this->errorset = 0;
		$this->network(True);
		if (isset($phpgw_info))
		{
			$this->tempfile = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'].'.mhd';
			$this->att_files_dir = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'];
		}
		else
		{
			// NEED GENERIC DEFAULT VALUES HERE
			
		}
	}

	function error()
	{
		global $phpgw;
		
		echo 'Error: '.$this->error['code'].' : '.$this->error['msg'].' - '.$this->error['desc']."<br>\n";
		$phpgw->common->phpgw_exit();
	}

	function create_header($line,$header,$line2='')
	{
		$thead = explode(':',$line);
		$key = trim($thead[0]);
		switch(count($thead))
		{
			case 1:
				$value = TRUE;
				break;
			case 2:
				$value = trim($thead[1]);
				break;
			default: 
				$thead[0] = '';
				$value = '';
				for($i=1,$j=count($thead);$i<$j;$i++)
				{
					$value .= $thead[$i].':';
				}
//				$value = trim($value.$thead[$j++]);
//				$value = trim($value);
				break;
		}
		$header[$key] = $value;
		if (ereg('^multipart/mixed;',$value))
		{
			if (! ereg('boundary',$header[$key]))
			{
				if ($line2 == 'True')
				{
					$line2 = $this->read_port();
					echo "Response = ".$line2."<br>\n";
				}
			}
			$header[$key] .= chop($line2);
		}
//		echo "Header[$key] = ".$header[$key]."<br>\n";
	}

	function build_address_structure($key)
	{
		$address = array(new address);
		// Build Address to Structure
		$temp_array = explode(';',$this->header[$key]);
		for ($i=0;$i<count($temp_array);$i++)
		{
			$this->decode_author($temp_array[$i],&$email,&$name);
			$temp = explode('@',$email);
			$address[$i]->personal = $this->decode_header($name);
			$address[$i]->mailbox = $temp[0];
			if (count($temp) == 2)
			{
				$address[$i]->host = $temp[1];
				$address[$i]->adl = $email;
			}
			return $address;
		}
	}

	function convert_date_array($field_list)
	{
		$new_list = Array();
		while(list($key,$value) = each($field_list))
		{
			$new_list[$key] = $this->convert_date($value);
		}
		return $new_list;
	}

	function convert_date($msg_date)
	{
		global $phpgw_info;
		
//		This may need to be a reference to the different months in native tongue....
		$month = Array(
			'Jan' => 1,
			'Feb' => 2,
			'Mar' => 3,
			'Apr' => 4,
			'May' => 5,
			'Jun' => 6,
			'Jul' => 7,
			'Aug' => 8,
			'Sep' => 9,
			'Oct' => 10,
			'Nov' => 11,
			'Dec' => 12
		);
		$dta = array();
		$ta = array();

		// Convert "Sat, 15 Jul 2000 20:50:22 +0200" to unixtime
		$comma = strpos($msg_date,',');
		if($comma)
		{
			$msg_date = substr($msg_date,$comma + 2);
		}
//		echo 'Msg Date : '.$msg_date."<br>\n";
		$dta = explode(' ',$msg_date);
		$ta = explode(':',$dta[3]);

		if(substr($dta[4],0,3) <> 'GMT')
		{
			$tzoffset = substr($dta[4],0,1);
			(int)$tzhours = substr($dta[4],1,2);
			(int)$tzmins = substr($dta[4],3,2);
			switch ($tzoffset)
			{
				case '+': 
					(int)$ta[0] -= (int)$tzhours;
					(int)$ta[1] -= (int)$tzmins;
					break;
				case '-':
					(int)$ta[0] += (int)$tzhours;
					(int)$ta[1] += (int)$tzmins;
					break;
			}
		}
		$new_time = mktime($ta[0],$ta[1],$ta[2],$month[$dta[1]],$dta[0],$dta[2]) - ((60 * 60) * intval($phpgw_info['user']['preferences']['common']['tzoffset']));
//		echo 'New Time : '.$new_time."<br>\n";
		return $new_time;
	}

	function ssort_prep($a)
	{
		$a = strtoupper($a);
		if(strpos(' '.$a,'FW: ') == 1 || strpos(' '.$a,'RE: ') == 1)
		{
			$a_mod = substr($a,4);
		}
		elseif(strpos(' '.$a,'FWD: ') == 1)
		{
			$a_mod = substr($a,5);
		}
		else
		{
			$a_mod = $a;
		}
		
		while(substr($a_mod,0,1) == ' ')
		{
			$a_mod = substr($a_mod,1);
		}

//		if(strpos(' '.$a_mod,'[') == 1)
//		{
//			$a_mod = substr($a_mod,1);
//		}
		return $a_mod;
	}
	
	function ssort_ascending($a,$b)
	{
		$a_mod = $this->ssort_prep($a);
		$b_mod = $this->ssort_prep($b);
		if ($a_mod == $b_mod)
		{
			return 0;
		}
		return ($a_mod < $b_mod) ? -1 : 1;
	}

	function ssort_decending($a,$b)
	{
		$a_mod = $this->ssort_prep($a);
		$b_mod = $this->ssort_prep($b);
		if ($a_mod == $b_mod)
		{
			return 0;
		}
		return ($a_mod > $b_mod) ? -1 : 1;
	}
	
	function mail_header($msgnum)
	{
		$this->msg = new msg;
		// This needs to be pulled back to the actual read header of the mailer type.
//		$this->mail_fetch_overview($msgnum);

		// From:
		$this->msg->from = array(new address);
		$this->msg->from = $this->build_address_structure('From');
		$this->msg->fromaddress = $this->header['From'];

		// To:
		$this->msg->to = array(new address);
		if (strtolower($this->type) == 'nntp')
		{
			$temp = explode(',',$this->header['Newsgroups']);
			$to = array(new address);
			for($i=0;$i<count($temp);$i++)
			{
				$to[$i]->mailbox = '';
				$to[$i]->host = '';
				$to[$i]->personal = $temp[$i];
				$to[$i]->adl = $temp[$i];
			}
			$this->msg->to = $to;
		}
		else
		{
			$this->msg->to = $this->build_address_structure('To');
			$this->msg->toaddress = $this->header['To'];
		}

		// Cc:
		$this->msg->cc = array(new address);
		if(isset($this->header['Cc']))
		{
			$this->msg->cc[] = $this->build_address_structure('Cc');
			$this->msg->ccaddress = $this->header['Cc'];
		}
    
		// Bcc:
		$this->msg->bcc = array(new address);
		if(isset($this->header['bcc']))
		{
			$this->msg->bcc = $this->build_address_structure('bcc');
			$this->msg->bccaddress = $this->header['bcc'];
		}

		// Reply-To:
		$this->msg->reply_to = array(new address);
		if(isset($this->header['Reply-To']))
		{
			$this->msg->reply_to = $this->build_address_structure('Reply-To');
			$this->msg->reply_toaddress = $this->header['Reply-To'];
		}

		// Sender:
		$this->msg->sender = array(new address);
		if(isset($this->header['Sender']))
		{
			$this->msg->sender = $this->build_address_structure('Sender');
			$this->msg->senderaddress = $this->header['Sender'];
		}

		// Return-Path:
		$this->msg->return_path = array(new address);
		if(isset($this->header['Return-Path']))
		{
			$this->msg->return_path = $this->build_address_structure('Return-Path');
			$this->msg->return_pathaddress = $this->header['Return-Path'];
		}

		// UDate
		$this->msg->udate = $this->convert_date($this->header['Date']);

		// Subject
		$this->msg->subject = $this->phpGW_quoted_printable_decode($this->header['Subject']);

		// Lines
		// This represents the number of lines contained in the body
		$this->msg->lines = $this->header['Lines'];
	}

	function mail_headerinfo($msgnum)
	{
		$this->mail_header($msgnum);
	}

	function read_and_load($end)
	{
		$this->header = Array();
		while ($line = $this->read_port())
		{
//			echo $line."<br>\n";
			if (chop($line) == $end) break;
			$this->create_header($line,&$this->header,"True");
		}
		return 1;
	}

	/*
	 * PHP `quoted_printable_decode` function does not work properly:
	 * it should convert '_' characters into ' '.
	*/
	function phpGW_quoted_printable_decode($string)
	{
		$string = str_replace('_', ' ', $string);
		return quoted_printable_decode($string);
	}

	/*
	 * Remove '=' at the end of the lines. `quoted_printable_decode` doesn't do it.
	*/
	function phpGW_quoted_printable_decode2($string)
	{
		$string = $this->phpGW_quoted_printable_decode($string);
		return preg_replace("/\=\n/", '', $string);
	}

	function decode_base64($string)
	{
		$string = ereg_replace("'", "\'", $string);
		$string = preg_replace("/\=\?(.*?)\?b\?(.*?)\?\=/ieU",base64_decode("\\2"),$string);
		return $string;
	}

	function decode_qp($string)
	{
		$string = ereg_replace("'", "\'", $string);
		$string = preg_replace("/\=\?(.*?)\?q\?(.*?)\?\=/ieU",$this->phpGW_quoted_printable_decode2("\\2"),$string);
		return $string;
	}

	function decode_header($string)
	{
		/* Decode from qp or base64 form */
		if (preg_match("/\=\?(.*?)\?b\?/i", $string))
		{
			return $this->decode_base64($string);
		}
		if (preg_match("/\=\?(.*?)\?q\?/i", $string))
		{
			return $this->decode_qp($string);
		}
		return $string;
	}

	function decode_author($author,&$email,&$name)
	{
		/* Decode from qp or base64 form */
		$author = $this->decode_header($author);
		/* Extract real name and e-mail address */
		/* According to RFC1036 the From field can have one of three formats:
			1. Real Name <name@domain.name>
			2. name@domain.name (Real Name)
			3. name@domain.name
		*/
		/* 1st case */
//		if (eregi("(.*) <([-a-z0-9\_\$\+\.]+\@[-a-z0-9\_\.]+[-a-z0-9\_]+)>",
		if (eregi("(.*) <([-a-z0-9_$+.]+@[-a-z0-9_.]+[-a-z0-9_]+)>",$author, $regs))
		{
			$email = $regs[2];
			$name = $regs[1];
		}
		/* 2nd case */
		elseif (eregi("([-a-z0-9_$+.]+@[-a-z0-9_.]+[-a-z0-9_]+) ((.*))",$author, $regs))
		{
//		if (eregi("([-a-z0-9\_\$\+\.]+\@[-a-z0-9\_\.]+[-a-z0-9\_]+) \((.*)\)",$author, $regs))
			$email = $regs[1];
			$name = $regs[2];
		}
		/* 3rd case */
		else
		{
			$email = $author;
		}
		if ($name == '')
		{
			$name = $email;
		}
		$name = eregi_replace("^\"(.*)\"$", "\\1", $name);
		$name = eregi_replace("^\((.*)\)$", "\\1", $name);
	}

	function get_mime_type($de_part)
	{
		if (!isset($de_part->type))
		{
			return 'unknown';
		}

		switch ($de_part->type)
		{
			case 0:		$mime_type = 'text'; break;
			case 1:		$mime_type = 'multipart'; break;
			case 2:		$mime_type = 'message'; break;
			case 3:		$mime_type = 'application'; break;
			case 4:		$mime_type = 'audio'; break;
			case 5:		$mime_type = 'image'; break;
			case 6:		$mime_type = 'video'; break;
			case 7:		$mime_type = 'other'; break;
			default:		$mime_type = 'unknown';
		}
		return $mime_type;
	}

	function get_mime_encoding($de_part)
	{
		switch ($de_part->encoding)
		{
			case 3:	$mime_encoding = 'base64'; break;
			case 4:	$mime_encoding = 'qprint'; break;
			case 5:	$mime_encoding = 'other';  break;
			default:	$mime_encoding = 'other';
		}
		return $mime_encoding;
	}

	function get_att_name($de_part)
	{
		$param = new parameter;
		$att_name = 'Unknown';
		if (!isset($de_part->parameters))
		{
			return $att_name;
		}
		for ($i=0;$i<count($de_part->parameters);$i++)
		{
			$param=(!$de_part->parameters[$i]?$de_part->parameters:$de_part->parameters[$i]);
			if(!$param)
			{
				break;
			}
			$pattribute = $param->attribute;
			if (strtolower($pattribute) == 'name')
			{
				$att_name = $param->value;
			}
		}
		return $att_name;
	}

	function attach_display($de_part,$part_no,$mailbox,$folder,$msgnum)
	{
		global $phpgw, $phpgw_info;
		$mime_type = $this->get_mime_type($de_part);  
		$mime_encoding = $this->get_mime_encoding($de_part);

		$att_name = 'unknown';
		$param = new parameter;

		for ($i = 0; $i < count($de_part->parameters); $i++)
		{
			if(!$de_part->parameters[$i])
			{
				break;
			}
			$param = $de_part->parameters[$i];
			$pattribute = $param->attribute;
			if (strtoupper($pattribute) == 'NAME')
			{
				$att_name = $param->value;
				$url_att_name = urlencode($att_name);
			}
		}

		return '<a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/get_attach.php',
					 'folder='.$folder.'&msgnum='.$msgnum.'&part_no='.$partno.'&type='.$mime_type
					.'&subtype='.$de_part->subtype.'&name='.$url_att_name.'&encoding='.$mime_encoding)
				.'">'.$att_name.'</a>';
	}

	Function inline_display($de_part,$dsp,$mime_section,$folder)
	{
		global $phpgw;
		
		$mime_type = $this->get_mime_type($de_part);
		$mime_encoding = $this->get_mime_encoding($de_part);
		$tag = 'pre';
//		$jnk = isset($de_part->disposition) ? $de_part->disposition : 'unknown';

//		echo "<!-- MIME disp: $jnk -->\n";
//		echo "<!-- MIME type: $mime_type -->\n";
//		echo "<!-- MIME subtype: $de_part->subtype -->\n";
//		echo "<!-- MIME encoding: $mime_encoding -->\n";
//		echo "<!-- MIME filename: $att_name -->\n";

		if ($mime_encoding == 'qprint')
		{
			$dsp = $this->decode_qp($dsp);
			$tag = 'tt';
		}

		// Thanks to Omer Uner Guclu <oquclu@superonline.com> for figuring out
		// a better way to do message wrapping

		if (isset($de_part->subtype) && strtoupper($de_part->subtype) == 'PLAIN')
		{
			// nlbr and htmlentities functions are strip latin5 characters
			$dsp = $phpgw->strip_html($dsp);
			$dsp = ereg_replace( "^","<p>",$dsp);
			$dsp = ereg_replace( "\r\n","<br>",$dsp);
			$dsp = ereg_replace( "\n","<br>",$dsp);
			$dsp = ereg_replace( "\t","    ",$dsp);
			$dsp = ereg_replace( "$","</p>", $dsp);
			$dsp = $this->make_clickable($dsp,$folder);
			return '<table border="0" align="left" cellpadding="10" width="80%"><tr><td>'.$dsp.'</td></tr></table>';
		}
		elseif (isset($de_part->subtype) && strtoupper($de_part->subtype) == 'HTML')
		{
			$str = $this->output_bound($mime_section.':',$mime_type.'/'.$de_part->subtype);
			return $str.$dsp;
		}
		elseif (isset($de_part->subtype) && 
			(strtoupper($de_part->subtype) == 'JPG' ||
			 strtoupper($de_part->subtype) == 'JPEG' ||
			 strtoupper($de_part->subtype) == 'PJPEG' ||
			 strtoupper($de_part->subtype) == 'GIF' ||
			 strtoupper($de_part->subtype) == 'PNG'))
		{
			$att_name = $this->get_att_name($de_part);
			$str = $this->output_bound($mime_section.':',$mime_type.'/'.$de_part->subtype);
			return $str.$this->image_display($dsp,$att_name);
		}
		else
		{
			$str = $this->output_bound($mime_section.':',$mime_type.'/'.$de_part->subtype);
			return $str.'<'.$tag.'>'.$dsp.'</'.$tag.'>'."\n";
		}
	}

	function output_bound($title, $str)
	{
		global $phpgw_info;

		return '</td></tr></table>'."\n"
			. '<table border="0" cellpadding="4" cellspacing="3" width="700">'."\n"
			. '<tr><td bgcolor"'.$phpgw_info['theme']['th_bg'].'" valign="top">'
			. '<font size="2" face="'.$phpgw_info['theme']['font'].'"><b>'.$title.'</b></td>'."\n"
			. '<td bgcolor="'.$phpgw_info['theme']['row_on'].'" width="570">'
			. '<font size="2" face="'.$phpgw_info['theme']['font'].'">'.$str.'</td></tr></table>'."\n"
			. '<p>'."\n".'<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td>';
	}

	function image_display($bsub,$att_name)
	{
		global $phpgw, $phpgw_info;

		$bsub = strip_tags($bsub);
		$unique_filename = tempnam($phpgw_info['user']['private_dir'],'mail');
		$unique_filename = str_replace($phpgw_info['user']['private_dir'].SEP,'',$unique_filename);
		$phpgw->vfs->write($unique_filename,base64_decode($bsub));
		// we want to display images here, even though they are attachments.
		return  '</td></tr><tr align="center"><td align="center">'
			.'<img src="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/view_attachment.php',
			 'file='.urlencode($unique_filename).'&attfile='.$att_name).'"><p>';
	}

	// function make_clickable ripped off from PHPWizard.net
	// http://www.phpwizard.net/phpMisc/
	// modified to make mailto: addresses compose in AeroMail
	function make_clickable($text,$folder)
	{
		global $phpgw, $phpgw_info;

		$ret = eregi_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=\:])",
			"<a href=\"\\1://\\2\\3\" target=\"_new\">\\1://\\2\\3</a>", str_replace("<br>","\n",$text));
		if($ret == $text)
		{
			$ret = eregi_replace("(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
				'a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder))."&to=\\1\">\\1</a>", $ret);
		}
		return(str_replace("\n","<br>",$ret));
	}

	function uudecode($str)
	{
		$file='';
		for($i=0;$i<count($str);$i++)
		{
			if ($i==count($str)-1 && $str[$i] == "`")
			{
				$phpgw->common->phpgw_exit();
			}
			$pos=1;
			$d=0;
			$len=(int)(((ord(substr($str[$i],0,1)) ^ 0x20) - ' ') & 077);
			while (($d+3<=$len) && ($pos+4<=strlen($str[$i])))
			{
				$c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
				$c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$c2=(ord(substr($str[$i],$pos+2,1)) ^ 0x20);
				$c3=(ord(substr($str[$i],$pos+3,1)) ^ 0x20);
				$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
				$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));
				$file .= chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077)      );
				$pos+=4;
				$d+=3;
			}
			if (($d+2<=$len) && ($pos+3<=strlen($str[$i])))
			{
				$c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
				$c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$c2=(ord(substr($str[$i],$pos+2,1)) ^ 0x20);
				$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
				$file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));
				$pos+=3;
				$d+=2;
			}
			if (($d+1<=$len) && ($pos+2<=strlen($str[$i])))
			{
				$c0=(ord(substr($str[$i],$pos  ,1)) ^ 0x20);
				$c1=(ord(substr($str[$i],$pos+1,1)) ^ 0x20);
				$file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));
			}
		}
		return $file;
	}
}
?>
