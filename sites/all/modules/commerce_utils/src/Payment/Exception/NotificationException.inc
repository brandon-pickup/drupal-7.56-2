<?php

namespace Commerce\Utils\Payment\Exception;

/**
 * Throw this exception only in case when notification handling impossible.
 *
 * @see commerce_utils_notification()
 * @see \Commerce\Utils\NotificationControllerBase::log()
 */
class NotificationException extends \RuntimeException {}
