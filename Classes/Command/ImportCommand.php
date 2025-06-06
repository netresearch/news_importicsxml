<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Command;

use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use GeorgRinger\NewsImporticsxml\Jobs\ImportJob;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function chr;

/**
 * Class ImportCommand
 */
class ImportCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                $this->getLabel('path')
            )
            ->addOption(
                'pid',
                'p',
                InputOption::VALUE_REQUIRED,
                $this->getLabel('pid')
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                $this->getLabel('format')
            )
            ->addOption(
                'slug',
                's',
                InputOption::VALUE_NONE,
                $this->getLabel('slug')
            )
            ->addOption(
                'cleanBeforeImport',
                null,
                InputOption::VALUE_NONE,
                $this->getLabel('cleanBeforeImport')
            )
            ->addOption(
                'persistAsExternalUrl',
                null,
                InputOption::VALUE_NONE,
                $this->getLabel('persistAsExternalUrl')
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_OPTIONAL,
                $this->getLabel('email'),
                ''
            )
            ->addOption(
                'mapping',
                null,
                InputOption::VALUE_OPTIONAL,
                $this->getLabel('mapping'),
                ''
            )
            ->setDescription('Import of ICS and XML (RSS) into EXT:news');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws AspectNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        /** @var ImportJob $importJob */
        $importJob = GeneralUtility::makeInstance(ImportJob::class);
        $importJob
            ->setConfiguration($this->createConfiguration($input))
            ->run();

        return 0;
    }

    /**
     * @param InputInterface $input
     *
     * @return TaskConfiguration
     */
    protected function createConfiguration(InputInterface $input): TaskConfiguration
    {
        $configuration = new TaskConfiguration();
        $configuration
            ->setPath($input->getArgument('path'))
            ->setPid((int) $input->getOption('pid'))
            ->setFormat($input->getOption('format'))
            ->setCleanBeforeImport($input->getOption('cleanBeforeImport'))
            ->setPersistAsExternalUrl($input->getOption('persistAsExternalUrl'))
            ->setEmail($input->getOption('email'))
            ->setSetSlug($input->getOption('slug'));

        $mapping = $input->getOption('mapping');

        if ($mapping !== '') {
            $mapping = str_replace(
                '|',
                chr(10),
                $mapping
            );

            $configuration->setMapping($mapping);
        }

        return $configuration;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getLabel(string $key): string
    {
        return $this->getLanguageService()
            ->sL('LLL:EXT:news_importicsxml/Resources/Private/Language/locallang.xlf:' . $key);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
