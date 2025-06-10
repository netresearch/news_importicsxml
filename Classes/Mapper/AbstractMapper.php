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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractMapper.
 */
abstract class AbstractMapper implements MapperInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var ConnectionPool
     */
    protected ConnectionPool $connectionPool;

    /**
     * @var SlugHelper
     */
    protected SlugHelper $slugHelper;

    /**
     * @var array<mixed>
     */
    protected array $extensionConfiguration = [];

    /**
     * @param Context                $context
     * @param ConnectionPool         $connectionPool
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(
        Context $context,
        ConnectionPool $connectionPool,
        ExtensionConfiguration $extensionConfiguration,
    ) {
        $this->context        = $context;
        $this->connectionPool = $connectionPool;

        $fieldConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];

        $this->slugHelper = GeneralUtility::makeInstance(
            SlugHelper::class,
            'tx_news_domain_model_news',
            'path_segment',
            $fieldConfig
        );

        try {
            $this->extensionConfiguration = $extensionConfiguration
                ->get('news_importicsxml');
        } catch (Exception) {
            // do nothing
        }
    }

    /**
     * @param int    $pid
     * @param string $importSource
     *
     * @return void
     */
    protected function removeImportedRecordsFromPid(int $pid, string $importSource): void
    {
        $this->connectionPool
            ->getConnectionForTable('tx_news_domain_model_news')
            ->delete(
                'tx_news_domain_model_news',
                [
                    'deleted'       => 0,
                    'pid'           => $pid,
                    'import_source' => $importSource,
                ]
            );
    }

    /**
     * @param string $message
     */
    protected function logInfo(string $message): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->info($message);
        }
    }

    /**
     * @param string $message
     */
    protected function logWarning(string $message): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->warning($message);
        }
    }

    /**
     * @param string $message
     */
    protected function logAlert(string $message): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->alert($message);
        }
    }
}
