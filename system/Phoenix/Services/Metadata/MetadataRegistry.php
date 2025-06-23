<?php
/**
 * @package     Phoenix/Services
 * @subpackage  Metadata
 * @file        MetadataRegistry
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-23 16:24:26
 * @version     1.0.0
 * @description Servicio centralizado para obtención y cacheo de metadatos de entidades.
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Services\Metadata;

use ReflectionClass;
use ReflectionException;

class MetadataRegistry
{
    private static array $cache = [];

    /**
     * Obtiene el nombre de la tabla asociada a una entidad.
     */
    public function getTableName(string $entityClass): string
    {
        $meta = $this->getMetadata($entityClass);
        return $meta['table'] ?? throw new \RuntimeException("No se pudo determinar el nombre de la tabla para $entityClass");
    }

    /**
     * Obtiene el nombre de la clave primaria de una entidad.
     */
    public function getPrimaryKey(string $entityClass): string
    {
        $meta = $this->getMetadata($entityClass);
        return $meta['primaryKey'] ?? 'id';
    }

    /**
     * Obtiene el mapeo columna → propiedad de la entidad.
     */
    public function getColumnToPropertyMap(string $entityClass): array
    {
        $meta = $this->getMetadata($entityClass);
        return $meta['columnToPropertyMap'] ?? [];
    }

    /**
     * Obtiene los nombres de todas las columnas persistentes.
     */
    public function getColumns(string $entityClass): array
    {
        $meta = $this->getMetadata($entityClass);
        return $meta['columns'] ?? [];
    }

    /**
     * Obtiene los metadatos completos de la entidad (cacheados).
     */
    public function getMetadata(string $entityClass): array
    {
        if (isset(self::$cache[$entityClass])) {
            return self::$cache[$entityClass];
        }

        try {
            $reflection = new ReflectionClass($entityClass);
        } catch (ReflectionException $e) {
            throw new \RuntimeException("No se pudo reflexionar la clase $entityClass", 0, $e);
        }

        // Buscar atributo Table (PHP 8+)
        $table = null;
        $primaryKey = 'id';
        $columns = [];
        $columnToPropertyMap = [];

        // Buscar atributo #[Table(name: ...)]
        $tableAttributes = $reflection->getAttributes(\Phast\System\Phoenix\Entity\Attributes\Table::class);
        if ($tableAttributes) {
            $table = $tableAttributes[0]->newInstance()->name ?? null;
        }

        // Buscar atributo #[PrimaryKey(name: ...)]
        $pkAttributes = $reflection->getAttributes(\Phast\System\Phoenix\Entity\Attributes\PrimaryKey::class);
        if ($pkAttributes) {
            $primaryKey = $pkAttributes[0]->newInstance()->name ?? 'id';
        }

        // Buscar atributos #[Column(name: ...)] en propiedades
        foreach ($reflection->getProperties() as $property) {
            $columnAttributes = $property->getAttributes(\Phast\System\Phoenix\Entity\Attributes\Column::class);
            if ($columnAttributes) {
                $columnName = $columnAttributes[0]->newInstance()->name ?? $property->getName();
                $columns[] = $columnName;
                $columnToPropertyMap[$columnName] = $property->getName();
            }
        }

        // Fallback: si no hay columnas, usar nombres de propiedades públicas/protegidas
        if (empty($columns)) {
            foreach ($reflection->getProperties() as $property) {
                $columns[] = $property->getName();
                $columnToPropertyMap[$property->getName()] = $property->getName();
            }
        }

        $meta = [
            'table' => $table ?? strtolower($reflection->getShortName()),
            'primaryKey' => $primaryKey,
            'columns' => $columns,
            'columnToPropertyMap' => $columnToPropertyMap,
        ];

        self::$cache[$entityClass] = $meta;
        return $meta;
    }
}