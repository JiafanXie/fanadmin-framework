<?php

namespace FanAdmin\enum\driver;


use FanAdmin\base\enum\BaseEnum;

/**
 * 获取所有事件类
 * Class Event
 * @package FanAdmin\enum\driver
 */
class Event extends BaseEnum
{
    /**
     * 加载事件
     * @param array $data
     * @return array|mixed
     */
    public function data(array $data)
    {
        $addons = $this->getLocalAddons();
        $event_files = [];

        foreach ($addons as $v) {
            $event_path = $this->getAddonAppPath($v) . "event.php";
            if (is_file($event_path)) {
                $event_files[] = $event_path;
            }
        }
        $files_data = $this->loadFiles($event_files);

        $files_data[1] = $data;

        $events = [];
        foreach ($files_data as $file_data) {
            $events = empty($events) ? $file_data : array_merge2($events, $file_data);
        }
        return $events;
    }
}
