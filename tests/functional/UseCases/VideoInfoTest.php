<?php

declare(strict_types=1);

namespace MediaToolsTest\Functional\UseCases;

use MediaToolsTest\Functional\ConfigUtilTrait;
use PHPUnit\Framework\TestCase;
use Soluble\MediaTools\Exception\FileNotFoundException;
use Soluble\MediaTools\Video\InfoServiceInterface;

class VideoInfoTest extends TestCase
{
    use ConfigUtilTrait;

    /** @var InfoServiceInterface */
    protected $infoService;

    /** @var string */
    protected $baseDir;

    /** @var string */
    protected $videoFile;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->infoService = $this->getVideoInfoService();

        $this->baseDir      = dirname(__FILE__, 3);
        $this->videoFile    = "{$this->baseDir}/data/big_buck_bunny_low.m4v";
    }

    public function testGetInfo(): void
    {
        $videoInfo = $this->infoService->getMediaInfo($this->videoFile);
        self::assertEquals(61.533000, $videoInfo->getDuration());
    }

    public function testGetMEdiaInfoThrowsFileNotFoundException(): void
    {
        self::expectException(FileNotFoundException::class);
        $this->infoService->getMediaInfo('/path/path/does_not_exist.mp4');
    }
}
