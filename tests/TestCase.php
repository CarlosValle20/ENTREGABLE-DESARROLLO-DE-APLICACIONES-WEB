<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $connection = config('database.default');
        $database = config('database.connections.'.$connection.'.database');
        if ($database !== ':memory:' && ! str_ends_with((string) $database, '_test')) {
            throw new \RuntimeException('Pruebas bloqueadas: utiliza :memory: o una base terminada en _test.');
        }
    }
}
