<?php

namespace FanAdmin\enum\driver;


use FanAdmin\base\enum\BaseEnum;

/**
 * 获取图标类
 * Class Icon
 * @package FanAdmin\enum\driver
 */
class Icon extends BaseEnum
{
    /**
     * 获取图标
     * @param array $data
     * @return array
     * @throws \JsonException
     */
    public function data(array $data): array
    {
        $sys_path = dirname(app()->getRootPath()) . str_replace('/', DIRECTORY_SEPARATOR, '/admin/src/styles/icon');
        $file_arr = getFileMap($sys_path);
        $icon_arr = [];
        if (!empty($file_arr)) {
            foreach ($file_arr as $ck => $cv) {
                if (str_contains($cv, '.json')) {
                    $json_string = file_get_contents($ck);
                    $icon = json_decode($json_string, true, 512, JSON_THROW_ON_ERROR);
                    $icon_arr[] = $icon;
                }
            }
        }

        if (count($icon_arr) > 1) {
            $last_icon = array_pop($icon_arr); // 最后一个
            $first_icon = array_shift($icon_arr); // 第一个

            array_unshift($icon_arr, $last_icon); // 将系统图标放到第一位置
            $icon_arr[] = $first_icon; // 交换位置
        }

        return $icon_arr;
    }
}