<?php

  function lang_tts($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "trouble ticket system":	$s = "에러리포트 시스템";	break;
       case "ticket":			$s = "작업";		break;
       case "prio":			$s = "중요도";		break;
       case "group":			$s = "그룹";		break;
       case "assigned to":		$s = "할당";	break;
       case "opened by":		$s = "작성자";	break;
       case "date opened":		$s = "작성일";	break;
       case "subject":			$s = "제목";		break;
       case "new ticket":		$s = "새 작업";	break;
       case "view all tickets":		$s = "모든 작업보기";	break;
       case "view only open tickets":	$s = "활성된 작업보기";	break;
       case "no tickets found":		$s = "작업이 없음";	break;
       case "status/date closed":	$s = "비활성된 상태/날짜";	break;
       case "add new ticket":		$s = "새 작업추가";	break;
       case "detail":			$s = "설명";		break;
       case "priority":			$s = "중요도";	break;
       case "add ticket":		$s = "새작업 추가";	break;
       case "clear form":		$s = "재작성";	break;
       case "no subject":		$s = "제목없음";	break;
       case "view job detail":		$s = "작업 내용보기";	break;
       case "assigned from":		$s = "작업자";	break;
       case "open date":		$s = "시작 날짜";	break;
       case "close date":		$s = "종료 날짜";	break;
       case "details":			$s = "설명";		break;
       case "additional notes":		$s = "내용 추가";	break;
       case "ok":			$s = "확인";		break;
       case "update":			$s = "업데이트";		break;
       case "close":			$s = "종료";		break;
       case "in progress":		$s = "진행중";	break;
       case "closed":			$s = "종료됨";		break;
       case "reopen":			$s = "재시작";		break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
