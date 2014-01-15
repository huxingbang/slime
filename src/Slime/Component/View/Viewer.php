<?php
namespace Slime\Component\View;

use Slime\Component\Helper\Sugar;

/**
 * Class View
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
final class Viewer
{
    /**
     * @param string $sAdaptor
     *
     * @return IAdaptor
     * @throws \Exception
     */
    public static function factory($sAdaptor)
    {
        return Sugar::createObjAdaptor(__CLASS__, func_get_args());
    }
}
