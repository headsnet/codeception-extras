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

use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Module\WebDriver;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * Get the URL of the Symfony Profiler for any requests that error
 *
 * This requires the test environment to have:
 *
 * framework:
 *     profiler:
 *         collect: true
 */
class SymfonyProfilerUrl extends Extension
{
    /**
     * @var string
     */
    private const PROFILER_LINK_STUB = 'https://app.3m.docker/_profiler/';

    /**
     * @var WebDriver
     */
    private $webDriverModule;

    /**
     * @var array
     */
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::STEP_AFTER => 'getDebugLink'
    ];

    public function beforeSuite(SuiteEvent $event)
    {
        $this->webDriverModule = $this->getModule('WebDriver');
    }

    public function getDebugLink(StepEvent $event)
    {
        $this->webDriverModule->executeInSelenium(function (RemoteWebDriver $webDriver): void
        {
            $log = $webDriver->manage()->getLog('performance');

            foreach ($log as $logEntry)
            {
                $message = json_decode($logEntry['message']);

                if ('Network.responseReceived' === $message->message->method)
                {
                    $headers = $message->message->params->response->headers;

                    if (isset($headers->status) &&
                        in_array($headers->status, [400, 401, 403, 404, 500]) &&
                        isset($headers->{'x-debug-token'}))
                    {
                        $this->writeln(
                            sprintf(
                                "\nProfiler URL for failed response: %s%s",
                                self::PROFILER_LINK_STUB,
                                $headers->{'x-debug-token'}
                            )
                        );
                    }
                }
            }
        });
    }
}
