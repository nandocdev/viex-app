<?php

/**
 * @package     system/Rendering
 * @subpackage  Engines
 * @file        PhpEnginer
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 21:50:24
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Rendering\Engines;

use Phast\System\Rendering\Contracts\ViewEngine;
use Phast\System\Rendering\Core\DataHandler;
use Phast\System\Rendering\Core\TemplateLoader;
use InvalidArgumentException;

class PhpEngine implements ViewEngine {
   private DataHandler $dataHandler;
   private TemplateLoader $templateLoader;
   // Opcional: private Logger $logger; // Para logear errores de parciales

   public function __construct(DataHandler $dataHandler, TemplateLoader $templateLoader /*, Logger $logger */) {
      $this->dataHandler = $dataHandler;
      $this->templateLoader = $templateLoader;
      // $this->logger = $logger;
   }

   /**
    * Renderiza una plantilla PHP (layout o vista) con los datos proporcionados.
    * @param string $templatePath La ruta completa del archivo de plantilla.
    * @param array $data Los datos a pasar a la plantilla.
    * @return string El contenido HTML/string renderizado.
    * @throws InvalidArgumentException Si el archivo de plantilla no existe.
    */
   public function render(string $templatePath, array $data): string {
      if (!file_exists($templatePath)) {
         throw new InvalidArgumentException("El archivo de plantilla no existe: {$templatePath}");
      }

      $renderer = function () use ($templatePath, $data) {
         // extract() es menos peligroso aquí porque no controlamos el flujo,
         // pero sigue siendo mejor pasar un objeto de datos o un array.
         extract($data);
         ob_start();
         include $templatePath;
         return ob_get_clean();
      };

      $output = $renderer();
      // Procesar directivas internas como @partial y @content
      return $this->processDirectives($output, $templatePath);
   }

   /**
    * Compila y procesa un fragmento de contenido (usado para inyectar la vista en el layout).
    * Este método es interno y no es la función principal de renderizado de un archivo.
    * @param string $content El contenido raw (ej. el de la vista o el layout con @content).
    * @param array $data Los datos a extraer.
    * @param string $baseTemplatePath La ruta de la plantilla desde la que se procesa (para parciales relativos).
    * @return string El contenido procesado.
    */
   public function compileContent(string $content, array $data, string $baseTemplatePath = ''): string {
      $this->dataHandler->setData($data);
      extract($this->dataHandler->prepareDataForView());

      ob_start();
      // Incluir el contenido como si fuera un archivo para ejecutar PHP incrustado
      // Esto es un poco más peligroso que `include $templatePath` si el $content no es de confianza
      // pero es necesario para la inyección de la vista en el layout.
      eval ('?>' . $content); // Se mantiene eval aquí para la flexibilidad con @content, pero se debe usar con precaución.
      $output = ob_get_clean();

      // Procesar directivas después de la inclusión (ej. parciales)
      return $this->processDirectives($output, $baseTemplatePath);
   }


   /**
    * Procesa directivas internas como @partial y @content (si no se han procesado ya).
    * @param string $content El contenido HTML/string a procesar.
    * @param string $baseTemplatePath La ruta de la plantilla desde la que se procesa (para parciales relativos).
    * @return string El contenido procesado.
    */
   protected function processDirectives(string $content, string $baseTemplatePath = ''): string {
      $content = $this->extractPartials($content, $baseTemplatePath);
      // Si hay otras directivas como @component, se procesarían aquí
      return $content;
   }

   /**
    * Busca y reemplaza los marcadores @partial() en el contenido.
    * Asume que los parciales reciben los mismos datos que la vista principal.
    * @param string $content El contenido donde buscar los parciales.
    * @param string $baseTemplatePath La ruta de la plantilla que contiene el parcial.
    * @return string El contenido con los parciales incrustados.
    */
   protected function extractPartials(string $content, string $baseTemplatePath): string {
      // Patrón para capturar @partial('partialName') o @partial('partialName', 'type')
      $pattern = '/@partial\(([\'"])(?<partial>[^\'"]+)\1(?:,\s*([\'"])(?<type>[^\'"]+)\3)?\)/';

      return preg_replace_callback($pattern, function ($matches) use ($baseTemplatePath) {
         $partialName = $matches['partial'];
         // $type = $matches['type'] ?? null; // Si se necesita un 'type' para el parcial

         try {
            // Cargar la ruta del parcial usando TemplateLoader
            $partialPath = $this->templateLoader->loadPartialPath($partialName, $baseTemplatePath);

            // Renderizar el parcial. Asume que el parcial accede a los datos actuales del DataHandler.
            // Es crucial que el parcial no tenga su propio `extract()` a menos que esté en un ámbito aislado.
            // Podríamos usar el método `render` recursivamente para los parciales.
            return $this->render($partialPath, $this->dataHandler->prepareDataForView());
         } catch (InvalidArgumentException $e) {
            // Opcional: logear el error
            // $this->logger->error("Error al cargar parcial: {$partialName}. " . $e->getMessage());
            return ""; // Mensaje útil en desarrollo
         } catch (\Throwable $e) {
            // Capturar cualquier otra excepción durante el renderizado del parcial
            // $this->logger->critical("Error renderizando parcial {$partialName}: " . $e->getMessage());
            return "";
         }
      }, $content);
   }
}
