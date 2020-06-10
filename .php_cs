<?php
$finder = PhpCsFixer\Finder::create()->in('.');
$config = new \Mygento\CS\Config\Symfony();
$config->setFinder($finder);
return $config;
