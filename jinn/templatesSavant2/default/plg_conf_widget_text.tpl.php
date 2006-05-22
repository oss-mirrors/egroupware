<tr>
   <td valign="top" class="<?=$rowval?>"><?=$this->cval[label]?></td>
   <td valign="top" class="<?=$rowval?>">
	  <?php if($this->cval['size']) $size='maxlength="'.$this->cval['size'].'"';?>
	  <input name="<?=$this->cval[fname]?>" <?=$size?> type="text" <?=$this->cval[extra_html]?> value="<?=$this->set_val?>" />
   </td>
</tr>

