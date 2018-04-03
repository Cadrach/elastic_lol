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
            self::$client = ClientBuilder::create()->build();
        }
        return self::$client;
    }
}