<?php

namespace Ptdi\Mpub\Validation;

use ArrayAccess;
use Countable;
use Iterator;
use Ptdi\Mpub\Main\CSDBObject;
use JsonSerializable;

class CSDBValidator extends Validation implements Iterator, Countable, ArrayAccess, JsonSerializable
{
  /**
   * untuk interface Iterator
   */
  private int $position = 0;

  #[\ReturnTypeWillChange]
  public function count() :int
  {
    return count($this->validator);
  }

  public function __construct(protected mixed $validator)
  {
    $this->validator = (!is_countable($validator)) ? [$validator] : $this->validator = array_filter($this->validator, fn ($v) => $v);
  }

  public function rewind(): void
  {
    $this->position = 0;
  }

  #[\ReturnTypeWillChange]
  public function current()
  {
    return $this->validator[$this->position];
  }

  #[\ReturnTypeWillChange]
  public function key()
  {
    return $this->position;
  }

  public function next(): void
  {
    ++$this->position;
  }

  public function valid(): bool
  {
    return isset($this->validator[$this->position]);
  }

  public function offsetExists(mixed $var) :bool
  {
    return $this->validator[$var] ? true : false;
  }

  #[\ReturnTypeWillChange]
  public function offsetGet(mixed $offset) :mixed
  {
    return $this->validator[$offset];
  }

  public function offsetSet(mixed $offset, mixed $value) :void
  {
    $this->validator[$offset] = $value;
  }

  public function offsetUnset(mixed $offset) :void
  {
    unset($this->validator[$offset]);
  }

  public function isReady()
  {
    return $this->check($this->validator);
  }

  public function jsonSerialize(): mixed
  {
    return [
      'validator' => $this->validator
    ];
  }
}
