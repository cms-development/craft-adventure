<?php

namespace modules\rugzak\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use modules\rugzak\traits\StashTrait;
use yii\filters\AccessControl;

class _BACKUP_ extends Controller
{
    use StashTrait;

    /**
     * This method is called before the action methods are run.
     * Bt adding the AccessControl behavior, we can ensure that only logged in users can access the methods in this controller.
     */
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
        $goodieId = Craft::$app->getRequest()->getRequiredParam('goodie');
        
        // get logged in user id
        $userId = Craft::$app->getUser()->getIdentity()->getId();

        // find existing open stash, if not found create a new one
        $entry = $this->findOrCreateStash($userId);


        // Get the matrix field data
        $itemQuery = $entry->getFieldValue('stash_items');
        $itemFieldId = $itemQuery->fieldId;
        $existing_items = $itemQuery->all();
        
        $stash_items = [];
        $sortOrder = [];
        foreach ($existing_items as $item) {
            $sortOrder[] = $item->id;
            // $entries[$item->id] = [
            //     'type' => 'stash_items',
            //     'fields' => [
            //         // 'title' => $item->title,
            //         'goodie' => [$item->goodie->one() ? $item->goodie->one()->id : null],
            //         'price' => $item->price->getAmount() 
            //     ]
            // ];
        }
        $stash_items['sortOrder'] = $sortOrder;

        // now add the new items
        // create new entry
        $newItem = new Entry(); // Maak een nieuw item aan
        $newItem->fieldId = $itemFieldId; // De ID van het matrix veld
        $newItem->typeId = 10; // De ID van het entry type
        $newItem->authorId = $userId; // De ID van de auteur
        $newItem->ownerId = $entry->id; // De ID van de eigenaar
        $newItem->enabled = true; // Zorg dat het item is ingeschakeld
        $newItem->title = 'Nieuw item' . time(); // Zorg dat de titel uniek is
        $newItem->setFieldValue('goodie', [$goodieId]); // Zorg dat 'adventure' overeenkomt met veldnaam
        $newItem->setFieldValue('price', 1500); // Zorg dat 'price' overeenkomt met veldnaam
        $newItem->siteId = Craft::$app->sites->getCurrentSite()->id; // Correcte site instellen
         
        Craft::$app->getElements()->saveElement($newItem);

        $stash_items['sortOrder'][] = $newItem->id;
        // $stash_items['entries'][$newItem->id] = ['type' => 'stash_items']; 

        // dd($stash_items);

        //$entry->title = "[OPENSTAAND] Stash voor " . Craft::$app->getUser()->getIdentity()->username . " (" . count($stash_items['sortOrder']) . " items)";
        $entry->stash_items = $stash_items;
        Craft::$app->getElements()->saveElement($entry);

        
        // redirect to the stash page
        return $this->redirect('/goodies');
    }

    private function findOrCreateStash($userId) {
        $entry = Entry::find()
            ->section('stash_section')
            ->stash_status('open')
            ->relatedTo([
                'targetElement' => $userId,
                'field' => 'stash_user',
            ])
            ->orderBy('dateCreated DESC')
            ->one();

        if (!$entry) {
            $entry = $this->createNewStash($userId);
        }

        return $entry;
    }

    private function createNewStash($userId)
    {
        $section = $this->getSectionByHandle('stash_section');
        $entryType = $this->getEntryType($section);

        $entry = new Entry();
        $entry->sectionId = $section->id;
        $entry->typeId = $entryType->id;
        $entry->authorId = $userId;
        $entry->enabled = true;
        $entry->title = '[NEW] Stash voor ' . Craft::$app->getUser()->getIdentity()->username;
        $entry->stash_status = 'open';
        $entry->stash_user = [$userId];
        Craft::$app->getElements()->saveElement($entry);

        return $entry;
    }

}