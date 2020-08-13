<?php
/*
 * This file is part of the Headsnet Codeception Extras package
 *
 * (c) Headstrong Internet Services Ltd 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Headsnet\CodeceptionExtras\Extensions;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;

/**
 * Apply a small delay after each test. This permits any late loading JS that
 * performs AJAX requests to the backend to still have a database file to access.
 * Otherwise the DB cleanup routine nukes the DB file and the HTTP request errors.
 */
class WaitAfterTest extends Extension
{
    private const DELAY = 1;

    /**
     * @var array
     */
    public static $events = [
        Events::TEST_AFTER => 'afterTest',
    ];

    public function afterTest(TestEvent $event)
    {
        sleep(self::DELAY);

        if ($this->options['verbosity'] >= 64)
        {
            parent::writeln(
                sprintf('Throttle applied for %s second after test complete', self::DELAY)
            );
        }
    }
}
