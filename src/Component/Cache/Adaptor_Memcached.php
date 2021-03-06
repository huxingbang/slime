<?php
namespace Slime\Component\Cache;

/**
 * Class Adaptor_Memcached
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
class Adaptor_Memcached implements IAdaptor
{
    /** @var \Memcached */
    public $Obj;

    /**
     * @param \Slime\Component\Memcached\PHPMemcached $Memcached
     */
    public function __construct($Memcached)
    {
        $this->Obj = $Memcached;
    }

    public function __call($sMethod, $aParam)
    {
        return empty($aParam) ? $this->Obj->$sMethod() : call_user_func_array(array($this->Obj, $sMethod), $aParam);
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return $this->Obj->get($sKey);
    }

    /**
     * @param string $sKey
     * @param mixed  $mValue
     * @param int    $iExpire
     *
     * @return bool
     */
    public function set($sKey, $mValue, $iExpire)
    {
        return $this->Obj->set($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->Obj->delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->Obj->flush();
    }
}