<?php

namespace core\lib\addon\trait;


/**
 * 编译手机端文件
 * Trait WapTrait
 * @package core\lib\addon\trait
 */
trait WapTrait
{

    // TODO 主题色调 theme

    // TODO 图标库 iconfont

    /**
     * 编译 diy-group 自定义组件代码文件
     * @param $compile_path
     * @param $addon
     * @return false|int
     */
    public function compileDiyComponentsCode($compile_path, $addon)
    {
        $content = "<template>\n";
        $content .= "    <view class=\"diy-group\" id=\"componentList\">\n";
        $content .= "        <view v-for=\"(component, index) in data.value\" :key=\"component.id\"\n";
        $content .= "        @click=\"diyStore.changeCurrentIndex(index, component)\"\n";
        $content .= "        :class=\"getComponentClass(index,component)\" :style=\"component.pageStyle\">\n";

        $root_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'app/components/diy'); // 系统自定义组件根目录
        $file_arr = getFileMap($root_path);

        if (!empty($file_arr)) {
            foreach ($file_arr as $ck => $cv) {
                if (str_contains($cv, 'index.vue')) {

                    $path = str_replace($root_path . '/', '', $ck);
                    $path = str_replace('/index.vue', '', $path);
                    if ($path == 'group') {
                        continue;
                    }

                    // 获取自定义组件 key 关键词
                    $name_arr = explode('-', $path);
                    foreach ($name_arr as $k => $v) {
                        // 首字母大写
                        $name_arr[ $k ] = strtoupper($v[ 0 ] ?? '') . substr($v, 1);
                    }
                    $name = implode('', $name_arr);
                    $file_name = 'diy-' . $path;

                    $content .= "            <template v-if=\"component.componentName == '{$name}'\">\n";
                    $content .= "                <$file_name :component=\"component\" :global=\"data.global\" :index=\"index\" :pullDownRefreshCount=\"props.pullDownRefreshCount\"></$file_name>\n";
                    $content .= "            </template>\n";
                }
            }
        }

