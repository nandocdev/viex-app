<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM
 * @file        Collection
 * @description Un objeto de colección para manejar conjuntos de modelos.
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable {
   /**
    * @param Model[] $items
    */
   public function __construct(protected array $items = []) {
   }

   /**
    * Obtiene todos los items de la colección.
    */
   public function all(): array {
      return $this->items;
   }

   /**
    * Obtiene el primer item de la colección.
    */
   public function first(): ?Model {
      return $this->items[0] ?? null;
   }

   /**
    * Determina si la colección está vacía.
    */
   public function isEmpty(): bool {
      return empty($this->items);
   }

   /**
    * Convierte la colección y sus modelos a un array.
    */
   public function toArray(): array {
      return array_map(fn($item) => $item->toArray(), $this->items);
   }

   /**
    * Convierte la colección a JSON.
    */
   public function toJson(int $options = 0): string {
      return json_encode($this->toArray(), $options);
   }

   // --- Implementación de interfaces de PHP ---

   public function count(): int {
      return count($this->items);
   }

   public function getIterator(): \ArrayIterator {
      return new \ArrayIterator($this->items);
   }

   public function offsetExists($offset): bool {
      return isset($this->items[$offset]);
   }

   public function offsetGet($offset): mixed {
      return $this->items[$offset];
   }

   public function offsetSet($offset, $value): void {
      if (is_null($offset)) {
         $this->items[] = $value;
      } else {
         $this->items[$offset] = $value;
      }
   }

   public function offsetUnset($offset): void {
      unset($this->items[$offset]);
   }

   public function jsonSerialize(): array {
      return $this->toArray();
   }
}