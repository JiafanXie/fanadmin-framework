<?php

namespace FanAdmin\enum\driver;


use FanAdmin\base\enum\BaseEnum;

/**
 * 语言类
 * Class Lang
 * @package FanAdmin\enum\driver
 */
class Lang extends BaseEnum
{
    /**
     * 加载语言
     * @param array $data
     * @return array|mixed
     */
    public function data(array $data)
    {
        $addons = $this->getLocalAddons();
        $system_lang_path = $this->getAppPath() . "lang" . DIRECTORY_SEPARATOR . $data['lang_type'] . DIRECTORY_SEPARATOR;
        $lang_files = [
            $system_lang_path . "api.php",
            $system_lang_path . "dict.php",
            $system_lang_path . "validate.php",
        ];


        foreach ($addons as $v) {
            $lang_path = $this->getAddonAppPath($v) . "lang" . DIRECTORY_SEPARATOR . $data['lang_type'] . DIRECTORY_SEPARATOR;

            $api_path = $lang_path . "api.php";
            $dict_path = $lang_path . "dict.php";
            $validate_path = $lang_path . "validate.php";
            if (is_file($api_path)) {
                $lang_files[] = $api_path;

            }
            if (is_file($dict_path)) {
                $lang_files[] = $dict_path;
            }
            if (is_file($validate_path)) {
                $lang_files[] = $validate_path;
            }
        }
        $files_data = $this->loadFiles($lang_files);
        $lang = [];
        foreach ($files_data as $file_data) {
            $lang = empty($lang) ? $file_data : array_merge2($lang, $file_data);
        }
        return $lang;
    }
}
