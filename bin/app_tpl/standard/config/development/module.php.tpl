<?php
use Slime\Component\Log\Logger;

return array(
    array(
        'module'   => 'Log',
        'class'    => 'Slime\\Component\\Log\\Logger',
        'params'   => array(
            array(
                '@File' => array(
                    function () {
                        return '/tmp/{{{APP_NAME}}}_cli_' . date('Y-m-d') . '.log';
                    },
                    1
                )
            ),
            Logger::LEVEL_ALL ^ Logger::LEVEL_DEBUG
        ),
        'run_mode' => 'cli'
    ),
    array(
        'module'   => 'Log',
        'class'    => 'Slime\\Component\\Log\\Logger',
        'params'   => array(
            array(
                '@WebPage' => array(),
                '@File' => array(
                    function () {
                        return sprintf('/tmp/{{{APP_NAME}}}_%s.log', date('Y-m-d'));
                    }
                )
            ),
            Logger::LEVEL_ALL ^ Logger::LEVEL_DEBUG
        ),
        'run_mode' => 'http'
    ),
    array(
        'module' => 'View',
        'class'  => 'Slime\\Component\\View\\Adaptor_PHP',
        'params' => array(DIR_VIEW)
    ),
    array(
        'module' => 'ModelFactory',
        'class'  => 'Slime\\Component\\RDS\\Model\\Factory',
        'params' => array(
            '@database',
            '@model',
            '{{{NS}}}\\Model',
            '{{{NS}}}\\System\\Model\\Model_Base',
            \Slime\Component\RDS\AopPDO::$aAopPreExecCost
        )
    ),
    array(
        'module' => 'Redis',
        'class'  => 'Slime\\Component\\Redis\\Redis',
        'params' => array('@redis'),
        'packer' => \Slime\Component\Redis\AopRedis::$aAopAllCMDCost
    ),
    array(
        'module' => 'Lock',
        'class'  => 'Slime\\Component\\Lock\\Adaptor_Redis',
        'params' => array(':Redis')
    ),
);