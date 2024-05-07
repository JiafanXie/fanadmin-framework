<?php


use think\exception\HttpResponseException;
use think\Response;

/**
 * 助手函数文件，通过composer autoload 加载
 */
if (!function_exists('success')) {
    /**
     * 返回成功方法
     *
     * @param string $msg
     * @param array $data
     * @return \think\response\Json
     */
    function success (string|array|object $msg = '操作成功', object|array $data = null) {
        $result = [
            'code' => 200,
            'msg'  => $msg,
            'data' => $data
        ];
        if (is_array($msg) || is_object($msg)) {
            $result = [
                'code' => 200,
                'msg'  => 'success',
                'data' => $msg
            ];
        }
        return json($result);
    }
}

/**
 * 返回失败方法
 */
if (!function_exists('error')) {
    /**
     * 返回失败方法
     *
     * @param string $msg
     * @param array $data
     * @return \think\response\Json
     */
    function error (string $msg = '操作失败', int|array|object $error_code = -1,  array $data = null, int $status_code = 200) {
        $result = [
            'error' => $error_code,
            'msg'   => $msg,
            'data'  => $data
        ];
        if (is_array($error_code) || is_object($error_code)) {
            $result = [
                'error' => -1,
                'msg'   => $msg,
                'data'  => $error_code
            ];
        }
        return json($result, $status_code);
        // 抛异常方式
        // if ($error_code != 1 || $status_code !== 200) {
        //     throw (new SheepException)->setMessage($msg, $error_code, $status_code);
        // } else {
        //     throw new SheepException($msg);
        // }
    }
}

/**
 * 格式化经纬度
 */
if (!function_exists('match_latlng')) {
    /**
     * 格式化经纬度
     * @param string  $latlng    要格式化的经纬度
     * @return string
     */
    function match_latlng ($latlng) {
        $match = "/^\d{1,3}\.\d{1,30}$/";
        return preg_match($match, $latlng) ? $latlng : 0;
    }
}

/**
 * 拼接查询距离 sql
 */
if (!function_exists('distanceBuilder')) {
    /**
     * 拼接查询距离 sql
     * @param string  $lat    纬度
     * @param string  $lng    经度
     * @return string
     */
    function distanceBuilder ($lat, $lng) {
        return "ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((" . match_latlng($lat) . " * PI() / 180 - latitude * PI() / 180) / 2), 2) + COS(" . match_latlng($lat) . " * PI() / 180) * COS(latitude * PI() / 180) * POW(SIN((" . match_latlng($lng) . " * PI() / 180 - longitude * PI() / 180) / 2), 2))) * 1000) AS distance";
    }
}

/**
 * 检测字符串是否是版本号
 */
if (!function_exists('isVersionStr')) {
    /**
     * 检测字符串是否是版本号
     * @param string  $version
     * @return boolean
     */
    function isVersionStr ($version) {
        $match = "/^([0-9]\d|[0-9])(\.([0-9]\d|\d))+/";
        return preg_match($match, $version) ? true : false;
    }
}

/**
 * 删除目录
 */
if (!function_exists('rmdirs')) {
    /**
     * 删除目录
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     * @return boolean
     */
    function rmdirs ($dirname, $withself = true) {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir() === 'rmdir') rmdir(($fileinfo->getRealPath()));
            if ($fileinfo->isDir() === 'unlink') unlink(($fileinfo->getRealPath()));
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }
}


/**
 * 复制目录
 */
