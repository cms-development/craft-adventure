<?php

namespace modules\rugzak;

use Craft;
use craft\elements\Entry;
use yii\base\BootstrapInterface;
use yii\base\Module;
use modules\rugzak\events\PaymentUpdate;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\services\Transaction;

class RugzakModule extends Module implements BootstrapInterface
{
    public function init()
    {
        parent::init();

        // Craft::info('Rugzak module geladen', __METHOD__);

        // Register Events
        PaymentUpdate::handle();
    }

    public function bootstrap($app)
    {


        /*
            The bootstrap method in a Yii module is typically used to perform actions 
            that need to be executed when the application is bootstrapping, 
            such as registering components, setting up event handlers, 
            or performing other initialization tasks.
            However, in many cases, the init method is sufficient for these purposes, 
            especially if the module does not need to interact with the application 
            at the very early stages of its lifecycle.
        */
    }
}