<?php

use PHPUnit\Framework\TestCase;
use Singiu\WordBan\WordBan;

class WordBanTest extends TestCase
{
    protected $text;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $test_data = ['SB', '傻逼', 'fuck'];
        $this->text = 'SB就是傻逼！fuck is a bad word!';
        WordBan::load($test_data);
    }

    public function testEscape()
    {
        $result = WordBan::escape($this->text);
        self::assertEquals('**就是**！**** is a bad word!', $result);
    }

    public function testSetEscapeChar()
    {
        WordBan::setEscapeChar('x');
        $result = WordBan::escape($this->text);
        self::assertEquals('xx就是xx！xxxx is a bad word!', $result);
    }

    public function testMatchCase()
    {
        $this->text = 'Sb就是傻逼！Fuck is a bad word!';
        WordBan::setEscapeChar('*'); // 因为是全局单例，所以要将替换字符换回来，不然就还是前一个测试函数中换掉的"x"。
        WordBan::setMatchCase(false);
        $result = WordBan::escape($this->text);
        self::assertEquals('**就是**！**** is a bad word!', $result);
    }
}