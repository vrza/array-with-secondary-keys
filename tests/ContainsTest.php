<?php

use PHPUnit\Framework\TestCase;
use VladimirVrzic\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use VladimirVrzic\ArrayWithSecondaryKeys\NoSuchIndexException;

final class ContainsTest extends TestCase
{
    public function testHasMethodReturnsNestedValue(): void
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
        $this->assertTrue(
            $a->has('22.state.pid')
        );
    }

    public function testContainsPrimaryKey(): void
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
        $this->assertTrue(
            $a->containsPrimaryKey(22)
        );
    }

    public function testIndexContainsSecondaryKey(): void
    {
        $pid = 12345;
        $a = new ArrayWithSecondaryKeys();
        $a->createIndex('state.pid');
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => $pid
                ]
            ]
        );
        try {
            $this->assertTrue(
                $a->containsSecondaryKey('state.pid', $pid)
            );
        } catch (NoSuchIndexException $e) {
            $this->fail();
        }
    }

    public function testContainsSecondaryKeyWithLateIndexing(): void
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
        try {
            $this->assertTrue(
                $a->containsSecondaryKey('state.pid', $pid)
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: state.pid");
        }
    }
}
