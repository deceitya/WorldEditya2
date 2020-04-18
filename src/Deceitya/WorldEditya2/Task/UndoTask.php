<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Task;

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
    /** @var array */
    private $currents;
    /** @var array */
    private $caches;
    /** @var array */
    private $start;
    /** @var array */
    private $end;
    /** @var int */
    private $level;
    /** @var callable|null */
    private $onDone;

    /**
     * @param Chunk[] $currents
     * @param Chunk[] $caches
     * @param Position $start
     * @param Position $end
     */
    public function __construct(array $currents, array $caches, Position $start, Position $end, ?callable $onDone = null)
    {
        $this->currents = array_map(function (Chunk $chunk) {
            return $chunk->fastSerialize();
        }, $currents);
        $this->caches = array_map(function (Chunk $chunk) {
            return $chunk->fastSerialize();
        }, $caches);
        $this->start = [$start->x, $start->y, $start->z];
        $this->end = [$end->x, $end->y, $end->z];
        $this->level = $start->level->getId();
        $this->onDone = $onDone;
    }

    public function onRun()
    {
        $currents = [];
        $caches = [];
        foreach ($this->currents as $chunkData) {
            $chunk = Chunk::fastDeserialize($chunkData);
            $currents[Level::chunkHash($chunk->getX(), $chunk->getZ())] = $chunk;
        }
        foreach ($this->caches as $chunkData) {
            $chunk = Chunk::fastDeserialize($chunkData);
            $caches[Level::chunkHash($chunk->getX(), $chunk->getZ())] = $chunk;
        }

        for ($x = $this->start[0]; $x <= $this->end[0]; $x++) {
            $chunkX = $x >> 4;
            $blockX = $x % 16;
            for ($z = $this->start[2]; $z <= $this->end[2]; $z++) {
                $hash = Level::chunkHash($chunkX, $z >> 4);
                $current = $currents[$hash];
                $cache = $caches[$hash];
                $blockZ = $z % 16;
                for ($y = $this->start[1]; $y <= $this->end[1]; $y++) {
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

        if ($this->onDone !== null) {
            $onDone = $this->onDone;
            $onDone($this);
        }
    }
}
