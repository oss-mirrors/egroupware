<?php

  function lang_pref($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "max matchs per page":
	$s = "�������� �ִ� �˻� ��� �׸� ��";		break;
       
       case "time zone offset":
	$s = "�ð��� ����";		break;
       
       case "this server is located in the x timezone":
	$s = "�� ������ " . $m1 . " �� �ð��븦 ����ϰ� �ֽ��ϴ�.";	break;
       
       case "date format":	$s = "��¥ ����";			break;
       case "time format":	$s = "�ð� ����";			break;
       case "language":		$s = "���";			break;

       case "show text on navigation icons":
	$s = "��������";			break;
       
       case "show current users on navigation bar":
	$s = "���� ����ڸ� navigation bar�� ǥ���մϴ�.";	break;
       
       case "show new messages on main screen":
	$s = "���ο� �޽����� ����ȭ�鿡 �����ݴϴ�.";	break;
       
       case "email signature":
	$s = "E-Mail ����";	break;
       
       case "show birthday reminders on main screen":
	$s = "������ ��� �˷��ֱ�.";	break;
       
       case "show high priority events on main screen":
	$s = "�߿䵵�� ���� �۾� �����ֱ�";	break;
       
       case "weekday starts on":
	$s = "�������� ����";	break;
       
       case "work day starts on":
	$s = "�ٹ� ���� �ð�";	break;
       
       case "work day ends on":
	$s = "�ٹ� ���� �ð�";	break;
       
       case "select headline news sites":
	$s = "ǥ���� ���� ����Ʈ ����";	break;
       
       case "change your password":
	$s = "��ȣ ����";		break;

       case "select different theme":
	$s = "�׸� �ٲٱ�";		break;

       case "change your settings":
	$s = "���� �ٲٱ�";		break;

       case "enter your new password":
	$s = "���ο� ��ȣ ";		break;

       case "re-enter your password":
	$s = "���ο� ��ȣ Ȯ��";	break;

       case "the two passwords are not the same":
	$s = "��ȣ�� �߸� �ԷµǾ����ϴ�.";	break;

       case "you must enter a password":
	$s = "��ȣ�� �Է��ؾ߸� �մϴ�.";	break;

       case "your current theme is: x":
	$s = "���� ������� �׸��� <b>" . $m1 . "</b> �Դϴ�.";	break;

       case "please, select a new theme":
	$s = "���ο� �׸��� �����ϼ���.";	break;

       case "note: this feature does *not* change your email password. this will need to be done manually.":
	$s = "����: e-mail�� ��ȣ�� ������� �ʽ��ϴ�. �������� �����Ͻʽÿ�.";	break;


       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }


