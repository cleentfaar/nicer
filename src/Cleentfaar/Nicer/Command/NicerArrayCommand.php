<?php

/*
 * This file is part of the Nicer CLI package.
 *
 * (c) Cas Leentfaar <info@casleentfaar.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cleentfaar\Nicer\Command;

use Cilex\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Example command for testing purposes.
 */
class NicerArrayCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('nicer:array')
            ->setDescription('Niceify a file that returns an array, such as a flat translation or configuration file')
            ->addArgument('file', InputArgument::REQUIRED, 'The file to nicefy which returns an array. Currently only php arrays are supported')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Use this option to only show what contents would be written to the file, without actually writing to it')
            ->addOption('indent-keys', 'i', InputOption::VALUE_REQUIRED, 'Use this option to indent keys with a tab-character, can be either true (default) or false', true)
            ->addOption('filter', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Use this option to apply a number of filters to the final formatted array.', array('alphabetic'))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $filters = $input->getOption('filter');
        $filesystem = $this->getService('filesystem');
        if (!$filesystem->exists($file)) {
            $output->writeln(sprintf("<error>The file argument given does not point to an existing file (attempted '%s')</error>", $file));
            return 1;
        }
        if (!is_readable($file)) {
            $output->writeln(sprintf("<error>The nicer:array command does not have acces to the given file (%s), make sure you execute the command with enough permissions</error>", $file));
            return 1;
        }
        $array = $this->getArrayFromFile($file);
        if (!is_array($array)) {
            $output->writeln(sprintf("<error>The command expected the given file to return a PHP array, but an %s was given instead</error>", gettype($array)));
            return 1;
        }
        $indentKeys = $input->getOption('indent-keys') ? true : false;
        $newContent = "";
        $newContent .= "<?php\n";
        $newContent .= "return array(\n";
        $this->applyFilters($filters, $array);
        foreach ($array as $key => $value) {
            $newContent .= ($indentKeys ? "\t" : "") . "'$key' => '$value',\n";
        }
        $newContent .= ");\n";
        if ($input->getOption('dry-run') == true) {
            $output->writeln(sprintf("Dry-run: new content of file '%s' would be:", $file));
            $output->writeln($newContent);
        } else {
            $output->writeln(sprintf("Writing new content to file '%s'", $file));
            $output->writeln($newContent);
            $success = $this->writeToFile($file, $newContent);
            $fh = fopen($file, 'w+');
            if ($success === false) {
                $output->writeln("FAILED!");
                $output->writeln("<error>The command could not open the given file to write the new content, make sure another program isn't using it</error>");
                return 1;
            }
            $output->writeln(sprintf("Cleaned up array with %s entries", count($array)));
        }
    }
    private function applyFilters(array $filters, array $array)
    {
        foreach ($filters as $filter) {
            switch ($filter) {
                case 'alphabetic':
                    ksort($array, SORT_ASC);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf("There is no filter with the name '%s' available for formatting", $filter));
            }
        }
        return $array;
    }

    private function getArrayFromFile($file)
    {
        ob_start();
        $fileReturn = include($file);
        ob_end_clean();
        return $fileReturn;
    }

    private function writeToFile($file, $content)
    {
        $fh = fopen($file, 'w+');
        if ($fh === false) {
            return false;
        }
        fwrite($fh, $content);
        fclose($fh);
        return true;
    }
}
