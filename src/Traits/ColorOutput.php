<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 2020-03-16
 * Time: 15:53
 */
namespace Touge\SwooleSocketServer\Traits;

trait ColorOutput
{
    protected $foregroundColors = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
    ];
    protected $backgroundColors = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    ];


    /**
     * to string
     * @param $data
     * @return false|string
     */
    protected function _to_string($data){
        if(is_array($data)){
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }

    /**
     * @param $string
     */
    public function info($string){
        $output= $this->getColoredString($this->_to_string($string), 'green', 'block');
        $this->output($output);
    }

    /**
     * warning..
     * @param $string
     */
    public function warning($string){
        $output= $this->getColoredString($this->_to_string($string), 'yellow', 'block');
        $this->output($output);
    }

    /**
     * error...
     * @param $string
     */
    public function error($string){
        $output= $this->getColoredString($this->_to_string($string), 'red', 'white');
        $this->output($output);
    }


    protected function output($message){
        echo date('Y-m-d H:i:s') . " -- " .$message . PHP_EOL;
    }

    /**
     * 获得输出的配置色彩
     *
     * @param $string
     * @param null $foregroundColor 字符色
     * @param null $backgroundColor 背景色
     * @return string
     */
    protected function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $coloredString = "";
        if (isset($this->foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
        }
        if (isset($this->backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
        }
        $coloredString .= $string . "\033[0m";
        return $coloredString;
    }

    /**
     * 获得所有的字符色列表
     *
     * @return array
     */
    protected function getForegroundColors()
    {
        return array_keys($this->foregroundColors);
    }

    /**
     * 背景色列表
     * @return array
     */
    protected function getBackgroundColors()
    {
        return array_keys($this->backgroundColors);
    }
}