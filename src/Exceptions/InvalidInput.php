<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a value passed to the library fails validation. Extends the SPL
 * InvalidArgumentException, so existing catches keep working.
 */
final class InvalidInput extends InvalidArgumentException implements PreviewException {}
