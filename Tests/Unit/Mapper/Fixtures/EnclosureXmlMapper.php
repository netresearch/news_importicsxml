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

use GeorgRinger\NewsImporticsxml\Mapper\XmlMapper;
use Override;
use ReflectionClass;

/**
 * XML mapper that answers enclosure downloads with a fixed body and writes below a given directory.
 */
final class EnclosureXmlMapper extends XmlMapper
{
    public int $fetchCount = 0;

    /**
     * @var list<string> URLs passed to fetchEnclosure()
     */
    public array $fetchedUrls = [];

    private string|false $response = false;

    private string $publicPath = '';

    /**
     * Creates the mapper without the constructor of AbstractMapper, which needs a bootstrapped TYPO3.
     *
     * @param string|false         $response               Body returned for every enclosure download
     * @param string               $publicPath             Directory used as the public directory
     * @param array<string, mixed> $extensionConfiguration
     */
    public static function create(string|false $response, string $publicPath, array $extensionConfiguration = []): self
    {
        $subject = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();

        $subject->response               = $response;
        $subject->publicPath             = $publicPath;
        $subject->extensionConfiguration = $extensionConfiguration;

        return $subject;
    }

    /**
     * @return array<string, mixed> The news item fields the enclosure added
     */
    public function addEnclosure(string $url, string $type, string $xmlPath): array
    {
        $item = [];

        $this->addRemoteFiles(
            $item,
            (object) [
                'url'  => $url,
                'type' => $type,
            ],
            $xmlPath
        );

        return $item;
    }

    #[Override]
    protected function fetchEnclosure(string $url): string|false
    {
        ++$this->fetchCount;
        $this->fetchedUrls[] = $url;

        return $this->response;
    }

    #[Override]
    protected function getPublicPath(): string
    {
        return $this->publicPath;
    }
}
