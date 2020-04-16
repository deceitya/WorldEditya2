<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Task;

use Deceitya\WorldEditya2\Config\MessageContainer;
use pocketmine\scheduler\AsyncTask;
use pocketmine\level\format\Chunk;
use pocketmine\level\Position;
use pocketmine\Server;

use function serialize;
use function unserialize;

/**
 * 設置タスク
 *
 * @author deceitya
 */
class SetTask extends AsyncTask
{
    /** @var string */
    private $start;
    /** @var string */
    private $end;
    /** @var string*/
    private $chunks;
    /** @var int */
    private $id;
    /** @var int */
    private $meta;
    /** @var int */
    private $level;

    /**
     * @param Chunk[] $chunks
     * @param Position $start
     * @param Position $end
     * @param int $id
     * @param int $meta
     */
    public function __construct(array $chunks, Position $start, Position $end, int $id, int $meta)
    {
        $this->start = serialize([$start->x, $start->y, $start->z]);
        $this->end = serialize([$end->x, $end->y, $end->z]);
        $this->chunks = serialize(array_map(function (Chunk $chunk) {
            return $chunk->fastSerialize();
        }, $chunks));
        $this->id = $id;
        $this->meta = $meta;
        $this->level = $start->level->getId();
    }

    public function onRun()
    {
        $start = unserialize($this->start);
        $end = unserialize($this->end);
        $chunks = [];
        foreach (unserialize($this->chunks) as $chunkData) {
            $chunk = Chunk::fastDeserialize($chunkData);
            $chunks[$chunk->getX()][$chunk->getZ()] = $chunk;
        }

        for ($x = $start[0]; $x <= $end[0]; $x++) {
            for ($z = $start[2]; $z <= $end[2]; $z++) {
                $chunk = $chunks[$x >> 4][$z >> 4];
                for ($y = $start[1]; $y <= $end[1]; $y++) {
                    $chunk->setBlock($x % 16, $y, $z % 16, $this->id, $this->meta);
                }
            }
        }

        $results = [];
        foreach ($chunks as $xx) {
            foreach ($xx as $zz => $chunk) {
                $results[] = $chunk;
            }
        }
        $this->setResult(serialize($results));
    }

    public function onCompletion(Server $server)
    {
        $level = $server->getLevel($this->level);
        foreach (unserialize($this->getResult()) as $chunk) {
            $level->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
        }

        $server->broadcastMessage(MessageContainer::get('command.set.complete', (string) $this->getTaskId()));
    }
}
