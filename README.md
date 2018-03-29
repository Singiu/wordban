
# 简单敏感词过滤组件，采用 DFA 算法。

[![Latest Stable Version](https://poser.pugx.org/singiu/wordban/v/stable)](https://packagist.org/packages/singiu/wordban)
[![Total Downloads](https://poser.pugx.org/singiu/wordban/downloads)](https://packagist.org/packages/singiu/wordban)
[![Latest Unstable Version](https://poser.pugx.org/singiu/wordban/v/unstable)](https://packagist.org/packages/singiu/wordban)
[![License](https://poser.pugx.org/singiu/wordban/license)](https://packagist.org/packages/singiu/wordban)

我们的应用往往要营造良好的交流氛围，或因一些政策上的原因，我们需要限制输出用户的某些输入内容（我们称之为敏感词），但用户的输入是不确定的，一般的我们都会对用户的输入进行二次处理，将一些敏感词进行过滤替换的操作。
如果你也有这方面的需要的话，那这个包可以很方便的实现这一功能。

## 基本用法

### 替换操作

首先你需要构建一个敏感词库，这个包中没有另外提供，你需要根据你自己的需要来创建。你可以选择保存在文件中，也可以存入到数据库中，但最终你需要将它们编成一个数组，类似这样：

```php
$sensitive_words = array(
  'SB', '傻逼', 'Fuck'
);
```

然后你就可以像这样简单的使用：

```php

use Singiu\WordBan;

$text = 'SB就是傻逼！fuck is a bad word!';

WordBan::load($sensitive_words);
$result = WordBan::escape($text); // escape 方法会将文本中找到的敏感词使用替代词（默认是*）替换掉。
echo $result; // **就是**！**** is a bad word!

// 你也可以改变默认的替换字符，如换成 'x'。
WordBan::setEscapeChar('x');
echo WordBan::escape($text); // xx就是xx！xxxx is a bad word!

```

### 检测操作

在某些情况下，我们不需要替换文本中的敏感词，只需要程序检测出是否有敏感词即可。
比如在用户注册时候填写的昵称，为了防止用户冒充官方人员对用户进行诈骗，一般会设置一些不能注册的昵称作为敏感词。
这时我们可以使用 scan 方法，它会返回一个数组，包含所有被检测的到敏感词：

```php

use Singiu\WordBan;

$username = $_POST['username'];

$sensitive_words = ['Singiu'];
WordBan::load($sensitive_words);
$bad_words = WordBan::scan($username);

if (count($bad_words) > 0) {
  echo '这个昵称已经被注册啦！';
}

```

## 关于性能问题

本包采用 DFA 算法来进行敏感词的查找，我自己的项目实测有 1000+ 的敏感词，查找和替换速度都保持在毫秒级。

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.