<?php

include_once '../smarty2php.php';

SmartyToPHP::config(array(
    'template_path' => './tpl/'
));

SmartyToPHP::fly();