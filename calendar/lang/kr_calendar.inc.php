<?php

  function lang_calendar($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "today":		$s = "����";	break;
       case "this week":	$s = "�̹���";	break;
       case "this month":	$s = "�̹���";	break;

       case "generate printer-friendly version":
	$s = "����Ʈ�ϱ�";	break;

       case "printer friendly":		$s = "����Ʈ�ϱ�";	break;

       case "you have not entered a\\nbrief description":
	$s = "������ ������ �Է��ϼ���.";	break;

       case "you have not entered a\\nvalid time of day.":
	$s = "�ð��� ��Ȯ�ϰ� �Է��ϼ���.";	break;

       case "are you sure\\nyou want to\\ndelete this entry ?":
	$s = "�� �׸��� ������ ���� �Ͻðڽ��ϱ� ?";	break;

       case "participants":		$s = "������";	break;
       case "calendar - edit":	$s = "�޷� - ����";	break;
       case "calendar - add":	$s = "�޷� - �߰�";	break;
       case "brief description":$s = "������ ����";break;
       case "full description":	$s = "�ڼ��� ����";break;
       case "duration":			$s = "�Ⱓ";		break;
       case "minutes":			$s = "��";			break;
       case "repeat type":		$s = "�ݺ����";		break;
       case "none":				$s = "����";			break;
       case "daily":			$s = "����";			break;
       case "weekly":			$s = "����";			break;
       case "monthly (by day)":	$s = "�ſ� (by day) ";break;
       case "monthly (by date)":$s = "�ſ� (by date)";break;
       case "yearly":			$s = "�ų�";	break;
       case "repeat end date":	$s = "�ݺ� ���ᳯ¥";	break;
       case "use end date":		$s = "������ ��¥ ���";	break;
       case "repeat day":		$s = "�ݺ� ����";		break;
       case "(for weekly)":		$s = "";	break;
       case "frequency":		$s = "��";		break;
       case "sun":				$s = "�Ͽ���";				break;
       case "mon":				$s = "������";				break;
       case "tue":				$s = "ȭ����";				break;
       case "wed":				$s = "������";				break;
       case "thu":				$s = "�����";				break;
       case "fri":				$s = "�ݿ���";				break;
       case "sat":				$s = "�����";				break;
       case "search results":	$s = "�˻����";	break;
       case "no matches found.":$s = "�˻����ǿ� �´� �׸��� �����ϴ�.";break;
       case "1 match found":	$s = "1���׸� ã��";	break;
       case "x matches found":	$s = "$m1�� �׸� ã��";break;
       case "description":		$s = "����";		break;
       case "repetition":		$s = "�ݺ�";		break;
       case "days repeated":	$s = "���� �ݺ��Ǿ���";	break;
       case "go!":				$s = "����!";				break;
       case "year":				$s = "��";			break;
       case "month":			$s = "��";			break;
       case "week":				$s = "��";			break;
       case "new entry":		$s = "���ο� �׸�";		break;
       case "view this entry":	$s = "�׸� ����";	break;

       case "the following conflicts with the suggested time:<ul>x</ul>":
	$s = "���� �׸��� ���ȵ� �ð��� �浹�մϴ�. :<ul>$m1</ul>";	break;

       case "your suggested time of <B> x - x </B> conflicts with the following existing calendar entries:":
	$s = "<B> $m1 - $m2 </B>�� �׸��� �޷¿� �ִ� ����� �����ϴ�.";	break;

       case "you must enter one or more search keywords":
	$s = "�ϳ� �̻��� �˻�Ű���带 �Է��ϼž� �մϴ�.";	break;

       case "are you sure\\nyou want to\\ndelete this entry ?\\n\\nthis will delete\\nthis entry for all users.":		$s = "�� �׸��� ������ �����Ͻðڽ��ϱ�?";	break;

       case "":		$s = "";	break;
       case "":		$s = "";	break;
       case "":		$s = "";	break;
       case "":		$s = "";	break;
       case "":		$s = "";	break;
       case "":		$s = "";	break;
       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>
