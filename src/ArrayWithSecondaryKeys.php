<?php

namespace VladimirVrzic\ArrayWithSecondaryKeys;

use Countable;

class ArrayWithSecondaryKeys implements Countable
{
    // primary map
    private $p;
    // secondary indexes
    private $s = [];

    public function __construct(array $array = [])
    {
        $this->p = $array;
    }

    public function dump()
    {
        var_dump($this->p);
        var_dump($this->s);
    }

    public function count(): int
    {
        return count($this->p);
    }

    public function isEmpty(): bool
    {
        return empty($this->p);
    }

    public function append($document)
    {
        $this->p[] = $document;
        // $primaryKey = array_key_last($this->p); // PHP >= 7.3
        $primaryKey = array_keys($this->p)[count($this->p) - 1];
        $this->updateAllIndexes($primaryKey, $document);
    }

    public function put($key, $document)
    {
        if (is_null($key)) {
            return;
        }

        $primaryKey = is_string($key) ? explode('.', $key)[0] : $key;

        $prevSecondaryValues = $this->getAllSecondaryIndexValues($primaryKey);

        if (is_string($key)) {
            ArrUtils::set($this->p, $key, $document);
        } else {
            $this->p[$key] = $document;
        }

        $this->updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues);
    }

    public function get($key, $default = null)
    {
        return ArrUtils::get($this->p, $key, $default);
    }

    public function has($keys): bool
    {
        return ArrUtils::has($this->p, $keys);
    }

    public function remove($key)
    {
        if (is_null($key)) {
            return;
        }

        $primaryKey = is_string($key) ? explode('.', $key)[0] : $key;

        $prevSecondaryValues = $this->getAllSecondaryIndexValues($primaryKey);

        if (is_string($key)) {
            ArrUtils::forget($this->p, $key);
        } else {
            unset($this->p[$key]);
        }

        $this->updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues);
    }

    public function containsPrimaryKey($primaryKey): bool {
        return array_key_exists($primaryKey, $this->p);
    }

    public function containsSecondaryKey($index, $secondaryKey): bool {
        if (!array_key_exists($index, $this->s)) {
            throw new NoSuchIndexException("Index $index not present");
        }
        return array_key_exists($secondaryKey, $this->s[$index]);
    }

    public function getByIndex($index, $secondaryKey, $default = null)
    {
        if (!array_key_exists($index, $this->s)) {
            throw new NoSuchIndexException("Index $index not present");
        }
        $primaryKey = ArrUtils::get($this->s[$index], $secondaryKey, null);
        return (
        is_null($primaryKey)
            ? $default
            : ArrUtils::get($this->p, $primaryKey, $default)
        );
    }

    public function updateByIndex($index, $secondaryKey, $document) {
        if (!array_key_exists($index, $this->s)) {
            throw new NoSuchIndexException("Index $index not present");
        }
        $primaryKey = ArrUtils::get($this->s[$index], $secondaryKey, null);
        if (!is_null($primaryKey)) {
            $this->p[$primaryKey] = $document;
            return true;
        } else {
            return false;
        }
    }

    public function removeByIndex($index, $secondaryKey) {
        if (!array_key_exists($index, $this->s)) {
            throw new NoSuchIndexException("Index $index not present");
        }
        $primaryKey = ArrUtils::get($this->s[$index], $secondaryKey, null);
        if (!is_null($primaryKey)) {
            unset($this->p[$primaryKey]);
            return true;
        } else {
            return false;
        }
    }

    public function putIfAbsent($key, $document)
    {
        $existing = ArrUtils::get($this->p, $key);
        if (is_null($existing)) {
            $this->put($key, $document);
            return null;
        } else {
            return $this->get($key, $document);
        }
    }

    public function asArray(): array
    {
        return $this->p;
    }

    public function primaryKeys(): array
    {
        return array_keys($this->p);
    }

    public function secondaryKeys($index): array
    {
        if (!array_key_exists($index, $this->s)) {
            throw new NoSuchIndexException("Index $index not present");
        }
        return array_keys($this->s[$index]);
    }

    public function createIndex($index)
    {
        if (array_key_exists($index, $this->s)) {
            return;
        }
        $this->s[$index] = [];
        foreach ($this->p as $primaryKey => $document) {
            $this->updateIndex($index, $primaryKey, $document);
        }
    }

    private function updateIndex($index, $primaryKey, $document)
    {
        $secondaryKey = ArrUtils::get($document, $index, null);
        if (!is_null($secondaryKey)) {
            $this->s[$index][$secondaryKey] = $primaryKey;
        }
    }

    private function updateAllIndexes($primaryKey, $document)
    {
        foreach (array_keys($this->s) as $index) {
            $this->updateIndex($index, $primaryKey, $document);
        }
    }

    private function getAllSecondaryIndexValues($primaryKey): array {
        $document = $this->p[$primaryKey] ?? null;
        $prevSecondaryValues = [];
        foreach (array_keys($this->s) as $index) {
            $prevSecondaryValues[$index] = ArrUtils::get($document, $index);
        }
        return $prevSecondaryValues;
    }

    private function updateAllSecondaryIndexValues($primaryKey, $prevSecondaryValues) {
        $document = $this->p[$primaryKey];
        foreach (array_keys($this->s) as $index) {
            $prevValue = $prevSecondaryValues[$index];
            $newValue = ArrUtils::get($document, $index);
            if ($prevValue != $newValue) {
                if (is_null($newValue)) {
                    unset($this->s[$index][$prevValue]);
                } else {
                    $this->s[$index][$newValue] = $primaryKey;
                }
            }
        }
    }

}
