<?php
/**
 * @package     phast/system
 * @subpackage  Console/Commands
 * @file        MakeControllerCommand
 * @description Comando para crear un nuevo controlador.
 */
declare(strict_types=1);

namespace Phast\System\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// El nombre y la descripción del comando se definen aquí, usando el atributo.
#[AsCommand(
   name: 'make:controller',
   description: 'Create a new controller class.'
)]
class MakeControllerCommand extends Command {
   // ¡Ya no necesitamos el constructor ni las propiedades estáticas $defaultName/$defaultDescription!

   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the controller (e.g., UserController, Api/PostController)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = $input->getArgument('name');

      // Añadimos una pequeña limpieza para asegurar que termine en 'Controller' si el usuario no lo pone
      if (!str_ends_with($name, 'Controller')) {
         $name .= 'Controller';
      }

      $path = PHAST_BASE_PATH . '/app/Controllers/' . $name . '.php';

      if (file_exists($path)) {
         $output->writeln("<error>Controller already exists!</error>");
         return Command::FAILURE;
      }

      $directory = dirname($path);
      if (!is_dir($directory)) {
         mkdir($directory, 0755, true);
      }

      $stub = file_get_contents(PHAST_BASE_PATH . '/system/Console/stubs/controller.stub');

      $stub = str_replace(
         ['{{ namespace }}', '{{ class }}'],
         [$this->getNamespace($name), $this->getClassName($name)],
         $stub
      );

      file_put_contents($path, $stub);

      $output->writeln("<info>Controller created successfully:</info> <comment>{$path}</comment>");

      return Command::SUCCESS;
   }

   private function getNamespace(string $name): string {
      $parts = explode('/', str_replace('\\', '/', $name));
      array_pop($parts);
      return 'Phast\\App\\Controllers' . (!empty($parts) ? '\\' . implode('\\', $parts) : '');
   }

   private function getClassName(string $name): string {
      $parts = explode('/', str_replace('\\', '/', $name));
      return array_pop($parts);
   }
}