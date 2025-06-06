<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

call_user_func(
    static function ($extKey) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1496812651] = [
            'nodeName' => 'json',
            'priority' => 40,
            'class'    => GeorgRinger\NewsImporticsxml\Hooks\Backend\Element\JsonElement::class,
        ];

        $GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = $extKey;

        spl_autoload_register(static function ($class) {
            if (str_starts_with($class, 'PicoFeed')) {
                $path = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('news_importicsxml') . 'Resources/Private/Contrib/picoFeed/lib/' . str_replace('\\',
                    '/', $class) . '.php';
                require_once $path;
            }
        });
    },
    'news_importicsxml'
);
