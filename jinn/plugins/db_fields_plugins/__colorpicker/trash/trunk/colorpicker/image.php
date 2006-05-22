<?
$x=$_GET['x'];
$y=$_GET['y'];
$im = ImageCreateFromJPEG("image.jpg");
$rgb = ImageColorAt($im, $x, $y);
$c = imagecolorsforindex($im, $rgb);

$str=sprintf("#%02x%02x%02x",$c["red"],$c["green"],$c["blue"]);
?>
<script language="javascript">
parent.document.getElementById("curColor").style.backgroundColor="<? print $str; ?>";
parent.sel2("<? print $str; ?>");
</script>