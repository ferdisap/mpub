<?php 

namespace Ptdi\Mpub\Validation;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use Ptdi\Mpub\Main\CSDBObject;

class CSDBValidatee extends Validation implements Iterator, Countable, ArrayAccess, JsonSerializable
{
  /**
   * untuk interface Iterator
   */
  private int $position = 0;

  public function __construct(protected mixed $validatee)
  {
    $this->validatee = (!is_countable($validatee)) ? [$validatee] : $this->validatee = array_filter($this->validatee, fn ($v) => $v);
  }
  
  #[\ReturnTypeWillChange]
  public function count() 
  {
    return count($this->validatee);
  }

  public function rewind(): void
  {
    $this->position = 0;
  }

  #[\ReturnTypeWillChange]
  public function current()
  {
    return $this->validatee[$this->position];
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
    return isset($this->validatee[$this->position]);
  }

  public function offsetExists(mixed $var) :bool
  {
    return $this->validatee[$var] ? true : false;
  }

  #[\ReturnTypeWillChange]
  public function offsetGet(mixed $offset) :mixed
  {
    return $this->validatee[$offset];
  }

  public function offsetSet(mixed $offset, mixed $value) :void
  {
    $this->validatee[$offset] = $value;
  }

  public function offsetUnset(mixed $offset) :void
  {
    unset($this->validatee[$offset]);
  }

  public function isReady()
  {
    return $this->check($this->validatee);
  }

  public function jsonSerialize(): mixed
  {
    return [
      'validatee' => $this->validatee
    ];
  }
}