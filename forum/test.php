<?

  $phpgw_flags["currentapp"] = "forum";
  include("../header.inc.php");

function check_perm($perm) {
 global $phpgw_info;
 $counter = 1;
 while($phpgw_info["user"]["app_perms"][$counter]) {
	if($phpgw_info["user"]["app_perms"][$counter] == $perm ) 
		return 1;
	$counter++;
 }
 return 0;
}


print $phpgw->session->loginid ."<br>\n";
print $phpgw->groups->sql_search() ."<br>\n";
print $phpgw->session->lpw_change ."<br>\n";
print $phpgw->permissions->check_permission_type("admin");

//if($phpgw->permissions->check_permission
print "<br>: ".$phpgw_info["user"]["app_perms"][1] ."<br>";
// = "tts") print "ad";
 print check_perm("");



$dattim = date("Y-m-d H:i:s",time());
print $dd;


$forums = array("1"=>"1","10"=>"5","8"=>"5","7"=>"6","6"=>"1","5"=>"1");
asort ($forums, SORT_NUMERIC);
reset ($forums);
while(list($key,$val) = each($forums)) {
	print "$key = $val<br>\n";
}





?>

