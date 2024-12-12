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
                // log the event
                Craft::info(['Transactie update']);
                
                // check if the status is paid
                if($event->status == "paid") {
                    // obtain the transaction ID from the event
                    $transactionUId = $event->transaction['id'];

                    // finally, update the related stash
                    self::updateStash($transactionUId);
                }

            }
        );
    }

    /**
     * Updates the stash with the given transaction ID.
     *
     * @param string $transactionId The ID of the transaction to update the stash with.
     *
     * @return void
     */
    private static function updateStash($transactionId) {
        // get the payment ID from the transaction
        $transactionRecord = (new Query())
            ->select(['payment'])
            ->from('mollie_transactions')
            ->where(['id' => $transactionId])
            ->one();
        
        // check if the transaction was found
        if ($transactionRecord) {
            // get the payment ID
            $paymentId = $transactionRecord['payment'];
        } else {
            // log an error and return
            Craft::error('Transaction not found: ' . $transactionId, __METHOD__);
            return;
        }

        // find the payment
        $payment = Payment::find()
            ->id($paymentId)
            ->one();
        
        // check if the payment was found
        if (!$payment) {
            Craft::error('Payment not found: ' . $paymentId, __METHOD__);
            return;
        }

        // get the stash ID from the payment
        $stashId = $payment->stash;

        // find the stash
        $stash = Entry::find()
            ->section('stash_section')
            ->uid($stashId)
            ->one();

        // check if the stash was found
        if (!$stash) {
            Craft::error('Stash not found: ' . $stashId, __METHOD__);
            return;
        }

        // title is in this format: [OPENSTAAND] Stash voor somebody (5 items)
        // change OPENSTAAND to BETAALD
        $stash->title = str_replace('OPENSTAAND', 'BETAALD', $stash->title);
        $stash->stash_status = "paid";
        if (!Craft::$app->elements->saveElement($stash)) {
            Craft::error('Failed to save stash element: ' . $stashId, __METHOD__);
        }
    }
}