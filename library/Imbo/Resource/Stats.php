<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\Resource;

use Imbo\EventManager\EventInterface,
    Imbo\EventListener\ListenerDefinition,
    Imbo\EventListener\ListenerInterface,
    Imbo\Model,
    DateTime,
    DateTimeZone;

/**
 * Stats resource
 *
 * This resource can be used to monitor the imbo installation to see if it has access to the
 * current database and storage.
 *
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @package Resources
 */
class Stats implements ResourceInterface, ListenerInterface {
    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods() {
        return array('GET', 'HEAD');
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition() {
        return array(
            new ListenerDefinition('stats.get', array($this, 'get')),
            new ListenerDefinition('stats.head', array($this, 'get')),
        );
    }

    /**
     * Handle GET requests
     *
     * @param EventInterface $event The current event
     */
    public function get(EventInterface $event) {
        $response = $event->getResponse();
        $response->setMaxAge(0)
                 ->setPrivate();

        $response->headers->addCacheControlDirective('no-store');

        $event->getManager()->trigger('db.stats.load');
    }
}
