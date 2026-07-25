<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Raised when an affiliate payout movement cannot be applied. The message is written for the
 * admin who clicked the button, so controllers flash it verbatim.
 */
class AffiliatePayoutException extends RuntimeException
{
}
