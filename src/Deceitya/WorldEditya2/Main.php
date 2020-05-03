<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2;

use Deceitya\WorldEditya2\Cache\CacheManager;
use Deceitya\WorldEditya2\Command\Pos1Command;
use Deceitya\WorldEditya2\Command\Pos2Command;
use Deceitya\WorldEditya2\Command\ReplaceCommand;
use Deceitya\WorldEditya2\Command\SetCommand;
use Deceitya\WorldEditya2\Command\UndoCommand;
use Deceitya\WorldEditya2\Config\MessageContainer;
use Deceitya\WorldEditya2\Config\WorldEdityaConfig;
use Deceitya\WorldEditya2\Selection\SelectionListener;
use pocketmine\plugin\PluginBase;

/**
 * PluginBase継承
 *
 * @author deceitya
 */
class Main extends PluginBase
{
    /** @var Main */
    private static $instance;

    public static function getInstance(): Main
    {
        return self::$instance;
    }

    /** @var WorldEdityaConfig */
    private $config;

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        $this->config = new WorldEdityaConfig;
        $this->config->reload();

        MessageContainer::init();
        $this->getServer()->getPluginManager()->registerEvents(new SelectionListener, $this);
        $this->getServer()->getCommandMap()->registerAll('WorldEditya2', [
            new Pos1Command('/pos1', MessageContainer::get('command.pos1.description')),
            new Pos2Command('/pos2', MessageContainer::get('command.pos2.description')),
            new ReplaceCommand('/replace', MessageContainer::get('command.replace.description')),
            new SetCommand('/set', MessageContainer::get('command.set.description')),
            new UndoCommand('/undo', MessageContainer::get('command.undo.description'))
        ]);
    }

    public function onDisable()
    {
        CacheManager::getInstance()->clear();
    }

    public function getResourcesFolder(): string
    {
        return "{$this->getFile()}resources/";
    }

    public function getWEConfing(): WorldedityaConfig
    {
        return $this->config;
    }
}
