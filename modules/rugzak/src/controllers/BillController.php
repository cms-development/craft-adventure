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

        // as example some hardcoded items
        $adventure = [
            'adventure' => [17],
            'price' => "1500",
        ];

        // Get the matrix field data
        $itemQuery = $entry->getFieldValue('bill_items');
        $itemFieldId = $itemQuery->fieldId;
        $existing_items = $itemQuery->all();
        

        $bill_items = [];
        $sortOrder = [];
        $entries = [];
        foreach ($existing_items as $item) {
            // dd($item);
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

        // now add the new items
        // create new entry
        $newItem = new Entry();
        $newItem->fieldId = $itemFieldId;
        $newItem->typeId = 6;
        $newItem->authorId = $userId;
        $newItem->ownerId = $entry->id;
        $newItem->enabled = true;
        $newItem->title = 'Test item' . time();
        $newItem->slug = 'nieuw-item-' . time();
        $newItem->setFieldValue('adventure', [17]); // Zorg dat 'adventure' overeenkomt met veldnaam
        $newItem->setFieldValue('price', 1500); // Zorg dat 'price' overeenkomt met veldnaam
        $newItem->siteId = Craft::$app->sites->getCurrentSite()->id; // Correcte site instellen
         
        Craft::$app->getElements()->saveElement($newItem);

        $bill_items['sortOrder'][] = $newItem->id;
        $bill_items['entries'][$newItem->id] = [
            'type' => 'bill_items',
            'fields' => $adventure
        ];

        $entry->title = "[OPENSTAAND] Rekening voor " . Craft::$app->getUser()->getIdentity()->username . " (" . count($bill_items['sortOrder']) . " items)";
        $entry->bill_items = $bill_items;
        Craft::$app->getElements()->saveElement($entry);

        


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