<?php

namespace FanAdmin\src\command\install;


use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\Console;
use think\helper\Str;

/**
 * 系统安装
 * Class AdminInstallCommand
 * @package FanAdmin\src\command\install
 */
class AdminInstallCommand extends Command {

    /**
     * @var string 前端项目文件地址
     */
    protected string $adminViewAddress = 'https://gitee.com/fana-dmin/admin.git';

    /**
     * @var array 数据库信息
     */
    protected array  $database = [];

    /**
     * @var string 应用域名
     */
    protected string $appDomain = '';

    protected function configure () {
        $this->setName('install:admin')
            ->addOption('reinstall', '-r',Option::VALUE_NONE, 'reinstall back')
            ->setDescription('安装项目');
    }

    protected function execute(Input $input, Output $output): void {
        if ($input->getOption('reinstall')) {
            // 重新安装
            $this->reinstall();
        } else {
            // 检查安装环境
            $this->checkInstallationEnvironment();
            // 设置数据库配置
            $this->setDatabaseConfiguration();
            // 安装数据库
            $this->installDatabase();
            // 安装完成过
            $this->installComplete();
        }
        // 完成
        $this->project();
    }

    /**
     * 安装环境检查
     */
    protected function checkInstallationEnvironment (): void {
        $this->output->info('环境开始检查...');
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            $this->output->error('PHP版本应该 >= 8.0.0');
            exit();
        }
        $this->output->info('php版本 ' . PHP_VERSION);
        if (!extension_loaded('mbstring')) {
            $this->output->error('mbstring扩展未安装');exit();
        }
        $this->output->info('mbstring扩展已安装');
        if (!extension_loaded('json')) {
            $this->output->error('json扩展未安装');
            exit();
        }
        $this->output->info('json扩展已安装');
        if (!extension_loaded('openssl')) {
            $this->output->error('openssl扩展未安装');
            exit();
        }
        $this->output->info('openssl扩展已安装');
        if (!extension_loaded('pdo')) {
            $this->output->error('pdo扩展未安装');
            exit();
        }
        $this->output->info('pdo扩展已安装');
        if (!extension_loaded('xml')) {
            $this->output->error('xml扩展未安装');
            exit();
        }
        $this->output->info('xml扩展已安装');
        $this->output->info('🎉安装环境检查完成');
    }


    /**
     * 设置数据库连接信息
     * @return false
     */
    protected function setDatabaseConfiguration () {
        if (file_exists($this->app->getRootPath() . '.env')) {
            return false;
        }
        $appDomain = strtolower($this->output->ask($this->input, '👉首先,你用该设置应用域名'));
        if (!str_contains($appDomain, 'http')) {
            $appDomain = 'http://' . $appDomain;
        }
        $this->appDomain = $appDomain;
        $answer = strtolower($this->output->ask($this->input, '🤔️你是否需要设置数据库信息? (Y/N)'));
        if ($answer === 'y' || $answer === 'yes') {
            $charset = $this->output->ask($this->input, '👉请输入数据库字符集,默认值(utf8mb4)') ? : 'utf8mb4';
            $database = '';
            while (!$database) {
                $database = $this->output->ask($this->input, '👉请输入数据库名称');
                if ($database) {
                    break;
                }
            }
            $host     = $this->output->ask($this->input, '👉请输入数据库主机,默认值 (127.0.0.1)') ? : '127.0.0.1';
            $port     = $this->output->ask($this->input, '👉请输入数据库主机端口,默认值 (3306)') ? : '3306';
            $prefix   = $this->output->ask($this->input, '👉请输入表前缀,默认值 (fa_)') ? : 'fa_';
            $username = $this->output->ask($this->input, '👉 请输入数据库默认用户名,默认值 (root)') ? : 'root';
            $password = '';
            $tryTimes = 0;
            while (!$password) {
                $password = $this->output->ask($this->input, '👉请输入数据库密码');
                if ($password) {
                    break;
                }
                $tryTimes++;
                if (!$password && $tryTimes > 2) {
                    break;
                }
            }
            $this->database = [$host, $database, $username, $password, $port, $charset, $prefix];
            $this->createEnvFile($host, $database, $username, $password, $port, $charset, $appDomain, $prefix);
        }
    }

    /**
     * 安装数据库
     */
    protected function installDatabase (): void {
        if (file_exists($this->getEnvFilePath())) {
            $connections = \config('database.connections');
            if (!$this->database) {
                unlink($this->getEnvFilePath());
                $this->execute($this->input, $this->output);
            } else {
                [
                    $connections['mysql']['hostname'],
                    $connections['mysql']['database'],
                    $connections['mysql']['username'],
                    $connections['mysql']['password'],
                    $connections['mysql']['hostport'],
                    $connections['mysql']['charset'],
                    $connections['mysql']['prefix'],
                ] = $this->database ?: [
                    env('mysql.hostname')
                ];
                \config([
                    'connections' => $connections,
                ], 'database');

            }
            Console::call('install:database');
        }
    }

    /**
     *  安装完成
     */
    protected function installComplete (): void {
        // create jwt
        Console::call('jwt:create');
        // 安装admin文件
        $this->installAdminView();
        // 安装uni-app文件

    }


    /**
     * 生成 .env 配置文件
     * @param $host
     * @param $database
     * @param $username
     * @param $password
     * @param $port
     * @param $charset
     * @param $appDomain
     * @param $prefix
     */
    protected function createEnvFile ($host, $database, $username, $password, $port, $charset, $appDomain, $prefix): void {
        try {
            $env = \parse_ini_file(root_path() . '.example.env', true);
            $env['APP_HOST']   = $appDomain;
            $env['DB_HOST']    = $host;
            $env['DB_NAME']    = $database;
            $env['DB_USER']    = $username;
            $env['DB_PASS']    = $password;
            $env['DB_PORT']    = $port;
            $env['DB_PREFIX']  = $prefix;
            $env['DB_CHARSET'] = $charset;
//            # JWT 密钥
//            $env['JWT_SECRET'] = md5(Str::random(8));
            $dotEnv = '';
            foreach ($env as $key => $e) {
                if (is_string($e)) {
                    $dotEnv .= sprintf('%s=%s', $key, $e === '1' ? 'true' : ($e === '' ? 'false' : $e));
                    $dotEnv .= PHP_EOL;
                } else {
                    $dotEnv .= sprintf('[%s]', $key);
                    foreach ($e as $k => $v) {
                        $dotEnv .= sprintf('%s=%s', $k, $v === '1' ? 'true' : ($v === '' ? 'false' : $v)) ;
                    }
                    $dotEnv .= PHP_EOL;
                }
            }
            if ($this->getEnvFile()) {
                $this->output->info('env file has been generated');
            }
            if ((new \mysqli($host, $username, $password, null, $port))
                ->query(
                    sprintf(
                        'CREATE DATABASE IF NOT EXISTS %s DEFAULT CHARSET %s COLLATE %s_general_ci;',
                $database, $charset, $charset))) {
                $this->output->info(sprintf('🎉成功创建数据库', $database));
            } else {
                $this->output->warning(sprintf('创建数据库失败,你需要先自己创建数据库: ', $database));
            }
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());
            exit(0);
        }
        file_put_contents(root_path() . '.env', $dotEnv);
    }

    /**
     * 获取环境变量文件
     * @return string
     */
    protected function getEnvFile (): string {
        return file_exists(root_path() . '.env') ? root_path() . '.env' : '';
    }


    /**
     * 使用项目
     */
    protected function project () {
        $year = date('Y');
        $this->output->info('项目已安装, welcome!');
        $this->output->info(sprintf('
 /------------------------  欢迎使用  -------------------------\      
|                                                              |
|        /_____               ___       __               _     |
|       / ____/___ _____     /   | ____/ /___ ___  (_)___      |
|      / /_  / __ `/ __ \   / /| |/ __  / __ `__ \/ / __ \     |
|     / __/ / /_/ / / / /  / ___ / /_/ / / / / / / / / / /     | 
|    /_/    \__,_/_/ /_/  /_/  |_\__,_/_/ /_/ /_/_/_/ /_/      |
|                                                              |   
 \ __ __ __ __ _ __ _ __ enjoy it ! _ __ __ __ __ __ __ ___ _ @   /_`_  _   /_/ _/_ _  ._ 
                                                                 /  /_|/ / / //_// / /// /
 初始账号: admin
 初始密码: admin23                                             
', $year));
        exit(0);
    }

    /**
     * 重新安装
     */
    protected function reinstall(): void {
        $ask = strtolower($this->output->ask($this->input,'重置项目? (Y/N)'));
        if ($ask === 'y' || $ask === 'yes' ) {
            Console::call('install:rollback');
            if (file_exists($this->getEnvFilePath())) {
                unlink($this->getEnvFilePath());
            }
        }
    }

    /**
     * 获取 env path
     *
     * @time 2020年04月06日
     * @return string
     */
    protected function getEnvFilePath(): string
    {
        return root_path() . '.env';
    }

    /**
     * 安装后台admin文件
     */
    protected function installAdminView (): void {
        $webPath = $this->app->getRootPath(). DIRECTORY_SEPARATOR . 'admin';
        if (! is_dir($webPath)) {
            $this->output->info('下载前端项目: ');
            shell_exec("git clone {$this->adminViewAddress} admin");
            if (is_dir($webPath)) {
                $this->output->info('下载前端项目成功');
                $this->output->info('设置镜像源: ');
                shell_exec('yarn config set registry https://registry.npmmirror.com');
                $this->output->info('安装前端依赖,如果安装失败,请检查是否已安装了前端 pnpm 管理工具,或者因为网络等原因');
                shell_exec('cd ' . $this->app->getRootPath() . DIRECTORY_SEPARATOR . 'web && pnpm install');
                $this->output->info('手动启动使用 pnpm run dev');
                $this->output->info('项目启动后不要忘记设置 web/.env 里面的环境变量 VITE_BASE_URL');
                $this->output->info('安装前端依赖成功,开始启动前端项目: ');
                file_put_contents($webPath . DIRECTORY_SEPARATOR . '.env', <<<STR
VITE_BASE_URL=$this->appDomain/api
VITE_APP_NAME=后台管理
STR
                );
                shell_exec("cd {$webPath} && pnpm run dev");
            } else {
                $this->output->error('下载前端项目失败, 请到该仓库下载 https://gitee.com/fana-dmin/admin.git');
            }
        }
    }
}
