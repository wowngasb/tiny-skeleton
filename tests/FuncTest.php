<?php
namespace tests;

use phpmock\phpunit\PHPMock;
use PHPUnit_Framework_Assert;
use Tiny\Func;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/10 0010
 * Time: 10:25
 */
class FuncTest extends BaseNothingTest
{
    public static $_class = '';

    use PHPMock;

    ##########################
    ######## 数组处理 ########
    ##########################

    public function __construct($name = '', array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        static::$_class = Func::_class();
    }

    public function test_filter_keys()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array([], ['a', 'b', 'c'], 1),
                'return' => ['a' => 1, 'b' => 1, 'c' => 1]
            ], [
                'args' => array(['a' => 2, 'b' => 2], ['a', 'b', 'c'], 1),
                'return' => ['a' => 2, 'b' => 2, 'c' => 1]
            ], [
                'args' => array(['a' => 2, 'b' => 2], [], 1),
                'return' => []
            ], [
                'args' => array(['a' => 2, 'b' => 2], ['a', 'b', 'c']),
                'return' => ['a' => 2, 'b' => 2, 'c' => '']
            ], [
                'args' => array([], []),
                'return' => []
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_v()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array(['a' => 2, 'b' => 2], 'c'),
                'return' => null
            ], [
                'args' => array(['a' => 2, 'b' => 2], 'b'),
                'return' => 2
            ], [
                'args' => array(['a' => 2, 'b' => 2], 'c', 3),
                'return' => 3
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    ##########################
    ######## 时间处理 ########
    ##########################

    public function test_add_month()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('2017-01-21 12:34:56', 1),
                'return' => '2017-02-21 12:34:56'
            ], [
                'args' => array('2017-01-31 12:34:56', 1),
                'return' => '2017-02-28 12:34:56'
            ], [
                'args' => array('2016-01-31 12:34:56', 1),
                'return' => '2016-02-29 12:34:56'
            ], [
                'args' => array('2016-02-29 12:34:56', 12),
                'return' => '2017-02-28 12:34:56'
            ], [
                'args' => array('2016-02-29 12:34:56', 0),
                'return' => '2016-02-29 12:34:56'
            ], [
                'args' => array('2016-02-29 12:34:56', -1),
                'return' => '2016-02-29 12:34:56'
            ], [
                'args' => array('2016-02-29 12:34:56', 48000),
                'return' => '6016-02-29 12:34:56'
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_diff_time()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array(1502435519, 1502435519 + 60 + 1),
                'return' => ["day" => 0, "hour" => 0, "min" => 1, "sec" => 1]
            ], [
                'args' => array(1502435519, 1502435519 + 3600 + 60 + 1),

                'return' => ["day" => 0, "hour" => 1, "min" => 1, "sec" => 1]
            ], [
                'args' => array(1502435519, 1502435519 + 24 * 3600 + 3600 + 60 + 1),
                'return' => ["day" => 1, "hour" => 1, "min" => 1, "sec" => 1]
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }

        for ($i = 0; $i < 100; $i++) {
            list($day, $hour, $min, $sec) = [rand(0, 10000), rand(0, 23), rand(0, 59), rand(0, 59)];
            $test_i = time();
            $tmp = $test_i + $day * 24 * 3600 + $hour * 3600 + $min * 60 + $sec * 1;
            $test_o = Func::diff_time($test_i, $tmp);
            $test_o2 = Func::diff_time($tmp, $test_i);
            $test_r = ["day" => $day, "hour" => $hour, "min" => $min, "sec" => $sec];
            PHPUnit_Framework_Assert::assertEquals($test_r, $test_o);
            PHPUnit_Framework_Assert::assertEquals($test_r, $test_o2);
        }
    }

    public function test_str_time()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array(1502435519, 1502435519 + 60 + 1),
                'return' => "1分1秒"
            ], [
                'args' => array(1502435519, 1502435519 + 3600 + 60 + 1),

                'return' => "1小时1分1秒"
            ], [
                'args' => array(1502435519, 1502435519 + 24 * 3600 + 3600 + 60 + 1),
                'return' => "25小时1分1秒"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }

        for ($i = 0; $i < 100; $i++) {
            list($hour, $min, $sec) = [rand(0, 23), rand(0, 59), rand(0, 59)];
            $test_i = time();
            $tmp = $test_i + $hour * 3600 + $min * 60 + $sec * 1;
            $test_o = Func::str_time($test_i, $tmp);
            $test_o2 = Func::str_time($tmp, $test_i);
            $test_r = $hour > 0 ? "{$hour}小时" : '';
            $test_r .= $min > 0 ? "{$min}分" : '';
            $test_r .= $sec > 0 ? "{$sec}秒" : '';
            PHPUnit_Framework_Assert::assertEquals($test_r, $test_o);
            PHPUnit_Framework_Assert::assertEquals($test_r, $test_o2);
        }
    }

    ##########################
    ######## 字符串处理 ########
    ##########################

    public function test_pass_filter()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('abc,def,abcd', 'abc'),
                'return' => false
            ], [
                'args' => array('abc,def,abcd', 'aaa|def'),

                'return' => false
            ], [
                'args' => array('abc,def,abcd', 'aaa,def,', ','),
                'return' => false
            ], [
                'args' => array('abc,def,abcd', 'abcde'),
                'return' => true
            ], [
                'args' => array('abc,def,abcd', 'abcde  |  | defa'),
                'return' => true
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }


    }

    public function test_byte2size()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array(1024),
                'return' => '1K'
            ], [
                'args' => array(1024 + 512, 'K'),

                'return' => '1.50M'
            ], [
                'args' => array(1024 + 512, 'K', '', 3),
                'return' => '1.500M'
            ], [
                'args' => array((1024 + 512) * 10, 'K', 'G', 3),
                'return' => '0.015G'
            ], [
                'args' => array(0.0125, 'G'),
                'return' => '12.80M'
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_anonymous_telephone()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('15099991234'),
                'return' => '150****1234'
            ], [
                'args' => array('150999912345678'),

                'return' => '150********5678'
            ], [
                'args' => array('15099991234', 2, 3),
                'return' => '15******234'
            ], [
                'args' => array('1509999'),
                'return' => '1509999'
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_anonymous_email()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('one20170101@test.com'),
                'return' => 'one********@test.com'
            ], [
                'args' => array('one20170101@test.com', 4),

                'return' => 'one2*******@test.com'
            ], [
                'args' => array('one@test.com'),
                'return' => 'one@test.com'
            ], [
                'args' => array('one20170101#test.com'),
                'return' => 'one20170101#test.com'
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }


    // PHP中 == 运算符的安全问题
    // http://blog.jobbole.com/104107/

    public function test_str_cmp()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            ['args' => array("0xff", "255")],
            ['args' => array("1.00000000000000001", "0.1e1")],
            ['args' => array("+1", "0.1e1")],
            ['args' => array("1e0", "0.1e1")],
            ['args' => array("-0e10", "0")],
            ['args' => array("1000", "0x3e8")],
            ['args' => array("1234", "  	1234")],
        ];

        $test_data[] = ['args' => array(md5('c!C123449477'), md5('d!D206687225'))];
        $test_data[] = ['args' => array(md5('e!E160399390'), md5('f!F24413812'))];
        $test_data[] = ['args' => array(sha1('aA1537368460!'), sha1('fF3560631665!'))];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = ($item[0] == $item[1]);
            PHPUnit_Framework_Assert::assertTrue($tmp, static::_buildMsg($_func, $item, $tmp));
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertFalse($tmp, static::_buildMsg($_func, $item, $tmp));
        }

        $test_data = [
            ['args' => array('abc', 'abc'), 'return' => true],
            ['args' => array('abc123', 'abc123'), 'return' => true],
            ['args' => array('20170101', 20170101), 'return' => true],
            ['args' => array('abc', 'Abc'), 'return' => false],
            ['args' => array('abc', 'abc '), 'return' => false],
            ['args' => array('abc', ' abc'), 'return' => false],
            ['args' => array('abc', 'ABC'), 'return' => false],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_stri_cmp()
    {
        $test_data = [
            ['str1' => 'abc', 'str2' => 'ABC', 'return' => true],
            ['str1' => 'abc', 'str2' => 'ABc', 'return' => true],
            ['str1' => '20170101', 'str2' => 20170101, 'return' => true],
            ['str1' => 'abc', 'str2' => 'Abc ', 'return' => false],
            ['str1' => 'abc1', 'str2' => 'ABC', 'return' => false],
        ];
        foreach ($test_data as $item) {
            $tmp = Func::stri_cmp($item['str1'], $item['str2']);
            PHPUnit_Framework_Assert::assertEquals($item['return'], $tmp);
        }
    }

    public function test_str_startwith()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('', ''),
                'return' => true
            ], [
                'args' => array('one20170101@test.com', 'one'),
                'return' => true
            ], [
                'args' => array('one@test.com', 'one1'),
                'return' => false
            ], [
                'args' => array('one20170101#test.com', 'onE'),
                'return' => false
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_str_endwith()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('', ''),
                'return' => true
            ], [
                'args' => array('one20170101@test.com', 'com'),
                'return' => true
            ], [
                'args' => array('one@test.com', 'com1'),
                'return' => false
            ], [
                'args' => array('one20170101#test.com', 'coM'),
                'return' => false
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_stri_startwith()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('', ''),
                'return' => true
            ], [
                'args' => array('one20170101@test.com', 'one'),
                'return' => true
            ], [
                'args' => array('one@test.com', 'one1'),
                'return' => false
            ], [
                'args' => array('one20170101#test.com', 'onE'),
                'return' => true
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_stri_endwith()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('', ''),
                'return' => true
            ], [
                'args' => array('one20170101@test.com', 'com'),
                'return' => true
            ], [
                'args' => array('one@test.com', 'com1'),
                'return' => false
            ], [
                'args' => array('one20170101#test.com', 'coM'),
                'return' => true
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    ##########################
    ######## 中文处理 ########
    ##########################

    public function test_utf8_strlen()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('utf8长度'),
                'return' => 6
            ], [
                'args' => array('长度'),
                'return' => 2
            ], [
                'args' => array('utf8'),
                'return' => 4
            ], [
                'args' => array(''),
                'return' => 0
            ], [
                'args' => array(' '),
                'return' => 1
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_utf8_gbk_able()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('你我他'),
                'return' => '你我他'
            ], [
                'args' => array('abc'),
                'return' => 'abc'
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_unicode_decode()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('\u89e3\u51bb\u8d26\u53f7\u6210\u529f'),
                'return' => '解冻账号成功'
            ], [
                'args' => array('abc'),
                'return' => 'abc'
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }

    }

    public function test_unicode_decode_char()
    {

        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('\u89e3'),
                'return' => '解'
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }

    }

    ##########################
    ######## 编码相关 ########
    ##########################

    protected static $test_str_data = [
        '',
        'abcd',
        '    ',
        1234,
        123456789,
        12345.6789,
        '123456789',
        'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        'akjdhawi323 234^$%#@213719823 hjadbgqu2y3r1276JHDWGSuqyw4352367432165432984238074530856szhbdchsaeryt324uahzddwad',
    ];

    public function test_safe_base64()
    {
        foreach (self::$test_str_data as $test_i) {
            $rst = Func::safe_base64_encode($test_i);
            $test_o = Func::safe_base64_decode($rst);
            PHPUnit_Framework_Assert::assertEquals($test_i, $test_o);
        }

        foreach (self::$test_str_data as $test_i) {
            $test_i = strval($test_i);
            $rst = Func::safe_base64_encode($test_i);
            $test_o = Func::safe_base64_decode($rst);
            PHPUnit_Framework_Assert::assertEquals($test_i, $test_o);
        }
    }

    public function test_authcode_expiry4()
    {
        $key = 'zT5hF$E24*(#dfS^Yq3&6A^6';
        $test_i = 'abc';
        $now = time();
        $rst = Func::encode($test_i, $key, 10 * 365 * 24 * 3600);   //设置有效期为 10 年

        $time = $this->getFunctionMock(Func::_namespace(), "time");  // Mock Func 命名空间下 time 函数
        $time->expects($this->once())->willReturn($now + 10 * 365 * 24 * 3600 - 100);    //模拟 10年 - 10秒后的时间

        $test_o = Func::decode($rst, $key);
        PHPUnit_Framework_Assert::assertEquals($test_i, $test_o);
    }

    public function test_authcode_expiry3()
    {
        $key = 'zT5hF$E24*(#dfS^Yq3&6A^6';
        $test_i = 'abc';
        $now = time();
        $rst = Func::encode($test_i, $key, 10 * 365 * 24 * 3600);   //设置有效期为 10 年

        $time = $this->getFunctionMock(Func::_namespace(), "time");  // Mock Func 命名空间下 time 函数
        $time->expects($this->once())->willReturn($now + 10 * 365 * 24 * 3600 + 100);    //模拟 10年 + 10秒后的时间

        $test_o = Func::decode($rst, $key);
        PHPUnit_Framework_Assert::assertEmpty($test_o);
    }

    public function test_authcode_expiry2()
    {
        $key = 'zT5hF$E24*(#dfS^Yq3&6A^6';
        $test_i = 'abc';
        $now = time();

        $rst = Func::encode($test_i, $key, 10);   //设置有效期为 10 s

        $time = $this->getFunctionMock(Func::_namespace(), "time");  // Mock Func 命名空间下 time 函数
        $time->expects($this->once())->willReturn($now + 100);

        $test_o = Func::decode($rst, $key);
        PHPUnit_Framework_Assert::assertEquals($test_o, '');
    }

    public function test_authcode_expiry1()
    {
        $key = 'zT5hF$E24*(#dfS^Yq3&6A^6';
        $test_i = 'abc';
        $rst = Func::encode($test_i, $key, 10);
        $test_o = Func::decode($rst, $key);
        PHPUnit_Framework_Assert::assertEquals($test_i, $test_o);

        /* $tmp = [
            'key' => $key,
            'test_list' => [],
        ];
        foreach ([
                     '', '1', '12', '123', '1234','1234','1234','1234','1234',
                 ] as $test) {
            $v = Func::encode($test, $key);
            $tmp['test_list'][$v] = $test;
        }
        echo "\n" . json_encode($tmp). "\n"; */
    }

    public function test_authcode()
    {
        $key = 'zT5hF$E24*(#dfS^Yq3&6A^1';
        foreach (self::$test_str_data as $test_i) {
            $rst = Func::encode($test_i, $key);
            $test_o = Func::decode($rst, $key);
            PHPUnit_Framework_Assert::assertEquals($test_i, $test_o);
        }

        $key = 'zT5hF$E24*(#dfS^Yq3&6A^2';
        foreach (self::$test_str_data as $test_i) {
            $test_i = strval($test_i);
            $rst = Func::encode($test_i, $key);
            $test_o = Func::decode($rst, $key);
            PHPUnit_Framework_Assert::assertEquals($test_i, $test_o);
        }

        $key1 = 'zT5hF$E24*(#dfS^Yq3&6A^3';
        $key2 = 'zT5hF$E24*(#dfS^Yq3&6A^4';
        foreach (self::$test_str_data as $test_i) {
            $rst = Func::encode($test_i, $key1);
            $test_o = Func::decode($rst, $key2);
            PHPUnit_Framework_Assert::assertEmpty($test_o);
        }
    }

    public function test_xss_filter()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array(['abc' => "<div >bcd\n</div>"], ['abc']),
                'return' => ['abc' => "div bcd/div"]
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    public function test_xss_clean()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array("<div >bcd\n</div>\t"),
                'return' => "div bcd/div"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    ##########################
    ######## URL相关 ########
    ##########################

    public function test_build_url()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array("http://test.com"),
                'return' => "http://test.com"
            ], [
                'args' => array("http://test.com", ['a' => 1, 'b' => 2]),
                'return' => "http://test.com/?a=1&b=2"
            ], [
                'args' => array("http://test.com/", ['a' => 1, 'b' => 2]),
                'return' => "http://test.com/?a=1&b=2"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    #########################################
    ########### 魔术常量相关函数 ############
    #########################################

    /**
     * 根据魔术常量获取获取 类名
     */
    public function test_class2name()
    {

        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('Foo\Test\testClass::testFunc'),
                'return' => "testClass"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    /**
     * 根据魔术常量获取获取 函数名
     */
    public function test_method2name()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('Foo\Test\testClass::testFunc'),
                'return' => "testFunc"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }

    }

    /**
     * 根据魔术常量获取获取 函数名 并转换为 小写字母加下划线格式 的 字段名
     */
    public function test_method2field()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('Foo\Test\testClass::testFunc'),
                'return' => "test_func"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }

    }

    /**
     * 根据魔术常量获取获取 类名 并转换为 小写字母加下划线格式 的 数据表名
     */
    public function test_class2table()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('Foo\Test\testClass::testFunc'),
                'return' => "test_class"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }


    }

    /**
     * 下划线转驼峰
     */
    public function test_convertUnderline()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('test_class'),
                'return' => "testClass"
            ], [
                'args' => array('_test_class'),
                'return' => "TestClass"
            ], [
                'args' => array('_Test_class'),
                'return' => "TestClass"
            ], [
                'args' => array('_Test_clasS'),
                'return' => "TestClasS"
            ], [
                'args' => array('__Test_class'),
                'return' => "TestClass"
            ], [
                'args' => array('__test_class'),
                'return' => "TestClass"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }

    }

    /**
     * 驼峰转下划线
     */
    public function test_humpToLine()
    {
        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('testClass'),
                'return' => "test_class"
            ], [
                'args' => array('TestClass'),
                'return' => "test_class"
            ], [
                'args' => array('TestClassSSS'),
                'return' => "test_class_sss"
            ], [
                'args' => array('_Test_clasS'),
                'return' => "_test_clas_s"
            ], [
                'args' => array('ABCTestClass'),
                'return' => "abctest_class"
            ], [
                'args' => array('123TestClass'),
                'return' => "123test_class"
            ], [
                'args' => array('TestClass123'),
                'return' => "test_class123"
            ], [
                'args' => array('TestClasS123'),
                'return' => "test_clas_s123"
            ], [
                'args' => array('TestClassEx123'),
                'return' => "test_class_ex123"
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }

    /**
     * 使用 seq 把 list 数组中的非空字符串连接起来  _join('_', [1,2,3]) = '1_2_3'
     */
    public function test_joinNotEmpty()
    {

        $_func = self::_buildFunc(__METHOD__);
        $test_data = [
            [
                'args' => array('_', [1, 2, 3]),
                'return' => "1_2_3"
            ], [
                'args' => array('_', [0, 1, 2, 3]),
                'return' => "0_1_2_3"
            ], [
                'args' => array('_', [0, '', 2, 3]),
                'return' => "0_2_3"
            ], [
                'args' => array('_', [0, '', 2, '']),
                'return' => "0_2"
            ], [
                'args' => array('_', ['', '']),
                'return' => ""
            ], [
                'args' => array('_', []),
                'return' => ""
            ], [
                'args' => array('', []),
                'return' => ""
            ],
        ];
        foreach ($test_data as $test_item) {
            $item = $test_item['args'];
            $tmp = call_user_func_array([static::$_class, $_func], $item);
            PHPUnit_Framework_Assert::assertEquals($test_item['return'], $tmp, static::_buildMsg($_func, $item, $tmp));
        }
    }
}