<?php

namespace FanAdmin\base\queue;


use FanAdmin\exception\CommonException;
use FanAdmin\job\Dispatch;
use think\facade\Log;
use think\queue\Job;

/**
 * 队列基类
 * Class BaseQueue
 * @package FanAdmin\base\queue
 */
abstract class BaseQueue extends Dispatch {

    /**
     * 消费任务
     * @param $params
     */
    public function fire($params): void {
        // 任务名
        $method = $params['method'] ?? 'runQueue';
        // 数据
        $data   = $params['data'] ?? [];
        $this->runQueue($method, $data);
    }


    /**
     * 执行任务
     * @param string $method
     * @param array $data
     * @return bool
     */
    protected function runQueue (string $method, array $data) {
        try {
            $method = method_exists($this, $method) ? $method : 'handle';
            if (!method_exists($this, $method)) {
                throw new CommonException('Job "'.static::class.'" not found！');
            }
            $this->{$method}(...$data);
            return true;
        } catch (\Throwable $e) {
            Log::write('队列错误:'.static::class.$method.'_'.'_'.$e->getMessage().'_'.$e->getFile().'_'.$e->getLine());
            throw new CommonException('Job "'.static::class.'" has error！');
        }
    }
}
