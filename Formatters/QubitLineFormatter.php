<?php

namespace Qubit\Bundle\LogBundle\Formatters;

use Monolog\Formatter\NormalizerFormatter;
use Qubit\Bundle\UtilsBundle\Context\Context;

/**
* Formats incoming records into a one-line string
* This is especially useful for logging to files
 * 
 * @package Qubit\Bundle\LogBundle\Formatters\QubitLineFormatter
*/
class QubitLineFormatter extends NormalizerFormatter
{
   const QUBIT_FORMAT = "[%datetime%] Duration: [ %duration_time% ] - IP: [ %ip_address% ] - UserId: [ %user_id% ] - %channel%.%level_name% - %tracking_code% : %message% [ %context% ] [ %extra% ]\n";

   /**
    * __construct
    */
    public function __construct()
    {
        parent::__construct();
    }

   /**
    * {@inheritdoc}
    */
    public function format(array $record)
    {
        $vars = parent::format($record);
        $output = self::QUBIT_FORMAT;

        if (!empty($vars['extra']['tracking_code'])) {

            $output = str_replace('%tracking_code%', $this->stringify($vars['extra']['tracking_code']), $output);
        }

        if (!empty($vars['extra']['duration_time'])) {

            $output = str_replace('%duration_time%', $this->stringify($vars['extra']['duration_time']), $output);
        }

        $output = str_replace('%ip_address%', $this->stringify(Context::getInstance()->getIpAddress()), $output);
        $output = str_replace('%user_id%', $this->stringify(Context::getInstance()->getUserId()), $output);
        
        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->stringify($val), $output);
            }
        }

        return $output;
    }
   
    /**
     * stringify
     *
     * @param string $value The string value to clean
     *
     * @return string
     */
    public function stringify($value)
    {
        return $this->replaceNewlines($this->convertToString($value));
    }
    
    /**
     * convertToString
     *
     * @param string $data Data cast to string
     *
     * @return string
     */
    protected function convertToString($data)
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return $this->toJson($data, true);
        }

        return str_replace('\\/', '/', @json_encode($data));
    }
    
    /**
     * replaceNewlines
     *
     * @param string $str String to clean spaces
     *
     * @return string
     */
    protected function replaceNewlines($str)
    {
//        if ($this->allowInlineLineBreaks) {
//            if (0 === strpos($str, '{')) {
//                return str_replace(array('\r', '\n'), array("\r", "\n"), $str);
//            }
//
//            return $str;
//        }

        return str_replace(array("\r\n", "\r", "\n"), ' ', $str);
    }
}
