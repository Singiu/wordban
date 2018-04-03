<?php

namespace Singiu\WordBan;

/**
 * 敏感词过滤类，使用 DFA 算法。
 * 敏感词库数据结构为 Trie Tree。
 *
 * @author Singiu <junxing.lin@foxmail.com>
 * @date 2018-03-23
 */
class WordBanWorker
{
    /**
     * 默认替代字符。
     *
     * @var string
     */
    protected $_escapeChar = '*';

    /**
     * 敏感词库，Trie Tree 格式的数组。
     *
     * @var array
     */
    protected $_wordsTrieTree = array();

    protected $_wordsTrieTreeLowCase = array();

    /**
     * 干扰字符集合。
     *
     * @var array
     */
    protected $_disturbList = array();

    /**
     * 匹配是否区分大小写。
     * @var bool
     */
    protected $_matchCase = true;

    /**
     * WordBan constructor.
     * @param array $sensitiveWords
     * @throws
     */
    public function __construct($sensitiveWords = null)
    {
        if ($sensitiveWords != null && is_array($sensitiveWords)) {
            $this->load($sensitiveWords);
        }
    }

    /**
     * 以数级的形式装载敏感词库，程序会自动将其转成 Trie Tree 格式。
     * 如果词库过大，这个过程会比较消耗性能，所以建议将结果缓存至 Redis 中，后续直接使用 WordBan::setTrieTree 方法来设置词库。
     *
     * @param array $sensitiveWords
     * @param int $trieTree
     * @return true 成功返回 true。
     * @throws
     */
    public function load($sensitiveWords = array(), $trieTree = WordBan::LOAD_WORDS)
    {
        if (!is_array($sensitiveWords) || empty($sensitiveWords)) {
            throw new \Exception('The loaded data is empty!');
        }
        if ($trieTree === WordBan::LOAD_TRIE_TREE && isset($sensitiveWords['Normal']) && isset($sensitiveWords['LowCase'])) {
            $this->_wordsTrieTree = $sensitiveWords['Normal'];
            $this->_wordsTrieTreeLowCase = $sensitiveWords['LowCase'];
        } else {
            foreach ($sensitiveWords as $word) {
                if ($word == '') break;
                $now_words = &$this->_wordsTrieTree;
                $now_words_lower = &$this->_wordsTrieTreeLowCase;
                $word_length = mb_strlen($word);
                for ($i = 0; $i < $word_length; $i++) {
                    $char = mb_substr($word, $i, 1);
                    $char_lower = strtolower($char);
                    if (!isset($now_words[$char])) {
                        $now_words[$char] = false;
                    }
                    if (!isset($now_words_lower[$char_lower])) {
                        $now_words_lower[$char_lower] = false;
                    }
                    $now_words = &$now_words[$char];
                    $now_words_lower = &$now_words_lower[$char_lower];
                }
            }
        }
        return true;
    }

    /**
     * 设置干扰字符集合。
     *
     * @param array $disturbList
     */
    public function setDisturbList($disturbList = array())
    {
        $this->_disturbList = $disturbList;
    }

    /**
     * 设置敏感词替换字符。
     *
     * @param $char
     */
    public function setEscapeChar($char)
    {
        $this->_escapeChar = $char;
    }

    /**
     * 设置程序在进行敏感词匹配时，是否需要对英文字母的大小进行区分。
     * 默认为 true，即对大小写是敏感的。
     *
     * @param boolean $matchCase
     */
    public function setMatchCase($matchCase)
    {
        $this->_matchCase = $matchCase;
    }

    /**
     * 获取解析后的数据，可用于缓存，以节约解析的性能。
     *
     * @return array|null
     */
    public function getTrieTree()
    {
        if ($this->_wordsTrieTree != [] && $this->_wordsTrieTreeLowCase != []) {
            return array(
                'Normal' => $this->_wordsTrieTree,
                'LowCase' => $this->_wordsTrieTreeLowCase
            );
        } else {
            return null;
        }
    }

    /**
     * 扫描并返回检测到的敏感词。
     *
     * @param string $text 要扫描的文本。
     * @param null $replaceList 如果传入此变量，函数会将需要替换的字符组扔进这个变量里，此变量可以在 WordBan::escape 方法中使用。
     * @return array 返回敏感词组成的数组。
     */
    public function scan($text, &$replaceList = null)
    {
        $scan_result = array();
        $text = $this->_matchCase ? $text : strtolower($text);
        $text_length = mb_strlen($text);
        for ($i = 0; $i < $text_length; $i++) {
            $word_length = $this->_check($text, $i, $text_length);
            if ($word_length > 0) {
                $word = mb_substr($text, $i, $word_length);
                $scan_result[] = $word;
                $replaceList !== null && $replaceList[] = str_repeat($this->_escapeChar, mb_strlen($word));
                $i += $word_length - 1;
            }
        }
        return $scan_result;
    }

    /**
     * 将文本中的敏感词使用替代字符替换，返回替换后的文本。
     *
     * @param string $text
     * @return mixed
     */
    public function escape($text)
    {
        $replace_list = array();
        $sensitive_words = $this->scan($text, $replace_list);
        if (empty($sensitive_words)) return $text;
        return $this->_matchCase
            ? str_replace($sensitive_words, $replace_list, $text)
            : str_ireplace($sensitive_words, $replace_list, $text);
    }

    /**
     * 从指定位置开始逐一扫描文本，如果扫描到敏感词，则返回敏感词长度。
     * 如果扫描的第一个字符不是敏感词头，则直接返回0。
     *
     * @param $text
     * @param $beginIndex
     * @param $length
     * @return int
     */
    protected function _check($text, $beginIndex, $length)
    {
        $flag = false;
        $word_length = 0;
        if ($this->_matchCase) {
            $trie_tree = &$this->_wordsTrieTree;
        } else {
            $trie_tree = &$this->_wordsTrieTreeLowCase; // 引用第一层词源。
        }
        for ($i = $beginIndex; $i < $length; $i++) {
            $word = mb_substr($text, $i, 1);
            if (in_array($word, $this->_disturbList)) { // 检查是不是干扰字，是的话指针往前走一步。
                $word_length++;
                continue;
            }
            if (!isset($trie_tree[$word])) { // 一旦发现没有匹配敏感词，则直接跳出。
                break;
            }
            $word_length++;
            if ($trie_tree[$word] !== false) { // 看看是否到达词尾。
                $trie_tree = &$trie_tree[$word]; // 往深层引用，继续检索。
            } else {
                $flag = true;
            }
        }
        $flag || $word_length = 0; // 如果检查到最后一个字条还没有匹配到词尾，则当作没有匹配到。
        return $word_length;
    }
}