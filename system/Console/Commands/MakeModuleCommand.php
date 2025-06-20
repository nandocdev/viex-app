<?php
/**
 * @package     system/Console
 * @subpackage  Commands
 * @file        MakeModuleCommand
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 10:26:08
 * @version     1.0.0
 * @description crea un modulo con la siguiente estructura:
 *             - app/Modules/<ModuleName>
 *            - app/Modules/<ModuleName>/Controllers
 *            - app/Modules/<ModuleName>/Controllers/<ModuleName>Controller.php
 *            - app/Modules/<ModuleName>/Models
 *            - app/Modules/<ModuleName>/Models/Entities
 *             - app/Modules/<ModuleName>/Models/Entities/<ModuleName>Entity.php
 *            - app/Modules/<ModuleName>/Models/Repositories
 *            - app/Modules/<ModuleName>/Models/ValueObjects
 *           - app/Modules/<ModuleName>/Services
 *            - app/Modules/<ModuleName>/routes.php
 */
declare(strict_types=1);
namespace Phast\System\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// El nombre y la descripción del comando se definen aquí.
#[AsCommand(
   name: 'make:module',
   description: 'Create a new module structure.'
)]

class MakeModuleCommand extends Command {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the module (e.g., User, PostCategory)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = camel_case($input->getArgument('name')); // Aseguramos que el nombre empiece con mayúscula
      $basePath = PHAST_BASE_PATH . '/app/Modules/' . $name;
      $prefix = strtolower($name);
      $viewPath = $prefix . '/index.view.phtml';
      $viewName = $prefix . '/index';
      $fullViewPath = PHAST_BASE_PATH . '/resources/views/' . $prefix;
      $baseControllerNamespace = "$name\Controllers\\$name" . 'Controller';



      if (is_dir($basePath)) {
         $output->writeln("<error>Module already exists!</error>");
         return Command::FAILURE;
      }

      // Creamos la estructura de directorios
      mkdir($basePath, 0755, true);
      $output->writeln("📁 Creado modulo: $name");

      mkdir($basePath . '/Controllers', 0755, true);
      $output->writeln("├── 📁 Creado carpeta: Controllers");

      // Crear el controlador del módulo
      $controllerNamespace = $name . '\\Controllers';
      $controllerStub = file_get_contents(PHAST_BASE_PATH . '/system/Console/stubs/module_controller.stub');
      $controllerStub = str_replace('{{ class }}', $name . 'Controller', $controllerStub);
      $controllerStub = str_replace('{{ namespace }}', $this->getNamespace($controllerNamespace), $controllerStub);
      $controllerStub = str_replace('{{ viewPath }}', $viewName, $controllerStub);
      file_put_contents($basePath . '/Controllers/' . $name . 'Controller.php', $controllerStub);
      $output->writeln("├  ├── 📄 Creado controlador: {$name}Controller.php");

      mkdir($basePath . '/Models', 0755, true);
      $output->writeln("├── 📁 Creado carpeta: Models");

      mkdir($basePath . '/Models/Entities', 0755, true);
      $output->writeln("├  ├── 📁 Creado carpeta: Entities");
      // Crear la entidad del módulo
      $entityNamespace = $name . '\\Models\\Entities';
      $entityStub = file_get_contents(PHAST_BASE_PATH . '/system/Console/stubs/module_entity.stub');
      $entityStub = str_replace('{{ class }}', $name . 'Entity', $entityStub);
      $entityStub = str_replace('{{ namespace }}', $this->getNamespace($entityNamespace), $entityStub);
      file_put_contents($basePath . '/Models/Entities/' . $name . 'Entity.php', $entityStub);
      $output->writeln("├  ├  ├── 📄 Creado entidad: {$name}Entity.php");

      mkdir($basePath . '/Models/Repositories', 0755, true);
      $output->writeln("├  ├── 📁 Creado carpeta: Repositories");

      mkdir($basePath . '/Models/ValueObjects', 0755, true);
      $output->writeln("├  ├── 📁 Creado carpeta: ValueObjects");

      mkdir($basePath . '/Services', 0755, true);
      $output->writeln("├── 📁 Creado carpeta: Services");

      mkdir($fullViewPath, 0755, true);
      $output->writeln("├── 📁 Creado carpeta: resources/views/{$prefix}");

      // Crear el archivo de rutas del módulo {{ prefix }}, {{ moduleNamespace }}, {{routename}}
      $routesStub = file_get_contents(PHAST_BASE_PATH . '/system/Console/stubs/module_routes.stub');
      $routesStub = str_replace('{{ prefix }}', $prefix, $routesStub);
      $routesStub = str_replace('{{ moduleNamespace }}', $baseControllerNamespace, $routesStub);
      $routesStub = str_replace('{{ routename }}', $prefix . '.index', $routesStub);
      file_put_contents($basePath . '/routes.php', $routesStub);
      $output->writeln("├── 📄 Creado archivo de rutas: {$name}/routes.php");

      // Crear el archivo de vistas del módulo {{ pageTitle }}, {{ projectName }}, {{ actionView }}
      $viewStub = file_get_contents(PHAST_BASE_PATH . '/system/Console/stubs/module_view.stub');
      $viewStub = str_replace('{{ pageTitle }}', "{$name} Module", $viewStub);
      $viewStub = str_replace('{{ projectName }}', config('app.name'), $viewStub);
      $viewStub = str_replace('{{ actionView }}', $viewPath, $viewStub);
      file_put_contents(PHAST_BASE_PATH . '/resources/views/' . $viewPath, $viewStub, FILE_APPEND);
      $output->writeln("├  ├── 📄 Creado vista: {$viewPath}");


      $output->writeln("<info>Module created successfully:</info> <comment>{$basePath}</comment>");

      return Command::SUCCESS;
   }

   private function getNamespace(string $name): string {
      $parts = explode('/', str_replace('\\', '/', $name));
      // array_pop($parts);
      return 'Phast\\App\\Modules' . (!empty($parts) ? '\\' . implode('\\', $parts) : '');
   }

   private function getClassName(string $name): string {
      $parts = explode('/', str_replace('\\', '/', $name));
      return array_pop($parts);
   }
}