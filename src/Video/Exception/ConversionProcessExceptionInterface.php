<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video\Exception;

use Soluble\MediaTools\Exception\ProcessExceptionInterface;

interface ConversionProcessExceptionInterface extends ProcessExceptionInterface, ConversionExceptionInterface
{
}
