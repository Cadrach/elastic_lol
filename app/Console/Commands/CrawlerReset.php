<?php

namespace App\Console\Commands;

use App\Console\ThrottleException;
use Carbon\Carbon;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use App\Models\ElasticSearchClient;
use Illuminate\Support\Collection;

class CrawlerReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset crawler data';

    /**
     * @var Client
     */
    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '2G');

        if ( ! $this->confirm('WARNING: this will remove ALL STORED DATA. Continue?')) {
            return;
        }

        $elastic = ElasticSearchClient::get();

        $dir = base_path('elasticsearch');
        foreach(scandir($dir) as $file){
            if(strpos($file, 'mapping.json')){

                $content = json_decode(file_get_contents("$dir/$file"), true);

                foreach($content as $index=>$mapping){
                    //Delete the index
                    try{
                        $elastic->indices()->delete(['index'=>$index]);
                    }catch(Missing404Exception $e){
                        //Index did not exist
                    }

                    //Create the new one
                    $elastic->indices()->create([
                        'index' => $index,
                        'body' => $mapping,
                    ]);
                }
            }
        }

    }
}
