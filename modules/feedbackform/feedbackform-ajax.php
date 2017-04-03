<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

$feedbackform = Module::getInstanceByName('feedbackform');
echo $feedbackform->ajaxCall();