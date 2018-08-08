<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Common\Assert;

use Soluble\MediaTools\Common\Exception\InvalidArgumentException;

trait BitrateAssertionsTrait
{
    /**
     * Ensure that a bitrate is valid (optional unit: k or M ).
     *
     * @throws InvalidArgumentException
     */
    protected function ensureValidBitRateUnit(string $bitrate): void
    {
        if (preg_match('/^\d+(k|M)?$/i', $bitrate) !== 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid bitrate given: "%s" (int(K|M)?)',
                    $bitrate
                )
            );
        }
    }
}
