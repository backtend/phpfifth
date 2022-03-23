<?php

if (!function_exists('halts')) {
    /**
     * 调试变量并且中断输出
     * @param mixed $vars 调试变量或者信息
     */
    function halts()
    {
        dumps(func_get_args());
        exit;
        //throw new HttpResponseException()
    }
}

if (!function_exists('dumps')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $vars 要输出的变量
     * @return void
     */
    function dumps($args)
    {
        ob_start();
        var_dump($args);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, 8);
            }
            $output = '<pre>' . $output . '</pre>';
        }

        echo $output;
    }
}

if (!function_exists('environ')) {
    /**
     * 获取环境变量
     * @param $name
     * @param null $default
     * @return array|bool|false|mixed|string|null
     * @throws Exception
     */
    function environ($name, $default = null)
    {
        return \backtend\phpfifth\Environ::get($name, $default);
    }
}