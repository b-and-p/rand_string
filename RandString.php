<?php

/**
 * RandString Class for generating random strings
 * @package RandString
 * @author B-and-P
 * @link https://github.com/b-and-p/ GitHub repo
 * @date 2016-03-10
 * PHP 5.4
 */

namespace b_and_p\RandStrNS;

/**
 * Random String Generator Class
 * 
 * Utility class to generate random strings.
 * 
 * @example 
 * Code example below:
 *        
 *        include ('RandString.php');
 *        $oRS = new RandStrNS\RandString();
 *        
 *        $oRS->part_length = 2; // INT
 *        $oRS->parts = 2;   // INT
 *        $oRS->delim = '-';   // STR
 *        $oRS->prefix = '';   // STR
 *        $oRS->suffix = '';   // STR
 *        $oRS->count = 1;   // STR
 *        $oRS->unique = false; //BOOL
 *        $oRS->flags = 3; // INT Bitmask (000011)
 * 
 *        $result = $oRS->generate();
 *        var_dump_result();
 * 
 * **Will Output:**
 * 
 * Array('gR-Fc')
 * 
 * @version 1.0
 * @since 2016-03-15
 * @author andrei_roslovtsev <andrei@bytes-and-pixels.com>
 */
class RandString {

    /**
     * Length of a string part/segment [default value: 3]
     * @var int 
     */
    private $part_length;

    /**
     * Delimiter to use for concatenating parts/segments [default value: '-']
     * @var string
     */
    private $delim;

    /**
     * Number of parts/segments [default value: 2]
     * @var int
     */
    private $parts;

    /**
     * Number of strings to generate [default value: 1]
     * @var int
     */
    private $count;

    /**
     * Bitmask for character sets to include in random string generation [default value: 2 (000010)]
     * @var int 
     */
    private $flags;

    /**
     * Generate unique values [default value: false]
     * @var bool 
     */
    private $unique;

    /**
     * Prefix for random string  [default value: empty]
     * @var str
     */
    private $prefix;

    /**
     * Suffix for random string [default value: empty]
     * @var str 
     */
    private $suffix;

    /**
     * Store of generated strings (needed for uniqueness check) [default value: empty]
     * @var array 
     */
    private $history;

    /**
     * Store of found duplicates during generation cycle (for stats and debugging)  [default value: empty]
     * @var array
     */
    private $duplicates;

    /**
     * Working character set [default value: alpha lower case + alpha upper case]
     * @var string 
     */
    private $working_charset;
//        const CHARSET_ALPHA_LOWER = 1;
//        const CHARSET_ALPHA_UPPER = 2;
//        const CHARSET_NUMERIC = 4;
//        const CHARSET_SPECIAL = 8;

    /**
     * Character sets serialized array of subsets. Constant
     * @var array
     */
    private $charsets;

    /**
     * Map of settable class properties. Constant
     * @var array
     */
    private $settable;

    /**
     * Map of gettable class properties. Constant
     * @var array
     */
    private $gettable;

    /**
     * Error message
     * @var boolean / string
     */
    private $error;

    /**
     * Constructor. Returns Generator object
     * 
     * @return object \RandStrNS\RandString
     */
    function __construct() {

        /* Set Defaults */
        $this->part_length = 3;
        $this->delim = '-';
        $this->prefix = '';
        $this->suffix = '';
        $this->parts = 2;
        $this->count = 1;
        $this->flags = 2;
        $this->unique = false;
        $this->charsets = array(
            0 => 'abcdefghijklmnopqrstuvyxyz',
            1 => 'ABCDEFGHIJKLMNOPQRSTUVYXYZ',
            2 => '0123456789',
            3 => '~!@#$%^&*()_+=<>?/\\[]{}'
        );

        $this->working_charset = '';

        $this->settable = array('part_length',
            'delim',
            'parts',
            'flags',
            'unique',
            'count',
            'prefix',
            'suffix');
        $this->gettable = array('part_length',
            'delim',
            'parts',
            'flags',
            'unique',
            'count',
            'prefix',
            'suffix',
            'duplicates',
            'history',
            'error');
        $this->history = array();
        $this->duplicates = array();

        return $this;
    }

    /**
     * Setter for properties, handles validation
     * @param string $prop Property name
     * @param variant $val Property value
     * @return boolean
     */
    public function __set($prop, $val) {
        try {
            $this->validate($prop, $val);
            $this->$prop = $val;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return true;
    }

    /**
     * Getter for properties, handles validation
     * @param string $prop Property name
     * @return variant
     */
    public function __get($prop) {
        try {
            $this->validate($prop);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        return $this->$prop;
    }

    /**
     * Generates *n* random strings
     * @return array
     * @throws \Exception
     */
    public function generate() {

        foreach ($this->charsets as $k => $charset) {
            if ($this->flags & (1 << $k)) {
                $this->working_charset .= $charset;
            }
        }

        /* check parameter validity */
        try {
            $check = $this->unique_possibility_check();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->count = 0;
        }


        $count = $this->count;
        $result = array();

        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->make_string();
        }

        return $result;
    }

    /**
     * Checks whether it is possible to produce required number of unique results with specified parameters
     * 
     * @return boolean
     * @throws \Exception
     */
    private function unique_possibility_check() {
        $variations = pow(strlen($this->working_charset), pow($this->part_length, $this->parts));
        if ($this->count > $variations && $this->unique) {
            throw new \Exception($this->exception_message('Not enough possible combinations for unique results', ''), 4);
        } else {
            return true;
        }
    }

    /**
     * handles generation of a single string
     * 
     * @return string
     */
    private function make_string() {

        $aParts = array();
        $charset_length = strlen($this->working_charset);
        for ($i = 0; $i < $this->parts; $i++) {
            $aParts[$i] = '';
            for ($j = 0; $j < $this->part_length; $j++) {
                $aParts[$i] .= substr($this->working_charset, mt_rand(0, $charset_length - 1), 1);
            }
        }
        $result = implode($this->delim, $aParts);
        $result = $this->prefix . $result . $this->suffix;

        if ($this->unique) {
            if (!in_array($result, $this->history)) {
                $this->history[] = $result;
                return $result;
            } else {
                $this->duplicates[] = $result;
                return $this->make_string();
            }
        } else {
            return $result;
        }
    }

    /**
     * 
     * Validation for getter and setter methods
     * 
     * @param string $prop Property name
     * @param string|int|bool $val Property value
     * @return boolean
     * @throws \Exception
     */
    private function validate($prop, $val = false) {

        if (!property_exists($this, $prop)) {
            throw new \Exception($this->exception_message('Unknown parameter requested', $prop), 1);
        }

        if ($val && !in_array($prop, $this->settable)) {
            throw new \Exception($this->exception_message('Setting value disallowed', $prop), 2);
        }

        if (!$val && !in_array($prop, $this->gettable)) {
            throw new \Exception($this->exception_message('Getting value disallowed', $prop), 5);
        }

        if ($val) {
            switch ($prop) {
                case 'part_length':
                case 'parts':
                    if (!is_numeric($val)) {
                        throw new \Exception($this->exception_message('Bad parameter value passed for', $prop), 2);
                    }
                    break;
            }
        }
        return true;
    }

    /**
     * Produces Exception message string
     * 
     * 
     * @param string $msg Message body
     * @param string $prop property name
     * @return string
     */
    private function exception_message($msg, $prop) {
        if ($prop) {
            return 'Cought Exception: ' . $msg . ' [' . $prop . ']';
        } else {
            return 'Cought Exception: ' . $msg;
        }
    }

}

/*
 * File: RandString.php
 */