<?php $runonrecordbutton=$this->runonrecordbuttons;?>
<?php if($runonrecordbutton['iconfilepath']):?>
<a href="<?=$runonrecordbutton['runonrecordevent_link']?>"><img src="<?=$runonrecordbutton['iconfilepath']?>" alt="<?=($runonrecordbutton['eventlabel']?$runonrecordbutton['eventlabel']:$runonrecordbutton['name'])?>" title="<?=($runonrecordbutton['eventlabel']?$runonrecordbutton['eventlabel']:$runonrecordbutton['name'])?>" /></a>
<?php else:?>
<input style="background-color:#abd3ab" type="button" name="<?=($runonrecordbutton['eventlabel']?$runonrecordbutton['eventlabel']:$runonrecordbutton['name'])?>" value="<?=($runonrecordbutton['eventlabel']?$runonrecordbutton['eventlabel']:$runonrecordbutton['name'])?>" onclick="location.href='<?=$runonrecordbutton['runonrecordevent_link']?>'" />
<?php endif?>
