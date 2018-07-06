<?php

declare(strict_types=1);

namespace MediaToolsTest\Recipes;

use MediaToolsTest\TestUtilTrait;
use PHPUnit\Framework\TestCase;
use Soluble\MediaTools\Exception\FileNotFoundException;
use Soluble\MediaTools\Video\ThumbServiceInterface;

class VideoThumbnailingTest extends TestCase
{
    use TestUtilTrait;

    /** @var ThumbServiceInterface */
    protected $thumbService;

    /** @var string */
    protected $baseDir;

    /** @var string */
    protected $outputDir;

    /** @var string */
    protected $videoFile;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->thumbService = $this->getVideoThumbService();

        $this->baseDir      = dirname(__FILE__, 3);
        $this->outputDir    = "{$this->baseDir}/output";
        $this->videoFile    = "{$this->baseDir}/data/big_buck_bunny_low.m4v";
    }

    public function testMakeThumbnail(): void
    {
        $outputFile = $this->outputDir . '/testMakeThumbnail.jpg';
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }
        $this->thumbService->makeThumbnail(
            $this->videoFile,
            $outputFile,
            0.2
        );
        self::assertFileExists($outputFile);
        self::assertGreaterThan(0, filesize($outputFile));
        unlink($outputFile);
    }

    public function testMakeThumbnailThrowsFileNotFoundException(): void
    {
        self::expectException(FileNotFoundException::class);
        $this->thumbService->makeThumbnail('/path/path/does_not_exist.mp4', '');
    }
}
