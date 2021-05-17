<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use VladimirVrzic\ArrayWithSecondaryKeys\NoSuchIndexException;

final class ExceptionTest extends TestCase
{
    public function testGetByIndexNoSuchIndexException(): void
    {
        $pid = 12345;
        $a = new ArrayWithSecondaryKeys();
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => $pid
                ]
            ]
        );
        $a->createIndex('state.pid');
        $this->expectException(NoSuchIndexException::class);
        $a->getByIndex('state.foobar', $pid)['name'];
    }

    public function testSecondaryKeysNoSuchIndexException(): void
    {
        $pid = 12345;
        $a = new ArrayWithSecondaryKeys();
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => $pid
                ]
            ]
        );
        $a->createIndex('state.pid');
        $this->expectException(NoSuchIndexException::class);
        $a->secondaryKeys('state.foobar');
    }

    public function testContainsSecondaryKeyNoSuchIndexException(): void
    {
        $pid = 12345;
        $a = new ArrayWithSecondaryKeys();
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => $pid
                ]
            ]
        );
        $a->createIndex('state.pid');
        $this->expectException(NoSuchIndexException::class);
        $a->containsSecondaryKey('state.foobar', $pid);
    }
}
