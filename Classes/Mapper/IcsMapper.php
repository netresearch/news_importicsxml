<?php

/**
 * This file is part of the package georgringer/news-importicsxml.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GeorgRinger\NewsImporticsxml\Mapper;

use TYPO3\CMS\Core\Context\Context;
use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use ICal\ICal;
use RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IcsMapper extends AbstractMapper implements MapperInterface
{
    /** @var bool */
    protected $pathIsModified = false;

    /**
     * @param TaskConfiguration $configuration
     *
     * @return array
     */
    public function map(TaskConfiguration $configuration): array
    {
        if ($configuration->getCleanBeforeImport()) {
            $this->removeImportedRecordsFromPid($configuration->getPid(), $this->getImportSource());
        }

        $data = [];
        $path = $this->getFileContent($configuration);

        $idCount = [];

        require_once ExtensionManagementUtility::extPath('news_importicsxml') . 'Resources/Private/Contrib/ICal.php';
        require_once ExtensionManagementUtility::extPath('news_importicsxml') . 'Resources/Private/Contrib/Event.php';
        $iCalService = new ICal($path);
        $events      = $iCalService->events();

        foreach ($events as $event) {
            $id = strlen($event->uid) < 90 ? $event->uid : md5($event > uid);
            if (!isset($idCount[$id])) {
                $idCount[$id] = 1;
            } else {
                ++$idCount[$id];
            }
            $datetime = $iCalService->iCalDateToUnixTimestamp($event->dtstart ?? $event->dtstamp);
            if ($datetime === false) {
                $datetime = $iCalService->iCalDateToUnixTimestamp($event->dtstamp);
            }

            $singleItem = [
                'import_source' => $this->getImportSource(),
                'import_id'     => $id . '-' . $idCount[$id],
                'crdate'        => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
                'cruser_id'     => $GLOBALS['BE_USER'] ?? $GLOBALS['BE_USER']->user['uid'] ?? 0,
                'type'          => 0,
                'hidden'        => 0,
                'pid'           => $configuration->getPid(),
                'title'         => $this->cleanup((string) $event->summary),
                'bodytext'      => $this->cleanup((string) $event->description),
                'datetime'      => $datetime,
                'archive'       => (isset($event->dtend) ? $iCalService->iCalDateToUnixTimestamp($event->dtend) + 86400 : ''),
                'categories'    => $this->getCategories((array) ($event->categories_array ?? []), $configuration),
                '_dynamicData'  => [
                    'location'          => (isset($event->location) ? $event->location : ''),
                    'datetime_end'      => (isset($event->dtend) ? $iCalService->iCalDateToUnixTimestamp($event->dtend) : ''),
                    'reference'         => $event,
                    'news_importicsxml' => [
                        'importDate' => date('d.m.Y h:i:s', GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp')),
                        'feed'       => $configuration->getPath(),
                        'UID'        => $event->uid,
                        'VARIANT'    => $idCount[$id],
                        'LOCATION'   => (isset($event->location) ? $event->location : ''),
                        'DTSTART'    => $event->dtstart ?? '',
                        'DTSTAMP'    => $event->dtstamp ?? '',
                        'DTEND'      => $event->dtend ?? '',
                        'PRIORITY'   => $event->priority ?? '',
                        'SEQUENCE'   => $event->sequence,
                        'STATUS'     => $event->status ?? '',
                        'TRANSP'     => $event->transp ?? '',
                        'URL'        => $event->url ?? '',
                        'ATTACH'     => $event->attach ?? '',
                        'SUMMARY'    => $event->summary ?? '',
                    ],
                ],
            ];

            if ($configuration->isSetSlug()) {
                $singleItem['generate_path_segment'] = true;
            }

            $data[] = $singleItem;
        }

        if ($this->pathIsModified) {
            $success = unlink($path);
        }

        return $data;
    }

    /**
     * @param array             $categoryTitles
     * @param TaskConfiguration $configuration
     *
     * @return array
     */
    protected function getCategories(array $categoryTitles, TaskConfiguration $configuration): array
    {
        $categoryIds = [];
        if (!empty($categoryTitles)) {
            if (!$configuration->getMapping()) {
                $this->logger->info('Categories found during import but no mapping assigned in the task!');
            } else {
                $categoryMapping = $configuration->getMappingConfigured();

                foreach ($categoryTitles as $rawTitle) {
                    $splitTitle = GeneralUtility::trimExplode(',', $rawTitle, true, 0);

                    foreach ($splitTitle as $title) {
                        if (!isset($categoryMapping[$title])) {
                            $this->logger->warning(sprintf('Category mapping is missing for category "%s"', $title));
                        } else {
                            $categoryIds[] = $categoryMapping[$title];
                        }
                    }
                }
            }
        }

        return $categoryIds;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function cleanup(string $content): string
    {
        $search  = ['\\,', '\\n'];
        $replace = [',', chr(10)];

        return str_replace($search, $replace, $content);
    }

    /**
     * @param TaskConfiguration $configuration
     *
     * @return string
     */
    protected function getFileContent(TaskConfiguration $configuration): string
    {
        $path = $configuration->getPath();
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $content = $this->getContentOfFile($path);

            $temporaryCopyPath = Environment::getPublicPath() . '/typo3temp/' . md5($path . GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'));
            GeneralUtility::writeFileToTypo3tempDir($temporaryCopyPath, $content);
            $this->pathIsModified = true;
        } else {
            $temporaryCopyPath = Environment::getPublicPath() . '/' . $configuration->getPath();
        }

        if (!is_file($temporaryCopyPath)) {
            throw new RuntimeException(sprintf('The path "%s" does not contain a valid file', $temporaryCopyPath));
        }

        return $temporaryCopyPath;
    }

    protected function getContentOfFile(string $url): string
    {
        $response = GeneralUtility::getUrl($url);

        if (empty($response)) {
            $message = sprintf('URL "%s" returned an empty content!', $url);
            $this->logger->alert($message);
            throw new RuntimeException($message);
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getImportSource(): string
    {
        return 'newsimporticsxml_ics';
    }
}
