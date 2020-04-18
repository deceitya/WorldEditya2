<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Task;

use Deceitya\WorldEditya2\Config\MessageContainer;
use pocketmine\block\Block;
use pocketmine\scheduler\AsyncTask;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Server;

use function serialize;
use function unserialize;

/**
 * 置換タスク
 *
 * @author deceitya
 */
class ReplaceTask extends AsyncTask
{
    /** @var array */
    private $chunks;
    /** @var array */
    private $start;
    /** @var array */
    private $end;
    /** @var array */
    private $search;
    /** @var array */
    private $replace;
    /** @var int */
    private $level;
    /** @var callable|null */
    private $onDone;

    /**
     * @param Chunk[] $chunks
     * @param Position $start
     * @param Position $end
     * @param int $id
     * @param int $meta
     * @param callable|null $onDone
     */
    public function __construct(array $chunks, Position $start, Position $end, Block $search, Block $replace, ?callable $onDone = null)
    {
        $this->chunks = array_map(function (Chunk $chunk) {
            return $chunk->fastSerialize();
        }, $chunks);
        $this->start = [$start->x, $start->y, $start->z];
        $this->end = [$end->x, $end->y, $end->z];
        $this->search = [$search->getId(), $search->getDamage()];
        $this->replace = [$replace->getId(), $replace->getDamage()];
        $this->level = $start->level->getId();
        $this->onDone = $onDone;
    }

    public function onRun()
    {
        $chunks = [];
        foreach ($this->chunks as $chunkData) {
            $chunk = Chunk::fastDeserialize($chunkData);
            $chunks[Level::chunkHash($chunk->getX(), $chunk->getZ())] = $chunk;
        }

        for ($x = $this->start[0]; $x <= $this->end[0]; $x++) {
            $chunkX = $x >> 4;
            $blockX = $x % 16;
            for ($z = $this->start[2]; $z <= $this->end[2]; $z++) {
                $blockZ = $z % 16;
                $chunk = $chunks[Level::chunkHash($chunkX, $z >> 4)];
                for ($y = $this->start[1]; $y <= $this->end[1]; $y++) {
                    if ($chunk->getBlockId($blockX, $y, $blockZ) === $this->search[0] && $chunk->getBlockData($blockX, $y, $blockZ) === $this->search[1]) {
                        $chunk->setBlock($blockX, $y, $blockZ, $this->replace[0], $this->replace[1]);
                    }
                }
            }
        }

        $this->setResult(serialize($chunks));
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
