<?php
namespace SlimeFramework\Component\DataStruct;

class Tree_Node
{
    /**
     * @var Tree_Node[]
     */
    public $aChildren = array();

    public function __construct(
        Tree_Pool $Pool,
        $sKey,
        $aAttr = array(),
        Tree_Node $Parent = null
    ) {
        $this->sKey   = $sKey;
        $this->Pool   = $Pool;
        $this->Parent = $Parent;
        $this->aAttr  = $aAttr;
        $this->iLevel = $Parent === null ? 0 : $Parent->iLevel + 1;
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function getAttr($sKey)
    {
        return isset($this->aAttr[$sKey]) ? $this->aAttr[$sKey] : null;
    }

    public function getChildren()
    {
        return $this->aChildren;
    }

    public function bornChild($sKey, $aAttr = array())
    {
        $Node = new static($this->Pool, $sKey, $aAttr, $this);
        if (!$Node instanceof Tree_Node) {
            $this->Pool->Log->warning('Node[{node}] is not impl {class}', array('node' => $sKey, 'class' => __CLASS__));
            return null;
        }
        $this->Pool->addNode($Node);
        $this->aChildren[$Node->sKey] = $Node;
        return $Node;
    }

    public function deleteChildren()
    {
        foreach ($this->aChildren as $Child) {
            $this->Pool->deleteNode($Child->sKey);
        }
        unset($this->aChildren);
    }

    public function changeParent(Tree_Node $Parent = null)
    {
        if ($this->Parent !== null) {
            unset($this->Parent->aChildren[$this->sKey]);
        }
        if ($Parent === null) {
            $iLevel = 0;
        } else {
            $this->Parent                   = $Parent;
            $Parent->aChildren[$this->sKey] = $this;
            $iLevel                         = $Parent->iLevel + 1;
        }
        $this->updateLevel($this, $iLevel);
    }

    private function updateLevel(Tree_Node $Node, $iLevel)
    {
        if (isset($this->Pool->aaPoolLevel[$Node->iLevel][$Node->sKey])) {
            unset($this->Pool->aaPoolLevel[$Node->iLevel][$Node->sKey]);
        }
        $Node->iLevel                                        = $iLevel;
        $this->Pool->aaPoolLevel[$Node->iLevel][$Node->sKey] = $Node;
        if (!empty($Node->aChildren)) {
            foreach ($Node->aChildren as $Child) {
                $this->updateLevel($Child, $iLevel + 1);
            }
        }
    }

    /**
     * @param int $iUntilLevel top is 0, n>0:level n, n<0:pre n
     *
     * @return $this|null
     */
    public function findParent($iUntilLevel)
    {
        $Node = $this;
        if ($iUntilLevel < 0) {
            $iUntilLevel = 0 - $iUntilLevel;
            for ($i = 0; $i < $iUntilLevel; $i++) {
                $Node = $Node->Parent;
                if ($Node === null) {
                    break;
                }
            }
            return $Node;
        } else {
            while ($Node->Parent !== null) {
                if ($iUntilLevel === $Node->iLevel) {
                    break;
                }
                $Node = $Node->Parent;
            }

            return $Node->iLevel === $iUntilLevel ? $Node : null;
        }
    }

    public function __toString()
    {
        //only php 5.4 support JSON_UNESCAPED_UNICODE
        return sprintf('%s[%d] : %s', $this->sKey, $this->iLevel, json_encode($this->aAttr, JSON_UNESCAPED_UNICODE));
    }

    public function treeString()
    {
        $this->__treeString($this, 0, $sStr);
        return $sStr;
    }

    private function __treeString(Tree_Node $Node, $iIndent, &$sStr)
    {
        $sStr .= '|' . str_repeat('----', $iIndent) . '[' . get_class($Node) . ']' . (string)$Node . PHP_EOL;
        if (!empty($Node->aChildren)) {
            $iIndent++;
            foreach ($Node->aChildren as $Child) {
                $this->__treeString($Child, $iIndent, $sStr);
            }
        }
    }
}