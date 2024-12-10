<?php

namespace modules\rugzak\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use modules\rugzak\traits\BillTrait;
use yii\filters\AccessControl;

class BillController extends Controller
{
    use BillTrait;

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Alleen ingelogde gebruikers
                    ],
                ],
                'denyCallback' => function () {
                    return $this->redirect('/authentication/login');
                },
            ],
        ]);
    }

    public function actionAddItem()
    {
        // get logged in user id
        $userId = Craft::$app->getUser()->getIdentity()->getId();

        // find existing open bill, if not found create a new one
        $entry = $this->findOrCreateBill($userId);

        // // update title
        // $entry->title = 'Openstaande pest';

        // // save the entry
        // Craft::$app->getElements()->saveElement($entry);
        // dd('test');

        // get the items from the request
        // $items = Craft::$app->getRequest()->getBodyParam('items');

        // as example some hardcoded items
        $items = [
            'id' => 23,
            'price' => 10,
        ];

        // Get the matrix field data
        $itemQuery = $entry->getFieldValue('bill_items');
        $existing_items = $itemQuery->all();

        $bill_items = [];
        foreach ($existing_items as $item) {
            $sortOrder[] = $item->id;
            $entries[$item->id] = [
                'type' => 'bill_items',
                'fields' => [
                    'adventure' => [$item->adventure->one() ? $item->adventure->one()->id : null],
                    'price' => $item->price->getAmount() 
                ]
            ];
        }
        $bill_items['sortOrder'] = $sortOrder;
        $bill_items['entries'] = $entries;


        // $entry->title = "Openstaande rekening hesp";
        // $entry->setFieldValue('bill_items', $bill_items);
        // Craft::$app->getElements()->saveElement($entry);

        dd($entry);
        


        return $this->asJson(['message' => 'Item toegevoegd!']);
    }

    private function findOrCreateBill($userId) {
        $entry = Entry::find()
            ->section('bills')
            ->bill_status('open')
            ->relatedTo([
                'targetElement' => $userId,
                'field' => 'bill_user',
            ])
            ->orderBy('dateCreated DESC')
            ->one();

        if (!$entry) {
            $entry = $this->createNewBill($userId);
        }

        return $entry;
    }

    private function createNewBill($userId)
    {
        $section = $this->getSectionByHandle('bills');
        $entryType = $this->getEntryType($section);

        $entry = new Entry();
        $entry->sectionId = $section->id;
        $entry->typeId = $entryType->id;
        $entry->authorId = $userId;
        $entry->enabled = true;
        $entry->title = 'Openstaande rekening';
        $entry->bill_status = 'open';
        $entry->bill_user = [$userId];
        Craft::$app->getElements()->saveElement($entry);

        return $entry;
    }

}