<?php
class StatusContainer{
    private $pool = [];
    private static function Search($lhs, $rhs){
        foreach($lhs as $k => $v){
            if(self::Equals($v, $rhs))return $k;
        }
        return -1;
    }
    private static function Equals($lhs, $rhs){
        if(gettype($lhs) == 'array' ||
        gettype($lhs) == 'object'){
            if(gettype($lhs) != gettype($rhs))return false;
            foreach($lhs as $k => $v){
                if(array_key_exists($k, $rhs) &&
                self::Equals($rhs[$k], $v)){
                    continue;
                }
                return false;
            }
            return true;
        }
        return $lhs == $rhs;
    }
    public function Create(){
        $val = new stdClass;
        $val->accept = false;
        $val->map = [];
        $val->closure = [];
        return array_push($this->pool, $val) - 1;
    }
    public function GetMap($ref){
        return $this->pool[$ref]->map;
    }
    public function SetMap($ref, $chr, $val){
        $this->pool[$ref]->map[$chr] = $val;
    }
    public function GetClosure($ref){
        return $this->pool[$ref]->closure;
    }
    public function SetClosure($ref, $val){
        return array_push($this->pool[$ref]->closure, $val);
    }
    public function GetAccept($ref){
        return $this->pool[$ref]->accept;
    }
    public function SetAccept($ref, $bool = true){
        $this->pool[$ref]->accept = $bool;
    }
}