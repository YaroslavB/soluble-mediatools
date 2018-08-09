<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video\Config;

use Soluble\MediaTools\Video\DetectionService;
use Soluble\MediaTools\Video\DetectionServiceFactory;
use Soluble\MediaTools\Video\DetectionServiceInterface;
use Soluble\MediaTools\Video\VideoThumbGenerator;
use Soluble\MediaTools\Video\VideoThumbGeneratorFactory;
use Soluble\MediaTools\Video\VideoThumbGeneratorInterface;
use Soluble\MediaTools\Video\VideoConverter;
use Soluble\MediaTools\Video\VideoConverterFactory;
use Soluble\MediaTools\Video\VideoConverterInterface;
use Soluble\MediaTools\Video\VideoQuery;
use Soluble\MediaTools\Video\VideoQueryFactory;
use Soluble\MediaTools\Video\VideoQueryInterface;

class ConfigProvider
{
    /**
     * @return array<string, array<string,array<string,mixed>>>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * @return array<string, array<string,string>>
     */
    public function getDependencies(): array
    {
        return [
            'aliases'   => $this->getAliases(),
            'factories' => $this->getFactories(),
        ];
    }

    /**
     * Return concrete implementation aliases if needed.
     *
     * @return array<string, string>
     */
    public function getAliases(): array
    {
        return [
            // Configuration holders
            FFMpegConfig::class           => FFMpegConfigInterface::class,
            FFProbeConfig::class          => FFProbeConfigInterface::class,

            // Services
            VideoConverter::class         => VideoConverterInterface::class,
            VideoQuery::class             => VideoQueryInterface::class,
            DetectionService::class       => DetectionServiceInterface::class,
            VideoThumbGenerator::class           => VideoThumbGeneratorInterface::class,
        ];
    }

    /**
     * Return interface based factories.
     *
     * @return array<string, string>
     */
    public function getFactories(): array
    {
        return [
            // Configuration holders
            FFMpegConfigInterface::class   => FFMpegConfigFactory::class,
            FFProbeConfigInterface::class  => FFProbeConfigFactory::class,

            // Services classes
            VideoConverterInterface::class    => VideoConverterFactory::class,
            VideoQueryInterface::class        => VideoQueryFactory::class,
            DetectionServiceInterface::class  => DetectionServiceFactory::class,
            VideoThumbGeneratorInterface::class      => VideoThumbGeneratorFactory::class,

            // Logger
            //LoggerConfigInterface::class    => <Factory to create / too much choice>
        ];
    }

    /**
     * @return array<string, array<string,mixed>>
     */
    public function getDefaultConfiguration(): array
    {
        return require dirname(__DIR__, 3) . '/config/soluble-mediatools.config.php';
    }
}
