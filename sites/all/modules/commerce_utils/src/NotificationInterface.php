<?php

namespace Commerce\Utils;

/**
 * Interface to distinguish notification data wrappers.
 */
interface NotificationInterface {

  /**
   * NotificationInterface constructor.
   *
   * @param \stdClass $data
   *   Inbound data received as POST request by the controller.
   *
   * @see \Commerce\Utils\NotificationControllerBase::setData()
   */
  public function __construct(\stdClass $data);

}
