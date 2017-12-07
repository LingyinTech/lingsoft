<?php
/**
 * Created by PhpStorm.
 * User: huanjin
 * Date: 2017/5/1
 * Time: 20:06
 */

namespace lingyin\profile;


class LogSync
{
    protected $_message;
    protected $_begin;

    protected $_config = [];

    protected $_adapter = 'Redis';
    protected $_driver = null;

    protected $_cmd;
    protected $_args;
    protected $_filePointer = 0;

    protected $_upSyncSecond = 300;
    protected $_upSyncMemory = 2 * 1024 * 1024;

    protected $_debug = false;

    /**
     * 初始化
     *
     * @param array $cfg
     * @return $this
     */
    public function handler(array $cfg = [])
    {
        $key = ['listen::', 'status::'];
        $opt = getopt('', $key);
        $this->_cmd = 'listen';
        foreach ($key as $v) {
            $v = str_replace('::', '', $v);
            if (isset($opt[$v])) {
                $this->_cmd = $v;
                $this->_args = $opt[$v];
                break;
            }
        }

        isset($cfg['up_sync_second']) && $this->_upSyncSecond = $cfg['up_sync_second'];
        isset($cfg['up_sync_memory']) && $this->_upSyncMemory = $cfg['up_sync_memory'];
        !empty($cfg['debug']) && $this->_debug = true;
        unset($cfg['up_sync_second'], $cfg['up_sync_memory'], $cfg['debug']);

        if (isset($cfg['adapter']) && in_array($cfg['adapter'], ['Http', 'Redis', 'ActiveMQ'])) {
            $this->_adapter = $cfg['adapter'];
            unset($cfg['adapter']);
        }

        isset($cfg['driver']) && $this->_config = $cfg['driver'];

        $this->initDriver();
        return $this;
    }

    public function run()
    {
        $cmd = $this->_cmd;
        $this->$cmd($this->_args);
    }

    public function listen()
    {
        $this->_begin = time();
        if ($this->_args) {
            //读文件方式同步
            while (true) {
                if (!file_exists($this->_args)) {
                    sleep(10);
                    continue;
                }

                $handle = fopen($this->_args, 'r');
                if ($handle) {
                    if (fseek($handle, $this->_filePointer) === -1) {
                        rewind($handle);
                    }
                    while ($line = trim(fgets($handle))) {
                        $this->_message[] = $line;
                    }
                    $this->_filePointer = ftell($handle);
                    $this->upAgent();
                    fclose($handle);
                    sleep(1);
                } else {
                    $this->_filePointer = 0;
                }
            }
        } else {

        }
    }

    private function initDriver()
    {
        $className = "lingyin\\profile\\sync\\{$this->_adapter}";
        $this->_driver = new $className($this->_config);
    }

    /**
     * 日志同步逻辑
     */
    private function upAgent()
    {
        if (empty($this->_message)) {
            $this->debug('没有信息要同步');
            return;
        }

        if ((memory_get_usage() > $this->_upSyncMemory) or ($this->_begin + $this->_upSyncSecond < time())) {
            $this->debug('开始同步信息:');
            if ($result = $this->_driver->syncMessage($this->_message)) {
                $this->debug('同步成功:');
                $this->_begin = time();
                $this->_message = [];
            } else {
                $this->debug('同步失败:');
            }
        }
    }

    private function debug($info)
    {
        if (!$this->_debug) {
            return;
        }
        echo date('Y-m-d H:i:s') . ' -- ' . $info . PHP_EOL;
    }
}