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

    /**
     * Handle event
     *
     * @param StepEvent $event
     * @return void
     */
    public function afterStep(StepEvent $event): void
    {
        $this->checkJsErrors($event);
    }

    /**
     * Handle js errors if there are any
     *
     * @param StepEvent $event
     * @return void
     * @throws \Codeception\Exception\ModuleRequireException
     */
    private function checkJsErrors(StepEvent $event): void
    {
        $this->webDriverModule->executeInSelenium(function (RemoteWebDriver $webDriver) use ($event): void {
            $log = $webDriver->manage()->getLog('browser');

            $logEntries = array_values(array_filter($log, function ($entry): bool {
                // Permit the error about insecure passwords on non-https
                return false === strpos($entry['message'], 'non-secure context') &&
                    false === strpos($entry['message'], '/_wdt/');
            }));

            if (count($logEntries) === 0) {
                return;
            }

            // set first entry message as general message
            $errorMsg = count($logEntries) > 0 ? $logEntries[0]['message'] : '';

            // write all entries into log file
            $this->writeLogFile($event, $log);
            $asserts = $this->getModule('Asserts');

            $groupedErrors = [
                'INFO'      => [],
                'WARNING'   => [],
                'SEVERE'    => []
            ];
            foreach($logEntries as $entry) {
                $groupedErrors[$entry['level']][] = $entry;
            }

            if (!empty($this->config['assert_no_warnings'])) {
                /** @var Asserts $asserts */
                $asserts->assertCount(
                    0,
                    $groupedErrors['WARNING'],
                    'Javascript warning: ' . $errorMsg
                );
            }

            if (!empty($this->config['assert_no_errors'])) {
                /** @var Asserts $asserts */
                $asserts->assertCount(
                    0,
                    $groupedErrors['SEVERE'],
                    'Javascript errors: ' . $errorMsg
                );
            }

            if (!empty($this->config['assert_no_console'])) {
                /** @var Asserts $asserts */
                $asserts->assertCount(
                    0,
                    $groupedErrors['INFO'],
                    'Javascript console entries: ' . $errorMsg
                );
            }
        });
    }

    /**
     * @param array<string> $log
     */
    private function writeLogFile(StepEvent $event, array $log): void
    {
        // logs need to be appended when test case uses data provider
        file_put_contents($this->getLogFileName($event), print_r($log, true), FILE_APPEND);
    }

    /**
     * @param StepEvent $event
     * @return string
     */
    private function getLogFileName(StepEvent $event): string
    {
        return sprintf(
            '%s/%s.%s.fail.js-errors.txt',
            $this->getLogDir(),
            str_replace(['\\', ':'], '.', $this->getTestName($event)),
            $this->environment
        );
    }

    /**
     * @param StepEvent $event
     * @return string
     */
    private function getTestName(StepEvent $event): string
    {
        /** @var SelfDescribing $test */
        $test = $event->getTest();

        return Descriptor::getTestSignature($test);
    }
}
