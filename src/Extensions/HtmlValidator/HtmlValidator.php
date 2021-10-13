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

namespace Headsnet\CodeceptionExtras\Extensions\HtmlValidator;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Test\Descriptor;
use Headsnet\CodeceptionExtras\Extensions\AbstractWebDriverExtension;
use PHPUnit\Framework\SelfDescribing;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Send the HTML source code from the test request to the W3C HTML Validator
 */
class HtmlValidator extends AbstractWebDriverExtension
{
    /**
     * @var int
     */
    private $totalErrors = 0;

    /**
     * @var int
     */
    private $totalWarnings = 0;

    /**
     * @var array<array|string>
     */
    public static $events = [
        Events::SUITE_BEFORE => [
            ['loadWebDriver', 100],
            ['loadCurrentEnvironment', 100],
            ['beforeSuite', 0]
        ],
        Events::SUITE_AFTER => 'afterSuite',
        Events::TEST_AFTER => 'validateSourceCode'
    ];

    public function beforeSuite(): void
    {
        $this->validateParameter('output_format', 'gnu');
        $this->validateParameter('validator_url');
    }

    public function validateSourceCode(TestEvent $event): void
    {
        $htmlSource = $this->webDriverModule->grabPageSource();

        $client = HttpClient::create();

        $response = $client->request('POST', $this->config['validator_url'], [
            'headers' => [
                'Content-Type' => 'text/html; charset=utf-8;',
            ],
            'query' => [
                'out' => $this->config['output_format'],
                'showsource' => 'yes' // Only for HTML output format
            ],
            'body' => $htmlSource
        ]);

        try {
            $reportData = $response->getContent();
        } catch (HttpExceptionInterface | TransportExceptionInterface $e) {
            throw new ExtensionException($this, 'Could not process result from HTML validator!');
        }

        if ($this->config['output_format'] === 'html') {
            $reportData = $this->processHtmlReport($reportData);
        }

        if ($this->config['output_format'] === 'gnu') {
            $errorsAndWarnings = $this->getErrorAndWarningTotals($reportData);
            $this->totalErrors += $errorsAndWarnings['errors'];
            $this->totalWarnings += $errorsAndWarnings['warnings'];

            $this->writeGnuSummaryLine($errorsAndWarnings);
            $this->displayGnuReport($reportData);
        }

        $this->writeLogFile($event, $reportData);
    }

    public function afterSuite(): void
    {
        $this->writeTotalSummaryLine();
    }

    private function writeLogFile(TestEvent $event, string $results): void
    {
        file_put_contents($this->getLogFileName($event), $results);
    }

    private function getLogFileName(TestEvent $event): string
    {
        return sprintf(
            '%s/%s.%s.fail.validation-errors.html',
            $this->getLogDir(),
            str_replace(['\\', ':'], '.', $this->getTestName($event)),
            $this->environment
        );
    }

    /**
     * @return array<string, int>
     */
    private function getErrorAndWarningTotals(string $reportData): array
    {
        return [
            'errors' => substr_count($reportData, 'error:'),
            'warnings' => substr_count($reportData, 'warning:')
        ];
    }

    /**
     * @param array<string, int> $errorsAndWarnings
     */
    private function writeGnuSummaryLine(array $errorsAndWarnings): void
    {
        parent::writeln(
            sprintf(
                'Validating HTML source code: Found %s errors and %s warnings',
                $errorsAndWarnings['errors'],
                $errorsAndWarnings['warnings']
            )
        );
    }

    private function writeTotalSummaryLine(): void
    {
        parent::writeln(
            sprintf(
                '%sHTML Source code validation found a total of %s errors and %s warnings',
                "\n",
                $this->totalErrors,
                $this->totalWarnings
            )
        );
    }

    private function displayGnuReport(string $reportData): void
    {
        foreach (explode("\n", $reportData) as $reportLine) {
            if ($reportLine === '') {
                continue;
            }

            parent::write(sprintf('  %s%s', $reportLine, "\n"));
        }
    }

    /**
     * Here we change references to local assets to pull in remote ones, and set the character set etc
     */
    private function processHtmlReport(string $htmlReport): string
    {
        $htmlReport = str_replace('style.css', 'https://validator.w3.org/nu/style.css', $htmlReport);
        $htmlReport = str_replace('script.js', 'https://validator.w3.org/nu/script.js', $htmlReport);
        $htmlReport = str_replace('icon.png', 'https://validator.w3.org/nu/icon.png', $htmlReport);
        return str_replace('<head>', '<head><meta charset="utf-8"/>', $htmlReport);
    }

    private function getTestName(TestEvent $event): string
    {
        /** @var SelfDescribing $test */
        $test = $event->getTest();

        return Descriptor::getTestSignature($test);
    }
}
