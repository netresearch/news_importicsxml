<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\EventListener;

use GeorgRinger\News\Event\NewsImportPostHydrateEvent;

/**
 * Persist dynamic data of import.
 */
class NewsImportListener
{
    public function __invoke(NewsImportPostHydrateEvent $event): void
    {
        $importData = $event->getImportItem();
        if (is_array($importData['_dynamicData']['news_importicsxml'] ?? null)) {
            $event->getNews()->setNewsImportData(json_encode($importData['_dynamicData']['news_importicsxml']));
        }
    }
}
