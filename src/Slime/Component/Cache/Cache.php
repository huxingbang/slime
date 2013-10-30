<?php
namespace Slime\Component\Cache;

final class Cache
{
    /** @var IAdaptor */
    private $Obj;

    /**
     * @param string $sAdaptor
     *
     * @throws \Exception
     */
    public function __construct($sAdaptor)
    {
        if ($sAdaptor[0] === '@') {
            $sAdaptor = __NAMESPACE__ . '\\Adaptor_' . substr($sAdaptor, 1);
        }
        $Ref = new \ReflectionClass($sAdaptor);
        $this->Obj = $Ref->newInstanceArgs(array_slice(func_get_args(), 1));
        if (!$this->Obj instanceof IAdaptor) {
            throw new \Exception("{$sAdaptor} must impl Slime\\Component\\Cache\\IAdaptor");
        }
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

    /**
     * @return IAdaptor
     */
    public function getAdaptor()
    {
        return $this->Obj;
    }
}