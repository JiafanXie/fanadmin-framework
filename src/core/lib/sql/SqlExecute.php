<?php

namespace FanAdmin\lib\sql;


/**
 * 数据库操作类
 * Class SqlExecute
 * @package FanAdmin\lib
 */
class SqlExecute {

    /**
     * mysqli连接对象
     * @var false|\mysqli|null
     */
    private $connect = null;

    /**
     * mysql配置
     * @var array|mixed
     */
    private $mysqlInfo = [];

    public function __construct ($mysqlInfo = []) {
        if (empty($mysqlInfo)) {
            $this->mysqlInfo = config('database.connections.mysql');
        } else {
            $this->mysqlInfo = $mysqlInfo;
        }
        // 链接数据库
        $this->connect = @mysqli_connect($this->mysqlInfo['hostname'] . ':' . $this->mysqlInfo['hostport'], $this->mysqlInfo['username'], $this->mysqlInfo['password']);
        // 查询数据库名
        $database = false;
        $mysql_table = @mysqli_query($this->connect, 'SHOW DATABASES');
        while ($row = @mysqli_fetch_assoc($mysql_table)) {
            if ($row['Database'] == $this->mysqlInfo['database']) {
                $database = true;
                break;
            }
        }
        // 没有数据库创建数据库
        if (!$database) {
            $query = "CREATE DATABASE IF NOT EXISTS `" . $this->mysqlInfo['database'] . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
            var_dump($query);
            if (!@mysqli_query($this->connect, $query)) {
                return error('数据库创建失败或已存在，请手动修改');
            }
        }
        mysqli_select_db($this->connect, $this->mysqlInfo['database']);
    }

    /**
     * 执行isntall.sql
     * @param $plugin
     * @return bool
     */
    public  function execInstallSql () {
        // 获取数据库文件
        $mysqlPath  = root_path().'/data/sql/' . 'install.sql';
        $sqlRecords = file_get_contents($mysqlPath);
        $sqlRecords = str_ireplace("\r", "\n", $sqlRecords);
        // 替换数据库表前缀
        $sqlRecords = explode(";\n", $sqlRecords);
        $sqlRecords = str_replace(" `__PREFIX__", " `{$this->mysqlInfo['prefix']}", $sqlRecords);
        // 执行sql
        $this->execSql($sqlRecords);
        return true;
    }

    /**
     * 执行data.sql
     * @param $plugin
     * @param int $getVer
     * @return array
     */
    public function execDataSql ($getVer = 0) {
        $mysqlPath = __DIR__.'/../install/sql/' . 'data.sql';
        $sqlRecords = file_get_contents($mysqlPath);
        $sqlRecords = str_replace(";\r\n", ";\n", $sqlRecords);
        $sqlRecords = str_replace(" `__PREFIX__", " `{$this->mysqlInfo['prefix']}", $sqlRecords);
		$vers = array();
		$curVer = 0;
		foreach (explode("\n", $sqlRecords) as $row) {
			$row = trim($row);
			preg_match_all('/^--\s*\[v(\d+)\]/', $row, $matches);
			if ($matches && $matches[1]) {
				$curVer = $matches[1][0];
				$vers[$curVer] = '';
			} elseif ($curVer > 0) {
				$vers[$curVer] .= $row . "\n";
			}
		}
		$return = [];
		foreach ($vers as $ver => $sql) {
			if ($getVer < $ver) { 
                $return = array_merge($return,explode("\n",$sql));
			}
		}
        $this->execSql($return);
		return [$return, $curVer];
    }

    /**
     * 读取sql文件为数组
     * @param $sqlFile
     * @param string $prefix
     * @return false|string[]
     */
    function get_sql_array ($sqlFile, $prefix = '') {
        if (!file_exists($sqlFile)) return false;
        $str = file_get_contents($sqlFile);
        $str = preg_replace('/(--.*)|(\/\*(.|\s)*?\*\/)|(\n)/', '', $str);
        $str = str_replace("`__prefix__", "`{$this->mysqlInfo['prefix']}", $str);
        if (!empty($prefix)) {
            $str = preg_replace_callback(
                "/(TABLE|INSERT\\s+?INTO|UPDATE|DELETE\\s+?FROM|SELECT.+?FROM|LEFT\\s+JOIN|JOIN|LEFT)([\\s]|[\\s`])+?(\\w+)([\\s`]|[\\s(])+?/is",
                function ($matches) use ($prefix) {
                    return str_replace($matches[3], $prefix . $matches[3], $matches[0]);
                },
                $str);
        }
        $list = explode(';', trim($str));
        foreach ($list as $key => $val) {
            if ( empty($val) ) {
                unset($list[$key]);
            } else {
                $list[$key].=';';
            }
        }
        return array_values($list);
    }

    /**
     * 执行sql
     * @param array $sqlRecords
     * @return bool|\think\response\Json
     */
    private function execSql (array $sqlRecords) {
        // sql转成数组
        $sqlRecords = $this->get_sql_array($mysqlPath = root_path("data/sql/") . 'install.sql');
        // 设置编码
        mysqli_query($this->connect, "set names utf8mb4");
        // 执行sql
        foreach ($sqlRecords as $index => $sqlLine) {
            $sqlLine = trim($sqlLine);
            if ($sqlLine != 'COMMIT;') {
                if ($sqlLine != 'BEGIN;') {
                    if (!empty($sqlLine) && $index > 1) {
                        try {
                            // 创建表数据
                            if (mysqli_query($this->connect, $sqlLine) === false) {
                                throw new \Exception(mysqli_error($this->connect));
                            }
                        } catch (\Throwable $th) {
                            return error($th->getMessage());
                        }
                    }
                }
            }
        }
        return true;
    }
}