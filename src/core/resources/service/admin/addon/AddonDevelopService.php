<?php

namespace FanAdmin\src\http\service\admin\addon;

use app\dict\sys\FileDict;
use FanAdmin\src\http\model\addon\AddonModel as Addon;
use FanAdmin\src\http\service\upload\CoreFetchService;
use FanAdmin\exception\AddonException;
use FanAdmin\exception\UploadException;

/**
 * 插件开发服务层
 */
class AddonDevelopService extends BaseAddonService
{
    public $baseAddonDir;
    private $map = [
        'admin' => [
            'api' => [
                [
                    'name' => 'hello_world.ts',
                    'vm' => 'admin' . DIRECTORY_SEPARATOR . 'api.vm',
                ]
            ],
            'assets' => [],
            'lang' => [
                'zh-cn' => [
                    [
                        'name' => 'hello_world.index.json',
                        'vm' => 'admin' . DIRECTORY_SEPARATOR . 'lang.vm',
                    ]
                ]
            ],
            'views' => [
                'hello_world' => [
                    [
                        'name' => 'index.vue',
                        'vm' => 'admin' . DIRECTORY_SEPARATOR . 'views.vm',
                    ]
                ]

            ]
        ],
        'app' => [
            'admin' => [
                'controller' => [
                    'hello_world' => [
                        [
                            'name' => 'Index.php',
                            'vm' => 'system' . DIRECTORY_SEPARATOR . 'admin_controller.vm',
                        ]
                    ]
                ],
                'route' => [
                    [
                        'name' => 'route.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'admin_route.vm',
                    ]
                ]
            ],
            'api' => [
                'controller' => [
                    'hello_world' => [
                        [
                            'name' => 'Index.php',
                            'vm' => 'system' . DIRECTORY_SEPARATOR . 'api_controller.vm',
                        ]
                    ],
                ],
                'route' => [
                    [
                        'name' => 'route.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'api_route.vm'
                    ]

                ]
            ],
            'dict' => [
                'menu' => [
                    [
                        'name' => 'admin.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'admin_menu.vm'
                    ],
                    [
                        'name' => 'site.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'menu.vm'
                    ]
                ]

            ],
            'job' => [

            ],
            'lang' => [
                'zh-cn' => [
                    [
                        'name' => 'api.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'common.vm'
                    ],
                    [
                        'name' => 'dict.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'common.vm'
                    ],
                    [
                        'name' => 'validate.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'common.vm'
                    ]
                ],

                'en' => [
                    [
                        'name' => 'api.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'common.vm'
                    ],
                    [
                        'name' => 'dict.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'common.vm'
                    ],
                    [
                        'name' => 'validate.php',
                        'vm' => 'system' . DIRECTORY_SEPARATOR . 'common.vm'
                    ]
                ]

            ],
            'listener' => [

            ],
            'model' => [

            ],
            'service' => [

            ],
            'validate' => [

            ],
            [
                'name' => 'event.php',
                'vm' => 'system' . DIRECTORY_SEPARATOR . 'event.vm'
            ]
        ],
        'sql' => [
            [
                'name' => 'install.sql',
            ],
            [
                'name' => 'uninstall.sql',
            ]
        ],
        'package' => [
            [
                'name' => 'uni-app-pages.php',
                'vm' => 'package' . DIRECTORY_SEPARATOR . 'uni-app-pages.vm'
            ],
        ],
        'resource' => [
            [
                'name' => 'cover.png',
                'is_cover' => true
            ],
            [
                'name' => 'icon.png',
                'is_cover' => true
            ],
        ],
        'uni-app' => [
            'api' => [
                [
                    'name' => 'hello_world.ts',
                    'vm' => 'uni-app' . DIRECTORY_SEPARATOR . 'api.vm',
                ],
            ],
            'components' => [],
            'locale' => [
                'zh-Hans' => [
                    [
                        'name' => 'pages.hello_world.index.json',
                        'vm' => 'uni-app' . DIRECTORY_SEPARATOR . 'lang.vm',
                    ],
                ],
                [
                    'name' => 'en.json',
                    'vm' => 'uni-app' . DIRECTORY_SEPARATOR . 'lang.vm',
                ],
                [
                    'name' => 'zh-Hans.json',
                    'vm' => 'uni-app' . DIRECTORY_SEPARATOR . 'lang.vm',
                ],
            ],
            'pages' => [
                'hello_world' => [
                    [
                        'name' => 'index.vue',
                        'vm' => 'uni-app' . DIRECTORY_SEPARATOR . 'views.vm',
                    ],
                ]
            ],
            'utils' => []
        ],

        'web' => [
            'api' => [
                [
                    'name' => 'hello_world.ts',
                    'vm' => 'web' . DIRECTORY_SEPARATOR . 'api.vm',
                ],
            ],
            'lang' => [
                'zh-cn' => [
                    [
                        'name' => 'pages.json',
                        'vm' => 'web' . DIRECTORY_SEPARATOR . 'lang_pages.vm',
                    ],
                    [
                        'name' => 'hello_world.index.json',
                        'vm' => 'web' . DIRECTORY_SEPARATOR . 'lang.vm',
                    ],
                ],
            ],
            'pages' => [
                'hello_world' => [
                    [
                        'name' => 'index.vue',
                        'vm' => 'web' . DIRECTORY_SEPARATOR . 'view.vm',
                    ],
                ],
                [
                    'name' => 'routes.ts',
                    'vm' => 'web' . DIRECTORY_SEPARATOR . 'routes.vm',
                ],
            ],
        ],
        'compile' => [
            'admin' => [

            ],
            'wap' => [

            ],
            'weapp' => [

            ],
            'web' => [

            ],
            'aliapp' => [

            ],
        ],
        [
            'name' => 'info.json',
            'vm' => 'system' . DIRECTORY_SEPARATOR . 'info.vm',
            'is_cover' => true
        ],
        [
            'name' => 'Addon.php',
            'vm' => 'system' . DIRECTORY_SEPARATOR . 'addon.vm'
        ]

    ];
    /**
     * @var
     */
    private $addonInfo;

