<?
$x=$_GET['imgimg_x'];
$y=$_GET['imgimg_y'];
#print_r($_GET);

$im = ImageCreateFromPNG($_GET['imgfile']);
#$im = ImageCreateFromPNG('image.png');
$rgb = ImageColorAt($im, $x, $y);
$c = imagecolorsforindex($im, $rgb);

$str=sprintf("#%02x%02x%02x",$c["red"],$c["green"],$c["blue"]);
?>
<script language="javascript">
//   alert("<?=$_GET['imgfile']?>");
   parent.document.getElementById("curColor").style.backgroundColor="<? print $str; ?>";
   parent.sel2("<? print $str; ?>");
</script>
