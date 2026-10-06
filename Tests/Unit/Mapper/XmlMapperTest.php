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

namespace GeorgRinger\NewsImporticsxml\Tests\Unit\Mapper;

use GeorgRinger\NewsImporticsxml\Tests\Unit\Mapper\Fixtures\EnclosureXmlMapper;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

use function strlen;

class XmlMapperTest extends UnitTestCase
{
    private const string FEED = 'https://feed.example.org/news.xml';

    /**
     * Smallest byte sequence that is detected as a PNG image.
     */
    private const string PNG = "\x89PNG\r\n\x1a\n\x00\x00\x00\x0dIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15\xc4\x89";

    private const string JPEG = "\xff\xd8\xff\xe0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xff\xd9";

    private const string PDF = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n";

    private string $root = '';

    private string $publicPath = '';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->root       = Environment::getVarPath() . '/tests/enclosure-' . bin2hex(random_bytes(6));
        $this->publicPath = $this->root . '/public';

        GeneralUtility::mkdir_deep($this->publicPath);

        $this->testFilesToDelete[] = $this->root;
    }

    /**
     * @param array<string, mixed> $extensionConfiguration
     */
    private function createSubject(string|false $response, array $extensionConfiguration = []): EnclosureXmlMapper
    {
        $subject = EnclosureXmlMapper::create($response, $this->publicPath, $extensionConfiguration);
        $subject->setLogger(new NullLogger());

        return $subject;
    }

    /**
     * @return list<string> Paths of all files below the temporary root, relative to it
     */
    private function filesBelowRoot(): array
    {
        $files    = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $files[] = substr($file->getPathname(), strlen($this->root));
        }

        sort($files);

        return $files;
    }

    private function storedName(string $directory, string $stem, string $url, string $extension): string
    {
        return $directory . $stem . '_' . substr(md5($url), 0, 8) . '.' . $extension;
    }

    private function feedDirectory(): string
    {
        return '/uploads/tx_newsimporticsxml/' . substr(md5(self::FEED), 0, 10) . '/';
    }

    #[Test]
    public function imageEnclosureIsStoredInTheFeedFolderOfTheImportDirectory(): void
    {
        $url  = 'https://media.example.org/2026/10/photo.png';
        $item = $this->createSubject(self::PNG)->addEnclosure($url, 'image/png', self::FEED);

        $expected = $this->storedName($this->feedDirectory(), 'photo', $url, 'png');

        self::assertSame(
            [
                'media' => [
                    [
                        'image'         => $expected,
                        'showinpreview' => true,
                    ],
                ],
            ],
            $item
        );
        self::assertSame(['/public' . $expected], $this->filesBelowRoot());
        self::assertSame(self::PNG, file_get_contents($this->publicPath . $expected));
    }

    #[Test]
    public function pdfEnclosureIsStoredAsRelatedFile(): void
    {
        $url  = 'https://media.example.org/report.pdf';
        $item = $this->createSubject(self::PDF)->addEnclosure($url, 'application/pdf', self::FEED);

        $expected = $this->storedName($this->feedDirectory(), 'report', $url, 'pdf');

        self::assertSame(['related_files' => [['file' => $expected]]], $item);
        self::assertSame(['/public' . $expected], $this->filesBelowRoot());
    }

    #[Test]
    public function declaredTypeImageJpgIsAcceptedAsJpeg(): void
    {
        $url  = 'https://media.example.org/photo.jpg';
        $item = $this->createSubject(self::JPEG)->addEnclosure($url, 'image/jpg', self::FEED);

        $expected = $this->storedName($this->feedDirectory(), 'photo', $url, 'jpg');

        self::assertSame([['image' => $expected, 'showinpreview' => true]], $item['media'] ?? null);
        self::assertSame(['/public' . $expected], $this->filesBelowRoot());
    }

    #[Test]
    public function directorySegmentsOfTheEnclosureUrlDoNotChangeTheTargetFolder(): void
    {
        $url  = 'https://media.example.org/a/../../../../b%2F..%2F..%2Fphoto.png';
        $item = $this->createSubject(self::PNG)->addEnclosure($url, 'image/png', self::FEED);

        $expected = $this->storedName($this->feedDirectory(), 'photo', $url, 'png');

        self::assertSame([['image' => $expected, 'showinpreview' => true]], $item['media'] ?? null);
        self::assertSame(['/public' . $expected], $this->filesBelowRoot());
    }

    #[Test]
    public function storedFileNameEndsWithTheExtensionOfTheDetectedType(): void
    {
        $url  = 'https://media.example.org/photo.png.phtml';
        $item = $this->createSubject(self::PNG)->addEnclosure($url, 'image/png', self::FEED);

        $expected = $this->storedName($this->feedDirectory(), 'photo_png', $url, 'png');

        self::assertSame([['image' => $expected, 'showinpreview' => true]], $item['media'] ?? null);
        self::assertSame(['/public' . $expected], $this->filesBelowRoot());
    }

    #[Test]
    public function fileNameWithoutUsableCharactersGetsAGenericName(): void
    {
        $url  = 'https://media.example.org/%E2%80%A6.png';
        $item = $this->createSubject(self::PNG)->addEnclosure($url, 'image/png', self::FEED);

        $expected = $this->storedName($this->feedDirectory(), 'enclosure', $url, 'png');

        self::assertSame([['image' => $expected, 'showinpreview' => true]], $item['media'] ?? null);
    }

    #[Test]
    public function textContentDeclaredAsImageIsNotStored(): void
    {
        $item = $this->createSubject("<?php\necho 'text';\n")
            ->addEnclosure('https://media.example.org/photo.png', 'image/png', self::FEED);

        self::assertSame([], $item);
        self::assertSame([], $this->filesBelowRoot());
    }

    #[Test]
    public function contentOfAnotherAllowedTypeThanDeclaredIsNotStored(): void
    {
        $item = $this->createSubject(self::PDF)
            ->addEnclosure('https://media.example.org/photo.png', 'image/png', self::FEED);

        self::assertSame([], $item);
        self::assertSame([], $this->filesBelowRoot());
    }

    /**
     * @return array<string, array{string|false}>
     */
    public static function unusableResponses(): array
    {
        return [
            'empty body'      => [''],
            'failed download' => [false],
        ];
    }

    #[Test]
    #[DataProvider('unusableResponses')]
    public function unusableDownloadIsNotStored(string|false $response): void
    {
        $item = $this->createSubject($response)
            ->addEnclosure('https://media.example.org/photo.png', 'image/png', self::FEED);

        self::assertSame([], $item);
        self::assertSame([], $this->filesBelowRoot());
    }

    #[Test]
    public function enclosureOfAnotherDeclaredTypeIsNotFetched(): void
    {
        $subject = $this->createSubject(self::PNG);
        $item    = $subject->addEnclosure('https://media.example.org/page.html', 'text/html', self::FEED);

        self::assertSame([], $item);
        self::assertSame(0, $subject->fetchCount);
        self::assertSame([], $this->filesBelowRoot());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function urlsWithoutHttpScheme(): array
    {
        return [
            'file scheme'   => ['file:///var/data/photo.png'],
            'absolute path' => ['/var/data/photo.png'],
            'relative path' => ['data/photo.png'],
            'ftp scheme'    => ['ftp://media.example.org/photo.png'],
            'php stream'    => ['php://filter/resource=/var/data/photo.png'],
            'no scheme'     => ['//media.example.org/photo.png'],
        ];
    }

    #[Test]
    #[DataProvider('urlsWithoutHttpScheme')]
    public function enclosureIsFetchedOnlyOverHttpOrHttps(string $url): void
    {
        $subject = $this->createSubject(self::PNG);
        $item    = $subject->addEnclosure($url, 'image/png', self::FEED);

        self::assertSame([], $item);
        self::assertSame(0, $subject->fetchCount);
        self::assertSame([], $this->filesBelowRoot());
    }

    #[Test]
    public function upperCaseSchemeIsFetchedWithLowerCaseScheme(): void
    {
        $subject = $this->createSubject(self::PNG);
        $item    = $subject->addEnclosure('HTTPS://media.example.org/Photo.png', 'image/png', self::FEED);

        self::assertCount(1, (array) ($item['media'] ?? []));
        self::assertSame(['https://media.example.org/Photo.png'], $subject->fetchedUrls);
    }

    #[Test]
    public function configuredImportDirectoryIsUsed(): void
    {
        $url  = 'https://media.example.org/photo.png';
        $item = $this->createSubject(self::PNG, ['importPath' => '/fileadmin/feeds/'])
            ->addEnclosure($url, 'image/png', self::FEED);

        $expected = $this->storedName(
            '/fileadmin/feeds/' . substr(md5(self::FEED), 0, 10) . '/',
            'photo',
            $url,
            'png'
        );

        self::assertSame([['image' => $expected, 'showinpreview' => true]], $item['media'] ?? null);
        self::assertSame(['/public' . $expected], $this->filesBelowRoot());
    }

    #[Test]
    public function importDirectoryThatLeavesThePublicDirectoryIsNotUsed(): void
    {
        $subject = $this->createSubject(self::PNG, ['importPath' => '/uploads/../../outside/']);
        $item    = $subject->addEnclosure('https://media.example.org/photo.png', 'image/png', self::FEED);

        self::assertSame([], $item);
        self::assertSame(0, $subject->fetchCount);
        self::assertSame([], $this->filesBelowRoot());
    }

    #[Test]
    public function existingFileIsReusedWithoutFetchingItAgain(): void
    {
        $url      = 'https://media.example.org/photo.png';
        $expected = $this->storedName($this->feedDirectory(), 'photo', $url, 'png');

        GeneralUtility::mkdir_deep($this->publicPath . $this->feedDirectory());
        file_put_contents($this->publicPath . $expected, self::PNG);

        $subject = $this->createSubject(self::PNG);
        $item    = $subject->addEnclosure($url, 'image/png', self::FEED);

        self::assertSame([['image' => $expected, 'showinpreview' => true]], $item['media'] ?? null);
        self::assertSame(0, $subject->fetchCount);
    }
}
