<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Command;

use CortexPE\Commando\BaseCommand;
use Deceitya\WorldEditya2\Cache\CacheManager;
use Deceitya\WorldEditya2\Config\MessageContainer;
use Deceitya\WorldEditya2\Task\UndoTask;
use pocketmine\command\CommandSender;
use pocketmine\Player;

/**
 * 戻すコマンド
 *
 * @author deceitya
 */
class UndoCommand extends BaseCommand
{
    protected function prepare(): void
    {
        $this->setPermission('worldeditya2.command.undo');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(MessageContainer::get('command.error.run_as_player'));

            return;
        }

        $cache = CacheManager::getInstance()->drop($sender->getName());
        if ($cache === null) {
            $sender->sendMessage(MessageContainer::get('command.undo.no_cache'));

            return;
        }

        $level = $cache->getStartPosition()->level;
        $chunks = [];
        foreach ($cache->getChunks() as $chunk) {
            $chunks[] = $level->getChunk($chunk->getX(), $chunk->getZ());
        }

        $task = new UndoTask($chunks, $cache->getChunks(), $cache->getStartPosition(), $cache->getEndPosition());
        $sender->getServer()->getAsyncPool()->submitTask($task);

        $sender->getServer()->broadcastMessage(MessageContainer::get(
            'command.undo.start',
            (string) $task->getTaskId(),
            $sender->getName()
        ));
    }
}
