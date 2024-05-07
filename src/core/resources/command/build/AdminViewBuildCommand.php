<?php

namespace FanAdmin\src\command\build;


use FanAdmin\lib\terminal\Terminal;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * admin后台vue文件打包正式
 * Class AdminViewBuildCommand
 * @package FanAdmin\src\command\build
 */
class AdminViewBuildCommand extends Command {

    protected function configure () {
        $this->setName('admin:build')
        	->setDescription('构建admin web');
    }

    protected function execute (Input $input, Output $output) {
        // 执行构建
        Terminal::execute(root_path('admin'),'npm run build');
    }
}