<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Task;

use Deceitya\WorldEditya2\Config\MessageContainer;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

use function array_map;
use function serialize;
use function unserialize;

class UndoTask extends AsyncTask
{
    /** @var string */
    private $currents;
    /** @var string */
    private $caches;
    /** @var string */
    private $start;
    /** @var string */
    private $end;
    /** @var int */
    private $level;

    /**
     * @param Chunk[] $currents
     * @param Chunk[] $caches
     * @param Position $start
     * @param Position $end
     */
    public function __construct(array $currents, array $caches, Position $start, Position $end)
    {
        $this->currents = serialize(array_map(function (Chunk $chunk) {
            return $chunk->fastSerialize();
        }, $currents));
        $this->caches = serialize(array_map(function (Chunk $chunk) {
            return $chunk->fastSerialize();
        }, $caches));
        $this->start = serialize([$start->x, $start->y, $start->z]);
        $this->end = serialize([$end->x, $end->y, $end->z]);
        $this->level = $start->level->getId();
    }

    public function onRun()
    {
        $start = unserialize($this->start);
        $end = unserialize($this->end);
        $currents = [];
        $caches = [];
        foreach (unserialize($this->currents) as $chunkData) {
            $chunk = Chunk::fastDeserialize($chunkData);
            $currents[Level::chunkHash($chunk->getX(), $chunk->getZ())] = $chunk;
        }
        foreach (unserialize($this->caches) as $chunkData) {
            $chunk = Chunk::fastDeserialize($chunkData);
            $caches[Level::chunkHash($chunk->getX(), $chunk->getZ())] = $chunk;
        }

        for ($x = $start[0]; $x <= $end[0]; $x++) {
            $chunkX = $x >> 4;
            $blockX = $x % 16;
            for ($z = $start[2]; $z <= $end[2]; $z++) {
                $hash = Level::chunkHash($chunkX, $z >> 4);
                $current = $currents[$hash];
                $cache = $caches[$hash];
                $blockZ = $z % 16;
                for ($y = $start[1]; $y <= $end[1]; $y++) {
                    $current->setBlock(
                        $blockX,
                        $y,
                        $blockZ,
                        $cache->getBlockId($blockX, $y, $blockZ),
                        $cache->getBlockData($blockX, $y, $blockZ)
                    );
                }
            }
        }

        $this->setResult(serialize($currents));
    }

    public function onCompletion(Server $server)
    {
        $level = $server->getLevel($this->level);
        foreach (unserialize($this->getResult()) as $chunk) {
            $level->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
        }

        $server->broadcastMessage(MessageContainer::get('command.undo.complete', (string) $this->getTaskId()));
    }
}
