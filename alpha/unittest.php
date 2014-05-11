<?php
require_once('FA1.php');
require_once('FA2.php');
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
function test($fa, $re, $ex){
    echo '#tesing RE /' . $re . "/\n";
    foreach(func_get_args() as $i => $str){
        if($i < 3)continue;
        expect($str, $ex, $fa->Test($str));
    }
}
function test_exception($fa, $re){
    echo '#tesing RE /' . $re . "/\n";
    foreach(func_get_args() as $i => $str){
        if($i < 2)continue;
        try{
            $fa->Test($str);
        }catch(Exception $e){
            echo "passed: expected exception received\n";
            return;
        }
        echo "!!error: expected exception was not caught\n";
    }
}

test(\FA1\RE(""), "", true, "");
test(\FA1\RE(""), "", false, "a");
test(\FA1\RE("a"), "a", true, "a");
test(\FA1\RE("a"), "a", false, "b", "abc", "");
test(\FA1\RE("ab"), "ab", true, "ab");
test(\FA1\RE("ab"), "ab", false, "", "a", "b", "c");
test(\FA1\RE("a|b"), "a|b", true, "a", "b");
test(\FA1\RE("a|b"), "a|b", false, "abc", "ab", "");
test(\FA1\RE("ab|c"), "ab|c", true, "ab", "c");
test(\FA1\RE("ab|c"), "ab|c", false, "a", "b", "abc");
test(\FA1\RE("ab|a"), "ab|a", true, "ab", "a");
test(\FA1\RE("ab|a"), "ab|a", false, "b", "c", "abc");
test(\FA1\RE("a|bc"), "a|bc", true, "a", "bc");
test(\FA1\RE("a|bc"), "a|bc", false, "ab", "c", "");
test(\FA1\RE("a*"), "a*", true, "", "a", "aaa");
test(\FA1\RE("a*"), "a*", false, "b", "ab", "ba", "aaaab");
test(\FA1\RE("ab*"), "ab*", true, "a", "abb");
test(\FA1\RE("ab*"), "ab*", false, "b", "bb", "bc", "abc", "");
test(\FA1\RE("a*b"), "a*b", true, "b", "aaab", "ab");
test(\FA1\RE("a*b"), "a*b", false, "aa", "ac", "");
test(\FA1\RE("a*ab"), "a*ab", true, "ab", "aab", "aaaab");
test(\FA1\RE("a*ab"), "a*ab", false, "a", "b", "c");
test(\FA1\RE("aa*b"), "aa*b", true, "ab", "aab", "aaaab");
test(\FA1\RE("aa*b"), "aa*b", false, "a", "b", "c");
test(\FA1\RE("a*|b"), "a*|b", true, "aaa", "b", "");
test(\FA1\RE("a*|b"), "a*|b", false, "abc", "ba", "cb", "ab");
test(\FA1\RE("a|b*"), "a|b*", true, "a", "bb", "");
test(\FA1\RE("a|b*"), "a|b*", false, "aa", "ab", "bc");
test(\FA1\RE("a|b|c"), "a|b|c", true, "a", "b", "c");
test(\FA1\RE("a|b|c"), "a|b|c", false, "ab", "d", "");
test(\FA1\RE("a|"), "a|", true, "a", "");
test(\FA1\RE("a|"), "a|", false, "b", "aa");
test(\FA1\RE("|a"), "|a", true, "a", "");
test(\FA1\RE("|a"), "|a", false, "b");
test(\FA1\RE("||"), "||", true, "");
test(\FA1\RE("||"), "||", false, "a");
test_exception(\FA1\RE("*"), "*", "", "a", "b");
test_exception(\FA1\RE("|*"), "|*", "", "a", "b");
test_exception(\FA1\RE("**"), "**", "", "a", "b");
test_exception(\FA1\RE("*|*"), "*|*", "", "a", "b");
test_exception(\FA1\RE("*||*"), "*||*", "", "a", "b");
test_exception(\FA1\RE("||*"), "||*", "", "a", "b");



test(\FA2\RE(""), "", true, "");
test(\FA2\RE(""), "", false, "a");
test(\FA2\RE("a"), "a", true, "a");
test(\FA2\RE("a"), "a", false, "b", "abc", "");
test(\FA2\RE("ab"), "ab", true, "ab");
test(\FA2\RE("ab"), "ab", false, "", "a", "b", "c");
test(\FA2\RE("a|b"), "a|b", true, "a", "b");
test(\FA2\RE("a|b"), "a|b", false, "abc", "ab", "");
test(\FA2\RE("ab|c"), "ab|c", true, "ab", "c");
test(\FA2\RE("ab|c"), "ab|c", false, "a", "b", "abc");
test(\FA2\RE("ab|a"), "ab|a", true, "ab", "a");
test(\FA2\RE("ab|a"), "ab|a", false, "b", "c", "abc");
test(\FA2\RE("a|bc"), "a|bc", true, "a", "bc");
test(\FA2\RE("a|bc"), "a|bc", false, "ab", "c", "");
test(\FA2\RE("a*"), "a*", true, "", "a", "aaa");
test(\FA2\RE("a*"), "a*", false, "b", "ab", "ba", "aaaab");
test(\FA2\RE("ab*"), "ab*", true, "a", "abb");
test(\FA2\RE("ab*"), "ab*", false, "b", "bb", "bc", "abc", "");
test(\FA2\RE("a*b"), "a*b", true, "b", "aaab", "ab");
test(\FA2\RE("a*b"), "a*b", false, "aa", "ac", "");
test(\FA2\RE("a*ab"), "a*ab", true, "ab", "aab", "aaaab");
test(\FA2\RE("a*ab"), "a*ab", false, "a", "b", "c");
test(\FA2\RE("aa*b"), "aa*b", true, "ab", "aab", "aaaab");
test(\FA2\RE("aa*b"), "aa*b", false, "a", "b", "c");
test(\FA2\RE("a*|b"), "a*|b", true, "aaa", "b", "");
test(\FA2\RE("a*|b"), "a*|b", false, "abc", "ba", "cb", "ab");
test(\FA2\RE("a|b*"), "a|b*", true, "a", "bb", "");
test(\FA2\RE("a|b*"), "a|b*", false, "aa", "ab", "bc");
test(\FA2\RE("a|b|c"), "a|b|c", true, "a", "b", "c");
test(\FA2\RE("a|b|c"), "a|b|c", false, "ab", "d", "");
test(\FA2\RE("a|"), "a|", true, "a", "");
test(\FA2\RE("a|"), "a|", false, "b", "aa");
test(\FA2\RE("|a"), "|a", true, "a", "");
test(\FA2\RE("|a"), "|a", false, "b");
test(\FA2\RE("||"), "||", true, "");
test(\FA2\RE("||"), "||", false, "a");
test_exception(\FA2\RE("*"), "*", "", "a", "b");
test_exception(\FA2\RE("|*"), "|*", "", "a", "b");
test_exception(\FA2\RE("**"), "**", "", "a", "b");
test_exception(\FA2\RE("*|*"), "*|*", "", "a", "b");
test_exception(\FA2\RE("*||*"), "*||*", "", "a", "b");
test_exception(\FA2\RE("||*"), "||*", "", "a", "b");