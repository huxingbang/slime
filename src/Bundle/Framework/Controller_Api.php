<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Http;
use Slime\Component\Helper\Arr2XML;
use Slime\Component\View;
use Slime\Component\Log\Writer_WebPage;

/**
 * Class Controller_API
 * Slime 内置Http控制器基类
 * 建议 Autoload View Module
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_Api extends Controller_ABS
{
    protected $sDefaultRender = '_renderJSON';
    protected $sJSCBParam = 'cb';
    protected $sXmlTPL = null;
    protected $sJsonTPL = null;
    protected $sJsonPTPL = null;

    protected $aData = array();

    public function __construct(array $aParam = array())
    {
        parent::__construct($aParam);
        foreach ($this->Log->aWriter as $Writer) {
            if ($Writer instanceof Writer_WebPage) {
                $Writer->setDisable();
            }
        }

        $this->HttpRequest  = $this->Context->HttpRequest;
        $this->HttpResponse = $this->Context->HttpResponse;
    }

    protected function success(array $aData = array())
    {
        $this->aData['data']    = $aData;
        $this->aData['errCode'] = 0;
        $this->aData['errMsg']  = '';
    }

    protected function fail($sErr, $iErr = 1)
    {
        $this->aData['data']    = array();
        $this->aData['errCode'] = $iErr;
        $this->aData['errMsg']  = $sErr;
    }

    public function __after__()
    {
        if (empty($this->aParam['__ext__'])) {
            $sMethodName = $this->sDefaultRender;
        } else {
            $sMethodName = '_render' . strtoupper($this->aParam['__ext__']);
            if ($this->sDefaultRender !== null && !method_exists($this, $sMethodName)) {
                $sMethodName = $this->sDefaultRender;
            }
        }

        $this->$sMethodName();
    }

    protected function _renderXML()
    {
        $this->HttpResponse->setHeader('Content-Type', 'text/xml', false);
        $this->HttpResponse->setContent(
            $this->sXmlTPL === null ?
                Arr2XML::factory()->Array2XML($this->aData) :
                $this->Context->View->assignMulti($this->aData)->setTpl($this->sXmlTPL)->renderAsResult()
        );
    }

    protected function _renderJSON()
    {
        $this->HttpResponse->setHeader('Content-Type', 'text/javascript', false);
        $this->HttpResponse->setContent(
            $this->sJsonTPL === null ?
                json_encode($this->aData) :
                $this->Context->View->assignMulti($this->aData)->setTpl($this->sJsonTPL)->renderAsResult()
        );
    }

    protected function _renderJSONP()
    {
        $sCB = $this->HttpRequest->getG($this->sJSCBParam);
        if ($sCB === null) {
            $sCB = 'cb';
        }
        $this->HttpResponse->setHeader('Content-Type', 'text/javascript', false);
        $this->HttpResponse->setContent(
            $this->sJsonPTPL === null ?
                $sCB . '(' . json_encode($this->aData) . ')' :
                $this->Context->View->assignMulti($this->aData)->setTpl($this->sJsonPTPL)->renderAsResult()
        );
    }

    protected function _renderJS()
    {
        $this->_renderJSONP();
    }
}