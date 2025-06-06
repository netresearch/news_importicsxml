<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Domain\Model\Dto;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class TaskConfigurationTest extends UnitTestCase
{
    /**
     * @var TaskConfiguration
     */
    protected $instance;

    /**
     * Setup.
     */
    protected function setUp()
    {
        $this->instance = new TaskConfiguration();
    }

    /**
     * @test
     */
    public function emailCanBeSet(): void
    {
        $value = 'fo@bar.com';
        $this->instance->setEmail($value);
        $this->assertEquals($value, $this->instance->getEmail());
    }

    /**
     * @test
     */
    public function pathCanBeSet(): void
    {
        $value = 'fileadmin/123.xml';
        $this->instance->setPath($value);
        $this->assertEquals($value, $this->instance->getPath());
    }

    /**
     * @test
     */
    public function formatCanBeSet(): void
    {
        $value = 'xml';
        $this->instance->setFormat($value);
        $this->assertEquals($value, $this->instance->getFormat());
    }

    /**
     * @test
     */
    public function pidCanBeSet(): void
    {
        $value = '456';
        $this->instance->setPid($value);
        $this->assertEquals($value, $this->instance->getPid());
    }

    /**
     * @test
     */
    public function mappingCanBeSet(): void
    {
        $value = 'fo:bar';
        $this->instance->setMapping($value);
        $this->assertEquals($value, $this->instance->getMapping());
    }
}
