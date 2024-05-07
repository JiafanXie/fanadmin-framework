<?php

namespace FanAdmin\src\command\build;


use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * admin后台vue发布正式
 * Class AdminViewPublishCommand
 * @package FanAdmin\src\command\build
 */
class AdminViewPublishCommand extends Command {

    protected function configure () {
        $this->setName('admin:publish')
        	->setDescription('发布FruitAdmin静态文件');
    }

    protected function execute (Input $input, Output $output) {
        // 发布admin
        // 备份
        copyDirectory(__DIR__.DIRECTORY_SEPARATOR.'../../admin/src/lnk',runtime_path('backups/admin/admin/'.time().'/src/lnk').DIRECTORY_SEPARATOR);
        // 删除
        deleteDirectory(root_path('admin/src/lnk'));
        // 复制
        copyDirectory(__DIR__.DIRECTORY_SEPARATOR.'../../admin/',root_path('admin/'));
        // 创建app目录
        if(!is_dir(root_path().'admin/src/app/')){
            mkdir(root_path().'admin/src/app/');
        }
        // 发布admin-static
        copyDirectory(__DIR__.DIRECTORY_SEPARATOR.'../../admin-static',public_path('admin-static'));
    	$output->info('FruitAdmin 发布成功');
    }
}