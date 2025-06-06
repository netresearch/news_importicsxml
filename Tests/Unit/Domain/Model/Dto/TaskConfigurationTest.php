<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Domain\Model\Dto;

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TaskConfigurationTest extends UnitTestCase
{
    /**
     * @var TaskConfiguration
     */
    protected TaskConfiguration $instance;

    /**
     * Setup.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new TaskConfiguration();
    }

    #[Test]
    public function emailCanBeSet(): void
    {
        $value = 'fo@bar.com';
        $this->instance->setEmail($value);

        self::assertEquals($value, $this->instance->getEmail());
    }

    #[Test]
    public function pathCanBeSet(): void
    {
        $value = 'fileadmin/123.xml';
        $this->instance->setPath($value);

        self::assertEquals($value, $this->instance->getPath());
    }

    #[Test]
    public function formatCanBeSet(): void
    {
        $value = 'xml';
        $this->instance->setFormat($value);

        self::assertEquals($value, $this->instance->getFormat());
    }

    #[Test]
    public function pidCanBeSet(): void
    {
        $value = 456;
        $this->instance->setPid($value);

        self::assertEquals($value, $this->instance->getPid());
    }

    #[Test]
    public function mappingCanBeSet(): void
    {
        $value = 'fo:bar';
        $this->instance->setMapping($value);

        self::assertEquals($value, $this->instance->getMapping());
    }
}
