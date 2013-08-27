<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo $this->Data('Title'); ?></h1>
<div class="Info">
   <?php echo $this->Data('Description'); ?>
</div>
<div>
<?php
    echo $this->Form->Open();
    echo $this->Form->Errors();
?>
<div class="Configuration">
   <div class="ConfigurationForm">
    <ul>
    <li>
      <h2><?php echo T('Hello World Settings'); ?></h2>
      <?php echo $this->Form->Label('Hello World Link','LinkName'); ?>
    </li>
    <li>
      <?php
      echo $this->Form->TextBox('LinkName');
      ?>
    </li>
    <li>
      <?php echo $this->Form->Label('Link URI'); ?>
    </li>
    <li>
      <?php
      echo Url('/',TRUE);
      echo $this->Form->TextBox('URI');
      ?>
    </li>
    <li>
      <?php echo $this->Form->Label('Message'); ?>
    </li>
    <li>
      <?php
      echo $this->Form->TextBox('Message');
      ?>
    </li>
    <li>
      <?php
      echo $this->Form->Button('Save', array('class'=>'SmallButton', 'name'=>'Submit'));
      ?>
    </li>
    </ul>
   </div>
</div>
<?php
      echo $this->Form->Close();
?>
</div>
