<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video\Filter;

use Soluble\MediaTools\Video\Filter\Type\FFMpegVideoFilterInterface;
use Soluble\MediaTools\Video\Filter\Type\VideoDeinterlacerInterface;

class YadifVideoFilter implements FFMpegVideoFilterInterface, VideoDeinterlacerInterface
{
    public const DEFAULT_MODE   = 0;
    public const DEFAULT_PARITY = -1;
    public const DEFAULT_DEINT  = 0;

    /** @var array<string, int> */
    protected $options = [
        'mode'   => self::DEFAULT_MODE,
        'parity' => self::DEFAULT_PARITY,
        'deint'  => self::DEFAULT_DEINT,
    ];

    /**
     * @param int $mode   The interlacing mode to adopt (0, send_frame Output one frame for each frame)
     * @param int $parity default=-1 Enable automatic detection of field parity. 0:
     * @param int $deint  Specify which frames to deinterlace (0: all - Deinterlace all frames.)
     */
    public function __construct(int $mode = self::DEFAULT_MODE, int $parity = self::DEFAULT_PARITY, int $deint = self::DEFAULT_DEINT)
    {
        $this->options = [
            'mode'   => $mode,
            'parity' => $parity,
            'deint'  => $deint,
        ];
    }

    public function getFFmpegCLIValue(): string
    {
        $yadifArg = sprintf(
            'yadif=mode=%s:parity=%s:deint=%s',
            $this->options['mode'],
            $this->options['parity'],
            $this->options['deint']
        );

        return $yadifArg;
    }
}
