<?php

require_once __DIR__ . '/vendor/tareq1988/wp-php-cs-fixer/loader.php';

$finder = PhpCsFixer\Finder::create()
    ->exclude( 'node_modules' )
    ->exclude( 'vendors' )
    ->exclude( 'assets' )
    ->exclude( 'i18n' )
    ->exclude( 'bin' )
    ->in( __DIR__ )
;

$config = PhpCsFixer\Config::create()
    ->registerCustomFixers( [
        new WeDevs\Fixer\SpaceInsideParenthesisFixer(),
        new WeDevs\Fixer\BlankLineAfterClassOpeningFixer(),
    ] )
    ->setRiskyAllowed( true )
    ->setUsingCache( false )
    ->setRules( WeDevs\Fixer\Fixer::rules() )
    ->setFinder( $finder )
;

return $config;
