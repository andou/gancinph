<?php

/**
  Copyright (c) 2013 Matthieu Moquet

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is furnished
  to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
 */

namespace Gancinph\Command;

use Herrera\Phar\Update\Manager;
use Symfony\Component\Console\Input\InputOption;
use Herrera\Json\Exception\FileException;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {

  const MANIFEST_FILE = 'http://andou.github.io/gancinph/manifest.json';

  protected function configure() {
    $this
            ->setName('update')
            ->setDescription('Updates gancinph.phar to the latest version')
            ->addOption('major', null, InputOption::VALUE_NONE, 'Allow major version update')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('Looking for updates...');

    try {
      $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
    } catch (FileException $e) {
      $output->writeln('<error>Unable to search for updates</error>');

      return 1;
    }

    $currentVersion = $this->getApplication()->getVersion();
    $allowMajor = $input->getOption('major');

    if ($manager->update($currentVersion, $allowMajor)) {
      $output->writeln('<info>Updated to latest version</info>');
    } else {
      $output->writeln('<comment>Already up-to-date</comment>');
    }
  }

}