        // 查询已安装的插件
        $addon_import_content = "";
        $addon_service = new AddonService();
        $addon_list = $addon_service->getInstallAddonList();
        $addon_arr = [];
        if (!empty($addon_list)) {
            foreach ($addon_list as $k => $v) {
                $addon_arr[] = $v[ 'key' ];
            }
        }
        $addon_arr[] = $addon; // 追加新装插件
        $addon_arr = array_unique($addon_arr);
        foreach ($addon_arr as $k => $v) {
            $addon_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'addon/' . $v . '/components/diy'); // 插件自定义组件根目录
            $addon_file_arr = getFileMap($addon_path);
            if (!empty($addon_file_arr)) {
                foreach ($addon_file_arr as $ck => $cv) {
                    if (str_contains($cv, 'index.vue')) {

                        $path = str_replace($addon_path . '/', '', $ck);
                        $path = str_replace('/index.vue', '', $path);

                        // 获取自定义组件 key 关键词
                        $name_arr = explode('-', $path);
                        foreach ($name_arr as $nk => $nv) {
                            // 首字母大写
                            $name_arr[ $nk ] = strtoupper($nv[ 0 ] ?? '') . substr($nv, 1);
                        }
                        $name = implode('', $name_arr);
                        $file_name = 'diy-' . $path;

                        $content .= "            <template v-if=\"component.componentName == '{$name}'\">\n";
                        $content .= "                <$file_name :component=\"component\" :index=\"index\" :pullDownRefreshCount=\"props.pullDownRefreshCount\"></$file_name>\n";
                        $content .= "            </template>\n";

                        $addon_import_content .= "   import diy{$name} from '@/addon/" . $v . "/components/diy/{$path}/index.vue';\n";
                    }
                }
            }
        }

        $content .= "        </view>\n";
        $content .= "        <template v-if=\"diyStore.mode == '' && data.global.bottomTabBarSwitch\">\n";
        $content .= "            <view class=\"pt-[20rpx]\"></view>\n";
        $content .= "            <tabbar :addon=\"tabbarAddonName\" />\n";
        $content .= "        </template>\n";
        $content .= "    </view>\n";
        $content .= "</template>\n";

        $content .= "<script lang=\"ts\" setup>\n";

        if (!empty($addon_import_content)) {
            $content .= $addon_import_content;
        }

        $content .= "   import useDiyStore from '@/app/stores/diy';\n";
        $content .= "   import { ref, onMounted, nextTick, computed } from 'vue';\n";
        $content .= "   import Sortable from 'sortablejs';\n";
        $content .= "   import { range } from 'lodash-es';\n";
        $content .= "   import useConfigStore from '@/stores/config'\n\n";

        $content .= "   const props = defineProps(['data','pullDownRefreshCount']);\n";
        $content .= "   const diyStore = useDiyStore();\n\n";

        $content .= "   const data = computed(() => {\n";
        $content .= "       if (diyStore.mode == 'decorate') {\n";
        $content .= "           return diyStore;\n";
        $content .= "       } else {\n";
        $content .= "           return props.data;\n";
        $content .= "       }\n";
        $content .= "   })\n\n";

        $content .= "   const tabbarAddonName = computed(() => {\n";
        $content .= "       return useConfigStore().addon;\n";
        $content .= "   })\n\n";

        $content .= "   const positionFixed = ref(['fixed', 'top_fixed','right_fixed','bottom_fixed','left_fixed'])\n\n";

        $content .= "   const getComponentClass = (index:any,component:any) => {\n\n";
        $content .= "      let obj: any = {\n\n";
        $content .= "         relative: true,\n\n";
        $content .= "         selected: diyStore.currentIndex == index,\n\n";
        $content .= "         decorate: diyStore.mode == 'decorate'\n\n";
        $content .= "      }\n\n";
        $content .= "      obj['top-fixed-' + diyStore.topFixedStatus] = true;\n\n";
        $content .= "      if (component.position && positionFixed.value.indexOf(component.position) != -1) {\n\n";
        $content .= "        //  找出置顶组件，设置禁止拖动\n\n";
        $content .= "        obj['ignore-draggable-element'] = true;\n\n";
        $content .= "      } else {\n\n";
        $content .= "        obj['draggable-element'] = true;\n\n";
        $content .= "      }\n\n";
        $content .= "      return obj;\n\n";
        $content .= "   }\n\n";

        $content .= "   onMounted(() => {\n";
        $content .= "       // #ifdef H5\n";
        $content .= "       if (diyStore.mode == 'decorate') {\n";
        $content .= "           var el = document.getElementById('componentList');\n";
        $content .= "           const sortable = Sortable.create(el, {\n";
        $content .= "               draggable: '.draggable-element',\n";
        $content .= "               animation: 200,\n";
        $content .= "               // 结束拖拽\n";
        $content .= "               onEnd: event => {\n";
        $content .= "                   let temp = diyStore.value[event.oldIndex!];\n";
        $content .= "                   diyStore.value.splice(event.oldIndex!, 1);\n";
        $content .= "                   diyStore.value.splice(event.newIndex!, 0, temp);\n\n";

        $content .= "                   nextTick(() => {\n";
        $content .= "                       sortable.sort(\n";
        $content .= "                           range(diyStore.value.length).map(value => {\n";
        $content .= "                               return value.toString();\n";
        $content .= "                           })\n";
        $content .= "                       );\n\n";

        $content .= "                       diyStore.postMessage(event.newIndex, diyStore.value[event.newIndex]);\n";
        $content .= "                   });\n";
        $content .= "               }\n";
        $content .= "           });\n";
        $content .= "       }\n";
        $content .= "       // #endif\n";
        $content .= "   });\n";

        $content .= "</script>\n";

        $content .= "<style lang=\"scss\" scoped>\n";
        $content .= "   @import './index.scss';\n";
        $content .= "</style>\n";

        return file_put_contents($compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'addon/components/diy/group/index.vue'), $content);
    }

    /**
     * 编译 fixed-group 固定模板组件代码文件
     * @param $compile_path
     * @param $addon
     * @return false|int
     */
    public function compileFixedComponentsCode($compile_path, $addon)
    {
        $content = "<template>\n";
        $content .= "    <view class=\"fixed-group\">\n";

        $root_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'app/components/fixed'); // 系统固定模板组件根目录
        $file_arr = getFileMap($root_path);

        if (!empty($file_arr)) {
            foreach ($file_arr as $ck => $cv) {
                if (str_contains($cv, 'index.vue')) {

                    $path = str_replace($root_path . '/', '', $ck);
                    $path = str_replace('/index.vue', '', $path);
                    if ($path == 'group') {
                        continue;
                    }

                    $file_name = 'fixed-' . $path;

                    $content .= "        <template v-if=\"props.data.global.component == '{$path}'\">\n";
                    $content .= "            <$file_name :data=\"props.data\" :pullDownRefreshCount=\"props.pullDownRefreshCount\"></$file_name>\n";
                    $content .= "        </template>\n";
                }
            }
        }

        // 查询已安装的插件
        $addon_import_content = "";
        $addon_service = new AddonService();
        $addon_list = $addon_service->getInstallAddonList();
        $addon_arr = [];
        if (!empty($addon_list)) {
            foreach ($addon_list as $k => $v) {
                $addon_arr[] = $v[ 'key' ];
            }
        }
        $addon_arr[] = $addon; // 追加新装插件
        $addon_arr = array_unique($addon_arr);
        foreach ($addon_arr as $k => $v) {
            $addon_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'addon/' . $v . '/components/fixed'); // 插件固定模板组件根目录
            $addon_file_arr = getFileMap($addon_path);

            if (!empty($addon_file_arr)) {
                foreach ($addon_file_arr as $ck => $cv) {
                    if (str_contains($cv, 'index.vue')) {

                        $path = str_replace($addon_path . '/', '', $ck);
                        $path = str_replace('/index.vue', '', $path);

                        // 获取自定义组件 key 关键词
                        $name_arr = explode('-', $path);
                        foreach ($name_arr as $nk => $nv) {
                            // 首字母大写
                            $name_arr[ $nk ] = strtoupper($nv[ 0 ] ?? '') . substr($nv, 1);
                        }
                        $name = implode('', $name_arr);
                        $file_name = 'fixed-' . $path;

                        $content .= "        <template v-if=\"props.data.global.component == '{$path}'\">\n";
                        $content .= "            <$file_name :data=\"props.data\" :pullDownRefreshCount=\"props.pullDownRefreshCount\"></$file_name>\n";
                        $content .= "        </template>\n";

                        $addon_import_content .= "   import fixed{$name} from '@/addon/" . $v . "/components/fixed/{$path}/index.vue';\n";
                    }
                }
            }
        }

        $content .= "    </view>\n";
        $content .= "</template>\n";

        $content .= "<script lang=\"ts\" setup>\n";
        if (!empty($addon_import_content)) {
            $content .= $addon_import_content;
        }
        $content .= "   const props = defineProps(['data','pullDownRefreshCount']);\n";
        $content .= "</script>\n";

        $content .= "<style lang=\"scss\" scoped>\n";
        $content .= "   @import './index.scss';\n";
        $content .= "</style>\n";

        return file_put_contents($compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'addon/components/fixed/group/index.vue'), $content);
    }

    /**
     * 编译 pages.json 页面路由代码文件，// {{PAGE}}
     * @param $compile_path
     * @return bool|int|void
     */
    public function installPageCode($compile_path)
    {
        if (!file_exists($this->geAddonPackagePath($this->addon) . 'uni-app-pages.php')) return;

        $uniapp_pages = require $this->geAddonPackagePath($this->addon) . 'uni-app-pages.php';

        if (empty($uniapp_pages[ 'pages' ])) {
            return;
        }

        $pages = [];
        $addon_arr = array_unique(array_merge([$this->addon], array_column((new AddonService())->getInstallAddonList(), 'key')));
        foreach ($addon_arr as $addon) {
            if (!file_exists($this->geAddonPackagePath($addon) . 'uni-app-pages.php')) continue;
            $uniapp_pages = require $this->geAddonPackagePath($addon) . 'uni-app-pages.php';
            if (empty($uniapp_pages[ 'pages' ])) continue;

            $page_begin = strtoupper($addon) . '_PAGE_BEGIN';
            $page_end = strtoupper($addon) . '_PAGE_END';

            // 对0.2.0之前的版本做处理
            $uniapp_pages[ 'pages' ] = preg_replace_callback('/(.*)(\\r\\n.*\/\/ PAGE_END.*)/s', function ($match){
                return $match[1] . (substr($match[1], -1) == ',' ? '' : ',') .$match[2];
            }, $uniapp_pages[ 'pages' ]);

            $uniapp_pages[ 'pages' ] = str_replace('PAGE_BEGIN', $page_begin, $uniapp_pages[ 'pages' ]);
            $uniapp_pages[ 'pages' ] = str_replace('PAGE_END', $page_end, $uniapp_pages[ 'pages' ]);
            $uniapp_pages[ 'pages' ] = str_replace('{{addon_name}}', $addon, $uniapp_pages[ 'pages' ]);

            $pages[] = $uniapp_pages[ 'pages' ];
        }

        $content = @file_get_contents($compile_path . "pages.json");
        $content = preg_replace_callback('/(.*\/\/ \{\{ PAGE_BEGAIN \}\})(.*)(\/\/ \{\{ PAGE_END \}\}.*)/s', function ($match) use ($pages) {
            return $match[1] . PHP_EOL . implode(PHP_EOL, $pages) . PHP_EOL . $match[3];
        }, $content);

        // 找到页面路由文件 pages.json，写入内容
        return file_put_contents($compile_path . "pages.json", $content);
    }

    /**
     * 编译 pages.json 页面路由代码文件
     * @param $compile_path
     * @return bool|int|void
     */
    public function uninstallPageCode($compile_path)
    {
        if (!file_exists($this->geAddonPackagePath($this->addon) . 'uni-app-pages.php')) return;

        $uniapp_pages = require $this->geAddonPackagePath($this->addon) . 'uni-app-pages.php';

        if (empty($uniapp_pages[ 'pages' ])) {
            return;
        }

        $pages = [];
        $addon_arr = array_diff(array_column((new AddonService())->getInstallAddonList(), 'key'), [$this->addon]);

        foreach ($addon_arr as $addon) {
            if (!file_exists($this->geAddonPackagePath($addon) . 'uni-app-pages.php')) continue;
            $uniapp_pages = require $this->geAddonPackagePath($addon) . 'uni-app-pages.php';
            if (empty($uniapp_pages[ 'pages' ])) continue;

            $page_begin = strtoupper($addon) . '_PAGE_BEGIN';
            $page_end = strtoupper($addon) . '_PAGE_END';

            $uniapp_pages[ 'pages' ] = str_replace('PAGE_BEGIN', $page_begin, $uniapp_pages[ 'pages' ]);
            $uniapp_pages[ 'pages' ] = str_replace('PAGE_END', $page_end, $uniapp_pages[ 'pages' ]);
            $uniapp_pages[ 'pages' ] = str_replace('{{addon_name}}', $addon, $uniapp_pages[ 'pages' ]);

            $pages[] = $uniapp_pages[ 'pages' ];
        }

        $content = @file_get_contents($compile_path . "pages.json");
        $content = preg_replace_callback('/(.*\/\/ \{\{ PAGE_BEGAIN \}\})(.*)(\/\/ \{\{ PAGE_END \}\}.*)/s', function ($match) use ($pages) {
            return $match[1] . PHP_EOL . implode(PHP_EOL, $pages) . PHP_EOL . $match[3];
        }, $content);
        // 找到页面路由文件 pages.json，写入内容
        return file_put_contents($compile_path . "pages.json", $content);
    }

    /**
     * 编译 加载插件标题语言包
     * @param $compile_path
     * @param $addon
     * @param $addon
     */
    public function compileLocale($compile_path, $addon)
    {
        $locale_data = [];

        $root_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'locale'); // 系统语言包根目录
        $file_arr = getFileMap($root_path, [], false);
        if (!empty($file_arr)) {
            foreach ($file_arr as $ck => $cv) {
                if (str_contains($cv, '.json')) {
                    $app_json = @file_get_contents($ck);
                    $json = json_decode($app_json, true);
                    // 清空当前安装/卸载的插件语言包
                    foreach ($json as $jk => $jc) {
                        if (strpos($jk, $addon) !== false) {
                            unset($json[ $jk ]);
                        }
                    }
                    $locale_data[ $cv ] = [
                        'path' => $ck,
                        'json' => $json
                    ];
                }
            }
        }

        // 查询已安装的插件
        $addon_service = new AddonService();
        $addon_list = $addon_service->getInstallAddonList();
        $addon_arr = [];
        if (!empty($addon_list)) {
            foreach ($addon_list as $k => $v) {
                $addon_arr[] = $v[ 'key' ];
            }
        }
        $addon_arr[] = $addon; // 追加新装插件
        $addon_arr = array_unique($addon_arr);
        foreach ($addon_arr as $k => $v) {
            $addon_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'addon/' . $v . '/locale'); // 插件语言包根目录
            $addon_file_arr = getFileMap($addon_path, [], false);
            if (!empty($addon_file_arr)) {
                foreach ($addon_file_arr as $ck => $cv) {
                    if (str_contains($cv, '.json')) {
                        $json = @file_get_contents($ck);
                        $json = json_decode($json, true);
                        $addon_json = [];
                        foreach ($json as $jk => $jv) {
                            $addon_json[ $v . '.' . $jk ] = $jv;
                        }
                        if (isset($locale_data[ $cv ])) $locale_data[ $cv ][ 'json' ] = array_merge($locale_data[ $cv ][ 'json' ], $addon_json);
                    }
                }
            }
        }

        foreach ($locale_data as $k => $v) {
            file_put_contents($v[ 'path' ], json_encode($v[ 'json' ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    /**
     * 合并manifest.json
     * @param string $compile_path
     * @param array $merge_data
     * @return void
     */
    public function mergeManifestJson(string $compile_path, array $merge_data) {
        $manifest_json = str_replace('/',  DIRECTORY_SEPARATOR, $compile_path . 'src/manifest.json');
        $manifest_content = $this->jsonStringToArray(file_get_contents($manifest_json));

        (new BaseAddonService())->writeArrayToJsonFile(array_merge2($manifest_content, $merge_data), $manifest_json);
    }

    /**
     * json 字符串解析成数组
     * @param $string
     * @return array
     */
    private function jsonStringToArray($string) {
        $list = explode(PHP_EOL, $string);

        $json_array = [];
        foreach ($list as $index => $item) {
            if (strpos($item, '/*') === false) {
                $json_array[] = $item;
            }
        }
        return json_decode(implode(PHP_EOL, $json_array), true);
    }
}
