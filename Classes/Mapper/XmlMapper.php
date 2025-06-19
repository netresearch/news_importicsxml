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
use Laminas\Feed\Reader\Collection\Category;
use Laminas\Feed\Reader\Entry\EntryInterface;
use Laminas\Feed\Reader\Reader;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function in_array;
use function sprintf;
use function strlen;

/**
 * Class XmlMapper.
 */
class XmlMapper extends AbstractMapper
{
    /**
     * @param TaskConfiguration $configuration
     *
     * @return list<array<string, int|string|bool|array<string, mixed>>>
     *
     * @throws AspectNotFoundException
     */
    public function map(TaskConfiguration $configuration): array
    {
        if ($configuration->isCleanBeforeImport()) {
            $this->removeImportedRecordsFromPid(
                $configuration->getPid(),
                $this->getImportSource()
            );
        }

        $data = [];

        $feed   = Reader::import($configuration->getPath());
        $crdate = (int) $this->context->getPropertyFromAspect('date', 'timestamp');

        /** @var EntryInterface $item */
        foreach ($feed as $item) {
            $id = strlen($item->getId()) > 100 ? md5($item->getId()) : $item->getId();

            $singleItem = [
                'import_source' => $this->getImportSource(),
                'import_id'     => $id,
                'crdate'        => $crdate,
                'cruser_id'     => isset($GLOBALS['BE_USER'], $GLOBALS['BE_USER']->user) ? $GLOBALS['BE_USER']->user['uid'] : 0,
                'type'          => 0,
                'hidden'        => 0,
                'pid'           => $configuration->getPid(),
                'title'         => $item->getTitle(),
                'teaser'        => trim($item->getDescription() ?? ''),
                'bodytext'      => trim($this->cleanup($item->getContent() ?? '')),
                'author'        => $item->getAuthor() ?? '',
                'datetime'      => $item->getDateCreated()?->getTimestamp() ?? 0,
                'categories'    => $this->getCategories($item->getCategories(), $configuration),
                '_dynamicData'  => [
                    'reference'         => $item,
                    'news_importicsxml' => [
                        'importDate' => date('d.m.Y h:i:s', $crdate),
                        'feed'       => $configuration->getPath(),
                        'url'        => trim($item->getLink() ?? ''),
                        'guid'       => $item->getId() ?? '',
                    ],
                ],
            ];

            if ($item->getEnclosure() !== null) {
                $this->addRemoteFiles(
                    $singleItem,
                    $item->getEnclosure(),
                    $configuration->getPath()
                );
            }

            if ($configuration->isPersistAsExternalUrl()) {
                $singleItem['type']        = 2;
                $singleItem['externalurl'] = $item->getLink() ?? '';
            }

            if ($configuration->isSetSlug()) {
                $singleItem['generate_path_segment'] = true;
            }

            $data[] = $singleItem;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $singleItem
     * @param object               $enclosure
     * @param string               $xmlPath
     *
     * @return void
     */
    protected function addRemoteFiles(array &$singleItem, object $enclosure, string $xmlPath): void
    {
        $extensions = [
            'image/jpg'       => 'jpg',
            'image/jpeg'      => 'jpg',
            'image/gif'       => 'gif',
            'image/png'       => 'png',
            'application/pdf' => 'pdf',
        ];

        $targetPath = trim($this->extensionConfiguration['importPath'] ?? '', '/');
        $targetPath = $targetPath !== '' ? $targetPath : 'uploads/tx_newsimporticsxml';
        $targetPath = '/' . $targetPath . '/';

        $url      = $enclosure->url;
        $mimeType = $enclosure->type;

        if (($url !== '') && isset($extensions[$mimeType])) {
            $urlInfo  = parse_url($url);
            $fileInfo = pathinfo($urlInfo['path']);
            $path     = $targetPath . substr(md5($xmlPath), 0, 10) . $fileInfo['dirname'] . '/';

            GeneralUtility::mkdir_deep(Environment::getPublicPath() . $path);

            $file = $path . rawurldecode($fileInfo['basename']);

            if (is_file(Environment::getPublicPath() . '/' . $file)) {
                $status = true;
            } else {
                $content = GeneralUtility::getUrl($url);
                $status  = GeneralUtility::writeFile(Environment::getPublicPath() . '/' . $file, $content);
            }

            if ($status) {
                if (in_array($extensions[$mimeType], ['gif', 'jpeg', 'jpg', 'png'], true)) {
                    $singleItem['media'][] = [
                        'image'         => $file,
                        'showinpreview' => true,
                    ];
                } else {
                    $singleItem['related_files'][] = [
                        'file' => $file,
                    ];
                }
            }
        }
    }

    /**
     * @param Category          $categories
     * @param TaskConfiguration $configuration
     *
     * @return string[]
     */
    protected function getCategories(Category $categories, TaskConfiguration $configuration): array
    {
        $categoryIds    = [];
        $categoryTitles = [];

        if ($categories->count() > 0) {
            foreach ($categories->getValues() as $category) {
                $categoryTitles[] = $category;
            }
        }

        if ($categoryTitles !== []) {
            if ($configuration->getMapping() !== '') {
                $categoryMapping = $configuration->getMappingConfigured();

                foreach ($categoryTitles as $title) {
                    if (isset($categoryMapping[$title])) {
                        $categoryIds[] = $categoryMapping[$title];
                    } else {
                        $this->logWarning(
                            sprintf(
                                'Category mapping is missing for category "%s"',
                                $title
                            )
                        );
                    }
                }
            } else {
                $this->logInfo('Categories found during import but no mapping assigned in the task!');
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
        $search = [
            '<br />',
            '<br>',
            '<br/>',
            LF . LF,
        ];
        $replace = [
            LF,
            LF,
            LF,
            LF,
        ];

        return str_replace(
            $search,
            $replace,
            $content
        );
    }

    /**
     * @return string
     */
    public function getImportSource(): string
    {
        return 'newsimporticsxml_xml';
    }
}
