# soluble-mediatools  

[![PHP Version](http://img.shields.io/badge/php-7.1+-ff69b4.svg)](https://packagist.org/packages/soluble/mediatools)
[![Build Status](https://travis-ci.org/soluble-io/soluble-mediatools.svg?branch=master)](https://travis-ci.org/soluble-io/soluble-mediatools)
[![codecov](https://codecov.io/gh/soluble-io/soluble-mediatools/branch/master/graph/badge.svg)](https://codecov.io/gh/soluble-io/soluble-mediatools)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/soluble-io/soluble-mediatools/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/soluble-io/soluble-mediatools/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/soluble/mediatools/v/stable.svg)](https://packagist.org/packages/soluble/mediatools)
[![Total Downloads](https://poser.pugx.org/soluble/mediatools/downloads.png)](https://packagist.org/packages/soluble/mediatools)
[![License](https://poser.pugx.org/soluble/mediatools/license.png)](https://packagist.org/packages/soluble/mediatools)

Media tools: toolbox for media processing, video conversions, transcoding, transmuxing... Wrapper for ffmpeg/ffprobe. 

## Status  

> WIP

## Features

- [x] Services
  - [X] `VideoConvert` for video conversion.
    - [X] Transcoding, transmuxing
    - [ ] Video scaling (todo)
    - [ ] Time slicing (todo)
    - [X] Filters
        - [X] De-interlace (Yadif, Hqdn3d)
  - [X] `VideoThumb` for thumbnail creation.
    - [ ] Stabilize API first
  - [X] `VideoProbe` for getting infos about a video.
    - [ ] Stabilize API         

## Requirements

- PHP 7.1+
- FFmpeg 3.4+, 4.0+

## Examples

> The following examples assumes that the different services 
> (VideoConvert, VideoProbe, VideoThumb) are configured and 
> available. Generally the services will be available through
> a psr-11 compatible container: 
>
> ```php
> <?php
> use \Psr\Container\ContainerInterface;
> use \Soluble\MediaTools\{VideoConvert, VideoProbe};
> /**
>  * @var ContainerInterface $anyPsr11Container (zend-servicemanager, pimple...) 
>  * @var VideoConvert       $videoConvert video conversion service
>  * @var VideoProbe         $videoProbe   video probe service
>  */ 
> $videoConvert = $anyPsr11Container->get(VideoConvert::class);
> $videoProbe   = $anyPsr11Container->get(VideoProbe::class);
> 
> ```
>
> See [configuration](#configuration) section for more info.


### Conversion

> Basic conversion from `mov` to `mp4/h264/aac`

```php
<?php
use Soluble\MediaTools\{VideoConvert, VideoConvertParams, Exception as MediaToolsException};

$convertParams = (new VideoConvertParams)
            ->withVideoCodec('h264')   // Video codec 
            ->withAudioCodec('aac')
            ->withAudioBitrate('128k')            
            ->withStreamable(true)     // Add streamable options (movflags & faststart) 
            ->withCrf(24)              // Level of compression: better size / less visual quality  
            ->withPreset('medium')     // Optional: see presets  
            ->withOutputFormat('mp4'); // Optional: if not set, will be detected from output file extension.
    
try {
    /** @var VideoConvert $videoConvert video conversion service */
    $videoConvert->convert('/path/inputFile.mov', '/path/outputFile.mp4', $convertParams);
} catch(MediaToolsException\ProcessConversionException $e) {
    
    // The ffmpeg 'symfony' process encountered a failure...
    // To see the reason you can either use:
    echo $e->getMessage();                      // full message 
    echo $e->getProcess()->getErrorOutput();    // process stdErr
    echo $e->wasCausedByTimeout() ? 'timeout' : '';
    echo $e->wasCausedBySignal() ? 'interrupted' : '';
    
} catch (MediaToolsException\FileNotFoundException $e) {
     echo "The input file does not exists";    
}
    
   
``` 

> Example of conversion from `mov` to `webm/vp9/opus`


```php
<?php
use Soluble\MediaTools\{VideoConvert, VideoConvertParams, Exception as MediaToolsException};

// Optional, whether the source is interlaced ?
$videoFilters = $videoConvert->getDeintFilter($file);

$convertParams = (new VideoConvertParams())
                ->withVideoCodec('libvpx-vp9')
                ->withVideoBitrate('750k')
                ->withQuality('good')
                ->withCrf(33)
                ->withAudioCodec('libopus')
                ->withAudioBitrate('128k')
                /**
                 * It is recommended to allow up to 240 frames of video between keyframes (8 seconds for 30fps content).
                 * Keyframes are video frames which are self-sufficient; they don't rely upon any other frames to render
                 * but they tend to be larger than other frame types.
                 * For web and mobile playback, generous spacing between keyframes allows the encoder to choose the best
                 * placement of keyframes to maximize quality.
                 */
                ->withKeyframeSpacing(240)
                // Most of the current VP9 decoders use tile-based, multi-threaded decoding.
                // In order for the decoders to take advantage of multiple cores,
                // the encoder must set tile-columns and frame-parallel.
                ->withTileColumns(2)
                ->withFrameParallel(1)
                ->withSpeed(0)
                // Optional: Use videoprobe to be sure of color conversions if any needed
                // ->withPixFmt('yuv420p') 
                ->withOutputFormat('webm');


try {
    /** @var VideoConvert $videoConvert video conversion service */
    $videoConvert->convert('/path/inputFile.mov', '/path/outputFile.webm', $convertParams);
} catch(MediaToolsException\ProcessConversionException $e) {
    
    // The ffmpeg 'symfony' process encountered a failure...
    // To see the reason you can either use:
    echo $e->getMessage();                      // full message 
    echo $e->getProcess()->getErrorOutput();    // process stdErr
    echo $e->wasCausedByTimeout() ? 'timeout' : '';
    echo $e->wasCausedBySignal() ? 'interrupted' : '';
    
} catch (MediaToolsException\FileNotFoundException $e) {
     echo "The input file does not exists";    
}

``` 

## Configuration

### Usage with psr/container

> MediaTools provides factories that can be easily used with any for psr-11 compatible container.
> See the [ConfigProvider](./src/Config/ConfigProvider.php) class to get an idea of how 
> service registration can be achieved.  

Example 1: with zend-service-manager.

```php
<?php declare(strict_types=1);

use Soluble\MediaTools\{VideoConvert, VideoProbe, VideoThumb};
use Soluble\MediaTools\Config\ConfigProvider;
use Zend\ServiceManager\ServiceManager;

// Config options can be in a file, i.e: `require 'config/soluble-mediatools.global.php';`
// or set via dotenv...

$config = [
    'soluble-mediatools' => [
        /**
         * Binaries
         */
        'ffmpeg.binary'         => 'ffmpeg',   // or a complete path /opt/local/ffmpeg/bin/ffmpeg
        'ffprobe.binary'        => 'ffprobe',  // or a complete path /opt/local/ffmpeg/bin/ffprobe

        /**
         * Conversion options
         */
        'ffmpeg.conversion.threads'      => null,   // <null>: single thread; <0>: number of cores, <1+>: number of threads
        'ffmpeg.conversion.timeout'      => null,   // <null>: no timeout, <number>: number of seconds before timing-out
        'ffmpeg.conversion.idle_timeout' => 60,     // <null>: no idle timeout, <number>: number of seconds of inactivity before timing-out
        'ffmpeg.conversion.env'          => []      // An array of additional env vars to set when running the ffmpeg conversion process        
    ],    
];

// Service manager
$container = new ServiceManager(
                array_merge([
                    // In Zend\ServiceManager configuration will be set
                    // in 'services'.'config'. 
                    'services' => [
                        'config' => $config
                    ]],
                    // Here the factories
                    (new ConfigProvider())->getDependencies()
             ));

// Now whenever you want an instance of a service:

$videoProbe     = $container->get(VideoProbe::class);
$VideoConvert   = $container->get(VideoConvert::class);
$videoThumb     = $container->get(VideoThumb::class);

```
  
## Coding standards and interop

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 3 Logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)

