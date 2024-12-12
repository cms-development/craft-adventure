<?php

namespace modules\rugzak\events;

use Craft;
use craft\db\Query;
use craft\elements\Entry;
use studioespresso\molliepayments\events\TransactionUpdateEvent;
use studioespresso\molliepayments\events\PaymentUpdateEvent;
use studioespresso\molliepayments\MolliePayments;
use yii\base\Event;
use studioespresso\molliepayments\elements\Payment;
use studioespresso\molliepayments\services\Transaction;

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
            }
        );

        Event::on(
            Transaction::class,
            MolliePayments::EVENT_AFTER_TRANSACTION_UPDATE,
            function (TransactionUpdateEvent $event) {
                // handle the event here
                Craft::info(['Transactie update']);
                Craft::info($event->transaction['id']);
                
                if($event->status == "paid") {
                    $transactionUId = $event->transaction['id'];
                    self::updateStash($transactionUId);
                }

            }
        );
    }

    private static function updateStash($transactionId) {
        //  $transactionId = "tr_UAdDeEc6k6";

        // Make DB query to get payment
        $transactionRecord = (new Query())
            ->select(['payment'])
            ->from('mollie_transactions')
            ->where(['id' => $transactionId])
            ->one();
        
        if ($transactionRecord) {
            $paymentId = $transactionRecord['payment'];
        } else {
            Craft::error('Transaction not found: ' . $transactionId, __METHOD__);
            return;
        }

        $payment = Payment::find()
            ->id($paymentId)
            ->one();
        
        if ($payment) {
            $stashId = $payment->stash;

            $stash = Entry::find()
            ->section('stash_section')
            ->uid($stashId)
            ->one();

            if ($stash) {
                // title is in this format: [OPENSTAAND] Stash voor somebody (5 items)
                // change OPENSTAAND to BETAALD
                $stash->title = str_replace('OPENSTAAND', 'BETAALD', $stash->title);
                $stash->stash_status = "paid";
            if (!Craft::$app->elements->saveElement($stash)) {
                Craft::error('Failed to save stash element: ' . $stashId, __METHOD__);
            }
            } else {
            Craft::error('Stash not found: ' . $stashId, __METHOD__);
            }
        } else {
            Craft::error('Payment not found: ' . $paymentId, __METHOD__);
        }
    }
}