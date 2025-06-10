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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use UnexpectedValueException;

use function count;
use function sprintf;

/**
 * Base import handling.
 */
class ImportJob implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var TaskConfiguration
     */
    protected TaskConfiguration $configuration;

    /**
     * @var XmlMapper
     */
    protected XmlMapper $xmlMapper;

    /**
     * @var IcsMapper
     */
    protected IcsMapper $icsMapper;

    /**
     * @var NewsImportService
     */
    protected NewsImportService $newsImportService;

    /**
     * ImportJob constructor.
     *
     * @param XmlMapper         $xmlMapper
     * @param IcsMapper         $icsMapper
     * @param NewsImportService $newsImportService
     */
    public function __construct(
        XmlMapper $xmlMapper,
        IcsMapper $icsMapper,
        NewsImportService $newsImportService,
    ) {
        $this->xmlMapper         = $xmlMapper;
        $this->icsMapper         = $icsMapper;
        $this->newsImportService = $newsImportService;
    }

    /**
     * @param TaskConfiguration $configuration
     *
     * @return ImportJob
     */
    public function setConfiguration(TaskConfiguration $configuration): ImportJob
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Import remote content.
     *
     * @throws AspectNotFoundException
     */
    public function run(): void
    {
        $this->logInfo(
            sprintf(
                'Starting import of "%s" (%s), reporting to "%s"',
                $this->configuration->getPath(),
                strtoupper($this->configuration->getFormat()),
                $this->configuration->getEmail()
            )
        );

        switch (strtolower($this->configuration->getFormat())) {
            case 'xml':
                $data = $this->xmlMapper->map($this->configuration);
                break;

            case 'ics':
                $data = $this->icsMapper->map($this->configuration);
                break;

            default:
                $message = sprintf(
                    'Format "%s" is not supported!',
                    $this->configuration->getFormat()
                );

                $this->logCritical($message);

                throw new UnexpectedValueException(
                    $message,
                    1527601575
                );
        }

        $this->import($data);
    }

    /**
     * @param list<array<string, int|string|bool|array<string, mixed>>> $data
     */
    private function import(array $data): void
    {
        $this->logInfo(
            sprintf(
                'Starting import of %s records',
                count($data)
            )
        );

        $this->newsImportService->import($data);
    }

    /**
     * @param string $message
     */
    private function logInfo(string $message): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->info($message);
        }
    }

    /**
     * @param string $message
     */
    private function logCritical(string $message): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->critical($message);
        }
    }
}
