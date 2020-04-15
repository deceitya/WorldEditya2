<?php

declare(strict_types=1);

namespace Deceitya\WorldEditya2\Config;

use Deceitya\WorldEditya2\Main;
use pocketmine\utils\TextFormat;

/**
 * メッセージを格納
 *
 * @author deceitya
 */
class MessageContainer
{
    /** @var array<string=>string> */
    private static $messages = [];

    public static function init()
    {
        self::$messages = parse_ini_file(Main::getInstance()->getResourcesFolder().'message.ini');
    }

    /**
     * テキストを取得
     *
     * @param string $key
     * @param string ...$params
     * @return string
     */
    public static function get(string $key, string ...$params): string
    {
        if (!isset(self::$messages[$key])) {
            return $key;
        }

        $search = [];
        $replace = [];
        $count = count($params);
        for ($i = 0; $i < $count; $i++) {
            $search[] = "%{$i}";
            $replace[] = $params[$i];
        }

        return TextFormat::colorize(str_replace($search, $replace, self::$messages[$key]));
    }

    private function __construct()
    {
    }
}
