<?php



  function lang_email($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")

  {

    $message = strtolower($message);



    switch($message)

    {

       case "reply":		$s = "답장";		break;

       case "reply all":	$s = "모두에게 답장";	break;

       case "forward":		$s = "전달";		break;

       case "delete":		$s = "삭제";		break;

       case "previous":		$s = "이전으로";	break;

       case "next":			$s = "다음으로";		break;

       case "from":			$s = "보내는 사람";		break;

       case "to":			$s = "받을 사람";			break;

       case "cc":			$s = "참조";			break;

       case "files":		$s = "Files";		break;

       case "date":			$s = "날짜";		break;

       case "send":			$s = "보내기";		break;

       case "subject":		$s = "제목";		break;

       case "folder":		$s = "폴더";		break;

       case "size":			$s = "크기";		break;

       case "section":		$s = "섹션";		break;

       case "image":		$s = "이미지";		break;

       case "no subject":	$s = "제목없음";	break;

       case "compose":		$s = "작성";		break;

       case "message":		$s = "메세지";		break;

       case "messages":		$s = "메세지";	break;

       case "new message":	$s = "세 메세지";	break;

       case "undisclosed sender":	$s = "";	break;

       case "undisclosed recipients":	$s = "Undisclosed Recipients";	break;

       case "please select a message first":	$s = "메세지를 먼저 선택하세요.";	break;



       case "this folder is empty":	$s = "비어있는 폴더";	break;



       case "switch current folder to":	$s = "다음으로 현재 폴더 바꾸기";	break;

       case "move selected messages into":	$s = "선택한 메세지 다음으로 옮기기";	break;

       case "add to addressbook":	$s = "주소록에 추가";	break;



       case "1 message has been deleted":

	$s = "1개의 메세지가 삭제되었습니다.";		break;



       case "x messages have been deleted":

	$s = "$m1개의 메세지가 삭제되었습니다." ;		break;






       case "monitor":		$s = "모니터";		break;



       default: $s = "<b>*</b> ". $message;

    }

    return $s;

  }



?>

