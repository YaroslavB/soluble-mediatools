<?php

declare(strict_types=1);

namespace Soluble\MediaTools;

use Soluble\MediaTools\Config\FFMpegConfigInterface;
use Soluble\MediaTools\Exception\FileNotFoundException;
use Soluble\MediaTools\Exception\UnsupportedParamException;
use Soluble\MediaTools\Exception\UnsupportedParamValueException;
use Soluble\MediaTools\Util\Assert\PathAssertionsTrait;
use Soluble\MediaTools\Video\Converter\FFMpegAdapter;
use Soluble\MediaTools\Video\Exception\ConversionExceptionInterface;
use Soluble\MediaTools\Video\Exception\ConversionProcessExceptionInterface;
use Soluble\MediaTools\Video\Exception\InvalidParamException;
use Soluble\MediaTools\Video\Exception\MissingInputFileException;
use Soluble\MediaTools\Video\Exception\ProcessFailedException;
use Soluble\MediaTools\Video\Exception\ProcessSignaledException;
use Soluble\MediaTools\Video\Exception\ProcessTimeOutException;
use Soluble\MediaTools\Video\Exception\RuntimeException;
use Soluble\MediaTools\Video\Filter\Type\VideoFilterInterface;
use Soluble\MediaTools\Video\SeekTime;
use Soluble\MediaTools\Video\ThumbServiceInterface;
use Symfony\Component\Process\Exception as SPException;
use Symfony\Component\Process\Process;

class VideoThumbService implements ThumbServiceInterface
{
    use PathAssertionsTrait;

    /** @var FFMpegConfigInterface */
    protected $ffmpegConfig;

    /** @var FFMpegAdapter */
    protected $adapter;

    public function __construct(FFMpegConfigInterface $ffmpegConfig)
    {
        $this->ffmpegConfig = $ffmpegConfig;
        $this->adapter      = new FFMpegAdapter($ffmpegConfig);
    }

    /**
     * Return ready-to-run symfony process object that you can use
     * to `run()` or `start()` programmatically. Useful if you want
     * handle the process your way...
     *
     * @see https://symfony.com/doc/current/components/process.html
     *
     * @throws UnsupportedParamException
     * @throws UnsupportedParamValueException
     */
    public function getSymfonyProcess(string $videoFile, string $thumbnailFile, ?SeekTime $time = null, ?VideoFilterInterface $videoFilter = null): Process
    {
        $params = (new VideoConversionParams());

        if ($time !== null) {
            // For performance reasons time seek must be
            // made at the beginning of options
            $params = $params->withSeekStart($time);
        }
        $params = $params->withVideoFrames(1);

        if ($videoFilter !== null) {
            $params = $params->withVideoFilter($videoFilter);
        }

        // Quality scale for the mjpeg encoder
        $params->withVideoQualityScale(2);

        $arguments = $this->adapter->getMappedConversionParams($params);
        $ffmpegCmd = $this->adapter->getCliCommand($arguments, $videoFile, $thumbnailFile);

        $process = new Process($ffmpegCmd);
        $process->setTimeout($this->ffmpegConfig->getTimeout());
        $process->setIdleTimeout($this->ffmpegConfig->getIdleTimeout());
        $process->setEnv($this->ffmpegConfig->getEnv());

        return $process;
    }

    /**
     * @throws ConversionExceptionInterface        Base exception class for conversion exceptions
     * @throws ConversionProcessExceptionInterface Base exception class for process conversion exceptions
     * @throws MissingInputFileException
     * @throws ProcessTimeOutException
     * @throws ProcessFailedException
     * @throws ProcessSignaledException
     * @throws RuntimeException
     * @throws InvalidParamException
     */
    public function makeThumbnail(string $videoFile, string $thumbnailFile, ?SeekTime $time = null, ?VideoFilterInterface $videoFilter = null, ?callable $callback = null): void
    {
        try {
            $this->ensureFileExists($videoFile);

            $process = $this->getSymfonyProcess($videoFile, $thumbnailFile, $time, $videoFilter);
            $process->mustRun($callback);
        } catch (FileNotFoundException $e) {
            throw new MissingInputFileException($e->getMessage());
        } catch (UnsupportedParamValueException | UnsupportedParamException $e) {
            throw new InvalidParamException($e->getMessage());
        } catch (SPException\ProcessTimedOutException $e) {
            throw new ProcessTimeOutException($e->getProcess(), $e);
        } catch (SPException\ProcessSignaledException $e) {
            throw new ProcessSignaledException($e->getProcess(), $e);
        } catch (SPException\ProcessFailedException $e) {
            throw new ProcessFailedException($e->getProcess(), $e);
        } catch (SPException\RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}
