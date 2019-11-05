<?php
namespace timerec;


use core\utils\config\GlobalConfig;

class Listener{
    private $themeName = 'timerec-theme';

    public function onLoad($result, $args = []){
        if($args['theme'] == $this->themeName){
            $modFolder = GlobalConfig::instance()->getValue("modulesfolder");
            $result['path'] = $modFolder . 'timeRegister/deploy/theme/';
        }
        return $result;
    }

    public function onList($result, $args = []){
        $result['names'][] = $this->themeName;
        return $result;
    }
}