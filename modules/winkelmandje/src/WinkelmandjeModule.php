<?php

namespace modules\winkelmandje;

use yii\base\BootstrapInterface;
use yii\base\Module;

class WinkelmandjeModule extends Module implements BootstrapInterface
{
    public function init()
    {
        parent::init();
        
        // dd("Yay, the Winkelmandje module is loaded!");
    }

    public function bootstrap($app)
    {
        
    }
}