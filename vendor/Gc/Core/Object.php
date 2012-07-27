<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 *(at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Core
 * @author      Pierre Rambaud(GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Core;

use Gc\Exception;

abstract class Object
{
    /**
     * Object attributes
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $_underscoreCache = array();

    /**
     * Set Id
     * @return \Gc\Core\Object
     */
    protected function setId($id = NULL)
    {
        return $this->setData('id', $id);
    }

    /**
     * Initialize constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
    }

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     * @return \Gc\Core\Object
     */
    public function addData(array $array)
    {
        foreach($array as $index => $value)
        {
            $this->setData($index, $value);
        }

        return $this;
    }

    /**
     * Overwrite data in the object.
     *
     * $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return \Gc\Core\Object
     */
    public function setData($key, $value = NULL)
    {
        if(is_array($key))
        {
            $this->_data = $key;
        }
        else
        {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Unset data from the object.
     *
     * $key can be a string only. Array will be ignored.
     *
     * @param string $key
     * @return \Gc\Core\Object
     */
    public function unsetData($key = NULL)
    {
        if(is_null($key))
        {
            $this->_data = array();
        }
        else
        {
            unset($this->_data[$key]);
        }

        return $this;
    }

    /**
     * Retrieves data from the object
     *
     * If $key is empty will return all the data as an array
     * Otherwise it will return value of the attribute specified by $key
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member.
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key='', $index = NULL)
    {
        if(''===$key)
        {
            return $this->_data;
        }

        $default = NULL;

        // accept a/b/c as ['a']['b']['c']
        if(strpos($key,'/'))
        {
            $keyArr = explode('/', $key);
            $data = $this->_data;
            foreach($keyArr as $i => $k)
            {
                if($k==='')
                {
                    return $default;
                }

                if(is_array($data))
                {
                    if(!isset($data[$k]))
                    {
                        return $default;
                    }

                    $data = $data[$k];
                }
                elseif($data instanceof Object)
                {
                    $data = $data->getData($k);
                }
                else
                {
                    return $default;
                }
            }

            return $data;
        }

        // legacy functionality for $index
        if(isset($this->_data[$key]))
        {
            if(is_null($index))
            {
                return $this->_data[$key];
            }

            $value = $this->_data[$key];
            if(is_array($value))
            {
                if(isset($value[$index]))
                {
                    return $value[$index];
                }

                return NULL;
            }
            elseif(is_string($value))
            {
                $array = explode("\n", $value);
                return(isset($array[$index]) &&(!empty($array[$index]) || strlen($array[$index]) > 0)) ? $array[$index] : NULL;
            }
            elseif($value instanceof Object)
            {
                return $value->getData($index);
            }

            return $default;
        }

        return $default;
    }

    /**
     * Get value from _data array without parse key
     *
     * @param   string $key
     * @return  mixed
     */
    protected function _getData($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : NULL;
    }

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return boolean
     */
    public function hasData($key='')
    {
        if(empty($key) || !is_string($key))
        {
            return !empty($this->_data);
        }

        return array_key_exists($key, $this->_data);
    }

    /**
     * Convert object attributes to array
     *
     * @param  array $array array of required attributes
     * @return array
     */
    public function __toArray(array $array = array())
    {
        if(empty($array))
        {
            return $this->_data;
        }

        $arrRes = array();
        foreach($array as $attribute)
        {
            if(isset($this->_data[$attribute]))
            {
                $arrRes[$attribute] = $this->_data[$attribute];
            }
            else
            {
                $arrRes[$attribute] = NULL;
            }
        }

        return $arrRes;
    }

    /**
     * Public wrapper for __toArray
     *
     * @param array $array
     * @return array
     */
    public function toArray(array $array = array())
    {
        return $this->__toArray($array);
    }

    /**
     * Convert object attributes to XML
     *
     * @param  array $array array of required attributes
     * @param string $rootName name of the root element
     * @return string
     */
    protected function __toXml(array $array = array(), $rootName = 'item', $addOpenTag=FALSE, $addCdata=TRUE)
    {
        $xml = '';
        if($addOpenTag)
        {
            $xml.= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        }

        if(!empty($rootName))
        {
            $xml.= '<'.$rootName.'>'."\n";
        }

        $xmlModel = new SimpleXMLElement('<node></node>');
        $arrData = $this->toArray($array);
        foreach($arrData as $fieldName => $fieldValue)
        {
            if($addCdata === TRUE)
            {
                $fieldValue = "<![CDATA[$fieldValue]]>";
            }
            else
            {
                $fieldValue = $xmlModel->xmlentities($fieldValue);
            }

            $xml.= "<$fieldName>$fieldValue</$fieldName>"."\n";
        }

        if(!empty($rootName))
        {

            $xml.= '</'.$rootName.'>'."\n";
        }

        return $xml;
    }

    /**
     * Public wrapper for __toXml
     *
     * @param array $array
     * @param string $rootName
     * @return string
     */
    public function toXml(array $array = array(), $rootName = 'item', $addOpenTag=FALSE, $addCdata=TRUE)
    {
        return $this->__toXml($array, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * Convert object attributes to JSON
     *
     * @param  array $array array of required attributes
     * @return string
     */
    protected function __toJson(array $array = array())
    {
        $arrData = $this->toArray($array);
        $json = Zend_Json::encode($arrData);
        return $json;
    }

    /**
     * Public wrapper for __toJson
     *
     * @param array $array
     * @return string
     */
    public function toJson(array $array = array())
    {
        return $this->__toJson($array);
    }

    /**
     * Public wrapper for __toString
     *
     * Will use $format as an template and substitute {{key}} for attributes
     *
     * @param string $format
     * @return string
     */
    public function toString($format='')
    {
        if(empty($format))
        {
            $str = implode(', ', $this->getData());
        }
        else
        {
            preg_match_all('/\{\{([a-z0-9_]+)\}\}/is', $format, $matches);
            foreach($matches[1] as $var)
            {
                $format = str_replace('{{'.$var.'}}', $this->getData($var), $format);
            }

            $str = $format;
        }

        return $str;
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  Zend_Db_Table
     */
    public function __call($method, $args)
    {
        switch(substr($method, 0, 3))
        {
            case 'get' :
                $key = $this->_underscore(substr($method,3));
                $data = $this->getData($key, isset($args[0]) ? $args[0] : NULL);
                return $data;

            case 'set' :
                $key = $this->_underscore(substr($method,3));
                $result = $this->setData($key, isset($args[0]) ? $args[0] : NULL);
                return $result;

            case 'uns' :
                $key = $this->_underscore(substr($method,3));
                $result = $this->unsetData($key);
                return $result;

            case 'has' :
                $key = $this->_underscore(substr($method,3));
                return isset($this->_data[$key]);
        }

        throw new Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
    }

    /**
     * Attribute getter(deprecated)
     *
     * @param string $var
     * @return mixed
     */

    public function __get($var)
    {
        $var = $this->_underscore($var);
        return $this->getData($var);
    }

    /**
     * Attribute setter(deprecated)
     *
     * @param string $var
     * @param mixed $value
     */
    public function __set($var, $value)
    {
        $var = $this->_underscore($var);
        $this->setData($var, $value);
    }

    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name)
    {
        if(isset(self::$_underscoreCache[$name]))
        {
            return self::$_underscoreCache[$name];
        }

        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));

        self::$_underscoreCache[$name] = $result;
        return $result;
    }

    protected function _camelize($name)
    {
        return uc_words($name, '');
    }

    /**
     * Get object loaded data(original data)
     *
     * @param string $key
     * @return mixed
     */
    public function getOrigData($key = NULL)
    {
        if(is_null($key))
        {
            return $this->_origData;
        }

        return isset($this->_origData[$key]) ? $this->_origData[$key] : NULL;
    }

    /**
     * Initialize object original data
     *
     * @param string $key
     * @param mixed $data
     * @return \Gc\Core\Object
     */
    public function setOrigData($key = NULL, $data = NULL)
    {
        if(is_null($key))
        {
            $this->_origData = $this->_data;
        }
        else
        {
            $this->_origData[$key] = $data;
        }

        return $this;
    }

    /**
     * Implementation of ArrayAccess::offsetSet()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    /**
     * Implementation of ArrayAccess::offsetExists()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     * Implementation of ArrayAccess::offsetUnset()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    /**
     * Implementation of ArrayAccess::offsetGet()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : NULL;
    }
}
