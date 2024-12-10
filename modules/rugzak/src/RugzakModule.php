<?php

namespace modules\rugzak;

use Craft;
use modules\rugzak\middleware\LoginMiddleware;
use yii\base\BootstrapInterface;
use yii\base\Module;

class RugzakModule extends Module implements BootstrapInterface
{
    public function init()
    {
        parent::init();

        // Craft::info('Rugzak module geladen', __METHOD__);
    }

    public function bootstrap($app)
    {
        // magic goes here
    }
}