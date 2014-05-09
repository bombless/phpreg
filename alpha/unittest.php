<?php
require_once('FA1.php');
function expect($expr, $ex, $ac){
    if(!$expr)$expr = '<Empty String>';
    $ex = $ex? 'true': 'false';
    $ac = $ac? 'true': 'false';
    if($ex == $ac){
        echo 'passed: ', $expr, ' = ', $ex, "\n";
    }else{
        echo '!!error: expected ', $expr, ' = ', $ex, ', get ',
            $ac, ' instead.', "\n";
    }
    return $ex == $ac;
}
function test($re, $ex){
    echo '#tesing RE /' . $re . "/\n";
    $re = new FA1($re);
    foreach(func_get_args() as $i => $str){
        if($i < 2)continue;
        expect($str, $ex, $re->Test($str));
    }
}
function test_exception($re, $func){
    $fa = new FA1($re);
    foreach(func_get_args() as $str){
        try{
            $fa->Test($str);
        }catch(Exception $e){
            echo "passed: expected exception received\n";
            return;
        }
        echo "!!error: expected exception was not caught\n";
    }
}
        
test("", true, "");
test("", false, "a");
test("a", true, "a");
test("a", false, "b", "abc", "");
test("ab", true, "ab");
test("ab", false, "", "a", "b", "c");
test("a|b", true, "a", "b");
test("a|b", false, "abc", "ab", "");
test("ab|c", true, "ab", "c");
test("ab|c", false, "a", "b", "abc");
test("ab|a", true, "ab", "a");
test("ab|a", false, "b", "c", "abc");
test("a|bc", true, "a", "bc");
test("a|bc", false, "ab", "c", "");
test("a*", true, "", "a", "aaa");
test("a*", false, "b", "ab", "ba", "aaaab");
test("ab*", true, "a", "abb");
test("ab*", false, "b", "bb", "bc", "abc", "");
test("a*b", true, "b", "aaab", "ab");
test("a*b", false, "aa", "ac", "");
test("a*ab", true, "ab", "aab", "aaaab");
test("a*ab", false, "a", "b", "c");
test("aa*b", true, "ab", "aab", "aaaab");
test("aa*b", false, "a", "b", "c");
test("a*|b", true, "aaa", "b", "");
test("a*|b", false, "abc", "ba", "cb", "ab");
test("a|b*", true, "a", "bb", "");
test("a|b*", false, "aa", "ab", "bc");
test("a|b|c", true, "a", "b", "c");
test("a|b|c", false, "ab", "d", "");
test("a|", true, "a", "");
test("a|", false, "b", "aa");
test("|a", true, "a", "");
test("|a", false, "b");
test("||", true, "");
test("||", false, "a");
test_exception("*", "", "a", "b");
test_exception("|*", "", "a", "b");
test_exception("**", "", "a", "b");
test_exception("*|*", "", "a", "b");
test_exception("*||*", "", "a", "b");
test_exception("||*", "", "a", "b");