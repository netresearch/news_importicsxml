<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Mapper;

use DateTimeImmutable;
use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GeorgRinger\NewsImporticsxml\Mapper\IcsMapper;
use GeorgRinger\NewsImporticsxml\Tests\Unit\Mapper\Fixtures\FixtureIcsMapper;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use stdClass;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class IcsMapperTest extends UnitTestCase
{
    private function createSubject(): IcsMapper
    {
        $context = new Context();
        $context->setAspect('date', new DateTimeAspect(new DateTimeImmutable('2026-10-01 08:00:00')));

        $subject = FixtureIcsMapper::create($context);
        $subject->setLogger(new NullLogger());

        return $subject;
    }

    private function createConfiguration(): TaskConfiguration
    {
        $configuration = new TaskConfiguration();
        $configuration->setPath('https://calendar.example.org/events.ics');
        $configuration->setPid(12);

        return $configuration;
    }

    #[Test]
    public function creatingUserIsTheUidOfTheLoggedInBackendUser(): void
    {
        $backendUser       = new stdClass();
        $backendUser->user = ['uid' => 7];

        $GLOBALS['BE_USER'] = $backendUser;

        $items = $this->createSubject()->map($this->createConfiguration());

        self::assertCount(1, $items);
        self::assertSame(7, $items[0]['cruser_id']);
    }

    #[Test]
    public function creatingUserIsZeroWithoutBackendUser(): void
    {
        unset($GLOBALS['BE_USER']);

        $items = $this->createSubject()->map($this->createConfiguration());

        self::assertCount(1, $items);
        self::assertSame(0, $items[0]['cruser_id']);
    }
}
