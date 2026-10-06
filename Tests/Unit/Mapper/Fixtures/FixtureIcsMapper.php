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

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Mapper\Fixtures;

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GeorgRinger\NewsImporticsxml\Mapper\IcsMapper;
use Override;
use ReflectionClass;
use TYPO3\CMS\Core\Context\Context;

/**
 * ICS mapper that reads the calendar from Fixtures/event.ics instead of the configured path.
 */
final class FixtureIcsMapper extends IcsMapper
{
    /**
     * Creates the mapper without the constructor of AbstractMapper, which needs a bootstrapped TYPO3.
     */
    public static function create(Context $context): self
    {
        $subject = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();

        $subject->context = $context;

        return $subject;
    }

    #[Override]
    protected function getFileContent(TaskConfiguration $configuration): string
    {
        return __DIR__ . '/event.ics';
    }
}