if (!function_exists('copyDirs')) {

    /**
     * 复制目录
     * @param string $source 源文件夹
     * @param string $dest   目标文件夹
     */
    function copyDirs ($source, $dest) {
        if (!is_dir($dest)) {
            @mkdir($dest, 0755, true);
        }
        foreach ($iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $item) {
            if ($item->isDir()) {
                $sontDir = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                   @mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }
}

/**
 * 是否是url
 */
if (!function_exists('isUrl')) {
    function isUrl ($url) {
        if (preg_match("/^(http:\/\/|https:\/\/)/", $url)) {
            return true;
        }
        return false;
    }
}

/**
 * 获取所有应用
 */
if (!function_exists('getApps')) {
    /**
     * 获取所有应用
     */
    function getApps () {
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->directories()->ignoreVCS(false)->depth('== 0')->in(root_path('app'));
        $apps = [];
        foreach ($finder as $dir) {
            $apps[] = $dir->getRelativePathname();
        }
        return $apps;
    }
}

/**
 * 快捷设置跨域请求头（跨域中间件失效时，一些特殊拦截时候需要用到）
 */
if (!function_exists('setCors')) {
    /**
     * 快捷设置跨域请求头（跨域中间件失效时，一些特殊拦截时候需要用到）
     *
     * @return void
     */
    function setCors () {
        $header = [
            'Access-Control-Allow-Origin' => '*',           // 规避跨域
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => 1800,
            'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, platform',
        ];
        // 直接将 header 添加到响应里面
        foreach ($header as $name => $val) {
            header($name . (!is_null($val) ? ':' . $val : ''));
        }

        if (request()->isOptions()) {
            // 如果是预检直接返回响应，后续都不在执行
            exit;
        }
    }
}

/**
 * 拼接资源域名，当前域名或者对象存储域名
 */
if (!function_exists('getFileDomain')) {
    /**
     * 获取本地 public 资源的域名的地址
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function getFileDomain ($url, $domain = true) {
        $regex  = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $driver = config('filesystem.default');
        $domainurl = config('filesystem.disks.' . $driver . '.url');
        $url = preg_match($regex, $url) || ($domainurl && stripos($url, $domainurl) === 0) ? $url : $domainurl . $url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}

/**
 * 如果是命令行模式，获取当前命令行正在执行的命令
 */
if (!function_exists('currentCommand')) {
    /**
     * 获取当前命令
     * @param string $type  all 整个命令从桉树，only_name 仅获取命令名称
     */
    function currentCommand ($type = 'all') {
        if (!request()->isCli()) {
            return null;
        }

        $argv = $_SERVER['argv'];
        array_shift($argv);

        return $type == 'only_name' ? ($argv[0] ?? null) : $argv;
    }
}

/**
 * 验证自定义签名
 */
if (!function_exists('getNonceStr')) {
    /**
     * 验证自定义签名 boolean
     *
     * @return void
     */
    function getNonceStr () {
        $license_key = config('app.license_key');
        $referer = request()->header('referer');
        $host = request()->host(true);
        if ($referer) {
            $hosts = parse_url($referer);
            $host = $hosts['host'] ?? ($hosts['path'] ?? $referer);
        }

        $timestamp = time();
        $nonce_str = md5($host . $license_key . $timestamp);

        return $nonce_str. '.' .$timestamp;
    }
}

if (!function_exists('diffInTime')) {
    /**
     * 计算两个时间相差多少天，多少小时，多少分钟
     * 
     * @param mixed $first 要比较的第一个时间 Carbon 或者时间格式
     * @param mixed $second 要比较的第二个时间 Carbon 或者时间格式
     * @param bool $format 是否格式化为字符串
     * @return string|array 
     */
    function diffInTime ($first, $second = null, $format = true, $simple = false) {
        $first = $first instanceof \Carbon\Carbon ? $first : \Carbon\Carbon::parse($first);
        $second = is_null($second) ? \Carbon\Carbon::now() : $second;
        $second = $second instanceof \Carbon\Carbon ? $second : \Carbon\Carbon::parse($second);

        $years = $first->diffInYears($second);
        $days = $first->diffInDays($second);
        $hours = $first->diffInHours($second);
        $minutes = $first->diffInMinutes($second);
        $second = $first->diffInSeconds($second);

        if (!$format) {
            return compact('years', 'days', 'hours', 'minutes', 'second');
        }

        $format_text = '';
        $start = false;
        if ($years) {
            $start = true;
            $format_text .= $years . '年';
        }
        if ($start || $days) {
            $start = true;
            $format_text .= ($days % 365) . '天';
        }

        if ($start || $hours) {
            $start = true;
            $format_text .= ($hours % 24) . '时';
        }
        if ($start || $minutes) {
            $start = true;
            $format_text .= ($minutes % 60) . '分钟';
        }
        if (($start || $second) && !$simple) {
            $start = true;
            $format_text .= ($second % 60) . '秒';
        }

        return $format_text;
    }
}

if (!function_exists('getSn')) {
    /**
     * 获取唯一编号
     *
     * @param mixed $id       唯一标识
     * @param string $type    类型
     * @return string
     */
    function getSn ($id, $type = '') {
        $id = (string)$id;

        $rand = $id < 9999 ? mt_rand(100000, 99999999) : mt_rand(100, 99999);
        $sn = date('Yhis') . $rand;

        $id = str_pad($id, (24 - strlen($sn)), '0', STR_PAD_BOTH);

        return $type . $sn . $id;
    }
}

if (!function_exists('stringHide')) {
    /**
     * 隐藏部分字符串
     *
     * @param string $string       原始字符串
     * @param int $start    开始位置
     * @return string
     */
    function stringHide ($string, $start = 2, $end = 0, $hidden_length = 3) {
        if (mb_strlen($string) > $start) {
            $hide = mb_substr($string, 0, $start) . str_repeat('*', $hidden_length) . ($end > 0 ? mb_substr($string, -$end) : '');
        } else {
            $hide = $string . '***';
        }

        return $hide;
    }
}

if (!function_exists('accountHide')) {
    /**
     * 隐藏账号部分字符串
     *
     * @param string $string       原始字符串
     * @param int $start    开始位置
     * @param int $end    开始位置
     * @return string
     */
    function accountHide ($string, $start = 2, $end = 2) {
        $hide = mb_substr($string, 0, $start) . '*****' . mb_substr($string, -$end);
        return $hide;
    }
}

if (!function_exists('getPhp')) {
    /**
     * 获取 php
     *
     * @param mixed $id       唯一标识
     * @param string $type    类型
     * @return string
     */
    function getPhp () {
        $phpBin = (new \Symfony\Component\Process\PhpExecutableFinder)->find();

        if ($phpBin === false) {
            // 如果都没有获取，尝试拼接宝塔 php 路径
            $phpBin = '/www/server/php/' . substr(str_replace('.', '', PHP_VERSION), 0, 2) . '/bin/php';
        } else {
            if (is_string($phpBin) && strpos(strtolower(PHP_OS), 'win') !== false) {
                // windows 上获取的 php 执行文件会是 php-cgi.exe，这里去掉 -cgi
                $phpBin = str_replace('-cgi', '', $phpBin);
            }
        }

        return $phpBin;
    }
}

if (!function_exists('isThrottle')) {
    /**
     * 判断路由是否需要限流控制
     *
     * @return boolean
     */
    function isThrottle () {
        $noThrottleUris = config('throttle.no_throttle_uris');

        $url = request()->baseUrl();
        foreach ($noThrottleUris as $uri) {
            if (strpos($uri, ':') !== false) {
                if (strpos($url, strstr($uri, ':', true)) !== false) {
                    return false;
                }
            } else {
                if ($url == $uri) {
                    return false;
                }
            }
        }

        return true;
    }
}

if (!function_exists('genRandomStr')) {
    /**
     * 随机生成字符串
     * @param int  $length    字符串长度
     * @return bool $upper 默认小写
     */
    function genRandomStr ($length = 10, $upper = false) {
        if ($upper) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        }

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

if(!function_exists('encryptPassword')){
    function encryptPassword ($password,$salt) {
        return md5(md5($password) . $salt);
    }
}
if (!function_exists('copyDirectory')) {
    /**
     * 复制目录
     *
     * @param mixed            $condition
     * @param Throwable|string $exception
     * @param array            ...$parameters
     * @return mixed
     *
     * @throws Throwable
     */
    function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $sourceFile = $source . '/' . $file;
                $destinationFile = $destination . '/' . $file;
                if (is_dir($sourceFile)) {
                    copyDirectory($sourceFile, $destinationFile);
                } else {
                    copy($sourceFile, $destinationFile);
                }
            }
        }
    }
}


if (!function_exists('deleteDirectory')) {

    function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}

if (!function_exists('getDomain')) {
    /**
     * 获取当前域名
     */
    function getDomain () {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
        $url = $protocol . $_SERVER['HTTP_HOST'];
        return $url;
    }
}

if (!function_exists('adminPath')) {
    /**
     * 获取admin目录
     */
    function adminPath ($path = "") {
        return __DIR__ . DIRECTORY_SEPARATOR . $path;
    }
}

if (!function_exists('listToTree')) {
    /**
     * 列表转换成树形
     */
    function listToTree ($items, $parentId = null, $keyName = 'id', $parentKeyName = 'parent_id',$children='children') {
        $tree = [];
        foreach ($items as $k => $item) {
            if ($item[$parentKeyName] == $parentId) {
                $item[$children]  = listToTree($items, $item[$keyName]);
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
if (!function_exists('getter')) {

    /**
     * 获取数组的下标值
     * @param $data
     * @param $field
     * @param string $default
     * @return array|mixed|string
     */
    function getter($data, $field, $default = '')
    {
        $result = $default;
        if (isset($data[$field])) {
            if (is_array($data[$field])) {
                $result = $data[$field];
            } else {
                $result = trim($data[$field]);
            }
        }
        return $result;
    }
}

if (!function_exists('dirMkdir')) {
    /**
     * 创建文件夹
     *
     * @param string $path 文件夹路径
     * @param int $mode 访问权限
     * @param bool $recursive 是否递归创建
     * @return bool
     */
    function dirMkdir($path = '', $mode = 0777, $recursive = true)
    {
        clearstatcache();
        if (!is_dir($path)) {
            @mkdir($path, $mode, $recursive);
            return chmod($path, $mode);
        }
        return true;
    }
}

if (!function_exists('dirCopy')) {
    /**
     * 文件夹文件拷贝
     * @param string $src 来源文件夹
     * @param string $dst 目的地文件夹
     * @param array $files 文件夹集合
     * @param array $exclude_dirs 排除无需拷贝的文件夹
     * @param array $exclude_files 排除无需拷贝的文件
     * @return bool
     */
    function dirCopy(string $src = '', string $dst = '', &$files = [], $exclude_dirs = [], $exclude_files = [])
    {
        if (empty($src) || empty($dst)) {
            return false;
        }
        if (!file_exists($src)) {
            return false;
        }
        $dir = opendir($src);
        dirMkdir($dst);
        while (false !== ( $file = readdir($dir) )) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    // 排除目录
                    if (count($exclude_dirs) && in_array($file, $exclude_dirs)) continue;
                    dirCopy($src . '/' . $file, $dst . '/' . $file, $files);
                } else {
                    // 排除文件
                    if (count($exclude_files) && in_array($file, $exclude_files)) continue;
                    copy($src . '/' . $file, $dst . '/' . $file);
                    $files[] = $dst . '/' . $file;
                }
            }
        }
        closedir($dir);
        return true;
    }
}

if (!function_exists('delTargetDir')) {
    /**
     * @notes 删除目标目录
     * @param $path
     * @param $delDir
     * @return bool|void
     */
    function delTargetDir($path, $delDir)
    {
        //没找到，不处理
        if (!file_exists($path)) {
            return false;
        }

        //打开目录句柄
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$path/$item")) {
                        delTargetDir("$path/$item", $delDir);
                    } else {
                        unlink("$path/$item");
                    }
                }
            }
            closedir($handle);
            if ($delDir) {
                return rmdir($path);
            }
        } else {
            if (file_exists($path)) {
                return unlink($path);
            }
            return false;
        }
    }
}

