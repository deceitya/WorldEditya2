<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Selection;

use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\Player;

use function min;
use function max;

/**
 * 選択座標
 *
 * @author deceitya
 */
class Selection
{
    /** @var array[string=>Selection] */
    private static $selections = [];

    /**
     * プレイヤーのSelectionを返す
     *
     * @param Player $player
     * @return Selection
     */
    public static function getSelection(Player $player): Selection
    {
        $name = $player->getName();
        if (!isset(self::$selections[$name])) {
            self::$selections[$name] = new Selection;
        }

        return self::$selections[$name];
    }

    public static function maxComponents(Vector3 ...$positions): Vector3
    {
        $xList = $yList = $zList = [];
        foreach ($positions as $position) {
            $xList[] = $position->x;
            $yList[] = $position->y;
            $zList[] = $position->z;
        }

        return new Vector3(max($xList), max($yList), max($zList));
    }

    public static function minComponents(Vector3 ...$positions): Vector3
    {
        $xList = $yList = $zList = [];
        foreach ($positions as $position) {
            $xList[] = $position->x;
            $yList[] = $position->y;
            $zList[] = $position->z;
        }

        return new Vector3(min($xList), min($yList), min($zList));
    }

    /** @var Position|null */
    private $pos1 = null;
    /** @var Position|null */
    private $pos2 = null;

    public function getFirstPosition(): ?Position
    {
        return $this->pos1;
    }

    public function setFirstPosition(Position $pos)
    {
        $this->pos1 = $pos;
    }

    public function getSecondPosition(): ?Position
    {
        return $this->pos2;
    }

    public function setSecondPosition(Position $pos)
    {
        $this->pos2 = $pos;
    }

    /**
     * 選択範囲のブロック数
     *
     * @return integer
     */
    public function count(): int
    {
        if ($this->pos1 instanceof Position && $this->pos2 instanceof Position) {
            $min = self::minComponents($this->pos1, $this->pos2);
            $max = self::maxComponents($this->pos1, $this->pos2);

            return ($max->x - $min->x + 1) * ($max->y - $min->y + 1) * ($max->z - $min->z + 1);
        }

        return 0;
    }

    /**
     * 実行可能か
     *
     * @return boolean pos1とpos2のワールドが違う場合false
     */
    public function canExecute(): bool
    {
        return ($this->pos1 instanceof Position && $this->pos2 instanceof Position) && $this->pos1->level === $this->pos2->level;
    }
}
