<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video;

use Soluble\MediaTools\Common\Exception\InvalidArgumentException;

interface ConversionParamsInterface
{
    public const PARAM_VIDEO_CODEC         = 'VIDEO_CODEC';
    public const PARAM_VIDEO_BITRATE       = 'VIDEO_BITRATE';
    public const PARAM_VIDEO_MIN_BITRATE   = 'VIDEO_MIN_BITRATE';
    public const PARAM_VIDEO_MAX_BITRATE   = 'VIDEO_MAX_BITRATE';
    public const PARAM_VIDEO_QUALITY_SCALE = 'VIDEO_QUALITY_SCALE';
    public const PARAM_VIDEO_FILTER        = 'VIDEO_FILTER';
    public const PARAM_AUDIO_CODEC         = 'AUDIO_CODEC';
    public const PARAM_AUDIO_BITRATE       = 'AUDIO_BITRATE';
    public const PARAM_CRF                 = 'CRF';
    public const PARAM_PIX_FMT             = 'PIX_FMT';
    public const PARAM_PRESET              = 'PRESET';
    public const PARAM_TUNE                = 'TUNE';
    public const PARAM_STREAMABLE          = 'STREAMABLE';
    public const PARAM_QUALITY             = 'QUALITY';
    public const PARAM_OUTPUT_FORMAT       = 'OUTPUT_FORMAT';
    public const PARAM_FRAME_PARALLEL      = 'FRAME_PARALLEL';
    public const PARAM_TILE_COLUMNS        = 'TILE_COLUMNS';

    public const PARAM_SEEK_START        = 'SEEK_START';
    public const PARAM_SEEK_END          = 'SEEK_END';

    // Only for vp9
    public const PARAM_SPEED             = 'SPEED';
    public const PARAM_THREADS           = 'THREADS';
    public const PARAM_KEYFRAME_SPACING  = 'KEYFRAME_SPACING';

    // File Options
    public const PARAM_OVERWRITE        = 'OVERWRITE';

    public const PARAM_NOAUDIO           = 'NOAUDIO';
    public const PARAM_VIDEO_FRAMES      = 'VIDEO_FRAMES';

    /**
     * Built-in params.
     *
     * @var string[]
     */
    public const BUILTIN_PARAMS = [
        self::PARAM_VIDEO_QUALITY_SCALE,
        self::PARAM_VIDEO_CODEC,
        self::PARAM_VIDEO_BITRATE,
        self::PARAM_VIDEO_MIN_BITRATE,
        self::PARAM_VIDEO_MAX_BITRATE,
        self::PARAM_VIDEO_FILTER,
        self::PARAM_AUDIO_CODEC,
        self::PARAM_AUDIO_BITRATE,
        self::PARAM_CRF,
        self::PARAM_PIX_FMT,
        self::PARAM_PRESET,
        self::PARAM_TUNE,
        self::PARAM_STREAMABLE,
        self::PARAM_QUALITY,
        self::PARAM_OUTPUT_FORMAT,
        self::PARAM_FRAME_PARALLEL,
        self::PARAM_TILE_COLUMNS,
        self::PARAM_THREADS,
        self::PARAM_SPEED,
        self::PARAM_KEYFRAME_SPACING,
        self::PARAM_OVERWRITE,
        self::PARAM_NOAUDIO,
        self::PARAM_VIDEO_FRAMES,
        self::PARAM_SEEK_START,
        self::PARAM_SEEK_END,
    ];

    /**
     * Return the internal array holding params.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Test whether a param is built-in or valid.
     */
    public function isParamValid(string $paramName): bool;

    /**
     * Set a built-in param...
     *
     * @param string $paramName  a param that must exist in builtInParams
     * @param mixed  $paramValue
     *
     * @throws InvalidArgumentException in case of unsupported builtin param
     */
    public function withBuiltInParam(string $paramName, $paramValue): self;

    /**
     * @param mixed $defaultValue if param does not exists set this one
     *
     * @return mixed
     */
    public function getParam(string $paramName, $defaultValue = null);

    public function hasParam(string $paramName): bool;
}
