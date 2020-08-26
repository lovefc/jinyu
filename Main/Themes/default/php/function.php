<?php

//判断是否是pjax请求  
function getNewTime(){  
    return array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX'];  
}