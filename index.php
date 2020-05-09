<?php

require 'vendor/autoload.php';

use QL\QueryList;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

$client = new Client();

//抓取分页
for ($i=1; $i <2 ; $i++) {
    $urls[] = "https://www.mzitu.com/page/".$i;
}

//内容切片
$range = '.postlist';

QueryList::range($range)
    ->multiGet($urls)
    // 设置并发数为2
    ->concurrency(2)
    // 设置GuzzleHttp的一些其他选项
    ->withOptions([
        'timeout' => 60
    ])
    // 设置HTTP Header
    ->withHeaders([
        "User-Agent"=>"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110Safari/537.36",
        "Referer"=>"https://www.mzitu.com/xinggan/"
    ])
    // HTTP success回调函数
    ->success(function (QueryList $ql, Response $response, $index) use ($client){

        $ql->find('#pins a img')->map(function($item) use ($client){
            $src = $item->attr('data-original');
            $alt = $item->alt;
            printf("正在抓取=====".$alt.PHP_EOL);
            $ext = pathinfo( parse_url( $src, PHP_URL_PATH ), PATHINFO_EXTENSION );
            $localSrc = 'image/'.$alt.'.'.$ext;
            //反 防盗链请求
            $response = $client->request('GET', $src, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110Safari/537.36',
                    "Referer"=>"https://www.mzitu.com/xinggan/",
                ]
            ]);
            $stream = $response->getBody()->getContents();
            file_put_contents($localSrc,$stream);
            printf("抓取完成=====".PHP_EOL);
        });

    })
    // HTTP error回调函数
    ->error(function (QueryList $ql, $reason, $index){
        printf("请求错误原因=====".PHP_EOL);
        print_r($reason);
    })
    ->send();







