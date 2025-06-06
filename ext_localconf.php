<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use GeorgRinger\NewsImporticsxml\Backend\Form\Element\JsonElement;

call_user_func(static function (): void {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1496812651] = [
        'nodeName' => 'json',
        'priority' => 40,
        'class'    => JsonElement::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = 'news_importicsxml';
});
