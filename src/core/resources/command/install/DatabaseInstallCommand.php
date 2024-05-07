<?php

namespace FanAdmin\src\command\install;


use FanAdmin\lib\sql\SqlExecute;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 数据库安装类
 * Class DatabaseInstallCommand
 * @package FanAdmin\src\command\install
 */
class DatabaseInstallCommand extends Command {

    protected function configure()
    {
        // 指令配置
        $this->setName('install:database')
            ->setDescription('the fan command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 执行安装sql
        (new SqlExecute())->execInstallSql();
    }
}