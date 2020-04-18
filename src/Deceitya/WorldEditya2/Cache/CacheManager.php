<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Cache;

use Deceitya\WorldEditya2\Main;

use function array_shift;
use function count;
use function serialize;
use function unserialize;

/**
 * キャッシュマネージャー
 *
 * @author deceitya
 */
class CacheManager
{
    /** @var CacheManager */
    private static $instance;

    /**
     * インスタンス取得
     *
     * @return CacheManager
     */
    public static function getInstance(): CacheManager
    {
        if (!isset(self::$instance)) {
            self::$instance = new CacheManager;
        }

        return self::$instance;
    }

    /** @var CacheProvider */
    private $provider;
    /** @var array */
    private $caches = [];

    private function __construct()
    {
        $this->provider = new CacheProvider(Main::getInstance()->getDataFolder().'caches/');
    }

    /**
     * キャッシュを追加
     *
     * @param string $player
     * @param WECache $cache
     * @return void
     */
    public function add(string $player, WECache $cache)
    {
        if (!isset($this->caches[$player])) {
            $this->caches[$player] = [];
        }

        $key = "$player".count($this->caches[$player]);
        array_unshift($this->caches[$player], $key);
        $this->provider->set($key, serialize($cache));
    }

    /**
     * 一番新しいキャッシュを取得
     *
     * @param string $player
     * @return WECache|null
     */
    public function drop(string $player): ?WECache
    {
        if (!isset($this->caches[$player])) {
            return null;
        }

        $key = array_shift($this->caches[$player]);
        if ($key === null) {
            return null;
        }

        $cache = unserialize($this->provider->get($key));
        $this->provider->delete($key);

        return $cache;
    }

    /**
     * キャッシュを全削除
     *
     * @return void
     */
    public function clear()
    {
        $this->caches = [];
        $this->provider->deleteAll();
    }
}
