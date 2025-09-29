<?php 
declare(strict_types=1);

P(666);



/**
 * P 打印函数
 * @param [type] $data [description]
 */
function P(...$data)
{
    echo "<pre>";
    echo PHP_EOL;
    print_r($data);
    echo "</pre>";
    echo PHP_EOL;
    exit('print end');
}