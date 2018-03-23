<?php

use PHPUnit\Framework\TestCase;
use Singiu\WordBan\WordBan;

class WordBanTest extends TestCase
{
    protected $testData;
    protected $words;
    protected $wordban;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->testData = ['SB', '傻逼', 'fuck'];
        $this->words = 'SB就是傻逼！fuck is a bad word!';
        $this->wordban = new WordBan($this->testData);
    }

    public function testEscape()
    {
        $result = $this->wordban->escape($this->words);
        self::assertEquals('**就是**！**** is a bad word!', $result);
    }

    public function testSetEscapeChar()
    {
        $this->wordban->setEscapeChar('x');
        $result = $this->wordban->escape($this->words);
        self::assertEquals('xx就是xx！xxxx is a bad word!', $result);
    }
}