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

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Command;

use GeorgRinger\NewsImporticsxml\Command\ImportCommand;
use Override;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

use function dirname;

class ImportCommandTest extends UnitTestCase
{
    private const string LLL_PREFIX = 'LLL:EXT:news_importicsxml/Resources/Private/Language/locallang.xlf:';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Return the label reference unchanged, so the test sees which key the command asks for.
        $languageService = $this->createMock(LanguageService::class);
        $languageService
            ->method('sL')
            ->willReturnArgument(0);

        $GLOBALS['LANG'] = $languageService;
    }

    #[Override]
    protected function tearDown(): void
    {
        unset($GLOBALS['LANG']);

        parent::tearDown();
    }

    #[Test]
    public function everyArgumentAndOptionDescriptionIsDefinedInTheLanguageFile(): void
    {
        $definition = (new ImportCommand('news:importicsxml'))->getDefinition();

        $descriptions = [];

        foreach ($definition->getArguments() as $argument) {
            $descriptions[$argument->getName()] = $argument->getDescription();
        }

        foreach ($definition->getOptions() as $option) {
            $descriptions[$option->getName()] = $option->getDescription();
        }

        $labelIds = $this->getLabelIds();

        foreach ($descriptions as $name => $description) {
            self::assertStringStartsWith(self::LLL_PREFIX, $description, $name);

            $key = substr($description, strlen(self::LLL_PREFIX));

            self::assertContains(
                $key,
                $labelIds,
                sprintf('The description of "%s" refers to the missing label "%s"', $name, $key)
            );
        }
    }

    /**
     * @return list<string>
     */
    private function getLabelIds(): array
    {
        $xliff = simplexml_load_file(
            dirname(__DIR__, 3) . '/Resources/Private/Language/locallang.xlf'
        );

        self::assertNotFalse($xliff);

        $ids = [];

        $units = $xliff->xpath('//trans-unit');

        self::assertIsArray($units);

        foreach ($units as $unit) {
            $ids[] = (string) $unit['id'];
        }

        return $ids;
    }
}
