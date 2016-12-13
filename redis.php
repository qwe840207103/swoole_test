<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/10
 * Time: 23:44
 */
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
echo "Connection to server sucessfully";
echo "Server is running: " . $redis->ping();
