<?php
/**
 * @package     phast/system
 * @subpackage  Console/Commands
 * @file        MakeModelCommand
 * @description Comando para crear un nuevo modelo ORM.
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
   name: 'make:model',
   description: 'Create a new ORM model class.'
)]
class MakeModelCommand extends Command {
   protected function configure(): void {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'The name of the model (e.g., User, PostCategory)');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int {
      $name = ucfirst($input->getArgument('name')); // Aseguramos que el nombre empiece con mayúscula
      $path = PHAST_BASE_PATH . '/app/Models/' . $name . '.php';

      if (file_exists($path)) {
         $output->writeln("<error>Model already exists!</error>");
         return Command::FAILURE;
      }

      $directory = dirname($path);
      if (!is_dir($directory)) {
         mkdir($directory, 0755, true);
      }

      $stub = file_get_contents(PHAST_BASE_PATH . '/system/Console/stubs/model.stub');

      $stub = str_replace(
         ['{{ namespace }}', '{{ class }}'],
         ['Phast\\App\\Models', $name],
         $stub
      );

      file_put_contents($path, $stub);

      $output->writeln("<info>Model created successfully:</info> <comment>{$path}</comment>");

      return Command::SUCCESS;
   }
}