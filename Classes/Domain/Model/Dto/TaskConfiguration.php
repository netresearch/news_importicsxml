<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Domain\Model\Dto;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration of the import task.
 */
class TaskConfiguration
{
    /**
     * @var string
     */
    protected string $email = '';

    /**
     * @var string
     */
    protected string $path = '';

    /**
     * @var string
     */
    protected string $mapping = '';

    /**
     * @var string
     */
    protected string $format = '';

    /**
     * @var int
     */
    protected int $pid = 0;

    /**
     * @var bool
     */
    protected bool $persistAsExternalUrl = false;

    /**
     * @var bool
     */
    protected bool $cleanBeforeImport = false;

    /**
     * @var bool
     */
    protected bool $setSlug = false;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return TaskConfiguration
     */
    public function setEmail(string $email): TaskConfiguration
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return TaskConfiguration
     */
    public function setPath(string $path): TaskConfiguration
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getMapping(): string
    {
        return $this->mapping;
    }

    /**
     * @param string $mapping
     *
     * @return TaskConfiguration
     */
    public function setMapping(string $mapping): TaskConfiguration
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     *
     * @return TaskConfiguration
     */
    public function setFormat(string $format): TaskConfiguration
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     *
     * @return TaskConfiguration
     */
    public function setPid(int $pid): TaskConfiguration
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPersistAsExternalUrl(): bool
    {
        return $this->persistAsExternalUrl;
    }

    /**
     * @param bool $persistAsExternalUrl
     *
     * @return TaskConfiguration
     */
    public function setPersistAsExternalUrl(bool $persistAsExternalUrl): TaskConfiguration
    {
        $this->persistAsExternalUrl = $persistAsExternalUrl;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCleanBeforeImport(): bool
    {
        return $this->cleanBeforeImport;
    }

    /**
     * @param bool $cleanBeforeImport
     *
     * @return TaskConfiguration
     */
    public function setCleanBeforeImport(bool $cleanBeforeImport): TaskConfiguration
    {
        $this->cleanBeforeImport = $cleanBeforeImport;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSetSlug(): bool
    {
        return $this->setSlug;
    }

    /**
     * @param bool $setSlug
     *
     * @return TaskConfiguration
     */
    public function setSetSlug(bool $setSlug): TaskConfiguration
    {
        $this->setSlug = $setSlug;

        return $this;
    }

    /**
     * Split the configuration from multiline to array
     * 123:This is a category title
     * 345:And another one.
     *
     * @return array
     */
    public function getMappingConfigured(): array
    {
        $out   = [];
        $lines = GeneralUtility::trimExplode('|', $this->mapping, true);
        foreach ($lines as $line) {
            $split          = GeneralUtility::trimExplode(':', $line, true, 2);
            $out[$split[1]] = $split[0];
        }

        return $out;
    }
}