if (!function_exists('projectPath')) {
    /**
     * 项目目录
     * @return string
     */
    function projectPath()
    {
        return dirname(root_path()) . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('searchDir')) {
    /**
     * 递归查询目录下所有文件
     * @param $path
     * @param $data
     * @param $search
     * @return void
     */
    function searchDir($path, &$data, $search = '')
    {
        if (is_dir($path)) {
            $path .= DIRECTORY_SEPARATOR;
            $fp = dir($path);
            while ($file = $fp->read()) {
                if ($file != '.' && $file != '..') {
                    searchDir($path . $file, $data, $search);
                }
            }
            $fp->close();
        }
        if (is_file($path)) {
            if ($search) $path = str_replace($search, '', $path);
            $data[] = $path;
        }
    }
}

if (!function_exists('getLang')) {
    /**
     * 自动侦测语言并转化
     * @param string $str
     * @return lang()
     */
    function getLang($str)
    {
        return \think\facade\Lang::get($str);
    }
}

if (!function_exists('getFileMap')) {
    /**
     * 获取文件地图
     * @param $path
     * @param array $arr
     * @return array
     */
    function getFileMap($path, $arr = [])
    {
        if (is_dir($path)) {
            $dir = scandir($path);
            foreach ($dir as $file_path) {
                if ($file_path != '.' && $file_path != '..') {
                    $temp_path = $path . '/' . $file_path;
                    if (is_dir($temp_path)) {
                        $arr[$temp_path] = $file_path;
                        $arr = getFileMap($temp_path, $arr);
                    } else {
                        $arr[$temp_path] = $file_path;
                    }
                }
            }
            return $arr;
        }
    }
}

if (!function_exists('checkFileIsRemote')) {
    /**
     * 检测文件是否是本地图片
     * @param string $file_path
     * @return void
     */
    function checkFileIsRemote(string $file_path)
    {
        return str_contains($file_path, 'https://') || str_contains($file_path, 'http://') || str_contains($file_path, '.com');
    }
}

if (!function_exists('parseSql')) {
    /**
     * 分割sql语句
     * @param string $content sql内容
     * @param bool $string 如果为真，则只返回一条sql语句，默认以数组形式返回
     * @param array $replace 替换前缀，如：['my_' => 'me_']，表示将表前缀my_替换成me_
     * @return array|string 除去注释之后的sql语句数组或一条语句
     */
    function parseSql($content = '', $string = false, $replace = [])
    {
        // 纯sql内容
        $pure_sql = [];
        // 被替换的前缀
        $from = '';
        // 要替换的前缀
        $to = '';
        // 替换表前缀
        if (!empty($replace)) {
            $to = current($replace);
            $from = current(array_flip($replace));
        }
        if ($content != '') {
            // 多行注释标记
            $comment = false;
            // 按行分割，兼容多个平台
            $content = str_replace(["\r\n", "\r"], "\n", $content);
            $content = explode("\n", trim($content));
            // 循环处理每一行
            foreach ($content as $line) {
                // 跳过空行
                if ($line == '') {
                    continue;
                }
                // 跳过以#或者--开头的单行注释
                if (preg_match("/^(#|--)/", $line)) {
                    continue;
                }
                // 跳过以/**/包裹起来的单行注释
                if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                    continue;
                }
                // 多行注释开始
                if (str_starts_with($line, '/*')) {
                    $comment = true;
                    continue;
                }
                // 多行注释结束
                if (str_ends_with($line, '*/')) {
                    $comment = false;
                    continue;
                }
                // 多行注释没有结束，继续跳过
                if ($comment) {
                    continue;
                }
                // 替换表前缀
                if ($from != '') {
                    $line = str_replace('`' . $from, '`' . $to, $line);
                }
                // sql语句
                $pure_sql[] = $line;
            }
            // 只返回一条语句
            if ($string) {
                return implode("", $pure_sql);
            }
            // 以数组形式返回sql语句
            $pure_sql = implode("\n", $pure_sql);
            $pure_sql = explode(";\n", $pure_sql);
        }
        return $pure_sql;
    }
}

