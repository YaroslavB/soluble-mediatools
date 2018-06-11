<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Filter\Video;

class EmptyVideoFilter implements VideoFilterInterface
{
    public function getFFMpegCliArgument(): string
    {
        return '';
    }

    public function getFFmpegCLIValue(): string
    {
        return '';
    }
}
