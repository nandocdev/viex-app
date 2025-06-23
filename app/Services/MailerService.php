<?php
/**
 * @package     viex.com/app
 * @subpackage  Services
 * @file        MailerService
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-23 09:01:00
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService {
   protected PHPMailer $mailer;

   public function __construct() {
      $this->mailer = new PHPMailer(true);
      $this->configure();
   }

   protected function configure(): void {
      $this->mailer->isSMTP();
      $this->mailer->Host = config('mail.host');
      $this->mailer->SMTPAuth = true;
      $this->mailer->Username = config('mail.username');
      $this->mailer->Password = config('mail.password');
      $this->mailer->SMTPSecure = config('mail.encryption');
      $this->mailer->Port = config('mail.port');
      $this->mailer->CharSet = 'UTF-8';

      $fromAddress = config('mail.from.address', 'hello@example.com');
      $fromName = config('mail.from.name', 'Example');
      $this->mailer->setFrom($fromAddress, $fromName);
   }

   public function send(string $to, string $subject, string $body): bool {
      try {
         $this->mailer->addAddress($to);
         $this->mailer->isHTML(true);
         $this->mailer->Subject = $subject;
         $this->mailer->Body = $body;

         $this->mailer->send();
         return true;
      } catch (Exception $e) {
         // En un entorno de producción, deberíamos loguear este error.
         // error_log("Mailer Error: {$this->mailer->ErrorInfo}");
         return false;
      }
   }
}