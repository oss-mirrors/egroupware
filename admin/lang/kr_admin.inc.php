<?php

  function lang_admin($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "last x logins":	$s = "Last $m1 logins";		break;
       case "loginid":			$s = "����ID";				break;
       case "ip":				$s = "IP";					break;
       case "total records":	$s = "Total records";		break;
       case "user accounts":	$s = "����� ����";		break;
       case "new group name":	$s = "�� �׷� �̸�";		break;
       case "create group":		$s = "�� �׷� �����";		break;
       case "kill":				$s = "����";				break;
       case "idle":				$s = "idle";				break;
       case "login time":		$s = "���� �ð�";			break;
       case "anonymous user":	$s = "���� �����";		break;
       case "manager":			$s = "������";				break;
       case "account active":	$s = "���� Ȱ��ȭ";		break;
       case "re-enter password": $s = "��й�ȣ ���Է�";	break;
       case "group name": 		$s = "�׷��̸�";			break;
       case "display":			$s = "Display";				break;
       case "base url":			$s = "�⺻  URL";			break;
       case "news file":		$s = "���ο� ����";			break;
       case "minutes between reloads":	$s = "��ε� �ð�(�д���)";		break;
       case "listings displayed":	$s = "Listings Displayed";		break;
       case "news type":		$s = "���� Ÿ��";			break;
       case "user groups":		$s = "����� �׷�";			break;
       case "headline sites":	$s = "������ ����Ʈ";		break;
       case "network news":	$s = "��Ʈ��ũ ����";		break;
       case "site":				$s = "Site";				break;
       case "view sessions":	$s = "���� ����";		break;
       case "view access log":	$s = "���� �α� ����";		break;
       case "active":			$s = "Ȱ��ȭ";				break;
       case "disabled":			$s = "��Ȱ��ȭ";			break;
       case "last time read":	$s = "������ ���� �ð�";		break;
       case "manager":			$s = "������";		break;

       case "are you sure you want to delete this group ?":
	$s = "�� �׷��� ���� �����Ͻðڽ��ϱ� ?"; break;

       case "are you sure you want to kill this session ?":
	$s = "�� ������ ���� �����Ű�ðڽ��ϱ� ?"; break;

       case "all records and account information will be lost!":
	$s = "�� ������ �ڷ�� ������ �����˴ϴ�.";	break;

       case "are you sure you want to delete this account ?":
	$s = "�� ������ ���� �����Ͻðڽ��ϱ� ?";	break;

       case "are you sure you want to delete this news site ?":
	$s = "�� ��������Ʈ�� ���� �����Ͻðڽ��ϱ�?";		break;

       case "* make sure that you remove users from this group before you delete it.":
	$s = "* �� �׷쿡�� ����ڸ� ���� �����Ͻðڽ��ϱ�?";	break;

       case "percent of users that logged out":
	$s = "�ۼ�Ʈ�� ����ڰ� �α׾ƿ� �Ͽ����ϴ�.";			break;

       case "list of current users":
	$s = "���� ����� ���";						break;

       case "new password [ leave blank for no change ]":
	$s = "�� �н�����[ �ٲ��� �������� ��ĭ���� ���ܵμ��� ]";	break;

       case "the two passwords are not the same":
	$s = "��й�ȣ�� ���� �ʽ��ϴ�.";			break;

       case "the login and password can not be the same":
	$s = "������ �н������ ���� �ʾƾ� �մϴ�.";	break;

       case "you must enter a password":	$s = "�н����带 �Է��ؾ� �մϴ�.";		break;

       case "that loginid has already been taken":
	$s = "�� ����� ������ �̹� ������Դϴ�.";			break;

       case "you must enter a display":		$s = "����� �Է��ؾ� �մϴ�.";		break;
       case "you must enter a base url":	$s = "�⺻ URL�� �Է��ؾ� �մϴ�.";		break;
       case "you must enter a news url":	$s = "���� URL�� �Է��ؾ� �մϴ�.";		break;

       case "you must enter the number of minutes between reload":
	$s = "��ε�� �ð� ������ �Է��ؾ� �մϴ�.";		break;

       case "you must enter the number of listings display":
	$s = "�ѹ��� ������ ������ �Է��ؾ� �մϴ�.";		break;

       case "you must select a file type":
	$s = "���� Ÿ���� �����ؾ� �մϴ�.";					break;

       case "that site has already been entered":
	$s = "�� ����Ʈ�� �̹� �ԷµǾ� �ֽ��ϴ�.";			break;

       case "select users for inclusion":
        $s = "������ ����ڸ� �����ϼ���.";	break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>
