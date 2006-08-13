<td>
   <? //echo $this->thumb_image; ?>
   <table width="102" border="0" cellpadding="0" cellspacing="2">
	  <tr> 
		 <td align="center" class="imgBorder" onMouseOver="pviiClassNew(this,'imgBorderHover')" onMouseOut="pviiClassNew(this,'imgBorder')">
			<a href="javascript:void(0);" onClick="javascript:imageSelected('<? echo $this->img_url; ?>', <? echo $this->info[0];?>, <? echo $this->info[1]; ?>,'<? echo $this->file; ?>');"><img src="<? echo $this->thumb_image; ?>" alt="<? echo $this->file; ?> - <? echo $this->filesize; ?>" border="0"></a></td>
	  </tr>
	  <tr> 
		 <td>
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			   <tr> 
				  <td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
					 <a href="javascript:;" onClick="javascript:preview('<? echo $this->img_url; ?>', '<? echo $this->file; ?>', ' <? echo $this->filesize; ?>',<? echo $this->info[0].','.$this->info[1]; ?>);"><img src="img/edit_pencil.gif" width="15" height="15" border="0"></a></td>
				  <td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
					 <a href="iframe.dircontent.php?field=<?php echo($_GET['field']); ?>&curr_obj_id=<?=$_GET[curr_obj_id]?>&delFile=<? echo $this->file; ?>&dir=<? echo $this->newPath; ?>" onClick="return deleteImage('<? echo $this->file; ?>');"><img src="img/edit_trash.gif" width="15" height="15" border="0"></a></td>
				  <td width="98%" class="imgCaption"><? echo $this->info[0].'x'.$this->info[1]; ?> <? //echo $file_ext; ?></td>
			   </tr>
			</table>
		 </td>
	  </tr>
   </table>
</td>
