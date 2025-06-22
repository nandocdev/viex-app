<?php

/**
 * @package     system/Rendering
 * @subpackage  Contracts
 * @file        ViewEngine
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 21:49:44
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Rendering\Contracts;

interface ViewEngine {
   /**
    * Renderiza un archivo de plantilla con los datos proporcionados.
    * @param string $templatePath La ruta completa del archivo de plantilla.
    * @param array $data Los datos a pasar a la plantilla.
    * @return string El contenido HTML/string renderizado.
    */
   public function render(string $templatePath, array $data): string;

   /**
    * Compila el contenido de una plantilla con los datos proporcionados.
    * @param string $content El contenido de la plantilla (sin procesar).
    * @param array $data Los datos a pasar a la plantilla.
    * @param string $baseTemplatePath Ruta base para buscar plantillas parciales.
    * @return string El contenido HTML/string compilado.
    * @throws \InvalidArgumentException Si el contenido no es válido o no se puede compilar.
    */
   public function compileContent(string $content, array $data, string $baseTemplatePath = ''): string;
}
