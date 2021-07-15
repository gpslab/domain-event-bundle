<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Fixtures;

class SimpleObject
{
    private string $foo;

    protected string $camelCase = 'boo';

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function setFoo(string $foo): void
    {
        $this->foo = $foo;
    }

    public function getCamelCase(): string
    {
        return $this->camelCase;
    }

    public function setCamelCase(string $camelCase): void
    {
        $this->camelCase = $camelCase;
    }
}
