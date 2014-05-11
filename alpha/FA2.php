<?php
namespace FA2{
    class Status{
        private $acceptOffset;
        private $pool;
        private function __construct(){
        }
        public static function CreateUnit(){
            $ret = new self;
            $item = new \stdClass;
            $item->map = [];
            $item->closure = [];
            $ret->pool = [$item];
            $ret->acceptOffset = 0;
            return $ret;
        }
        public function CreateSingleTransition($chr){
            $chr = $chr[0];
            $s = new \stdClass;
            $s->map = [$chr => 1];
            $s->closure = [];
            $e = new \stdClass;
            $e->map = [];
            $e->closure = [];
            $ret = new self;        
            $ret->pool = [$s, $e];
            $ret->acceptOffset = 1;
            return $ret;
        }
        private function Adjust($limit){
            $ret = new self;
            $ret->acceptOffset = $this->acceptOffset + $limit;
            $ret->pool = [];
            foreach($this->pool as $i => $ptr){
                if(!$ptr)continue;
                $item = new \stdClass;
                $item->map = [];
                $item->closure = [];
                foreach($ptr->map as $k => $v){
                    $item->map[$k] = $v + $limit;
                }
                foreach($ptr->closure as $v){
                    array_push($item->closure, $v + $limit);
                }
                $ret->pool[$i + $limit] = $item;
            }
            return $ret;
        }
        public function Concat($rhs){
            $adjust = max(array_keys($this->pool)) + 1;
            $rhs = $rhs->Adjust($adjust);
            $pool = $this->pool;
            $offset = $this->acceptOffset;
            array_push($pool[$offset]->closure, $adjust);
            $ret = new self;
            $ret->acceptOffset = $rhs->acceptOffset;
            $ret->pool = $pool + $rhs->pool;
            return $ret;
        }
        public function Kleene(){
            $ret = $this->Adjust(1);
            $offsetOld = $ret->acceptOffset;
            $offsetNew = max(array_keys($ret->pool)) + 1;
            array_push($ret->pool[$offsetOld]->closure, 0);
            array_push($ret->pool[$offsetOld]->closure, $offsetNew);
            $s = new \stdClass;
            $s->map = [];
            $s->closure = [1, $offsetNew];
            $e = new \stdClass;
            $e->map = [];
            $e->closure = [];
            $ret->pool[0] = $s;
            $ret->pool[$offsetNew] = $e;
            $ret->acceptOffset = $offsetNew;
            return $ret;
        }
        public function Pipe($rhs){
            $ret = $this->Adjust(1);
            $adjust = max(array_keys($ret->pool)) + 1;
            $rhs = $rhs->Adjust($adjust);
            $s = new \stdClass;
            $s->closure = [1, $adjust];
            $s->map = [];
            $ret->pool[0] = $s;
            $ret->pool = $ret->pool + $rhs->pool;
            $offsetLhs = $ret->acceptOffset;
            $offsetRhs = $rhs->acceptOffset;
            $offsetRet = max(array_keys($rhs->pool)) + 1;
            $e = new \stdClass;
            $e->map = [];
            $e->closure = [];
            array_push($ret->pool[$offsetLhs]->closure, $offsetRet);
            array_push($ret->pool[$offsetRhs]->closure, $offsetRet);
            $ret->acceptOffset = $offsetRet;
            $ret->pool[$offsetRet] = $e;
            return $ret;
        }
        public function GetPool(){
            return $this->pool;
        }
        public function GetAccept(){
            return $this->acceptOffset;
        }
    }
    class FA2{
        private $s0;
        public function __construct($str){
            $this->s0 = self::Construct($str);
        }
        private static function Construct($str){
            if(strlen($str) == 0)return Status::CreateUnit();
            else if($str[0] == '*')return null;
            else if(strlen($str) == 1)return Status::CreateSingleTransition($str);
            $firstPipe = strpos($str, '|');
            if($firstPipe === false){
                if($str[1] == '*'){
                    $left = Status::CreateSingleTransition($str)->Kleene();
                    $right = self::Construct(substr($str, 2));
                }else{
                    $left = Status::CreateSingleTransition($str);
                    $right = self::Construct(substr($str, 1));
                }
                if(!$right)return null;
                return $left->Concat($right);
            }else{
                $left = self::Construct(substr($str, 0, $firstPipe));
                $right = self::Construct(substr($str, $firstPipe + 1));
                if(!$left || !$right)return null;
                return $left->Pipe($right);
            }
        }
        private function GetClosures($ref, $acc = []){
            $pool = $this->s0->GetPool();
            $ret = array_diff($pool[$ref]->closure, $acc);
            $acc = array_merge($ret, $acc);
            $attach = [];
            foreach($ret as $item){
                $attach = array_merge($this->GetClosures($item, $acc), $attach);
            }
            return array_merge($ret, $attach);                
        }
        public function Test($str){
            if(!$this->s0)throw new \Exception("syntax error");
            $accept = $this->s0->GetAccept();
            $pool = $this->s0->GetPool();
            $p = [0];
            $attach = $this->GetClosures(0);if(is_object($str)){var_dump($str); die;}
            for(; strlen($str) > 0; $str = substr($str, 1)){
                $chr = $str[0];
                $p = array_merge($p, $attach);
                $attach = [];
                for($i = 0; $i < count($p); ++$i){
                    $item = $pool[$p[$i]];
                    if(array_key_exists($chr, $item->map)){
                        $p[$i] = $item->map[$chr];
                        $attach = $this->GetClosures($p[$i]);
                    }else{
                        for($j = $i + 1; $j < count($p); ++$j){
                            $p[$j - 1] = $p[$j];
                        }
                        array_pop($p);
                        $i -= 1;
                    }
                }
            }
            if(strlen($str) > 0)return false;
            return in_array($accept, $p) || in_array($accept, $attach);
        }
    }
    function RE($re){ return new FA2($re); }
}
