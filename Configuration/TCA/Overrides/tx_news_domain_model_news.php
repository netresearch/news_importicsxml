<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$fields = [
    'news_import_data' => [
        'exclude' => true,
        'label'   => 'LLL:EXT:news_importicsxml/Resources/Private/Language/locallang.xlf:tx_news_domain_model_news.news_import_data',
        'config'  => [
            'type'       => 'text',
            'cols'       => 60,
            'rows'       => 20,
            'readOnly'   => true,
            'renderType' => 'json',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('tx_news_domain_model_news', $fields);
ExtensionManagementUtility::addToAllTCAtypes('tx_news_domain_model_news', 'news_import_data');
