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

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Module\WebDriver;

/**
 * Base class for extensions
 */
abstract class AbstractWebDriverExtension extends AbstractCodeceptionExtension
{
    protected \Codeception\Module\WebDriver $webDriverModule;

    protected string $environment = 'testing';

    public function loadWebDriver(SuiteEvent $event): void
    {
        try {
            $this->webDriverModule = $this->getModule('WebDriver');
        } catch (ModuleRequireException $moduleRequireException) {
            throw new ExtensionException($this, 'This extension requires the WebDriver module!');
        }
    }

    public function loadCurrentEnvironment(SuiteEvent $event): void
    {
        if (array_key_exists('current_environment', $event->getSettings())) {
            $this->environment = str_replace(',', '.', $event->getSettings()['current_environment']);
        }
    }
}