if (!function_exists('getFilesByDir')) {
    /**
     * 通过目录获取文件结构1
     * @param $dir
     * @return array
     */
    function getFilesByDir($dir)
    {
        $dh = @opendir($dir);             //打开目录，返回一个目录流
        $return = array();
        while ($file = @readdir($dh)) {     //循环读取目录下的文件
            if ($file != '.' and $file != '..') {
                $path = $dir . DIRECTORY_SEPARATOR . $file;     //设置目录，用于含有子目录的情况
                if (is_dir($path)) {
                    $return[] = $file;
                }
            }
        }
        @closedir($dh);             //关闭目录流
        return $return;               //返回文件
    }
}

if (!function_exists('imageToBase64')) {
    /**
     * 图片转base64
     * @param string $path
     * @param $is_delete 转换后是否删除原图
     * @return string
     */
    function imageToBase64(string $path, $is_delete = false)
    {
        if (!file_exists($path)) return 'image not exist';

        $mime = getimagesize($path)['mime'];
        $image_data = file_get_contents($path);
        // 将图片转换为 base64
        $base64_data = base64_encode($image_data);

        if ($is_delete) @unlink($path);

        return "data:$mime;base64,$base64_data";
    }
}

if (!function_exists('mkdirs')) {
    /**
     * 多级目录不存在则创建
     * @param $dir
     * @param $mode
     * @return bool
     */
    function mkdirs($dir, $mode = 0777)
    {
        if (str_contains($dir, '.')) $dir = dirname($dir);
        if (is_dir($dir) || @mkdir($dir, $mode)) return true;
        if (!mkdirs(dirname($dir), $mode)) return false;
        return @mkdir($dir, $mode);
    }
}

