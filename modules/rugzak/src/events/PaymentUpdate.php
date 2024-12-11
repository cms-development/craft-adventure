<?php

namespace modules\rugzak\events;

use Craft;
use craft\elements\Entry;
use studioespresso\molliepayments\events\TransactionUpdateEvent;
use studioespresso\molliepayments\events\PaymentUpdateEvent;
use studioespresso\molliepayments\MolliePayments;
use studioespresso\molliepayments\services\Payment;
use studioespresso\molliepayments\services\Transaction;
use Yii;
use yii\base\Event;

/**
 * PaymentUpdate class.
 */
class PaymentUpdate {

    public static function handle() {
        Event::on(
            Payment::class,
            MolliePayments::EVENT_BEFORE_PAYMENT_SAVE,
            function (PaymentUpdateEvent $event) {
                // handle the event here
                
                // Craft::info(['Betaling update']);
                // Craft::info(json_encode($event));
                // Craft::info([$event->isNew]);   
                // Craft::info([$event->payment]);   
                // Craft::info([$event]);   
            }
        );

        Event::on(
            Transaction::class,
            MolliePayments::EVENT_AFTER_TRANSACTION_UPDATE,
            function (TransactionUpdateEvent $event) {
                // handle the event here
                Craft::info(['Transactie update']);
                Craft::info($event->transaction['uid']);
                Craft::info($event->status);
                
                if($event->status == "paid") {
                    $transactionId = $event->transaction['uid'];
                    
                    // $transactionId = $event->transaction['uid'];
                    $uid = "b35c3124-c8b2-473d-9ab2-bc05ada6bf5d";
                    $transaction = Entry::find()
                        ->section('mollie-payments')
                        // ->uid($uid)
                        ->one();

                    $payment = Payment::find()
                        ->uid($uid)
                        ->one();
                    $stashId = $payment->stash;

                    $stash = Entry::find()
                        ->section('stash_section')
                        ->uid($stashId)
                        ->one();

                    $stash->stash_status = "paid";
                    Craft::$app->elements->saveElement($stash);
                }

            }
        );
    }
}