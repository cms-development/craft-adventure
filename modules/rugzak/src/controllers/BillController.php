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

        // update title
        $entry->title = 'Openstaande test';


        // get the items from the request
        // $items = Craft::$app->getRequest()->getBodyParam('items');

        // as example some hardcoded items
        $items = [
            'id' => 23,
            'price' => 10,
        ];

        // Get the matrix field data
        $billItemsField = $entry->getFieldValue('bill_items');
        $billItems = $billItemsField->all();
        // $sortOrder = [];
        // foreach ($billItems as $billItem) {
        //     $sortOrder[] = $billItem->id;
        // }

        // // Maak een nieuw MatrixBlock aan
        // $newMatrixBlock = [
        //     'id' => 'new:1',  // Temporarily unique identifier for the new block
        //     'type' => 'bill_items',  // Ensure this matches the block type handle
        //     'sortOrder' => count($billItems) + 1,  // Append to the end of the list
        //     'fields' => [
        //         'adventure' => [23],  // Adventure related to the bill (replace with dynamic value)
        //         'price' => 100  // Price field (replace with dynamic value)
        //     ]
        // ];

        
        // $sortOrder = array_merge($sortOrder, [$newMatrixBlock['id']]);
        // $entries = $entry->bill_items->all();

        // // Voeg de nieuwe MatrixBlock toe aan de entries
        // $entries['new:1'] = $newMatrixBlock['fields'];

        // // Sla de entry op met de nieuwe gegevens
        // $entry->setFieldValue('bill_items', $entries);
        // $entry->setFieldValue('bill_items[sortOrder]', $sortOrder);  // Voeg sortOrder toe

        
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