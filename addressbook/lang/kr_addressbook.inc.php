<?php

  function lang_addressbook($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "address book":     $s = "�ּҷ�";          break;
       case "last name":        $s = "�̸�";            break;
       case "first name":       $s = "��";              break;
       case "e-mail":           $s = "E-Mail";          break;
       case "home phone":       $s = "����ȭ";          break;
       case "fax":              $s = "�ѽ�";            break;
       case "work phone":       $s = "������ȭ";        break;
       case "pager":            $s = "�߻�";            break;
       case "mobile":           $s = "�ڵ���";          break;
       case "other number":     $s = "��Ÿ��ȣ";    	break;
       case "street":           $s = "�ּ�";          	break;
       case "birthday":         $s = "����";        	break;
       case "city":             $s = "����";            break;
       case "state":            $s = "����";           	break;
       case "zip code":         $s = "�����ȣ";        break;
       case "notes":            $s = "��Ÿ";           	break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
