<tr>
<td valign="top" class="<?=$rowval?>"><?=$this->cval[label]?></td><td valign="top" class="<?=$rowval?>">
<?php foreach($this->radio_arr as $radio):?>
<?php 
   unset($checked);
   if($this->set_val==$radio) $checked='checked';
?>
<input name="<?=$this->cval[fname]?>" type="radio" <?=$checked?> value="<?=$radio?>"><?=$radio?><br/>
<?php endforeach?>
</td></tr>