if (!function_exists('addonResource')) {
    /**
     * 获取插件对应资源文件(插件安装后获取)
     * @param $addon //插件名称
     * @param $file_name //文件名称（包含resource文件路径）
     */
    function addonResource($addon, $file_name)
    {
        return "addon/" . $addon . "/" . $file_name;
    }
}


if (!function_exists('is_write')) {
    /**
     * 判断 文件/目录 是否可写（取代系统自带的 is_writeable 函数）
     *
     * @param string $file 文件/目录
     * @return boolean
     */
    function is_write($file)
    {
        if (is_dir($file)) {
            $dir = $file;
            if ($fp = @fopen("$dir/test.txt", 'wb')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = true;
            } else {
                $writeable = false;
            }
        } else {
            if ($fp = @fopen($file, 'ab+')) {
                @fclose($fp);
                $writeable = true;
            } else {
                $writeable = false;
            }
        }
        return $writeable;
    }
}

if (!function_exists('cacheRemember')) {
    /**
     * 如果不存在则写入缓存
     * @param string|null $name
     * @param $value
     * @param $tag
     * @param $options
     * @return mixed|string
     */
    function cacheRemember(string $name = null, $value = '', $tag = null, $options = null)
    {
        if (!empty($hit = \think\facade\Cache::get($name)))//可以用has
            return $hit;
        if ($value instanceof Closure) {
            // 获取缓存数据
            $value = \think\Container::getInstance()->invokeFunction($value);
        }
        if (is_null($tag)) {
            \think\facade\Cache::set($name, $value, $options['expire'] ?? null);
        } else {
            \think\facade\Cache::tag($tag)->set($name, $value, $options['expire'] ?? null);
        }
        return $value;
    }
}

