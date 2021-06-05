<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use CardinalCollections\ArrayWithSecondaryKeys\NoSuchIndexException;

final class GetTest extends TestCase
{
    public function testGetMethodReturnsNestedValue(): void
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
        $this->assertEquals(
            $pid,
            $a->get('22.state.pid')
        );
    }

    public function testGetFromIndex(): void
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
            $this->assertEquals(
                'twenty-two',
                $a->getByIndex('state.pid', $pid)['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail();
        }
    }

    public function testGetFromIndexWithLateIndexing(): void
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
            $this->assertEquals(
                'twenty-two',
                $a->getByIndex('state.pid', $pid)['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: state.pid");
        }
    }

    public function testIdempotentCreateIndex(): void
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
        $a->createIndex('state.pid');
        try {
            $this->assertEquals(
                'twenty-two',
                $a->getByIndex('state.pid', $pid)['name']
            );
        } catch (NoSuchIndexException $e) {
            $this->fail("No such index: state.pid");
        }
    }
}
