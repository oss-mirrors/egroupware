<?php

  function lang_tts($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "trouble ticket system":	$s = "��������Ʈ �ý���";	break;
       case "ticket":			$s = "�۾�";		break;
       case "prio":			$s = "�߿䵵";		break;
       case "group":			$s = "�׷�";		break;
       case "assigned to":		$s = "�Ҵ�";	break;
       case "opened by":		$s = "�ۼ���";	break;
       case "date opened":		$s = "�ۼ���";	break;
       case "subject":			$s = "����";		break;
       case "new ticket":		$s = "�� �۾�";	break;
       case "view all tickets":		$s = "��� �۾�����";	break;
       case "view only open tickets":	$s = "Ȱ���� �۾�����";	break;
       case "no tickets found":		$s = "�۾��� ����";	break;
       case "status/date closed":	$s = "��Ȱ���� ����/��¥";	break;
       case "add new ticket":		$s = "�� �۾��߰�";	break;
       case "detail":			$s = "����";		break;
       case "priority":			$s = "�߿䵵";	break;
       case "add ticket":		$s = "���۾� �߰�";	break;
       case "clear form":		$s = "���ۼ�";	break;
       case "no subject":		$s = "�������";	break;
       case "view job detail":		$s = "�۾� ���뺸��";	break;
       case "assigned from":		$s = "�۾���";	break;
       case "open date":		$s = "���� ��¥";	break;
       case "close date":		$s = "���� ��¥";	break;
       case "details":			$s = "����";		break;
       case "additional notes":		$s = "���� �߰�";	break;
       case "ok":			$s = "Ȯ��";		break;
       case "update":			$s = "������Ʈ";		break;
       case "close":			$s = "����";		break;
       case "in progress":		$s = "������";	break;
       case "closed":			$s = "�����";		break;
       case "reopen":			$s = "�����";		break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
