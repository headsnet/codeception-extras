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

namespace Headsnet\CodeceptionExtras\Extensions\JsConsoleLogger;

use Codeception\Event\StepEvent;
use Codeception\Events;
use Codeception\Module\Asserts;
use Codeception\Test\Descriptor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Headsnet\CodeceptionExtras\Extensions\AbstractWebDriverExtension;
use PHPUnit\Framework\SelfDescribing;

final class JsConsoleLogger extends AbstractWebDriverExtension
{
    /**
     * @var array<array|string>
     */
    public static $events = [
        Events::SUITE_BEFORE => [
            ['loadWebDriver', 100],
            ['loadCurrentEnvironment', 100]
        ],
        Events::STEP_AFTER => 'afterStep',
    ];

    public function afterStep(StepEvent $event): void
    {
        $this->checkJsErrors($event);
    }

    private function checkJsErrors(StepEvent $event): void
    {
        $this->webDriverModule->executeInSelenium(function (RemoteWebDriver $webDriver) use ($event): void {
            $log = $webDriver->manage()->getLog('browser');

            $errors = array_values(array_filter($log, function ($entry): bool {
                // Permit the error about insecure passwords on non-https
                return false === strpos($entry['message'], 'non-secure context') &&
                    false === strpos($entry['message'], '/_wdt/');
            }));

            $errorMsg = count($errors) > 0 ? $errors[0]['message'] : '';

            if (count($errors) > 0) {
                $this->writeLogFile($event, $log);
            }

            /** @var Asserts $asserts */
            $asserts = $this->getModule('Asserts');
            $asserts->assertCount(
                0,
                $errors,
                'Javascript warning: ' . $errorMsg
            );
        });
    }

    /**
     * @param array<string> $log
     */
    private function writeLogFile(StepEvent $event, array $log): void
    {
        file_put_contents($this->getLogFileName($event), print_r($log, true));
    }

    private function getLogFileName(StepEvent $event): string
    {
        return sprintf(
            '%s/_output/%s.%s.fail.js-errors.txt',
            dirname(__DIR__),
            str_replace(['\\', ':'], '.', $this->getTestName($event)),
            $this->environment
        );
    }

    private function getTestName(StepEvent $event): string
    {
        /** @var SelfDescribing $test */
        $test = $event->getTest();

        return Descriptor::getTestSignature($test);
    }
}
