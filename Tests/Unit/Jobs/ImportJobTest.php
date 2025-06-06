<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Jobs;

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GeorgRinger\NewsImporticsxml\Jobs\ImportJob;
use GeorgRinger\NewsImporticsxml\Mapper\IcsMapper;
use GeorgRinger\NewsImporticsxml\Mapper\XmlMapper;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use UnexpectedValueException;

class ImportJobTest extends UnitTestCase
{
    /**
     * @var ImportJob
     */
    protected ImportJob $mockedJob;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockedJob = $this->getAccessibleMock(
            ImportJob::class,
            ['import'],
            [],
            '',
            false
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
