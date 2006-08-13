<?php if($this->popup):?>
<a href="javascript:<?=$this->popup?>"><img <?php if($this->size_back):?>width="150"<?php endif?> id="<?=$this->name?>" src="<?=$this->tmblink?>" alt="<?=$this->file_name?>" /></a>
<?php else:?>
<img id="<?=$this->name?>" src="<?=$this->imglink?>" alt="<?=$this->file_name?>" />
<?php endif ?>
<br/>
<a id="<?=$this->linkid?>" href="<?=$this->filelink?>"><span id="<?=$this->span_id?>"><?=$this->file_name?></span></a>
