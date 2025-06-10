<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Jobs;

use GeorgRinger\News\Domain\Service\NewsImportService;
use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GeorgRinger\NewsImporticsxml\Jobs\ImportJob;
use GeorgRinger\NewsImporticsxml\Mapper\IcsMapper;
use GeorgRinger\NewsImporticsxml\Mapper\XmlMapper;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use UnexpectedValueException;

class ImportJobTest extends UnitTestCase
{
    /**
     * @var MockObject
     */
    protected MockObject $mockedJob;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedJob = $this->getAccessibleMock(
            ImportJob::class,
            ['import'],
            [
                $this->createMock(XmlMapper::class),
                $this->createMock(IcsMapper::class),
                $this->createMock(NewsImportService::class),
            ],
        );

        $this->mockedJob->_set(
            'logger',
            new NullLogger()
        );
    }

    #[Test]
    public function xmlMapperIsCalledWithXmlConfiguration(): void
    {
        $configuration = new TaskConfiguration();
        $configuration->setFormat('xml');

        $xmlMapper = $this->getAccessibleMock(
            XmlMapper::class,
            ['map'],
            [],
            '',
            false
        );

        $xmlMapper
            ->expects($this->once())
            ->method('map');

        $this->mockedJob->_set(
            'xmlMapper',
            $xmlMapper
        );

        $this->mockedJob
            ->setConfiguration($configuration)
            ->run();
    }

    #[Test]
    public function icsMapperIsCalledWithXmlConfiguration(): void
    {
        $configuration = new TaskConfiguration();
        $configuration->setFormat('ics');

        $icsMapper = $this->getAccessibleMock(
            IcsMapper::class,
            ['map'],
            [],
            '',
            false
        );

        $icsMapper
            ->expects($this->once())
            ->method('map');

        $this->mockedJob->_set(
            'icsMapper',
            $icsMapper
        );

        $this->mockedJob
            ->setConfiguration($configuration)
            ->run();
    }

    #[Test]
    public function nonSupportedMapperThrowsException(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $configuration = new TaskConfiguration();
        $configuration->setFormat('fo');

        $this->mockedJob
            ->setConfiguration($configuration)
            ->run();
    }
}
