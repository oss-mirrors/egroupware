<?php

  function lang_email($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "reply":		$s = "Reply";		break;
       case "reply all":	$s = "Reply All";	break;
       case "forward":		$s = "Forward";		break;
       case "delete":		$s = "Delete";		break;
       case "previous":		$s = "Previous";	break;
       case "next":			$s = "Next";		break;
       case "from":			$s = "From";		break;
       case "to":			$s = "To";			break;
       case "cc":			$s = "CC";			break;
       case "files":		$s = "Files";		break;
       case "date":			$s = "Date";		break;
       case "send":			$s = "Send";		break;
       case "subject":		$s = "Subject";		break;
       case "folder":		$s = "Folder";		break;
       case "size":			$s = "Size";		break;
       case "section":		$s = "Section";		break;
       case "image":		$s = "Image";		break;
       case "no subject":	$s = "No Subject";	break;
       case "compose":		$s = "Compose";		break;
       case "message":		$s = "Message";		break;
       case "messages":		$s = "Messages";	break;
       case "new message":	$s = "New message";	break;
       case "undisclosed sender":	$s = "Undisclosed Sender";	break;
       case "undisclosed recipients":	$s = "Undisclosed Recipients";	break;
       case "please select a message first":	$s = "Please select a message first";	break;

       case "this folder is empty":	$s = "This folder is empty";	break;

       case "switch current folder to":	$s = "Switch Current Folder To";	break;
       case "move selected messages into":	$s = "Move Selected Messages into";	break;
       case "add to addressbook":	$s = "Add to addressbook";	break;

       case "1 message has been deleted":
	$s = "1 message has been deleted";		break;

       case "x messages have been deleted":
	$s = "$m1 messages have been deleted";		break;

       case "your mail has been sent successfully":
	$s = "Your mail has been sent successfully.";	break;

       case "monitor":		$s = "Monitor";		break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }

?>
