<?php
namespace Slime\Component\Log;

/**
 * Class Writer_ECHO
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 */
class Writer_ECHO implements IWriter
{
    public $sFormat = '[:sGuid][:iLevel] : :sTime , :sMessage';

    public function acceptData($aRow)
    {
        $sStr = str_replace(
                array(':sTime', ':iLevel', ':sMessage', ':sGuid'),
                array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
                $this->sFormat
            ) . PHP_EOL;

        echo $sStr;
    }
}