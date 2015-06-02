<?php
/**
 * Curl wrapper v1.0
 * @package net
 * @author DefanYu
 * @date 2015-03-18
 */

class Curl {

    private $defaultOptions = array(
        CURLOPT_CONNECTTIMEOUT=>5,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_BINARYTRANSFER=>true,
    );

    private $requestOptions=array();

    private $timeOut = 30;

    public function __construct(){
        if (!extension_loaded('curl')) {
            throw new Exception('curl library is not loaded');
        }
    }

    /**
     * curl get
     * @param $url
     * @return mixed
     */
    public function get($url){
        return $this->exec($url);
    }

    /**
     * curl post
     * @param $url
     * @param $data postdata
     * @return mixed
     */
    public function post($url, $data){
        $this->setOption(CURLOPT_POST,true);
        $this->setOption(CURLOPT_POSTFIELDS,$data);
        return $this->exec($url);
    }

    private function exec($url){
        $res['status'] = true;

        $ch = curl_init($url) ;
        $this->setOption(CURLOPT_TIMEOUT,$this->timeOut);
        $this->setOption(CURLOPT_USERAGENT,'KG_LIB_WEB_CURL-'.$_SERVER['SERVER_ADDR']);
        curl_setopt_array($ch,$this->getAllOptions());

        $res['content'] = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($httpCode != 200){
            $res['status'] = false;
            $res['code'] = $httpCode;
            $res['error'] = curl_error($ch);
            $res['info'] = curl_getinfo($ch);
        }
        curl_close($ch);

        return $res;
    }

    /**
     * clear all options
     * only clear request options
     * @return $this
     */
    function clearAllOptions(){
        $this->requestOptions = array();
        return $this;
    }

    /**
     * clear options
     * only clear request options
     * @param $optionKeyList   array(CURLOPT_TIMEOUT ,CURLOPT_CONNECTTIMEOUT);
     * @return $this
     */
    function clearOptions($optionKeyList){
        if(count($optionKeyList)){
            foreach($optionKeyList as $optionKey){
               $this->clearOption($optionKey);
            }
        }
        return $this;
    }

    /**
     * clear option
     * only clear request option
     * @param $key
     * @return $this
     */
    function clearOption($key){
        if(isset($this->requestOptions[$key])){
            unset($this->requestOptions[$key]);
        }
        return $this;
    }

    /**
     * set option
     * only set request option
     * @param $key
     * @param $value
     * @return $this
     */
    function setOption($key,$value){
        $this->requestOptions[$key] = $value;
        return $this;
    }

    /**
     * set options
     * only set request options
     * @param $options    array(CURLOPT_TIMEOUT =>30,CURLOPT_CONNECTTIMEOUT =>30);
     * @return $this
     */
    function setOptions($options){
        $this->requestOptions = $options;
        return $this;
    }

    /**
     * get all options
     * default options and request options
     * @return array
     */
    function getAllOptions(){
        return self::mergeArray($this->defaultOptions,$this->requestOptions);
    }

    /**
     * set time out
     * @param $timeOut
     * @return array
     */
    function setTimeOut($timeOut){
        return $this->timeOut = $timeOut;
    }

    /**
     * get time out
     * @return array
     */
    function getTimeOut(){
        return $this->timeOut;
    }

    public static function mergeArray(){
        $args=func_get_args();
        $res=array_shift($args);
        while(!empty($args)){
            $next=array_shift($args);
            foreach($next as $k => $v){
                if(is_array($v) && isset($res[$k]) && is_array($res[$k]))
                    $res[$k]=self::mergeArray($res[$k],$v);
                elseif(is_numeric($k))
                    isset($res[$k]) ? $res[]=$v : $res[$k]=$v;
                else
                    $res[$k]=$v;
            }
        }
        return $res;
    }
}