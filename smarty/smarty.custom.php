<?php
include('smarty/Smarty.class.php');

class Smarty_Template extends Smarty {

    function Smarty_Template() {
        $this->Smarty();

        $this->template_dir = $_SERVER["DOCUMENT_ROOT"] .
                              "../smarty/templates/";
        $this->compile_dir  = $_SERVER["DOCUMENT_ROOT"] .
                              "../smarty/templates_c/";
        $this->config_dir   = $_SERVER["DOCUMENT_ROOT"] .
                              "../smarty/configs/";
        $this->cache_dir    = $_SERVER["DOCUMENT_ROOT"] .
                              "../smarty/cache/";

    }
}

?>
