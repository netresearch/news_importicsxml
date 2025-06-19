<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

$EM_CONF['news_importicsxml'] = [
    'title'          => 'Import of ICS & XML to EXT:news',
    'description'    => 'Versatile news import from ICS + XML (local files or remote URLs) including images and category mapping',
    'category'       => 'backend',
    'author'         => 'Georg Ringer, Rico Sonntag',
    'author_email'   => 'mail@ringer.it, rico.sonntag@netresearch.de',
    'author_company' => 'Ringer IT, Netresearch DTT GmbH',
    'state'          => 'stable',
    'version'        => '7.0.0',
    'constraints'    => [
        'depends' => [
            'typo3' => '13.4.0-13.99.99',
            'news'  => '12.0.0-12.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
