<?php

  function lang_forums($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {


	case "forums":		$s = "Forums";		break;

	case "new topic":		$s = "New Topic";		break;

	case "admin":		$s = "Admin";		break;
	case "topic":		$s = "Topic";		break;
	case "reply to this message":		$s = "Reply to this message";		break;
	case "view threads":		$s = "View Threads";		break;
	case "collapse threads":		$s = "Collapse Threads";		break;
	case "search":		$s = "Search";		break;
	case "submit":		$s = "Submit";		break;
	case "reply":		$s = "Reply";		break;

	case "author":		$s = "Author";		break;
	case "date":		$s = "Date";		break;
	case "replies":		$s = "Replies";		break;
	case "latest reply":		$s = "Latest Reply";		break;

	case "your name":		$s = "Your Name";		break;

	case "your email":		$s = "Your Email";		break;
	case "subject":		$s = "Subject";		break;
	case "email replies to this thread, to the address above":		$s = "Email replies to this thread, to the address above";		break;

	case "no messages available!":		$s = "No messages available!";		break;



       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>
