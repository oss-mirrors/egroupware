<?php



  function lang_email($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")

  {

    $message = strtolower($message);



    switch($message)

    {

       case "reply":		$s = "����";		break;

       case "reply all":	$s = "��ο��� ����";	break;

       case "forward":		$s = "����";		break;

       case "delete":		$s = "����";		break;

       case "previous":		$s = "��������";	break;

       case "next":			$s = "��������";		break;

       case "from":			$s = "������ ���";		break;

       case "to":			$s = "���� ���";			break;

       case "cc":			$s = "����";			break;

       case "files":		$s = "Files";		break;

       case "date":			$s = "��¥";		break;

       case "send":			$s = "������";		break;

       case "subject":		$s = "����";		break;

       case "folder":		$s = "����";		break;

       case "size":			$s = "ũ��";		break;

       case "section":		$s = "����";		break;

       case "image":		$s = "�̹���";		break;

       case "no subject":	$s = "�������";	break;

       case "compose":		$s = "�ۼ�";		break;

       case "message":		$s = "�޼���";		break;

       case "messages":		$s = "�޼���";	break;

       case "new message":	$s = "�� �޼���";	break;

       case "undisclosed sender":	$s = "";	break;

       case "undisclosed recipients":	$s = "Undisclosed Recipients";	break;

       case "please select a message first":	$s = "�޼����� ���� �����ϼ���.";	break;



       case "this folder is empty":	$s = "����ִ� ����";	break;



       case "switch current folder to":	$s = "�������� ���� ���� �ٲٱ�";	break;

       case "move selected messages into":	$s = "������ �޼��� �������� �ű��";	break;

       case "add to addressbook":	$s = "�ּҷϿ� �߰�";	break;



       case "1 message has been deleted":

	$s = "1���� �޼����� �����Ǿ����ϴ�.";		break;



       case "x messages have been deleted":

	$s = "$m1���� �޼����� �����Ǿ����ϴ�." ;		break;






       case "monitor":		$s = "�����";		break;



       default: $s = "<b>*</b> ". $message;

    }

    return $s;

  }



?>

