<tr>
   <td valign="top" class="<?=$rowval?>"><?=$this->cval[label]?></td><td valign="top" class="<?=$rowval?>">
	  <?php if(!trim($this->set_val)) $checkeddef='checked="checked"'?>
	  <?php foreach($this->cval[radio_arr] as $radio_val=>$radio_label):?>
	  <?php 
		 unset($checked);
		 if($this->set_val==$radio_val) $checked='checked="checked"';
	  ?>
	  <input name="<?=$this->cval[fname]?>" type="radio" <?=$checkeddef?> <?=$checked?> value="<?=$radio_val?>"><?=$radio_label?><br/>
	  <?php unset($checkeddef);?>
	  <?php endforeach?>
</td></tr>
