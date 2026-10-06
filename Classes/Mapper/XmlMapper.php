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

namespace GeorgRinger\NewsImporticsxml\Mapper;

use finfo;
use GeorgRinger\NewsImporticsxml\Domain\Model\Dto\TaskConfiguration;
use Laminas\Feed\Reader\Collection\Category;
use Laminas\Feed\Reader\Entry\Atom;
use Laminas\Feed\Reader\Entry\Rss;
use Laminas\Feed\Reader\Reader;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

use function in_array;
use function sprintf;
use function strlen;

/**
 * Class XmlMapper.
 */
class XmlMapper extends AbstractMapper
{
    /**
     * Content types of enclosures that are downloaded, with the extension of the stored file.
     *
     * @var array<string, string>
     */
    private const array ENCLOSURE_TYPES = [
        'image/jpeg'      => 'jpg',
        'image/gif'       => 'gif',
        'image/png'       => 'png',
        'application/pdf' => 'pdf',
    ];

    /**
     * Content types that feeds declare for one of the types above.
     *
     * @var array<string, string>
     */
    private const array ENCLOSURE_TYPE_ALIASES = [
        'image/jpg'   => 'image/jpeg',
        'image/pjpeg' => 'image/jpeg',
    ];

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

        /** @var Atom|Rss $item */
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
     * Downloads the enclosure of a feed entry into the import directory and adds it to the news item.
     *
     * Only http(s) URLs are fetched. The file is stored in a folder of the configured import directory that
     * belongs to the feed; its name is built from the last segment of the URL path, reduced to letters, digits,
     * "_" and "-", and its extension is taken from the content type detected in the downloaded data, which
     * has to match the type declared in the feed.
     *
     * @param array<string, mixed> $singleItem
     * @param object               $enclosure
     * @param string               $xmlPath
     *
     * @return void
     */
    protected function addRemoteFiles(array &$singleItem, object $enclosure, string $xmlPath): void
    {
        $url          = trim((string) ($enclosure->url ?? ''));
        $declaredType = strtolower(trim((string) ($enclosure->type ?? '')));
        $declaredType = self::ENCLOSURE_TYPE_ALIASES[$declaredType] ?? $declaredType;

        if (($url === '') || !isset(self::ENCLOSURE_TYPES[$declaredType])) {
            return;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (!in_array($scheme, ['http', 'https'], true)) {
            $this->logWarning(sprintf('Enclosure "%s" skipped: only http and https URLs are fetched', $url));

            return;
        }

        $directory = $this->getEnclosureDirectory($xmlPath);

        if ($directory === null) {
            $this->logWarning('Enclosures skipped: the configured import directory is not a folder of the public directory');

            return;
        }

        $extension    = self::ENCLOSURE_TYPES[$declaredType];
        $file         = $directory . $this->getEnclosureFileName($url) . '.' . $extension;
        $absoluteFile = $this->getPublicPath() . $file;

        if (!is_file($absoluteFile)) {
            $content = $this->fetchEnclosure($url);

            if (($content === false) || ($content === '')) {
                $this->logWarning(sprintf('Enclosure "%s" skipped: the download returned no content', $url));

                return;
            }

            $detectedType = (string) (new finfo(FILEINFO_MIME_TYPE))->buffer($content);

            if ((self::ENCLOSURE_TYPES[$detectedType] ?? null) !== $extension) {
                $this->logWarning(
                    sprintf(
                        'Enclosure "%s" skipped: the feed declares "%s", the downloaded content is "%s"',
                        $url,
                        $declaredType,
                        $detectedType
                    )
                );

                return;
            }

            GeneralUtility::mkdir_deep($this->getPublicPath() . $directory);

            if (!GeneralUtility::writeFile($absoluteFile, $content)) {
                $this->logWarning(sprintf('Enclosure "%s" skipped: the file "%s" could not be written', $url, $file));

                return;
            }
        }

        if ($extension === 'pdf') {
            $singleItem['related_files'][] = [
                'file' => $file,
            ];
        } else {
            $singleItem['media'][] = [
                'image'         => $file,
                'showinpreview' => true,
            ];
        }
    }

    /**
     * Returns the folder for the enclosures of a feed, relative to the public directory, with leading and
     * trailing slash, or NULL if the configured import directory is not a folder of the public directory.
     *
     * @param string $xmlPath
     */
    protected function getEnclosureDirectory(string $xmlPath): ?string
    {
        $importPath = trim((string) ($this->extensionConfiguration['importPath'] ?? ''), '/');
        $importPath = $importPath !== '' ? $importPath : 'uploads/tx_newsimporticsxml';

        $directory = '/' . $importPath . '/' . substr(md5($xmlPath), 0, 10) . '/';

        if (!GeneralUtility::validPathStr($directory) || str_contains($directory, '/./')) {
            return null;
        }

        $publicPath = PathUtility::getCanonicalPath($this->getPublicPath());
        $canonical  = PathUtility::getCanonicalPath($publicPath . $directory);

        if (!str_starts_with($canonical . '/', $publicPath . '/' . $importPath . '/')) {
            return null;
        }

        return $directory;
    }

    /**
     * Returns the file name (without extension) for an enclosure: the last segment of the URL path reduced to
     * letters, digits, "_" and "-", followed by a hash of the URL that keeps files with the same name apart.
     *
     * @param string $url
     *
     * @return string
     */
    protected function getEnclosureFileName(string $url): string
    {
        $segment = rawurldecode((string) parse_url($url, PHP_URL_PATH));
        $segment = basename(str_replace('\\', '/', $segment));

        $stem = pathinfo($segment, PATHINFO_FILENAME);
        $stem = trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '_', $stem), '_-');
        $stem = substr($stem, 0, 100);

        if ($stem === '') {
            $stem = 'enclosure';
        }

        return $stem . '_' . substr(md5($url), 0, 8);
    }

    /**
     * @param string $url
     */
    protected function fetchEnclosure(string $url): string|false
    {
        return GeneralUtility::getUrl($url);
    }

    /**
     * @return string
     */
    protected function getPublicPath(): string
    {
        return Environment::getPublicPath();
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
