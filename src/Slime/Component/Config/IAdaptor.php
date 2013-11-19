<?php
namespace Slime\Component\Config;

/**
 * Interface IAdaptor
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
interface IAdaptor
{
    /**
     * @param string $sKey
     * @param mixed  $mDefaultValue
     * @param bool   $bForce
     *
     * @return mixed
     */
    public function get($sKey, $mDefaultValue = null, $bForce = false);
}