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

use Codeception\Exception\ExtensionException;
use Codeception\Extension;

abstract class AbstractCodeceptionExtension extends Extension
{
    protected function validateParameter($paramName, $default = null): void
    {
        if (!array_key_exists($paramName, $this->config)) {
            if (!$default) {
                throw new ExtensionException(
                    $this,
                    sprintf('You must specify the "%s" option', $paramName)
                );
            }

            $this->config[$paramName] = $default;
        }
    }
}
