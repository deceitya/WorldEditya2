<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2;

use Deceitya\WorldEditya2\Exception\ChunkNotLoadedException;
use Deceitya\WorldEditya2\Selection\Selection;
use Deceitya\WorldEditya2\Task\ReplaceTask;
use Deceitya\WorldEditya2\Task\SetTask;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;

class WorldEdityaAPI
{
    /**
     * //set
     *
     * @param Level $level
     * @param Vector3 $pos1
     * @param Vector3 $pos2
     * @param Block $block
     * @param callable|null $onDone
     * @return boolean
     */
    public static function set(Level $level, Vector3 $pos1, Vector3 $pos2, Block $block, ?callable $onDone = null): bool
    {
        try {
            $start = Selection::minComponents($pos1, $pos2)->floor();
            $end = Selection::maxComponents($pos1, $pos2)->floor();
            $chunks = self::getChunks($level, $start, $end);

            Server::getInstance()->getAsyncPool()->submitTask(new SetTask($chunks, Position::fromObject($start, $level), Position::fromObject($end, $level), $block, $onDone));
        } catch (\Exception $e) {
            Server::getInstance()->getLogger()->logException($e);

            return false;
        }

        return true;
    }

    /**
     * //replace
     *
     * @param Level $level
     * @param Vector3 $pos1
     * @param Vector3 $pos2
     * @param Block $search
     * @param Block $replace
     * @param callable|null $onDone
     * @return boolean
     */
    public static function replace(Level $level, Vector3 $pos1, Vector3 $pos2, Block $search, Block $replace, ?callable $onDone = null): bool
    {
        try {
            $start = Selection::minComponents($pos1, $pos2)->floor();
            $end = Selection::maxComponents($pos1, $pos2)->floor();
            $chunks = self::getChunks($level, $start, $end);

            Server::getInstance()->getAsyncPool()->submitTask(new ReplaceTask($chunks, Position::fromObject($start, $level), Position::fromObject($end, $level), $search, $replace, $onDone));
        } catch (\Exception $e) {
            Server::getInstance()->getLogger()->logException($e);

            return false;
        }

        return true;
    }

    private static function getChunks(Level $level, Vector3 $start, Vector3 $end): array
    {
        $chunks = [];
        $maxX = $end->x >> 4;
        $maxZ = $end->z >> 4;
        for ($x = $start->x >> 4; $x <= $maxX; $x++) {
            for ($z = $start->z >> 4; $z <= $maxZ; $z++) {
                $chunk = $level->getChunk($x, $z);
                if ($chunk !== null) {
                    $chunks[] = $chunk;
                } else {
                    throw new ChunkNotLoadedException("X:{$x} Z:{$z}");
                }
            }
        }

        return $chunks;
    }

    private function __construct()
    {
    }
}
