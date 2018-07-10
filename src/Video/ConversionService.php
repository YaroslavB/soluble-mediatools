<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video;

use Soluble\MediaTools\Common\Assert\PathAssertionsTrait;
use Soluble\MediaTools\Common\Exception\FileNotFoundException;
use Soluble\MediaTools\Common\Exception\UnsupportedParamException;
use Soluble\MediaTools\Common\Exception\UnsupportedParamValueException;
use Soluble\MediaTools\Common\Process\ProcessParamsInterface;
use Soluble\MediaTools\Config\FFMpegConfigInterface;
use Soluble\MediaTools\Video\Adapter\FFMpegAdapter;
use Soluble\MediaTools\Video\Exception\ConversionExceptionInterface;
use Soluble\MediaTools\Video\Exception\ConversionProcessExceptionInterface;
use Soluble\MediaTools\Video\Exception\InvalidParamException;
use Soluble\MediaTools\Video\Exception\MissingInputFileException;
use Soluble\MediaTools\Video\Exception\ProcessFailedException;
use Soluble\MediaTools\Video\Exception\ProcessSignaledException;
use Soluble\MediaTools\Video\Exception\ProcessTimedOutException;
use Soluble\MediaTools\Video\Exception\RuntimeException;
use Symfony\Component\Process\Exception as SPException;
use Symfony\Component\Process\Process;

class ConversionService implements ConversionServiceInterface
{
    use PathAssertionsTrait;

    /** @var ProcessParamsInterface */
    protected $processParams;

    /** @var FFMpegAdapter */
    protected $adapter;

    public function __construct(FFMpegConfigInterface $ffmpegConfig)
    {
        $this->adapter       = new FFMpegAdapter($ffmpegConfig);
        $this->processParams = $ffmpegConfig;
    }

    /**
     * Return ready-to-run symfony process object that you can use
     * to `run()` or `start()` programmatically. Useful if you want to make
     * things async...
     *
     * @see https://symfony.com/doc/current/components/process.html
     *
     * @throws UnsupportedParamException
     * @throws UnsupportedParamValueException
     */
    public function getSymfonyProcess(string $inputFile, string $outputFile, ConversionParamsInterface $convertParams, ?ProcessParamsInterface $processParams = null): Process
    {
        if (!$convertParams->hasParam(ConversionParamsInterface::PARAM_THREADS)
            && $this->adapter->getDefaultThreads() !== null) {
            $convertParams = $convertParams->withBuiltInParam(
                ConversionParamsInterface::PARAM_THREADS,
                $this->adapter->getDefaultThreads()
            );
        }

        $arguments = $this->adapter->getMappedConversionParams($convertParams);
        $ffmpegCmd = $this->adapter->getCliCommand($arguments, $inputFile, $outputFile);

        $pp = $processParams ?? $this->processParams;

        $process = new Process($ffmpegCmd);
        $process->setTimeout($pp->getTimeout());
        $process->setIdleTimeout($pp->getIdleTimeout());
        $process->setEnv($pp->getEnv());

        return $process;
    }

    /**
     * Run a conversion, throw exception on error.
     *
     * @param callable|null $callback A PHP callback to run whenever there is some
     *                                tmp available on STDOUT or STDERR
     *
     * @throws ConversionExceptionInterface        Base exception class for conversion exceptions
     * @throws ConversionProcessExceptionInterface Base exception class for process conversion exceptions
     * @throws MissingInputFileException
     * @throws ProcessTimedOutException
     * @throws ProcessFailedException
     * @throws ProcessSignaledException
     * @throws InvalidParamException
     * @throws RuntimeException
     */
    public function convert(string $inputFile, string $outputFile, ConversionParamsInterface $convertParams, ?callable $callback = null, ?ProcessParamsInterface $processParams = null): void
    {
        try {
            $this->ensureFileExists($inputFile);
            $process = $this->getSymfonyProcess($inputFile, $outputFile, $convertParams, $processParams);
            $process->mustRun($callback);
        } catch (FileNotFoundException $e) {
            throw new MissingInputFileException($e->getMessage());
        } catch (UnsupportedParamValueException | UnsupportedParamException $e) {
            throw new InvalidParamException($e->getMessage());
        } catch (SPException\ProcessTimedOutException $e) {
            throw new ProcessTimedOutException($e->getProcess(), $e);
        } catch (SPException\ProcessSignaledException $e) {
            throw new ProcessSignaledException($e->getProcess(), $e);
        } catch (SPException\ProcessFailedException $e) {
            throw new ProcessFailedException($e->getProcess(), $e);
        } catch (SPException\RuntimeException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /*
     * FOR LATER REFERENCE !!!
    public function convertMultiPass(string $videoFile, string $outputFile, ConversionParams $convertParams, VideoFilterInterface $videoFilter=null): void {

        $this->ensureFileExists($videoFile);
        if ($videoFilter === null) {
            $videoFilter = new EmptyVideoFilter();
        }


        $threads = $convertParams->getOption(VideoConversionParams::OPTION_THREADS, $this->ffmpegConfig->getThreads());

        $ffmpegBin = $this->ffmpegConfig->getBinary();

        $commonArgs = array_merge([
                $ffmpegBin,
                sprintf('-i %s', escapeshellarg($videoFile)), // input filename
                $videoFilter->getFFMpegCliArgument(), // add -vf yadif,nlmeans
                ($threads === null) ? '' : sprintf('-threads %s', $threads),
        ], $convertParams->getFFMpegArguments());

        $pass1Cmd = implode(' ', array_merge(
            $commonArgs,
            [
                '-pass 1',
                // tells VP9 to encode really fast, sacrificing quality. Useful to speed up the first pass.
                '-speed 4',
                '-y /dev/null',
            ]
        ));

        $pass2Cmd = implode( ' ', array_merge(
            $commonArgs,
            [
                '-pass 2',
                // speed 1 is a good speed vs. quality compromise.
                // Produces tmp quality typically very close to speed 0, but usually encodes much faster.
                '-speed 1',
                '-y',
                sprintf("%s", escapeshellarg($outputFile))
            ]
        ));


        $process = new Process($pass1Cmd);
        $process->setTimeout(null);
        $process->setIdleTimeout(60); // 60 seconds without tmp will stop the process
        $process->start();
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                echo "\nRead from stdout: ".$data;
            } else { // $process::ERR === $type
                echo "\nRead from stderr: ".$data;
            }
        }

        $process = new Process($pass2Cmd);
        $process->setTimeout(null);
        $process->setIdleTimeout(60); // 60 seconds without tmp will stop the process
        $process->start();
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                echo "\nRead from stdout: ".$data;
            } else { // $process::ERR === $type
                echo "\nRead from stderr: ".$data;
            }
        }

    }
    */
}
