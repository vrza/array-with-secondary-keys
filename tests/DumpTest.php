<?php

use PHPUnit\Framework\TestCase;
use CardinalCollections\ArrayWithSecondaryKeys\ArrayWithSecondaryKeys;

final class DumpTest extends TestCase
{
    public function testDump(): void
    {
        $a = new ArrayWithSecondaryKeys();
        $a->createIndex('state.pid');
        $a->put(
            22,
            [
                'name' => 'twenty-two',
                'state' => [
                    'pid' => 12345
                ]
            ]
        );
        ob_start();
        $a->dump();
        $dump = ob_get_clean();
        $this->assertIsInt(
            strpos($dump, '[12345]')
        );
    }
}
