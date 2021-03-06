<?php
namespace Slime\Component\Memcached;

use Slime\Bundle\Framework\Context;
use Slime\Component\Context\Event;
use Slime\Component\Log\Logger;

/**
 * Class Event_Register
 *
 * @package Slime\Component\Memcached
 * @author  smallslime@gmail.com
 */
class Event_Register
{
    const E_ALL_BEFORE = 'Slime.Component.Memcached.PHPMemcached.__All__:before';
    const E_ALL_AFTER  = 'Slime.Component.Memcached.PHPMemcached.__All__:after';
    const GV_TIME_PAST = 'Slime.Component.Memcached.EventRegister:time';

    public static function register_ALL()
    {
        Event::regEvent(
            self::E_ALL_BEFORE,
            function ($MC, $sMethodName, $aArgv) {
                Context::getInst()->Arr[self::GV_TIME_PAST] = microtime(true);
            }
        );

        Event::regEvent(
            self::E_ALL_AFTER,
            function ($mResult, $MC, $sMethodName, $aArgv) {
                $Log = Context::getInst()->Log;
                if ($Log->needLog(Logger::LEVEL_INFO)) {
                    $Log->info(
                        '[MC] : {cost} ; {cmd}',
                        array(
                            'cost' => microtime(true) - Context::getInst()->Arr[self::GV_TIME_PAST],
                            'cmd'  => $sMethodName,
                        )
                    );
                }
            }
        );
    }
}