<?php
require_once('StatusContainer.php');
class FA1{
    private static $c;
    private $s0;
    private static function GetStatus($lhs){
        $c = self::$c;
        $p = $waitForAttach = $list = [];
        $p[] = $lhs;
        while(count($p) > 0){
            foreach($p as &$item){
                if(in_array($item, $list)){
                    $remainder = $p[0];
                    $item = $remainder;
                    array_shift($p);
                    continue;
                }
                array_push($list, $item);
                foreach($c->GetMap($item) as $v){
                    $waitForAttach[] = $v;
                }
                foreach($c->GetClosure($item) as $v){
                    $waitForAttach[] = $v;
                }
            }
            foreach($waitForAttach as $v){
                if(!in_array($v, $p) && !in_array($v, $list)){
                    $p[] = $v;
                }
            }
        }
        return $list;
    }
    private static function Copy($lhs){
        $c = self::$c;
        $list = self::GetStatus($lhs);
        $pool = [];
        $mapToIndex = [];
        foreach($list as $i => $item){
            $pool[$i] = $c->Create();
            $mapToIndex[$item] = $i;
        }
        foreach($list as $index => $item){
            foreach($c->GetMap($item) as $k => $v){
                $i = $mapToIndex[$v];
                $c->SetMap($pool[$index], $k, $pool[$i]);
            }
            foreach($c->GetClosure($item) as $v){
                $i = $mapToIndex[$v];
                $c->SetClosure($pool[$index], $pool[$i]);
            }
            $c->SetAccept($pool[$index], $c->GetAccept($item));
        }
        return $pool[0];
    }
        
    private static function Constructor($str){
        if(strlen($str) == 0){
            return self::ConstructUnitFA();
        }
        $f = strpos($str, '|');
        if($f === false){
            $left = self::ConstructSingleTransitionFA($str);
            if($left < 0)return -1;
            if(substr($str, 1, 1) == '*'){
                $left = self::Kleene($left);
                $right = self::Constructor(substr($str, 2));
            }else{
                $right = self::Constructor(substr($str, 1));
            }
            if($right < 0)return -1;
            return self::Concat($left, $right);
        }else{
            $left = self::Constructor(substr($str, 0, $f));
            if($left < 0)return -1;
            $right = self::Constructor(substr($str, $f + 1));
            if($right < 0)return -1;
            return self::Pipe($left, $right);
        }
    }
    private static function ConstructUnitFA(){
        $c = self::$c;
        $ret = $c->Create();
        $c->SetAccept($ret);
        return $ret;
    }
    private static function ConstructSingleTransitionFA($str){
        $c = self::$c;
        $chr = $str[0];
        if($chr == '*')return -1;
        $s = $c->Create();
        $e = $c->Create();
        $c->SetAccept($e);
        $c->SetMap($s, $chr, $e);
        return $s;
    }
    private static function ConnectTo($lhs, $rhs){
        $c = self::$c;
        $acceptStatus = self::FindAcceptStatus($lhs);
        $c->SetAccept($acceptStatus, false);
        foreach($rhs as $item){
            $c->SetClosure($acceptStatus, $item);
        }
        return $acceptStatus;
    }
    private static function SetClosure($lhs, $rhs){
        $c = self::$c;
        $c->SetClosure($lhs, $rhs);
    }
    private static function GetClosures($lhs, $acc = []){
        $c = self::$c;
        $add = false;
        $ret = [];
        foreach($c->GetClosure($lhs) as $item){
            if(!in_array($item, $acc)){
                $ret[] = $item;
                $acc[] = $item;
                $add = true;
            }
        }
        if(!$add)return $ret;
        foreach($c->GetClosure($lhs) as $item){
            foreach(self::GetClosures($item, $acc) as $i){
                if($i != $lhs)$ret[] = $i;
            }
        }
        return $ret;
    }
    private static function Concat($lhs, $rhs){
        $c = self::$c;
        $ret = self::Copy($lhs);        $retAcceptStatus = self::FindAcceptStatus($ret);
        $rhsCopy = self::Copy($rhs);
        $rhsCopyAcceptStatus = self::FindAcceptStatus($rhsCopy);
        self::ConnectTo($ret, [$rhsCopy]);
        $c->SetAccept($retAcceptStatus, false);
        return $ret;
    }
    private static function Pipe($lhs, $rhs){
        $c = self::$c;
        $lhsCopy = self::Copy($lhs);
        $rhsCopy = self::Copy($rhs);
        $ret = $c->Create();
        $c->SetClosure($ret, $lhsCopy);
        $c->SetClosure($ret, $rhsCopy);
        $f = $c->Create();
        $c->SetAccept($f);
        self::ConnectTo($lhsCopy, [$f]);
        self::ConnectTo($rhsCopy, [$f]);
        return $ret;
    }
    private static function Kleene($lhs){
        $c = self::$c;
        $s = $c->Create();
        $e = $c->Create();
        $lhsCopy = self::Copy($lhs);
        self::ConnectTo($lhsCopy, [$lhsCopy, $e]);
        $c->SetAccept($e);
        $c->SetClosure($s, $lhsCopy);
        $c->SetClosure($s, $e);
        return $s;
    }
    private static function FindAcceptStatus($lhs){
        $c = self::$c;
        $list = self::GetStatus($lhs);
        foreach($list as $i){
            if($c->GetAccept($i))return $i;
        };
    }
    public function __construct($str, $debug = false){
        self::$c = new StatusContainer;
        $this->s0 = self::Constructor($str);
        if($debug){
            var_dump($this->s0);
            var_dump(self::$c);
        }
    }
    public function Test($str){
        $c = self::$c;
        if($this->s0 < 0)throw new Exception("RE syntax error");
        $p = [$this->s0];
        $waitForAttach = self::GetClosures($this->s0);
        for(; strlen($str) > 0; $str = substr($str, 1)){
            $p = array_merge($p, $waitForAttach);
            $waitForAttach = [];
            for($i = 0; $i < count($p); ++$i){
                $item = $p[$i];
                $map = $c->GetMap($item);
                if(array_key_exists($str[0], $map)){
                    $p[$i] = $map[$str[0]];
                    foreach(self::GetClosures($p[$i]) as $v){
                        $waitForAttach[] = $v;
                    }
                }else{
                    if(count($p) == 1)return false;
                    for($j = $i; $j < count($p) - 1; ++$j){
                        $p[$j] = $p[$j + 1];
                    }
                    $i -= 1;
                    array_pop($p);
                }
            }
        }
        if(strlen($str) > 0)return false;
        foreach($p as $item){
            if($c->GetAccept($item))return true;
        }
        foreach($waitForAttach as $item){
            if($c->GetAccept($item))return true;
        }
        return false;
    }
}
           