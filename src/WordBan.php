<?php

namespace Singiu\WordBan;

/**
 * Class WordBan
 * @method static boolean load(array $sensitiveWords, int $loadType = 1)
 * @method static array scan(string $text)
 * @method static string escape(string $text)
 * @method static setEscapeChar(string $char)
 * @method static setMatchCase(bool $matchCase)
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
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (self::$_worker == null) {
            self::$_worker = new WordBanWorker();
        }
        if (!method_exists(self::$_worker, $name)) {
            throw new \Exception('Can not found Method: ' . $name);
        }
        return call_user_func_array(array(self::$_worker, $name), $arguments);
    }
}