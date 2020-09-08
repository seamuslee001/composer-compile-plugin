<?php

namespace Civi\CompilePlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class CompilePlugin implements PluginInterface, EventSubscriberInterface, Capable
{

    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;

    public static function getSubscribedEvents()
    {
        return [
          ScriptEvents::POST_INSTALL_CMD => ['runTasks', 5],
          ScriptEvents::POST_UPDATE_CMD => ['runTasks', 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities()
    {
        return [
          'Composer\Plugin\Capability\CommandProvider' => CommandProvider::class,
        ];
    }


    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function runTasks(Event $event)
    {
        $active = getenv('COMPOSER_COMPILE_PLUGIN');
        if ($active === '0' || $active === 0 || $active === 'off') {
            return;
        }

        $taskList = new TaskList($this->composer, $this->io);
        $taskList->load();
        TaskRunner::create()->run($this->io, $taskList->getAll());
    }

}
