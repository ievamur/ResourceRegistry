<div id="pagetitle"><?php echo lang('userregisterform');?></div>

<?php
echo validation_errors('<p class="error">', '</p>');

if(!empty($message))
{
        echo $message;
}

