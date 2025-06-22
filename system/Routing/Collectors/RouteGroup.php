<?php
/**
 * @package     Phast/System
 * @subpackage  Routing/Collectors
 * @file        RouteGroup.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Representa los atributos de un grupo de rutas.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Collectors;

/**
 * Un objeto inmutable que contiene los atributos para un grupo de rutas.
 */
final class RouteGroup {
   /**
    * @param string $prefix El prefijo de la URI para el grupo.
    * @param array $middleware El middleware a aplicar al grupo.
    */
   public function __construct(
      public readonly string $prefix = '',
      public readonly array $middleware = []
   ) {
   }
}