<?php
/**
 * @package     system/Rendering
 * @subpackage  Components
 * @file        AcctionButtonsComponent
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 00:35:19
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);
namespace Phast\System\Rendering\Components;

class AcctionButtonsComponent {
   private static $buttons = [
      'add' => [
         'color' => 'success',
         'icon' => 'fa fa-plus',
         'placement' => 'left',
         'title' => 'Agregar',
      ],
      'edit' => [
         'color' => 'primary',
         'icon' => 'fa fa-pencil',
         'placement' => 'bottom',
         'title' => 'Editar',
      ],
      'delete' => [
         'color' => 'danger',
         'icon' => 'fa fa-trash',
         'placement' => 'top',
         'title' => 'Eliminar',
      ],
      'update' => [
         'color' => 'primary',
         'icon' => 'fa fa-save',
         'placement' => 'top',
         'title' => 'Actualizar',
      ],
      'view' => [
         'color' => 'info',
         'icon' => 'fa fa-eye',
         'placement' => 'top',
         'title' => 'Ver',
      ],
      'print' => [
         'color' => 'secondary',
         'icon' => 'fa fa-print',
         'placement' => 'top',
         'title' => 'Imprimir',
      ],
      'download' => [
         'color' => 'secondary',
         'icon' => 'fa fa-download',
         'placement' => 'top',
         'title' => 'Descargar',
      ],
      'upload' => [
         'color' => 'secondary',
         'icon' => 'fa fa-upload',
         'placement' => 'top',
         'title' => 'Subir',
      ],
      'search' => [
         'color' => 'secondary',
         'icon' => 'fa fa-search',
         'placement' => 'top',
         'title' => 'Buscar',
      ],
      'reset' => [
         'color' => 'secondary',
         'icon' => 'fa fa-refresh',
         'placement' => 'top',
         'title' => 'Actualizar',
      ],
      'save' => [
         'color' => 'success',
         'icon' => 'fa fa-save',
         'placement' => 'top',
         'title' => 'Guardar',
      ],
      'cancel' => [
         'color' => 'danger',
         'icon' => 'fa fa-ban',
         'placement' => 'top',
         'title' => 'Cancelar',
      ],
      'back' => [
         'color' => 'secondary',
         'icon' => 'fa fa-arrow-left',
         'placement' => 'top',
         'title' => 'Atrás',
      ],
      'next' => [
         'color' => 'secondary',
         'icon' => 'fa fa-arrow-right',
         'placement' => 'top',
         'title' => 'Siguiente',
      ],
      'previous' => [
         'color' => 'secondary',
         'icon' => 'fa fa-arrow-left',
         'placement' => 'top',
         'title' => 'Anterior',
      ],
      'forward' => [
         'color' => 'secondary',
         'icon' => 'fa fa-arrow-right',
         'placement' => 'top',
         'title' => 'Adelante',
      ],
      'first' => [
         'color' => 'secondary',
         'icon' => 'fa fa-fast-backward',
         'placement' => 'top',
         'title' => 'Primero',
      ],
      'last' => [
         'color' => 'secondary',
         'icon' => 'fa fa-fast-forward',
         'placement' => 'top',
         'title' => 'Último',
      ],
      'list' => [
         'color' => 'secondary',
         'icon' => 'fa fa-list',
         'placement' => 'top',
         'title' => 'Lista',
      ],
      'grid' => [
         'color' => 'secondary',
         'icon' => 'fa fa-th-large',
         'placement' => 'top',
         'title' => 'Cuadrícula',
      ],
      'filter' => [
         'color' => 'secondary',
         'icon' => 'fa fa-filter',
         'placement' => 'top',
         'title' => 'Filtrar',
      ],
      'sort' => [
         'color' => 'secondary',
         'icon' => 'fa fa-sort',
         'placement' => 'top',
         'title' => 'Ordenar',
      ],
      'sort_asc' => [
         'color' => 'secondary',
         'icon' => 'fa fa-sort-asc',
         'placement' => 'top',
         'title' => 'Ordenar Ascendente',
      ],
      'sort_desc' => [
         'color' => 'secondary',
         'icon' => 'fa fa-sort-desc',
         'placement' => 'top',
         'title' => 'Ordenar Descendente',
      ],
      'sort_alpha_asc' => [
         'color' => 'secondary',
         'icon' => 'fa fa-sort-alpha-asc',
         'placement' => 'top',
         'title' => 'Ordenar Alfabéticamente Ascendente',
      ],
      'sort_alpha_desc' => [
         'color' => 'secondary',
         'icon' => 'fa fa-sort-alpha-desc',
         'placement' => 'top',
         'title' => 'Ordenar Alfabéticamente Descendente',
      ],
      'sort_disabled' => [
         'color' => 'secondary',
         'icon' => 'fa fa-sort disabled',
         'placement' => 'top',
         'title' => 'Ordenar por Cantidad Ascendente',
      ],
      'export' => [
         'color' => 'secondary',
         'icon' => 'fa fa-file-excel-o',
         'placement' => 'top',
         'title' => 'Exportar',
      ],
      'import' => [
         'color' => 'secondary',
         'icon' => 'fa fa-file-excel-o',
         'placement' => 'top',
         'title' => 'Importar',
      ],
      'copy' => [
         'color' => 'secondary',
         'icon' => 'fa fa-copy',
         'placement' => 'top',
         'title' => 'Copiar',
      ],
      'cut' => [
         'color' => 'secondary',
         'icon' => 'fa fa-cut',
         'placement' => 'top',
         'title' => 'Cortar',
      ],
      'paste' => [
         'color' => 'secondary',
         'icon' => 'fa fa-paste',
         'placement' => 'top',
         'title' => 'Pegar',
      ],
      'undo' => [
         'color' => 'secondary',
         'icon' => 'fa fa-undo',
         'placement' => 'top',
         'title' => 'Deshacer',
      ],
      'redo' => [
         'color' => 'secondary',
         'icon' => 'fa fa-repeat',
         'placement' => 'top',
         'title' => 'Rehacer',
      ],
      'help' => [
         'color' => 'secondary',
         'icon' => 'fa fa-question-circle',
         'placement' => 'top',
         'title' => 'Ayuda',
      ],
      'info' => [
         'color' => 'secondary',
         'icon' => 'fa fa-info-circle',
         'placement' => 'top',
         'title' => 'Información',
      ],
      'warning' => [
         'color' => 'secondary',
         'icon' => 'fa fa-exclamation-triangle',
         'placement' => 'top',
         'title' => 'Advertencia',
      ],
      'danger' => [
         'color' => 'secondary',
         'icon' => 'fa fa-exclamation-circle',
         'placement' => 'top',
         'title' => 'Error',
      ],
      'success' => [
         'color' => 'secondary',
         'icon' => 'fa fa-check-circle',
         'placement' => 'top',
         'title' => 'Éxito',
      ],
      'close' => [
         'color' => 'secondary',
         'icon' => 'fa fa-times',
         'placement' => 'top',
         'title' => 'Cerrar',
      ],
      'check' => [
         'color' => 'secondary',
         'icon' => 'fa fa-check',
         'placement' => 'top',
         'title' => 'Comprobar',
      ],
      'uncheck' => [
         'color' => 'secondary',
         'icon' => 'fa fa-times',
         'placement' => 'top',
         'title' => 'Desmarcar',
      ],
      'disable' => [
         'color' => 'warning',
         'icon' => 'fa fa-ban',
         'placement' => 'top',
         'title' => 'Estado',
      ],
      'enable' => [
         'color' => 'success',
         'icon' => 'fa fa-check',
         'placement' => 'top',
         'title' => 'Estado',
      ],
      // reset password
      'reset_password' => [
         'color' => 'secondary',
         'icon' => 'fa fa-key',
         'placement' => 'top',
         'title' => 'Restablecer Contraseña',
      ],
   ];

   // crea botones de acción, recibe un array con los botones a crear, el id del registro, el estado del registro y el texto a mostrar

   public static function buttonsAction($buttons = [], $params = [], $text = "", $alt = true) {
      $html = '<div class="btn-group" role="group">';
      foreach ($buttons as $button) {
         // busca inicio espacios en blanco
         $el = '';
         foreach ($params as $key => $value) {
            $el .= ' data-' . $button . '-' . $key . '="' . $value . '" ';
         }
         $altc = '';
         if ($alt) {
            $altc = 'alt-';
         }
         $html .= '<button' . $el . ' type="button" class="btn btn-sm btn-' . $altc . self::$buttons[$button]['color'] . ' btn-' . strtolower(self::$buttons[$button]['title']) . '"';
         $html .= $text == "" ? ' data-bs-toggle="tooltip" data-bs-placement="' . self::$buttons[$button]['placement'] . '" title="' . self::$buttons[$button]['title'] . '"' : '';
         $html .= '>';
         $html .= '<i class="' . self::$buttons[$button]['icon'] . '"></i>';
         if ($text != "") {
            $html .= ' ' . $text;
         }
         $html .= '</button>';
      }
      $html .= '</div>';
      return $html;
   }

   private static function setAttibutes($attributes = []): string {
      $html = '';
      if (array_key_exists('type', $attributes)) {
         $html .= 'type="' . $attributes['type'] . '" ';
         unset($attributes['type']);
      } else {
         $html .= 'type="button" ';
      }
      foreach ($attributes as $key => $value) {
         $html .= 'data-' . $key . '="' . $value . '" ';
      }

      return $html;
   }

   private static function setHtmlClass($button = []): string {
      $html = 'btn btn-sm ';
      $html .= 'btn-alt-' . $button['color'] . ' ';
      $html .= 'btn-' . strtolower($button['title']) . ' ';
      return $html;
   }

   private static function setToolTip($button = []): string {
      $html = 'data-bs-toggle="tooltip" ';
      $html .= 'data-bs-placement="' . $button['placement'] . '" ';
      $html .= 'title="' . $button['title'] . '" ';
      return $html;
   }

   private static function setIcon($button = []): string {
      $html = '<i class="' . $button['icon'] . '"></i>';
      return $html;
   }

   private static function setText($text = ''): string {
      $html = ' ' . $text;
      return $html;
   }

   private static function setHtml($button = [], $attributes = [], $text = ''): string {
      $html = '<button ';
      $html .= self::setAttibutes($attributes);
      $html .= 'class="' . self::setHtmlClass($button) . '" ';
      $html .= $text == "" ? self::setToolTip($button) : '';
      $html .= '>';
      $html .= self::setIcon($button);
      $html .= $text != "" ? self::setText($text) : '';
      $html .= '</button>';
      return $html;
   }

   public static function init($buttons = [], $attributes = [], $text = "") {
      $html = '<div class="btn-group" role="group">';
      foreach ($buttons as $button) {
         $html .= self::setHtml(self::$buttons[$button], $attributes, $text);
      }
      $html .= '</div>';
      return $html;
   }
}