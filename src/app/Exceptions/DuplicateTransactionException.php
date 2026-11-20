<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Paystack already knows this reference: an earlier initialise reached them
 * even though we never saw the answer (timeout, dropped connection, killed
 * request). The caller retires that attempt and opens a fresh one instead of
 * showing the customer an error.
 */
class DuplicateTransactionException extends PaymentGatewayException {}
