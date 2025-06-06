<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Mapper;

use Exception;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractMapper
{
    /** @var Logger */
    protected $logger;

    /** @var SlugHelper */
    protected object $slugHelper;

    protected array $extensionConfiguration = [];

    public function __construct()
    {
        $this->logger     = GeneralUtility::makeInstance(LogManager::class)->getLogger(self::class);
        $fieldConfig      = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];
        $this->slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $fieldConfig);

        try {
            $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('news_importicsxml');
        } catch (Exception) {
            // do nothing
        }
    }

    protected function removeImportedRecordsFromPid(int $pid, string $importSource): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_news_domain_model_news');
        $connection->delete(
            'tx_news_domain_model_news',
            [
                'deleted'       => 0,
                'pid'           => $pid,
                'import_source' => $importSource,
            ]
        );
    }
}