if (!function_exists('checkPassword')) {
    /**
     * 校验比对密码和加密密码是否一致
     * @param $password
     * @param $hash
     * @return bool
     */
    function checkPassword($password, $hash)
    {
        if (!password_verify($password, $hash)) return false;
        return true;
    }
}

if (!function_exists('getThumbImages')) {
    /**
     * 获取缩略图
     * @param $site_id
     * @param $image
     * @param string $thumb_type
     * @param bool $is_throw_exception
     * @return mixed
     * @throws Exception
     */
    function getThumbImages($site_id, $image, $thumb_type = 'all', bool $is_throw_exception = false)
    {

        return (new \app\service\admin\file\ImageService())->thumb($site_id, $image, $thumb_type, $is_throw_exception);
    }
}

if (!function_exists('pathToUrl')) {
    /**
     * 路径转链接
     * @param $path
     * @return string
     */
    function pathToUrl($path)
    {
        return trim(str_replace(DIRECTORY_SEPARATOR, '/', $path), '.');
    }
}

if (!function_exists('mkdirsOrNotexist')) {
    /**
     * 创建文件夹
     * @param $dir
     * @param $mode
     * @return true
     */
    function mkdirsOrNotexist($dir, $mode = 0777)
    {
        if (!is_dir($dir) && !mkdir($dir, $mode, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        return true;
    }
}

if (!function_exists('urlToPath')) {
    /**
     * 链接转化路径
     * @param $url
     * @return string
     */
    function urlToPath($url)
    {
        if (str_contains($url, 'http://') || str_contains($url, 'https://')) return $url;//网络图片不必
        return public_path() . trim(str_replace('/', DIRECTORY_SEPARATOR, $url));
    }
}
if (!function_exists('systemName')) {
    /**
     * 获取一些公共的系统参数
     * @param string|null $key
     * @return array|mixed
     */
    function systemName(?string $key = '')
    {
        $params = [
            'admin_token_name' => env('system.admin_token_name', 'token'),///todo !!! 注意  header参数  不能包含_ , 会自动转成 -
            'api_token_name' => env('system.api_token_name', 'token'),
            'admin_site_id_name' => env('system.admin_site_id_name', 'site-id'),
            'api_site_id_name' => env('system.api_site_id_name', 'site-id'),
            'channel_name' => env('system.channel_name', 'channel'),
        ];
        if (!empty($key)) {
            return $params[$key];
        } else {
            return $params;
        }
    }
}

if (!function_exists('uniqueRandom')) {
    /**
     * 获取唯一随机字符串
     * @param int $len
     * @return string
     */
    function uniqueRandom($len = 10)
    {
        $str = 'qwertyuiopasdfghjklzxcvbnmasdfgh';
        str_shuffle($str);
        return substr(str_shuffle($str), 0, $len);
    }
}

if (!function_exists('array_merge2')) {
    /**
     * 二维数组合并
     * @param array $array1
     * @param array $array2
     * @return array
     */
    function array_merge2(array $array1, array $array2)
    {
        foreach ($array2 as $array2_k => $array2_v) {
            if (array_key_exists($array2_k, $array1)) {
                if (is_array($array2_v)) {
                    foreach ($array2_v as $array2_kk => $array2_vv) {
                        if (array_key_exists($array2_kk, $array1[$array2_k])) {
                            if (is_array($array2_vv)) {
                                $array1[$array2_k][$array2_kk] = array_merge($array1[$array2_k][$array2_kk], $array2_vv);
                            }
                        } else {
                            $array1[$array2_k][$array2_kk] = $array2_vv;
                        }
                    }
                } else {
                    $array1[$array2_k] = $array2_v;
                }
            } else {
                $array1[$array2_k] = $array2_v;
            }
        }
        return $array1;
    }
}




