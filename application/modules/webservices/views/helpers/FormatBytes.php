<?php
class Zend_View_Helper_FormatBytes extends Zend_View_Helper_Abstract
{
    //http://php.net/manual/en/function.number-format.php
    public function formatBytes($a)
    {
        $unim = array("B","KB","MB","GB","TB","PB");
        $c = 0;
        while ($a>=1024) {
            $c++;
            $a = $a/1024;
        }
        return number_format($a,($c ? 2 : 0),".",",")." ".$unim[$c];
    }
}

