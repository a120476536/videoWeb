<?php

namespace common;

use DateTime;

class VideoLogUtils
{
    public static function info($message,$action="日志输出",$time = "默认时间"): void
    {
        
        $currentDateTime = new DateTime();
        $time = $currentDateTime->format('Y-m-d H:i:s');
        $lastOut = json_encode($message, JSON_UNESCAPED_UNICODE);
        var_dump('时间：'.$time.' ;动作：'.$action." ;内容：".$lastOut);
    }
    public static function warning($message,$action="日志输出",$time = "默认时间"): void
    {
        $currentDateTime = new DateTime();
        $time = $currentDateTime->format('Y-m-d H:i:s');
        $lastOut = json_encode($message, JSON_UNESCAPED_UNICODE);
        var_dump('警告--时间：'.$time.' ;动作：'.$action." ;内容：".$lastOut);
    }
}