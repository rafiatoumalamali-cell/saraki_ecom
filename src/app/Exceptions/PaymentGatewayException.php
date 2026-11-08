<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Anything that stops us talking to Paystack — missing keys, a refused
 * transaction, a timeout. The message is written for the customer, never for the
 * gateway's own log.
 */
class PaymentGatewayException extends RuntimeException {}
