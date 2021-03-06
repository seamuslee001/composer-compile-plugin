<?php

namespace Civi\CompilePlugin\Tests;

use Composer\Plugin\PluginInterface;
use ProcessHelper\ProcessHelper as PH;

/**
 * Class DownloadTest
 * @package Civi\CompilePlugin\Tests
 *
 * This is general integration test of the plugin. It runs composer.json with
 * a combination of composer-compile-plugin and composer-downloads-plugin.
 *
 * Ensure that `extra.downloads` run before `extra.compile`.
 */
class DownloadTest extends IntegrationTestCase
{

    public static function getComposerJson()
    {
        return parent::getComposerJson() + [
            'name' => 'test/download-test',
            'require' => [
                'civicrm/composer-compile-plugin' => '@dev',
                'test/rosti' => '@dev',
            ],
            'minimum-stability' => 'dev',
        ];
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::initTestProject(static::getComposerJson());
    }

    /**
     * When running 'composer install', it should generate 'jam.out' with suitable patches in place.
     */
    public function testComposerInstall()
    {
        $version = PH::runOk('composer --version')->getOutput();
        if (!preg_match(';version 1;', $version)) {
            $this->markTestSkipped('Cannot test civicrm/composer-downloads-plugin on composer v2. It does not yet support v2.');
        }

        $this->assertFileNotExists('vendor/test/rosti/potato.in');
        $this->assertFileNotExists('vendor/test/rosti/rosti.out');

        PH::runOk('COMPOSER_COMPILE=1 composer install -v');

        $rostiOut = trim(file_get_contents(self::getTestDir() . '/vendor/test/rosti/rosti.out'));
        $this->assertEquals('GNU AFFERO GENERAL PUBLIC LICENSE', $rostiOut);
    }
}
