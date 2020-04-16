<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Config;

use Deceitya\WorldEditya2\Main;

/**
 * コンフィグ
 *
 * @author deceitya
 */
class WorldedityaConfig
{
    /** @var int */
    private $selectionItemId;

    public function __construct()
    {
        $this->reload();
    }

    /**
     * リロード
     *
     * @return void
     */
    public function reload()
    {
        Main::getInstance()->reloadConfig();
        $config = Main::getInstance()->getConfig();

        $this->selectionItemId = $config->get('pos-item');
    }

    /**
     * 範囲選択アイテムIDを取得
     *
     * @return integer
     */
    public function getSelectionItemId(): int
    {
        return $this->selectionItemId;
    }
}
