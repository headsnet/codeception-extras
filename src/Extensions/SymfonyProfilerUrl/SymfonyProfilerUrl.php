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

namespace Headsnet\CodeceptionExtras\Extensions\SymfonyProfilerUrl;

use Codeception\Events;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Headsnet\CodeceptionExtras\Extensions\AbstractWebDriverExtension;

/**
 * Get the URL of the Symfony Profiler for any requests that error
 *
 * This requires the test environment to have:
 *
 * framework:
 *     profiler:
 *         collect: true
 */
class SymfonyProfilerUrl extends AbstractWebDriverExtension
{
    /**
     * @var array<array|string>
     */
    public static $events = [
        Events::SUITE_BEFORE => [
            ['loadWebDriver', 100],
            ['beforeSuite', 0]
        ],
        Events::STEP_AFTER => 'getDebugLink'
    ];

    public function beforeSuite(): void
    {
        $this->validateParameter('profiler_link_base');
    }

    public function getDebugLink(): void
    {
        $this->webDriverModule->executeInSelenium(function (RemoteWebDriver $webDriver): void {
            $log = $webDriver->manage()->getLog('performance');

            foreach ($log as $logEntry) {
                $message = json_decode($logEntry['message']);

                if ('Network.responseReceived' === $message->message->method) {
                    $headers = $message->message->params->response->headers;

                    if (isset($headers->status) &&
                        in_array($headers->status, [400, 401, 403, 404, 500]) &&
                        isset($headers->{'x-debug-token'})) {
                        $this->writeln(
                            sprintf(
                                "\nProfiler URL for failed response: %s%s",
                                $this->config['profiler_link_base'],
                                $headers->{'x-debug-token'}
                            )
                        );
                    }
                }
            }
        });
    }
}
