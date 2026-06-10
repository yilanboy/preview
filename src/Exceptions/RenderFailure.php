<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Exceptions;

use RuntimeException;

/**
 * Thrown when GD fails at render time (image creation, color allocation, text
 * measurement). Extends the SPL RuntimeException, so existing catches keep
 * working.
 */
final class RenderFailure extends RuntimeException implements PreviewException {}
