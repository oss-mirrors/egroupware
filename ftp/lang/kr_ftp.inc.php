<?php

  function lang_ftp($message, $m1 = "", $m2 = "", $m3 = "", $m4 = "")
  {
    $message = strtolower($message);

    switch($message)
    {
       case "relogin": $s = "로그아웃/재로그인"; break;
       case "cd": $s = "cd"; break;
       case "delete": $s = "삭제 $m1"; break;
       case "deleted": $s = "$m1가 삭제되었습니다."; break;
       case "failed to delete": $s = "$m1를 삭제하는데 에러가 발생하였습니다."; break;
       case "view": $s = "보기"; break;
       case "save": $s = "저장"; break;
       case "save to filemanager": $s = "파일메니저로 저장"; break;
       case "upload": $s = "업로드"; break;
       case "uploaded": $s = "$m1를 업로드 했습니다."; break;
       case "failed to upload": $s = "$m1를 업로드 실패"; break;
       case "create new directory": $s = "새 디렉토리 생성"; break;
       case "failed to mkdir": $s = "디렉토리 $m1 생성필패"; break;
       case "empty dirname": $s = "이름 없는 디렉토리를 만듭니다."; break;
       case "module name": $s = "FTP 클라이언트"; break;
       case "home": $s = "접속지"; break;
       case "username": $s="사용자이름"; break;
       case "password": $s="패스워드"; break;
       case "ftpserver": $s="FTP 서버"; break;
       case "connect": $s="접속"; break;
       case "bad connection": $s="$m1로 사용자 ID $m2 와 비밀번호 $m3로 접속하는 실패했습니다."; break;
       case "cancel": $s="취소"; break;
       case "rename": $s="이름바꾸기 $m1"; break;
       case "renamed": $s="$m1 을  $m2로 이름을 바꿨습니다."; break;
       case "created directory": $s="$m1가 성공적으로 생성되었습니다."; break;
       case "failed to rename": $s="$m1 을  $m2으로 바꾸는데 실패했습니다."; break;
       case "rename from": $s="이름 바꿈"; break;
       case "rename to": $s="으로"; break;
       case "confirm delete": $s="$m1를 정말 삭제하시겠습니까 ?"; break;

       default: $s = "<b>*</b> ". $message;
    }
    return $s;
  }
?>
