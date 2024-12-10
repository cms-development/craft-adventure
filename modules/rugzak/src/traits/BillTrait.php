<?php

namespace modules\rugzak\traits;

use Craft;
use craft\records\EntryType;
use craft\records\Section;

trait BillTrait
{
    public function getSectionByHandle($sectionHandle)
    {
        $section = Section::findOne(['handle' => $sectionHandle]);

        if (!$section) {
            throw new \Exception("Section not found: " . $sectionHandle);
        }

        return $section;
    }

    public function getEntryType($section)
    {
         // Verkrijg de projectconfig secties
        $sectionsConfig = Craft::$app->projectConfig->get('sections');
        
        // Haal de configuratie op voor de sectie op basis van de UID
        $sectionConfig = $sectionsConfig[$section->uid] ?? null;

        if (!$sectionConfig) {
            throw new \Exception("Section config not found for section UID: " . $section->uid);
        }

        // Controleer of er entryTypes zijn gedefinieerd voor de sectie
        $entryTypeUids = $sectionConfig['entryTypes'] ?? [];

        if (empty($entryTypeUids)) {
            throw new \Exception("No entry types found for section: " . $section->handle);
        }

        // Haal het eerste entryType UID uit de lijst van entry types
        $entryTypeUid = reset($entryTypeUids);
        $entryType = EntryType::find()->where(['uid' => $entryTypeUid])->one();

        if (!$entryType) {
            throw new \Exception("Entry type not found for UID: " . $entryTypeUid);
        }

        return $entryType;
    }

    public function calculateTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function applyDiscount($total, $discount)
    {
        return $total - ($total * ($discount / 100));
    }
}