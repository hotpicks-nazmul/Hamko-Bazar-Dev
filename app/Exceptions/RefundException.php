<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A refund the shop's rules do not allow: disabled, wrong order state, outside the window, already
 * refunded, or a transition the status map forbids. Controllers catch it and turn it into a flash
 * message instead of a 500.
 */
class RefundException extends RuntimeException
{
}
