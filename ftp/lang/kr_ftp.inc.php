<?php

  function lang_ftp($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "relogin": $s = "�α׾ƿ�/��α���"; break;
       case "cd": $s = "cd"; break;
       case "delete": $s = "���� $m1"; break;
       case "deleted": $s = "$m1�� �����Ǿ����ϴ�."; break;
       case "failed to delete": $s = "$m1�� �����ϴµ� ������ �߻��Ͽ����ϴ�."; break;
       case "view": $s = "����"; break;
       case "save": $s = "����"; break;
       case "save to filemanager": $s = "���ϸ޴����� ����"; break;
       case "upload": $s = "���ε�"; break;
       case "uploaded": $s = "$m1�� ���ε� �߽��ϴ�."; break;
       case "failed to upload": $s = "$m1�� ���ε� ����"; break;
       case "create new directory": $s = "�� ���丮 ����"; break;
       case "failed to mkdir": $s = "���丮 $m1 ��������"; break;
       case "empty dirname": $s = "�̸� ���� ���丮�� ����ϴ�."; break;
       case "module name": $s = "FTP Ŭ���̾�Ʈ"; break;
       case "home": $s = "������"; break;
       case "username": $s="������̸�"; break;
       case "password": $s="�н�����"; break;
       case "ftpserver": $s="FTP ����"; break;
       case "connect": $s="����"; break;
       case "bad connection": $s="$m1�� ����� ID $m2 �� ��й�ȣ $m3�� �����ϴ� �����߽��ϴ�."; break;
       case "cancel": $s="���"; break;
       case "rename": $s="�̸��ٲٱ� $m1"; break;
       case "renamed": $s="$m1 ��  $m2�� �̸��� �ٲ���ϴ�."; break;
       case "created directory": $s="$m1�� ���������� �����Ǿ����ϴ�."; break;
       case "failed to rename": $s="$m1 ��  $m2���� �ٲٴµ� �����߽��ϴ�."; break;
       case "rename from": $s="�̸� �ٲ�"; break;
       case "rename to": $s="����"; break;
       case "confirm delete": $s="$m1�� ���� �����Ͻðڽ��ϱ� ?"; break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>
