<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2;

use Deceitya\WorldEditya2\Command\Pos1Command;
use Deceitya\WorldEditya2\Command\Pos2Command;
use Deceitya\WorldEditya2\Command\SetCommand;
use Deceitya\WorldEditya2\Config\MessageContainer;
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

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        MessageContainer::init();
        $this->getServer()->getPluginManager()->registerEvents(new SelectionListener, $this);
        $this->getServer()->getCommandMap()->registerAll('WorldEditya2', [
            new Pos1Command('/pos1', MessageContainer::get('command.pos1.description')),
            new Pos2Command('/pos2', MessageContainer::get('command.pos2.description')),
            new SetCommand('/set', MessageContainer::get('command.set.description'))
        ]);
    }

    public function getResourcesFolder(): string
    {
        return "{$this->getFile()}resources/";
    }
}
