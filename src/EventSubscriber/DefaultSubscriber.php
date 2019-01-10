<?php

namespace Drupal\idix_rgpd\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class DefaultSubscriber.
 */
class DefaultSubscriber implements EventSubscriberInterface {


  /**
   * Constructs a new DefaultSubscriber object.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['config.save'] = ['config_save'];

    return $events;
  }

  /**
   * This method is called whenever the config.save event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function config_save(Event $event) {
    _idix_rgpd_generate_services();
  }

}
