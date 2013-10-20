<?php
namespace Slime\Component\RDS\Model;

/**
 * Class Item
 *
 * @package Slime\Component\RDS
 * @property-read array $aData
 * @property-read array $aOldData
 */
class Item implements \ArrayAccess
{
    private $aRelation = array();
    private $Log;

    /** @var Model */
    public $Model;

    /** @var Group|null */
    public $Group;

    /** @var array */
    public $aData;

    /** @var array */
    public $aOldData = array();

    public function __construct(array $aData, Model $Model, $Group = null)
    {
        $this->aData = $aData;
        $this->Model = $Model;
        $this->Group = $Group;
        $this->Log   = $Model->Logger;
    }

    public function __get($sKey)
    {
        return isset($this->aData[$sKey]) ? $this->aData[$sKey] : null;
    }

    public function __set($sKey, $mValue)
    {
        $this->_set($sKey, $mValue);
    }

    public function set($mKeyOrKVMap, $mValue = null)
    {
        if (!is_array($mKeyOrKVMap)) {
            $this->_set($mKeyOrKVMap, $mValue);
        } else {
            foreach ($mKeyOrKVMap as $sKey => $mValue) {
                $this->_set($sKey, $mValue);
            }
        }
        return $this;
    }

    private function _set($sKey, $mValue)
    {
        if (isset($this->aData[$sKey]) && $this->aData[$sKey] == $mValue) {
            return;
        }
        $this->aOldData[$sKey] = isset($this->aData[$sKey]) ? $this->aData[$sKey] : null;
        $this->aData[$sKey]    = $mValue;
    }

    /**
     * @param string $sModelName
     * @param array  $mValue
     *
     * @return $this|null
     */
    public function __call($sModelName, $mValue = array())
    {
        if (!isset($this->Model->aRelConf[$sModelName])) {
            $this->Model->Logger->error('can not find relation for [{model}]', array('model' => $sModelName));
            exit(1);
        }

        if (!isset($this->aRelation[$sModelName])) {
            $sMethod = $this->Model->aRelConf[$sModelName];
            $sRelation = strtolower($this->Model->aRelConf[$sModelName]);
            if ($sRelation === 'hasone' || $sRelation == 'belongsto') {
                $this->aRelation[$sModelName] = $this->Group===null ?
                    $this->$sMethod($sModelName) :
                    $this->Group->relation($sModelName, $this);
            } else {
                $this->aRelation[$sModelName] = $this->$sMethod($sModelName, (isset($mValue[0]) ? $mValue[0] : null));
            }
        }
        return $this->aRelation[$sModelName];
    }

    public function add()
    {
        $M   = $this->Model;
        $iID = $M->CURD->insertSmarty($M->sTable, $this->aData);
        if ($iID === null) {
            $bRS = false;
        } else {
            $this->aData[$M->sPKName] = $iID;
            $bRS                      = true;
        }
        return $bRS;
    }

    public function delete()
    {
        $M = $this->Model;
        return $M->CURD->deleteSmarty($M->sTable, array($M->sPKName => $this->aData[$M->sPKName]));
    }

    public function update()
    {
        $M   = $this->Model;
        $bRS = $M->CURD->updateSmarty(
            $M->sTable,
            array_intersect_key($this->aData, $this->aOldData),
            array($M->sPKName => $this->aData[$M->sPKName])
        );
        if ($bRS) {
            $this->aOldData = array();
        }
        return $bRS;
    }

    public function deleteSafe(&$iErr, &$sErr)
    {
        $bRS = false;
        $M   = $this->Model;
        if (!empty($M->aRelConf['relation'])) {
            foreach ($M->aRelConf['relation'] as $sModelName => $sMethod) {
                $sMethod = strtolower($sMethod);
                if ($sMethod === 'hasone' || $sMethod === 'hasmany' || $sMethod === 'hasmanythrough') {
                    //@todo
                }
            }
        }
        if ($bRS) {
            $bRS = $this->delete();
        }
        return $bRS;
    }

    /**
     * @param string $sModelName
     *
     * @return Item|null
     */
    public function hasOne($sModelName)
    {
        $M     = $this->Model;
        $Model = $M->Factory->get($sModelName);
        return $Model->find(array($M->sFKName => $this->aData[$M->sPKName]));
    }

    /**
     * @param string $sModelName
     *
     * @return Item|null
     */
    public function belongsTo($sModelName)
    {
        $Model = $this->Model->Factory->get($sModelName);
        return $Model->find(array($Model->sPKName => $this->aData[$Model->sFKName]));
    }

    /**
     * @param string     $sModel
     * @param array|null $aParam
     *
     * @return Group
     */
    public function hasMany($sModel, $aParam = null)
    {
        $M     = $this->Model;
        $Model = $M->Factory->get($sModel);
        return $Model->findMulti(
            (empty($aParam) ?
                array($M->sFKName => $this->aData[$Model->sPKName]) :
                array_merge(array($M->sFKName => $this->aData[$Model->sPKName]), $aParam)
            )
        );
    }

    /**
     * @param string      $sModelTarget
     * @param array|null  $aParam
     *
     * @return Group
     */
    public function hasManyThrough($sModelTarget, $aParam = null)
    {
        $ModelTarget = $this->Model->Factory->get($sModelTarget);
        $ModelOrg    = $this->Model;
        //if ($sModelRelated === null) {
        $sRelatedTableName = 'rel__' . (strcmp($ModelOrg->sTable, $ModelTarget->sTable) > 0 ?
                $ModelTarget->sTable . '__' . $ModelOrg->sTable :
                $ModelOrg->sTable . '__' . $ModelTarget->sTable);
        $CURD              = $ModelOrg->CURD;
        //} else {
        //    $ModelRelated      = $this->Model->Factory->get($sModelRelated);
        //    $CURD              = $ModelRelated->CURD;
        //    $sRelatedTableName = $ModelRelated->sTable;
        //}


        $aIDS = $CURD->querySmarty(
            $sRelatedTableName,
            (empty($aParam) ?
                array($ModelOrg->sFKName => $this->aData[$ModelOrg->sPKName]) :
                array_merge(array($ModelOrg->sFKName => $this->aData[$ModelOrg->sPKName]), $aParam)
            ),
            '',
            $ModelTarget->sFKName,
            false,
            \PDO::FETCH_COLUMN,
            0
        );

        return empty($aIDS) ? null : $ModelTarget->findMulti(array($ModelTarget->sPKName . ' IN' => $aIDS));
    }

    public function toArray()
    {
        return $this->aData;
    }

    public function __toString()
    {
        return var_export($this->aData, true);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     *       The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->aData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->aData[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->aData[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->aData[$offset]);
    }
}