    /**
     * @var
     */
    private $action;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Addon();
    }

    /**
     * 开发中插件列表
     * @param string $search
     * @return array
     */
    public function getList(string $search = '')
    {
        $list = [];
        $addonList = $this->model->column('name, icon, key, desc, status, author, version, install_time, update_time, cover, type', 'key');
        $files = getFilesByDir($this->addonPath);
        if (!empty($files)) {
            foreach ($files as $path) {
                $data = $this->getAddonConfig($path);
                if (isset($data['key'])) {
                    $key = $data['key'];
                    $data['install_info'] = $addonList[$key] ?? [];
                    $data['icon']         = is_file($data['icon']) ? imageToBase64($data['icon']) : '';
                    $data['cover']        = is_file($data['cover']) ? imageToBase64($data['cover']) : '';
                    $data['is_download']  = true;
                    $data['type_name']    = empty($data['type']) ? '' : ['app' => '应用', 'addon' => '插件'][$data['type']] ?? '';
                    $list[$key]           = $data;
                }
            }
        }
        if ($search) {
            foreach ($list as $k => $v) {
                if (!str_contains($v['name'], $search)) unset($list[$k]);
            }
        }
        return array_values($list);
    }

    /**
     * 开发插件详情
     * @param $key
     * @return mixed
     */
    public function getInfo($key){
        $dir = $this->addonPath . $key . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) return [];
        $data = $this->getAddonConfig($key);
        if (isset($data['key'])) {
            $data['icon']      = is_file($data['icon']) ? imageToBase64($data['icon']) : '';
            $data['cover']     = is_file($data['icon']) ? imageToBase64($data['cover']) : '';
            $data['type_name'] = empty($data['type']) ? '' : ['app' => '应用', 'addon' => '插件'][$data['type']] ?? '';
        }
        if (isset($data['support_app']) && !empty($data['support_app'])) {
            $data['support_type'] = 2;
        } else {
            $data['support_type'] = 1;
        }
        return $data;
    }

    /**
     * 开发插件添加
     * @param array $data
     * @return bool
     */
    public function add(array $data)
    {
        $this->baseAddonDir = $this->addonPath. DIRECTORY_SEPARATOR . $data['key'];
        if (is_dir($this->baseAddonDir)) throw new AddonException('当前目录中已存在key值一致的插件.');
        $this->setAddonInfo($data);
        $this->filePut($this->map, $this->baseAddonDir);
        return true;
    }

    /**
     * 开发插件编辑
     * @param string $key
     * @param array $data
     * @return bool
     */
    public function edit(string $key, array $data)
    {
        $this->baseAddonDir = $this->baseAddonDir. DIRECTORY_SEPARATOR . $data['key'];
        if (!is_dir($this->baseAddonDir)) throw new AddonException('当前目录中不存在此项插件');
        $this->action = 'edit';
        $this->setAddonInfo($data);
        $this->filePut($this->map, $this->baseAddonDir);
        $where = [
            ['key', '=', $key]
        ];
        $info = $this->model->where($where)->findOrEmpty();
        if (!$info->isEmpty()) {
            $info->save([
                    'title' => $data['title'],
                    'desc' => $data['desc'],
                    'author' => $data['author'],
                    'version' => $data['version'],
                    'type' => $data['type'],
                    'support_app' => $data['support_app'],
                    'update_time' => time(),
                ]);
        }
        return true;
    }

    /**
     * 删除
     * @return bool
     */
    public function delete(string $key)
    {
        $this->baseAddonDir = $this->baseAddonDir. DIRECTORY_SEPARATOR . $key;
        if (!is_dir($this->baseAddonDir)) throw new AddonException('当前目录中不存在此项插件');
        $where = [
            ['key', '=', $key]
        ];
        $info = $this->model->where($where)->findOrEmpty();
        if (!$info->isEmpty()) {
            throw new AddonException('ADDON_IS_INSTALLED_NOT_ALLOW_DEL');
        }
        //删除目录文件
        delTargetDir($this->baseAddonDir, true);
        return true;
    }

    /**
     * 设置插件信息
     * @param $data
     */
    public function setAddonInfo($data)
    {
        $this->addonInfo = $data;
    }

    /**
     * 文件创建
     * @param $item
     * @param string $root_k
     * @param string $key
     * @return bool
     */
    public function filePut($item, $root_k = '', $key = '')
    {
        //key为int为文件,否者是文件夹
        if (is_int($key)) {
            $this->fileAdd($item, $root_k);
        } else {
            $itemDir = $root_k . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR;
            if ($key) {
                if (!is_dir($itemDir) && !mkdir($itemDir, 0777, true) && !is_dir($itemDir)) {
                    throw new AddonException(sprintf('Directory "%s" was not created', $itemDir));
                }
            }
            if (!empty($item)) {
                foreach ($item as $k => $v) {
                    $this->filePut($v, $itemDir, $k);
                }
            }
        }
        return true;
    }

    /**
     * 文本替换
     * @param $item
     * @param string $dir
     * @return bool
     */
    public function fileAdd($item, $dir = '')
    {
        $is_cover = $item['is_cover'] ?? false;
        if ($this->action == 'edit' && !$is_cover) {
            return true;
        }
        $name = $item['name'] ?? '';
        if (!$name) {
            return true;
        }
        $file = $dir . $name;
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new AddonException(sprintf('Directory "%s" was not created', $dir));
        }
        if (strpos($name, 'png') || strpos($name, 'jpg')) {
            $file_name = explode('.', $name)[0] ?? '';
            if (empty($file_name)) return true;
            $image = $this->addon_info[$file_name] ?? '';
            if (empty($image)) return true;
            if (checkFileIsRemote($image)) {
                try {
//                    (new CoreFetchService())->setRootPath($dir)->setRename($name)->image($image, 0, FileDict::LOCAL);
                } catch ( UploadException $e ) {
                    return true;
                }
            } else {
                @copy($image, $file);
            }
        } else {
            //创建路由文件
            $vm_root_dir = root_path('app') . 'service' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR;
            $vm = $item['vm'] ?? '';
            if (is_file($vm_root_dir . $vm)) {
                $content = file_get_contents($vm_root_dir . $vm);
                $content = $this->contentReplace($content, $this->addonInfo);
            } else {
                $content = '';
            }
            //如果已存在就不要创建了
//        if(!is_file($file)){
            file_put_contents($file, $content);
//        }
        }

        return true;
    }

    /**
     * 文本根据变量组来替换字符
     * @param $content
     * @param $vars
     * @return array|mixed|string|string[]
     */
    public function contentReplace($content, $vars)
    {
        foreach ($vars as $k => $v) {
            $content = str_replace('{' . $k . '}', $v, $content);
        }
        return $content;
    }
}
