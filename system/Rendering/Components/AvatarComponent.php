<?php
/**
 * @package     system/Rendering
 * @subpackage  Components
 * @file        AvatarComponent
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 00:34:24
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Rendering\Components;

class AvatarComponent {
   private $text;
   private $round;
   private $size;
   private $bg_color;
   private $fg_color;
   private $font;
   private $font_size;
   private $font_weight;

   public function __construct($name, $surname) {
      $this->setText($name, $surname);
      $this->round = true;
      $this->size = 100;
      $this->bg_color = '#ffffff';
      $this->fg_color = '#000000';
      $this->font = "Arial, sans-serif";
      $this->font_size = 40;
      $this->font_weight = 'bold';
   }

   public function setText($name, $surname) {
      $this->text = strtoupper(substr($name, 0, 1) . substr($surname, 0, 1));
   }

   public function setRound($round) {
      $this->round = $round;
   }

   public function setSize($size) {
      $this->size = $size;
   }

   public function setBackgroundColor($bg_color) {
      $this->bg_color = $bg_color;
   }

   public function setForegroundColor($fg_color) {
      $this->fg_color = $fg_color;
   }

   public function setFont($font) {
      $this->font = $font;
   }

   public function setFontSize($font_size) {
      $this->font_size = $font_size;
   }

   public function setFontWeight($font_weight) {
      $this->font_weight = $font_weight;
   }

   public function generate() {
      $svg = "<svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'";
      $svg .= " width='" . $this->size . "px' height='" . $this->size . "px' viewBox='0 0 " . $this->size . " " . $this->size . "'";
      $svg .= " version='1.1'>";

      if ($this->round) {
         // Generar un círculo con el color de fondo
         $svg .= "<circle fill='" . $this->bg_color . "' cx='" . $this->size / 2 . "' cy='" . $this->size / 2 . "' r='" . $this->size / 2 . "' />";
      } else {
         // Generar un rectángulo con el color de fondo
         $svg .= "<rect fill='" . $this->bg_color . "' width='" . $this->size . "' height='" . $this->size . "' />";
      }

      // Generar el texto del avatar en el centro del círculo o rectángulo
      $svg .= "<text x='" . $this->size / 2 . "' y='" . $this->size / 2 . "' fill='" . $this->fg_color . "' font-family='" . $this->font . "' font-size='" . $this->font_size . "' font-weight='" . $this->font_weight . "' text-anchor='middle' alignment-baseline='central'>" . $this->text . "</text>";

      $svg .= "</svg>";

      return $svg;
   }
}