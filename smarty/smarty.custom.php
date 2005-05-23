<?php
include('smarty/Smarty.class.php');

class Smarty_Template extends Smarty {

    function Smarty_Template() {
        $this->Smarty();

        $this->template_dir = '/usr/local/projects/mfile/smarty/templates/';
        $this->compile_dir  = '/usr/local/projects/mfile/smarty/templates_c/';
        $this->config_dir   = '/usr/local/projects/mfile/smarty/configs/';
        $this->cache_dir    = '/usr/local/projects/mfile/smarty/cache/';
    }
}

?>
