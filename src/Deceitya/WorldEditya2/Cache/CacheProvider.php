<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Cache;

use Generator;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function mkdir;
use function str_replace;
use function unlink;

/**
 * キャッシュプロバイダー
 *
 * @author deceitya
 */
class CacheProvider
{
    /** @var string */
    private $folder;
    /** @var string */
    private $ext;

    public function __construct(string $folder, string $ext = '.cache')
    {
        if (!file_exists($folder)) {
            mkdir($folder);
        }

        $this->folder = $folder;
        $this->ext = $ext;
    }

    /**
     * キャッシュ取得
     *
     * @param string $key
     * @return string
     */
    public function get(string $key): string
    {
        $file = $this->getFile($key);
        if (file_exists($file)) {
            return file_get_contents($file);
        } else {
            throw new CacheFileNotFoundException($file);
        }
    }

    /**
     * キャッシュを保存・上書き
     *
     * @param string $key
     * @param string $data
     * @return void
     */
    public function set(string $key, string $data)
    {
        file_put_contents($this->getFile($key), $data);
    }

    /**
     * キャッシュファイル削除
     *
     * @param string $key
     * @return void
     */
    public function delete(string $key)
    {
        return unlink($this->getFile($key));
    }

    /**
     * 全部のキャッシュのキーを取得
     *
     * @return Generator
     */
    public function getKeys(): Generator
    {
        $files = glob("{$this->folder}*{$this->ext}");
        foreach ($files as $file) {
            yield str_replace([$this->folder, $this->ext], ['', ''], $file);
        }
    }

    /**
     * 全キャッシュファイル削除
     *
     * @return void
     */
    public function deleteAll()
    {
        foreach ($this->getKeys() as $key) {
            $this->delete($key);
        }
    }

    /**
     * キャッシュファイル取得
     *
     * @param string $key
     * @return string
     */
    private function getFile(string $key): string
    {
        return "{$this->folder}{$key}{$this->ext}";
    }
}
