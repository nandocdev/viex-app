<?php
/**
 * @package     phast/system
 * @subpackage  Rendering/Components
 * @file        AvatarComponent.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20
 * @version     1.1.0
 */

declare(strict_types=1);

namespace Phast\System\Rendering\Components;

class AvatarComponent {
   private string $text;
   private bool $round = true;
   private int $size = 100;

   private string $bgColor = '#ffffff';
   private string $fgColor = '#000000';
   private string $font = 'Arial, sans-serif';
   private int $fontSize = 40;
   private string $fontWeight = 'bold';

   public function __construct(string $name, string $surname) {
      $this->setText($name, $surname);
      $this->setSize();
   }

   public function setText(string $name, string $surname): void {
      $this->text = strtoupper(substr($name, 0, 1) . substr($surname, 0, 1));
   }

   public function setRound(bool $round): void {
      $this->round = $round;
   }

   public function setSize(int $size = 100): void {
      if (!is_int($size) || $size <= 0) {
         throw new \InvalidArgumentException("Size must be a positive integer.");
      }

      $this->size = $size;
      $minSize = 100;
      $this->size = max($minSize, $size);

      // El tamaño de la fuente será el 80% del tamaño de la imagen
      $this->fontSize = (int) ($this->size * 0.75);
   }

   public function setBackgroundColor(string $bgColor): void {
      $this->bgColor = $bgColor;
   }

   public function setForegroundColor(string $fgColor): void {
      $this->fgColor = $fgColor;
   }

   public function setFont(string $font): void {
      $this->font = $font;
   }

   public function setFontSize(int $fontSize): void {
      $this->fontSize = $fontSize;
   }

   public function setFontWeight(string $fontWeight): void {
      $this->fontWeight = $fontWeight;
   }

   public function generate(): string {
      $svg = [
         "<svg xmlns='http://www.w3.org/2000/svg' width='{$this->size}' height='{$this->size}' viewBox='0 0 {$this->size} {$this->size}'>",
         $this->round
         ? "<circle fill='{$this->bgColor}' cx='" . ($this->size / 2) . "' cy='" . ($this->size / 2) . "' r='" . ($this->size / 2) . "' />"
         : "<rect fill='{$this->bgColor}' width='{$this->size}' height='{$this->size}' />",
         "<text x='" . ($this->size / 2) . "' y='" . ($this->size / 2) . "' " .
         "fill='{$this->fgColor}' font-family='{$this->font}' font-size='{$this->fontSize}' font-weight='{$this->fontWeight}' " .
         "text-anchor='middle' dominant-baseline='central'>{$this->text}</text>",
         "</svg>"
      ];

      return implode("\n", $svg);
   }
}
