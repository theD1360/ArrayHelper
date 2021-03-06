<?php namespace Utilities;

/**
 * File Name: Arr
 * Path: app/utils
 * Date Created: Nov 22, 2013
 * Original Author: Diego Alejos
 * Description: Arr provides an object oriented wrapper for arrays and adds some much needed functions
 *      adds :      append, insert, pluck and flatten
 *      interfaces: all other array_* functions
 *
 * Significant Changes (please indicate date and who made change):
 *
 */

class Arr implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected $data = [];
    public function __construct($array = [])
    {
        if(is_array($array))
            $this->build($array);

        if(is_string($array))
            $this->fromJSON($array);

    }
    public function build($array = [])
    {
        foreach ($array as $key => $val) {
            $this->set($key, $val);
        }
    }
    public function reset($val = [])
    {
        $this->clear();
        $this->build($val);
        return $this;
    }
    public function clear()
    {
        $this->data = [];
    }
    public function insert($val = [])
    {
        if (method_exists($val, "toArray")) {
            $val = $val->toArray();
        }
        if (is_array($val) || is_object($val)) {
            $val = new Arr($val);
        }
        array_push($this->data, $val);
        return $this;
    }
    public function append()
    {
        return call_user_func_array(array($this, "insert"), func_get_args());
    }
    public function set($key, $val)
    {
        if(is_a($val, "Utilities\Arr")){
             $this->data[$key] = $val;
             return $this;
        }

        if (method_exists($val, "toArray")) {
            $val = $val->toArray();
        }
        if (is_object($val) || is_array($val)) {
            $this->data[$key]   = new Arr($val);
        } else {
            $this->data[$key] = $val;
        }

        return $this;
    }
    public function has($key = null)
    {
        return isset($this->data[$key]);
    }
    // is empty will return if the specified key is empty if no key provided it will check itself.
    public function isEmpty($key = null)
    {
        if ($key === null) {
            return empty($this->data);
        }
        return empty($this->data[$key]);
    }
    public function get($key)
    {
        if ($this->has($key)) {
            return  $this->data[$key];
        }
    }
    public function __isset($index = null)
    {
        return $this->has($index);
    }
    public function __get($key)
    {
        return $this->get($key);
    }
    public function __set($key, $val)
    {
        $this->set($key, $val);
    }
    public function length()
    {
        return count($this->data);
    }
    public function count()
    {
        return $this->length;
    }    
    public function at($index = null)
    {
        return $this->data[$index];
    }
    public function slice($offset, $length = null, $preserve_keys = false)
    {
        $slice = array_slice($this->data, $offset, $length, $preserve_keys);
        return new Arr($slice);
    }
    public function remove($index = null)
    {
        unset($this->data[$index]);
    }
    public function each($callback = null)
    {
        array_walk($this->data, $callback);
    }
    // Warning: filter will not return the object it will only return a copy of the object.
    public function filter($callback = null)
    {
        $test = array_filter($this->data, $callback);
        $clone = clone $this;
        return $clone->reset($test);
    }
    public function where ($key, $operator = "=", $val = true)
    {
        return $this->filter(function ($item) use ($key, $operator, $val) {
            $cell = $item;
            if (isset($cell->{$key})) {
                $cell = $cell->{$key};
            }
            switch ($operator) {
                case "=":
                    return ($cell == $val)?true:false;
                case "==":
                    return ($cell == $val)?true:false;
                case ">":
                    return ($cell > $val)?true:false;
                case ">=":
                    return ($cell >= $val)?true:false;
                case "<":
                    return ($cell < $val)?true:false;
                case "<=":
                    return ($cell <= $val)?true:false;
                case "!=":
                    return ($cell != $val)?true:false;
                default:
                    return null;
            }
        });
    }
    // checks to see if the arguments given exist using intersect
    // basically a fancy in_array that takes multiple vals 
    public function contains()
    {
        $args = func_get_args();
        return (bool) $this->intersect($args);
    }
    public function intersect($args)
    {
        $data = $this->toArray();
        return array_intersect($data, $args);
    }    
    public function diff($args)
    {
        $data = $this->toArray();
        return array_diff($data, $args);
    }    
    public function pop()
    {
        return array_pop($this->data);
    }
    public function shift()
    {
        return array_shift($this->data);
    }
    public function unshift($val)
    {
        return array_unshift($this->data, $val);
    }
    public function pluck()
    {
        $keys = func_get_args();
        return $this->map(function ($item) use ($keys) {
            $arr = [];
            foreach ($keys as $key) {
                if (isset($item->{$key})) {
                    $arr[$key] = $item->{$key};
                }
            }
            return $arr;
        });
    }
    public function flatten($key)
    {
        return $this->map(function ($item) use ($key) {
            if ($item->{$key}) {
                return $item->{$key};
            }
        });
    }
    public function map($closure = null)
    {
        if (is_callable($closure)) {
            return array_map($closure, $this->data);
        }
    }
    public function sum()
    {
        return array_sum($this->data);
    }
    public function avg()
    {
        return $this->sum()/$this->length();
    }
    public function next()
    {
        return next($this->data);
    }
    public function prev()
    {
        return prev($this->data);
    }
    public function current()
    {
        return current($this->data);
    }
    public function rewind()
    {
        return reset($this->data);
    }
    public function first()
    {
        return $this->at(0);
    }
    public function last()
    {
        return $this->at($this->length()-1);
    }
    public function end()
    {
        return end($this->data);
    }
    // Stolen from http://www.php.net/manual/en/class.arrayaccess.php
    // allows this object array access
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
    // Takes this objects toArray value and merges it with the given
    // array then rebuilds the data array. Returns itself
    public function merge($arr = [])
    {
        $newData = array_merge($this->toArray(), $arr);
        return $this->reset($newData);
    }

    // Creates a copy of the current object
    public function copy()
    {
        return new Arr($this->toArray());
    }
    public function toArray()
    {
        $return = [];
        foreach ($this->data as $key => $item) {
            $return[$key] = $item;
            if (method_exists($item, "toArray")) {
                $return[$key] = $item->toArray();
            }
        }
        return (array) $return;
    }
    public function toObject()
    {
        return (object) $this->toArray();
    }
    
    public function fromJSON($json = "[]")
    {
        $this->build(json_decode($json, true));
    }

    public function toJSON()
    {
        $var = $this->toArray();
        return json_encode($var);
    }
    public function __toString()
    {
        return $this->toJSON();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

}
