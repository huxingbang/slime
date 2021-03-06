<?php
namespace Slime\Component\Redis;

use Slime\Component\Context\Event;

/**
 * Class Redis
 *
 * @package Slime\Component\Redis
 * @author  smallslime@gmail.com
 */
class Redis
{
    /**
     * @var \Redis
     */
    private $Redis;

    /**
     * @var array
     */
    protected $aConfig;

    /**
     * @param array $aConfig
     */
    public function __construct(array $aConfig)
    {
        $this->aConfig = $aConfig;
    }

    public function __call($sMethod, $aArgs)
    {
        Event::occurEvent(Event_Register::E_ALL_BEFORE, $this, $sMethod, $aArgs);
        $mRS = call_user_func_array(array($this->getInstance(), $sMethod), $aArgs);
        Event::occurEvent(Event_Register::E_ALL_AFTER, $mRS, $this, $sMethod, $aArgs);
        return $mRS;
    }

    public function getInstance()
    {
        if (!$this->Redis) {
            if ($this->aConfig['type'] == 'single') {
                $this->Redis = new \Redis();
                call_user_func_array(
                    array($this->Redis, !empty($this->aConfig['pconnect']) ? 'pconnect' : 'connect'),
                    $this->aConfig['server']
                );
            } else {
                $Ref         = new \ReflectionClass('\RedisArray');
                $this->Redis = $Ref->newInstance($this->aConfig['servers']);
            }
            if (!empty($this->aConfig['options'])) {
                foreach ($this->aConfig['options'] as $mK => $mV) {
                    $this->Redis->setOption($mK, $mV);
                }
            }
            if (!empty($this->aConfig['db'])) {
                $this->Redis->select($this->aConfig['db']);
            }
        }
        return $this->Redis;
    }
}
