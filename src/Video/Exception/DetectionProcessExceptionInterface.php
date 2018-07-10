<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video\Exception;

use Soluble\MediaTools\Common\Exception\ProcessExceptionInterface;

interface DetectionProcessExceptionInterface extends ProcessExceptionInterface, DetectionExceptionInterface
{
}
