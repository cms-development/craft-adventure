<?php
/**
 * Site URL Rules
 *
 * You can define custom site URL rules here, which Craft will check in addition
 * to routes defined in Settings → Routes.
 *
 * Read all about Craft’s routing behavior, here:
 * https://craftcms.com/docs/4.x/routing.html
 */

return [
    [
        'pattern' => 'rugzak/add-item',
        'route' => 'rugzak-module/bill/add-item',
        'verb' => ['POST', 'GET'],
    ],
    [
        'pattern' => 'rugzak/remove-item/<id:\d+>',
        'route' => 'rugzak-module/bill/remove-item',
        'verb' => 'POST',
    ],
];
