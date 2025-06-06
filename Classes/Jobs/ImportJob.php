<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Jobs;

use GeorgRinger\News\Domain\Service\NewsImportService;
use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GeorgRinger\NewsImporticsxml\Mapper\IcsMapper;
use GeorgRinger\NewsImporticsxml\Mapper\XmlMapper;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

/**
 * Base import handling.
 */
class ImportJob
{
    /**
     * @var TaskConfiguration
     */
    protected $configuration;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var XmlMapper
     */
    protected $xmlMapper;

    /**
     * @var IcsMapper
     */
    protected $icsMapper;

    /**
     * @var NewsImportService
     */
    protected $newsImportService;

    /**
     * ImportJob constructor.
     *
     * @param TaskConfiguration $configuration
     * @param XmlMapper         $xmlMapper
     * @param IcsMapper         $icsMapper
     * @param NewsImportService $newsImportService
     */
    public function __construct(
        XmlMapper $xmlMapper,
        IcsMapper $icsMapper,
        NewsImportService $newsImportService,
    ) {
        $this->logger            = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->xmlMapper         = $xmlMapper;
        $this->icsMapper         = $icsMapper;
        $this->newsImportService = $newsImportService;
    }

    /**
     * @param TaskConfiguration $configuration
     */
    public function setConfiguration(TaskConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * Import remote content.
     */
    public function run()
    {
        $this->logger->info(sprintf(
            'Starting import of "%s" (%s), reporting to "%s"',
            $this->configuration->getPath(),
            strtoupper($this->configuration->getFormat()),
            $this->configuration->getEmail()));

        switch (strtolower($this->configuration->getFormat())) {
            case 'xml':
                $data = $this->xmlMapper->map($this->configuration);
                break;
            case 'ics':
                $data = $this->icsMapper->map($this->configuration);
                break;
            default:
                $message = sprintf('Format "%s" is not supported!', $this->configuration->getFormat());
                $this->logger->critical($message);
                throw new UnexpectedValueException($message, 1527601575);
        }

        $this->import($data);
    }

    /**
     * @param array|null $data
     */
    protected function import(?array $data = null)
    {
        $this->logger->info(sprintf('Starting import of %s records', count($data)));
        $this->newsImportService->import($data);
    }
}
