<?php
/**
 * Created by PhpStorm.
 * User: rachid
 * Date: 03/04/2018
 * Time: 11:55
 */

namespace App\Models;
use Elasticsearch\ClientBuilder;


class ElasticSearchClient
{
    protected static $client = null;

    /**
     * @return \Elasticsearch\Client
     */
    public static function get(){
        if(self::$client === null){
            $host = [
                'host' => env('ELASTICSEARCH_HOST'),
                'port' => env('ELASTICSEARCH_PORT'),
                'scheme' => env('ELASTICSEARCH_SCHEME'),
                'user' => env('ELASTICSEARCH_USER'),
                'pass' => env('ELASTICSEARCH_PASS'),
            ];

            self::$client = ClientBuilder::create()
                ->setHosts([$host])
                ->build();
        }
        return self::$client;
    }
}