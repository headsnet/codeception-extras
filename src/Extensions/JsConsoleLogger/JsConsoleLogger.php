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
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Module\Asserts;
use Codeception\Test\Descriptor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Headsnet\CodeceptionExtras\Extensions\AbstractWebDriverExtension;

final class JsConsoleLogger extends AbstractWebDriverExtension
{
    /**
     * @var Asserts
     */
    private $assertsModule;

    /**
     * @var string
     */
    private $currentTestName;

    /**
     * @var array
     */
    public static $events = [
        Events::SUITE_BEFORE => [
            ['loadWebDriver', 100],
            ['loadCurrentEnvironment', 100],
            ['beforeSuite', 0]
        ],
        Events::STEP_AFTER => 'afterStep',
    ];

    public function beforeSuite(SuiteEvent $event)
    {
        $this->assertsModule = $this->getModule('Asserts');
    }

    public function afterStep(StepEvent $event)
    {
        $this->currentTestName = Descriptor::getTestSignature($event->getTest());

        $this->checkJsErrors();
    }

    private function checkJsErrors(): void
    {
        $this->webDriverModule->executeInSelenium(function (RemoteWebDriver $webDriver): void {
            $log = $webDriver->manage()->getLog('browser');

            $errors = array_values(array_filter($log, function ($entry): bool {
                // Permit the error about insecure passwords on non-https
                return false === strpos($entry['message'], 'non-secure context') &&
                    false === strpos($entry['message'], '/_wdt/');
            }));

            $errorMsg = count($errors) > 0 ? $errors[0]['message'] : '';

            if (count($errors) > 0) {
                $this->writeLogFile($log);
            }

            $this->assertsModule->assertCount(
                0,
                $errors,
                'Javascript warning: ' . $errorMsg
            );
        });
    }

    private function writeLogFile(array $log): void
    {
        file_put_contents($this->getLogFileName(), print_r($log, true));
    }

    private function getLogFileName(): string
    {
        return sprintf(
            '%s/_output/%s.%s.fail.js-errors.txt',
            dirname(__DIR__),
            str_replace(['\\', ':'], '.', $this->currentTestName),
            $this->environment
        );
    }
}
