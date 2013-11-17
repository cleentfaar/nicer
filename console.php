<?php

/*
 * This file is part of the Nicr CLI package.
 *
 * (c) Cas Leentfaar <info@casleentfaar.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/cilex.phar';

$app = new \Cilex\Application('Nicr', '0.1');
$app->register(new \Cleentfaar\Cilex\Provider\FilesystemServiceProvider());
$app->command(new \Nicr\Command\NicrArrayCommand());
$app->run();