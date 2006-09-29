Het is vandaag <?php echo($this->tday);?> <?php echo($this->tmonth);?> <?php echo($this->tyear);?><br>
<select name="<?php echo('DATE_DD'.$this->field_name);?>">
   <option value=""></option>
   <?php for ($d=1;$d <=31;$d++):?>
   <option value="<?php echo($d);?>" <?php if($d == $this->day){echo'selected="selected"';}?>><?php echo($d);?></option>
   <?php endfor?>
</select>

<select name="<?php echo('DATE_MM'.$this->field_name);?>">
   <?php foreach($this->months as $key => $val):?>
   <option value="<?php echo($key);?>" <?php if($key == $this->month){echo'selected="selected"';}?>><?php echo($val);?></option>
	 <?php endforeach?>
</select>

<select name="<?php echo('DATE_YY'.$this->field_name);?>">
   <option value=""></option>
   <?php for ($y=2000;$y <=2015;$y++):?>
   <option value="<?php echo($y);?>"<?php if($y == $this->year){echo'selected="selected"';}?>><?php echo($y);?></option>
   <?php endfor?>
</select>

<input type="hidden" name="<?php echo($this->field_name);?>" value="">
