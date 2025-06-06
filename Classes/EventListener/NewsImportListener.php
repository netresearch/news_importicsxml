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
use JsonException;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use function is_array;

/**
 * Persist dynamic data of import.
 */
class NewsImportListener
{
    /**
     * @throws JsonException
     */
    public function __invoke(NewsImportPostHydrateEvent $event): void
    {
        $importData = $event->getImportItem();

        if (
            isset($importData['_dynamicData']['news_importicsxml'])
            && is_array($importData['_dynamicData']['news_importicsxml'])
        ) {
DebuggerUtility::var_dump($event);
exit;
            $event
                ->getNews()
                ->setNewsImportData(
                    json_encode(
                        $importData['_dynamicData']['news_importicsxml'],
                        JSON_THROW_ON_ERROR
                    )
                );
        }
    }
}
