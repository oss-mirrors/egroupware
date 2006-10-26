<?php
   $x=$_GET['imgimg_x'];
   $y=$_GET['imgimg_y'];

   $im = ImageCreateFromPNG($_GET['imgfile']);
   $rgb = ImageColorAt($im, $x, $y);
   $c = imagecolorsforindex($im, $rgb);

   $str=sprintf("#%02x%02x%02x",$c["red"],$c["green"],$c["blue"]);
?>
<script language="javascript">
   parent.document.getElementById("curColor").style.backgroundColor="<?php print $str; ?>";
   parent.sel2("<?php print $str; ?>");
</script>
