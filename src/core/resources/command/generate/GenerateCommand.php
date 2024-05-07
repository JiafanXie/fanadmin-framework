<?php

namespace FanAdmin\src\command\generate;


use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 一键生成代码
 * Class GenerateCommand
 * @package FanAdmin\src\command\generate
 */
class GenerateCommand extends Command {

    /**
     * 应用基础目录
     * @var
     */
    protected $basePath;

    protected function configure() {
        $this->setName('generate:create')
            ->addArgument('generate', Argument::OPTIONAL, 'generate name .')
            ->setDescription('Create App Dirs');
    }

    protected function execute (Input $input, Output $output) {
        $this->basePath = $this->app->getBasePath();
        $app            = $input->getArgument('generate') ?: '';

        // 判断是否存在这个应用
        if(is_dir($this->basePath.$app)){
            return $output->error('应用已经存在');
        }
        $list = [
            '__dir__' => ['controller', 'model', 'view','install','route'],
            'install'=> ['install','data'],
            'route' => ['index'],
            'view' => ['index'],

        ];
        $this->buildApp($app, $list);
        $output->writeln("<info>Successed</info>");
    }

    /**
     * 创建应用
     * @param string $app
     * @param array $list
     */
    protected function buildApp (string $app, array $list = []): void {
        if (!is_dir($this->basePath . $app)) {
            // 创建应用目录
            mkdir($this->basePath . $app);
        }

        $appPath   = $this->basePath . ($app ? $app . DIRECTORY_SEPARATOR : '');
        $namespace = 'app' . ($app ? '\\' . $app : '');

        // 创建配置文件和公共文件
        $this->buildCommon($app);
        // 创建模块的默认页面
        $this->buildHello($app, $namespace);
        // 创建plugin.json
        $this->bulidPluginJosn($app);
        // 创建菜单文件
        $this->buildPluginMenu($app);

        foreach ($list as $path => $file) {
            if ('__dir__' == $path) {
                // 生成子目录
                foreach ($file as $dir) {
                    $this->checkDirBuild($appPath . $dir);
                }
            } elseif ('__file__' == $path) {
                // 生成（空白）文件
                foreach ($file as $name) {
                    if (!is_file($appPath . $name)) {
                        file_put_contents($appPath . $name, 'php' == pathinfo($name, PATHINFO_EXTENSION) ? '<?php' . PHP_EOL : '');
                    }
                }
            } else {
                // 生成相关MVC文件
                foreach ($file as $val) {
                    $val      = trim($val);
                    $filename = $appPath . $path . DIRECTORY_SEPARATOR . $val . '.php';
                    $space    = $namespace . '\\' . $path;
                    $class    = $val;
                    switch ($path) {
                        case 'controller': // 控制器
                            if ($this->app->config->get('route.controller_suffix')) {
                                $filename = $appPath . $path . DIRECTORY_SEPARATOR . $val . 'Controller.php';
                                $class    = $val . 'Controller';
                            }
                            $content = "<?php" . PHP_EOL . "namespace {$space};" . PHP_EOL . PHP_EOL . "class {$class}" . PHP_EOL . "{" . PHP_EOL . PHP_EOL . "}";
                            break;
                        case 'model': // 模型
                            $content = "<?php" . PHP_EOL . "namespace {$space};" . PHP_EOL . PHP_EOL . "use think\Model;" . PHP_EOL . PHP_EOL . "class {$class} extends Model" . PHP_EOL . "{" . PHP_EOL . PHP_EOL . "}";
                            break;
                        case 'view': // 视图
                            $filename = $appPath . $path . DIRECTORY_SEPARATOR . $val . '.html';
                            $this->checkDirBuild(dirname($filename));
                            $content = '';
                            break;
                        case 'install': // 安装文件
                            $filename = $appPath . $path . DIRECTORY_SEPARATOR . $val . '.sql';
                            $this->checkDirBuild(dirname($filename));
                            $content = '';
                            break;
                        case 'route': // 路由
                            $filename = $appPath . $path . DIRECTORY_SEPARATOR . $val . '.php';
                            $this->checkDirBuild(dirname($filename));
                            $content = "<?php".PHP_EOL."use think\facade\Route;" . PHP_EOL . "Route::get('/','Index/index');";
                            break; 
                        default:
                            // 其他文件
                            $content = "<?php" . PHP_EOL . "namespace {$space};" . PHP_EOL . PHP_EOL . "class {$class}" . PHP_EOL . "{" . PHP_EOL . PHP_EOL . "}";
                    }

                    if (!is_file($filename)) {
                        file_put_contents($filename, $content);
                    }
                }
            }
        }
    }

    /**
     * 创建应用的欢迎页面
     * @param string $app
     * @param string $namespace
     */
    protected function buildHello (string $app, string $namespace): void {
        $suffix   = $this->app->config->get('route.controller_suffix') ? 'Controller' : '';
        $filename = $this->basePath . ($app ? $app . DIRECTORY_SEPARATOR : '') . 'controller' . DIRECTORY_SEPARATOR . 'Index' . $suffix . '.php';

        if (!is_file($filename)) {
            $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.stub');
            $content = str_replace(['{%name%}', '{%app%}', '{%layer%}', '{%suffix%}'], [$app, $namespace, 'controller', $suffix], $content);
            $this->checkDirBuild(dirname($filename));

            file_put_contents($filename, $content);
        }
    }

    /**
     * 创建应用的公共文件
     * @param string $app
     */
    protected function buildCommon (string $app): void {
        $appPath = $this->basePath . ($app ? $app . DIRECTORY_SEPARATOR : '');

        if (!is_file($appPath . 'common.php')) {
            file_put_contents($appPath . 'common.php', "<?php" . PHP_EOL . "// 这是系统自动生成的公共文件" . PHP_EOL);
        }

        foreach (['event', 'middleware', 'common'] as $name) {
            if (!is_file($appPath . $name . '.php')) {
                file_put_contents($appPath . $name . '.php', "<?php" . PHP_EOL . "// 这是系统自动生成的{$name}定义文件" . PHP_EOL . "return [" . PHP_EOL . PHP_EOL . "];" . PHP_EOL);
            }
        }
    }

    /**
     * 创建配置文件plugin.josn
     * @param $app
     */
    protected function bulidPluginJosn ($app) {
        $appPath = $this->basePath . ($app ? $app . DIRECTORY_SEPARATOR : '');
        $content = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'plugin.json.stub');
        $content = str_replace(['{%plugin%}'], [$app], $content);
       
        $filename = $this->basePath . ($app ? $app . DIRECTORY_SEPARATOR : '').'plugin.json';
        file_put_contents($filename, $content);
    }

    /**
     * 创建菜单文件
     * @param $plugin
     */
    public function buildPluginMenu ($plugin) {
        $appPath = $this->basePath . ($plugin ? $plugin . DIRECTORY_SEPARATOR : '');
        file_put_contents($appPath . 'menu.php', "<?php" . PHP_EOL . "// 这是系统自动生成的菜单文件" . PHP_EOL."return [];");
    }

    /**
     * 创建目录
     * @param string $dirname
     */
    protected function checkDirBuild (string $dirname): void {
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
    }
}