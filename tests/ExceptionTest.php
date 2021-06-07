<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;
use CardinalCollections\ArrayWithSecondaryKeys\NoSuchIndexException;

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

    public function testPutNullKeyInvalidArgumentException(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $this->expectException(InvalidArgumentException::class);
        $a->put(null, ['k' => 'v']);
    }

    public function testPutArrayAsKeyInvalidArgumentException(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $this->expectException(InvalidArgumentException::class);
        $a->put(['k' => 'v'], 'document');
    }

    public function testRemoveNullKeyInvalidArgumentException(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $this->expectException(InvalidArgumentException::class);
        $a->remove(null);
    }

    public function testRemoveArrayAsKeyInvalidArgumentException(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $this->expectException(InvalidArgumentException::class);
        $a->remove([1, 2, 3]);
    }

    public function testPutIfAbsentNulllKeyInvalidArgumentException(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $this->expectException(InvalidArgumentException::class);
        $a->putIfAbsent(null, ['k' => 'v']);
    }
}
