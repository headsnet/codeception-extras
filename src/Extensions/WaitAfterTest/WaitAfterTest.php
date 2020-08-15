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

namespace Headsnet\CodeceptionExtras\Extensions\WaitAfterTest;

use Codeception\Events;
use Headsnet\CodeceptionExtras\Extensions\AbstractCodeceptionExtension;

/**
 * Apply a small delay after each test. This permits any late loading JS that
 * performs AJAX requests to the backend to still have a database file to access.
 * Otherwise the DB cleanup routine nukes the DB file and the HTTP request errors.
 */
class WaitAfterTest extends AbstractCodeceptionExtension
{
    /**
     * @var array<string, string>
     */
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_AFTER => 'afterTest',
    ];

    public function beforeSuite(): void
    {
        $this->validateParameter('wait_time', 1);
    }

    public function afterTest(): void
    {
        sleep($this->config['wait_time']);

        if ($this->options['verbosity'] >= 64) {
            parent::writeln(
                sprintf('Throttle applied for %s second after test complete', $this->config['wait_time'])
            );
        }
    }
}
