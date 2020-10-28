<?php

namespace Singiu\WordBan;

use Exception;

/**
 * Class WordBan
 *
 * @method static array|null getTrieTree()
 * @method static boolean load(array $sensitiveWords, int $loadType = 1)
 * @method static string escape(string $text)
 * @method static void reset()
 * @method static array scan(string $text)
 * @method static void setDisturbList(array $disturbList = [])
 * @method static void setEscapeChar(string $char)
 * @method static void setMatchCase(bool $matchCase)
 */
class WordBan
{
    const LOAD_WORDS = 1;
    const LOAD_TRIE_TREE = 2;
    /**
     * @var WordBanWorker
     */
    private static $_worker;

    /**
     * 静态方法解析执行。
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (static::$_worker == null) {
            static::$_worker = new WordBanWorker();
        }
        if (!method_exists(static::$_worker, $name)) {
            throw new Exception('Can not found Method: ' . $name);
        }
        return call_user_func_array([static::$_worker, $name], $arguments);
    }
}