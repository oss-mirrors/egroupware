<?
require("connection.inc.php");


$im = imagecreatefrompng('palet4.png');
$p=4;

$width=imagesx($im);
$height=imagesy($im);

for($y=0;$y<$height;$y++){
	for($x=0;$x<$width;$x++){
		// get a color
		$color_index = imagecolorat($im, $x, $y);
		$c=imagecolorsforindex($im, $color_index);
		$arr[]=sprintf("#%02x%02x%02x",$c["red"],$c["green"],$c["blue"]);
	}
}
$arr=array_unique($arr);

$x=0;
foreach ($arr as $v) {
	print($x."-".$v."<br>");
	$x++;
	$query="INSERT INTO pallet_kleuren (pallet_namen_ID,Kleur) VALUES ($p,'$v')";
	$resultid=mysql_query($query)or die (mysql_error());
}




?> 

