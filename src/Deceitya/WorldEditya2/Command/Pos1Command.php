<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Command;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use Deceitya\WorldEditya2\Config\MessageContainer;
use Deceitya\WorldEditya2\Selection\Selection;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

use function count;

/**
 * pos1を設定するコマンド
 *
 * @author deceitya
 */
class Pos1Command extends BaseCommand
{
    protected function prepare(): void
    {
        $this->registerArgument(0, new IntegerArgument('x', true));
        $this->registerArgument(1, new IntegerArgument('y', true));
        $this->registerArgument(2, new IntegerArgument('z', true));

        $this->setPermission('worldeditya2.command.pos1');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage(MessageContainer::get('command.error.run_as_player'));

            return;
        }

        $count = count($args);
        $pos = null;
        if ($count === 0) {
            $pos = Position::fromObject($sender->floor(), $sender->level);
        } elseif ($count === 3) {
            $pos = Position::fromObject(new Vector3($args['x'], $args['y'], $args['z']), $sender->level);
        } else {
            $this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);

            return;
        }

        $selection = Selection::getSelection($sender);
        $selection->setFirstPosition($pos);

        $sender->sendMessage(MessageContainer::get(
            'command.pos1.success',
            (string) $pos->x,
            (string) $pos->y,
            (string) $pos->z,
            $pos->level->getFolderName()
        ));
    }
}
