<?php

declare(strict_types=1);

namespace Phast\System\Providers;

use Phast\System\Core\Application;
use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use Phast\System\Rendering\Contracts\ViewEngine;
use Phast\System\Rendering\Core\DataHandler;
use Phast\System\Rendering\Core\TemplateLoader;
use Phast\System\Rendering\Engines\PhpEngine;
use Phast\System\Rendering\Render;

class ViewServiceProvider implements ServiceProviderInterface {
   public function register(Container $container): void {
      $container->singleton(DataHandler::class, function () {
         return new DataHandler();
      });

      $container->singleton(TemplateLoader::class, function ($c) {
         $app = $c->resolve(Application::class);
         return new TemplateLoader($app->basePath);
      });

      $container->singleton(ViewEngine::class, function ($c) {
         return new PhpEngine(
            $c->resolve(DataHandler::class),
            $c->resolve(TemplateLoader::class)
         );
      });

      $container->singleton(Render::class, function ($c) {
         return new Render(
            $c->resolve(TemplateLoader::class),
            $c->resolve(ViewEngine::class)
         );
      });
   }
}