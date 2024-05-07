<?php

namespace FanAdmin\enum\driver;


use FanAdmin\base\enum\BaseEnum;

/**
 * 通知类
 * Class Notice
 * @package FanAdmin\enum\driver
 */
class Notice extends BaseEnum
{
    /**
     * 加载通知
     * @param array $data
     * @return array|mixed
     */
    public function data(array $data)
    {
        $template_files = [];
        $system_path = $this->getDictPath() . "notice" . DIRECTORY_SEPARATOR . $data[ 'type' ] . ".php";
        if (is_file($system_path)) {
            $template_files[] = $system_path;
        }
        $addons = $this->getLocalAddons();
        foreach ($addons as $v) {
            $template_path = $this->getAddonDictPath($v) . "notice" . DIRECTORY_SEPARATOR . $data[ 'type' ] . ".php";
            if (is_file($template_path)) {
                $template_files[] = $template_path;
            }
        }
        $template_files_data = $this->loadFiles($template_files);

        $template_data_array = [];
        foreach ($template_files_data as $file_data) {
            $template_data_array = empty($template_data_array) ? $file_data : array_merge($template_data_array, $file_data);
        }
        return $template_data_array;
    }
}
