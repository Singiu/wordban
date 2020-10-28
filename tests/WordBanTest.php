<?php

use PHPUnit\Framework\TestCase;
use Singiu\WordBan\WordBan;

class WordBanTest extends TestCase
{
    public function testEscape()
    {
        $text = 'SB就是傻逼！fuck is a bad word!';
        WordBan::load(['SB', '傻逼', 'fuck']);
        $result = WordBan::escape($text);
        self::assertEquals('**就是**！**** is a bad word!', $result);
    }

    public function testMatchCase()
    {
        $text = 'Sb就是傻逼！Fuck is a bad word!';
        WordBan::setEscapeChar('*'); // 因为是全局单例，所以要将替换字符换回来，不然就还是前一个测试函数中换掉的"x"。
        WordBan::setMatchCase(false);
        $result = WordBan::escape($text);
        self::assertEquals('**就是**！**** is a bad word!', $result);
    }

    public function testMultiEscape()
    {
        $text = 'SB就是傻逼！fuck is a bad word!';
        WordBan::load(['SB', '傻逼', '傻逼靠', 'fuck']);
        $result = WordBan::escape($text);
        self::assertEquals('**就是**！**** is a bad word!', $result);
    }

    public function testReset()
    {
        $text = 'Sb就是傻逼！Fuck is a bad word!';
        WordBan::setMatchCase(false); // 不匹配大小写
        WordBan::load(['sb', '你妈的', 'fuck']);
        $resultOne = WordBan::scan($text);
        self::assertEquals(['sb', '傻逼', 'fuck'], $resultOne);
        WordBan::reset();
        WordBan::load(['就是']);
        $result = WordBan::scan($text);
        self::assertEquals(['就是'], $result);
    }

    public function testSetEscapeChar()
    {
        $text = 'Sb就是傻逼！Fuck is a bad word!';
        WordBan::reset();
        WordBan::load(['SB', '傻逼', 'fuck']);
        WordBan::setEscapeChar('x');
        $result = WordBan::escape($text);
        self::assertEquals('xx就是xx！xxxx is a bad word!', $result);
    }
}