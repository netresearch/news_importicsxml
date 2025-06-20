<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Mapper;

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;

/**
 * Interface MapperInterface.
 */
interface MapperInterface
{
    /**
     * @param TaskConfiguration $configuration
     *
     * @return list<array<string, int|string|bool|array<string, mixed>>>
     */
    public function map(TaskConfiguration $configuration): array;

    /**
     * Get the import source identifier.
     *
     * @return string
     */
    public function getImportSource(): string;
}
