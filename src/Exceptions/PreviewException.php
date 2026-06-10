<?php

declare(strict_types=1);

namespace Yilanboy\Preview\Exceptions;

use Throwable;

/**
 * Marker interface implemented by every exception this library throws, so
 * consumers can catch them all with a single `catch (PreviewException $e)`.
 */
interface PreviewException extends Throwable {}
