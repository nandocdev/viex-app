<?php

declare(strict_types=1);

namespace Phast\App\Providers;

use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;

// Ejemplo: Si tuvieras un servicio de pagos.
// use App\Services\Payment\StripeGateway;
// use App\Services\Payment\PaymentGatewayInterface;

class AppServiceProvider implements ServiceProviderInterface {
   /**
    * Registra cualquier servicio específico de la aplicación.
    *
    * @param Container $container
    * @return void
    */
   public function register(Container $container): void {
      // ¡AQUÍ ES DONDE REGISTRAS TUS PROPIOS SERVICIOS!
      // Ejemplo:
      /*
      $container->singleton(PaymentGatewayInterface::class, function ($c) {
          return new StripeGateway(config('services.stripe.secret'));
      });
      */
   }
}