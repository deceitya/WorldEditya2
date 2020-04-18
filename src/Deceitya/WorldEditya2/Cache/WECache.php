<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Cache;

use Serializable;
use pocketmine\level\format\Chunk;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;

use function array_map;
use function serialize;
use function unserialize;

/**
 * キャッシュ
 *
 * @author deceitya
 */
class WECache implements Serializable
{
    /** @var Chunk[] */
    private $chunks = [];
    /** @var Position */
    private $start;
    /** @var Position */
    private $end;

    /**
     * @param Chunk[] $chunks
     * @param Position $start
     * @param Position $end
     */
    public function __construct(array $chunks, Position $start, Position $end)
    {
        $this->chunks = $chunks;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return Chunk[]
     */
    public function getChunks(): array
    {
        return $this->chunks;
    }

    public function getStartPosition(): Position
    {
        return $this->start;
    }

    public function getEndPosition(): Position
    {
        return $this->end;
    }

    public function serialize()
    {
        return serialize([
            array_map(function (Chunk $chunk) {
                return $chunk->fastSerialize();
            }, $this->chunks),
            [$this->start->x, $this->start->y, $this->start->z],
            [$this->end->x, $this->end->y, $this->end->z],
            $this->start->level->getFolderName(),
        ]);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        Server::getInstance()->loadLevel($data[3]);
        $level = Server::getInstance()->getLevelByName($data[3]);
        $this->chunks = array_map(function ($chunkData) {
            return Chunk::fastDeserialize($chunkData);
        }, $data[0]);
        $this->start = Position::fromObject(new Vector3($data[1][0], $data[1][1], $data[1][2]), $level);
        $this->end = Position::fromObject(new Vector3($data[2][0], $data[2][1], $data[2][2]), $level);
    }
}
