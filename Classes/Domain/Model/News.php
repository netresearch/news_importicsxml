<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Domain\Model;

/**
 * Class News
 */
class News extends \GeorgRinger\News\Domain\Model\News
{
    /**
     * @var string
     */
    protected string $newsImportData = '';

    /**
     * @return string
     */
    public function getNewsImportData(): string
    {
        return $this->newsImportData;
    }

    /**
     * @param string $newsImportData
     */
    public function setNewsImportData(string $newsImportData): void
    {
        $this->newsImportData = $newsImportData;
    }
}
