<?php

namespace App\Console\Commands;

use App\Console\ThrottleException;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use App\Models\ElasticSearchClient;
use Illuminate\Support\Collection;

class CrawlerLaunch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:launch {--server=euw1 : br1, eun1, euw1, jp1, kr, la1, la2, na1, oc1, tr1, ru, pbe1} {--max=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Launch a LoL API crawler';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $throttles = [
        [20, 1],
        [100, 125],
//        [3, 10],
    ];

    /**
     * Created matches
     * @var int
     */
    protected $created = 0;

    /**
     * Queues to take in account
     * @var array
     */
    protected $queues = [400, 420, 430, 440];

    /**
     * Storing requests for throttling
     * @var Collection
     */
    protected $requests;

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
        $maxMatches = intval($this->option('max'));
        $server = trim($this->option('server'));
        $accountId = env('LOL_DEFAULT_ACCOUNT_ID');
        $root = 'https://'.$server.'.api.riotgames.com';

        $this->requests = collect();

        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $root . '/lol/',
            // You can set any number of default request options.
            'timeout'  => 10.0,
            'verify' => false, //do not check certificates
            'headers' => [
                'X-Riot-Token' => env('LOL_API_KEY'),
            ]
        ]);

        $count = 0;
        $treated = [];

        while($count < $maxMatches){
            //Store this accountId as treated
            $treated[] = $accountId;

            //Store currently created
            $createdBefore = $this->created;

            //Store matches in elasticsearch
            $matches = $this->storeMatchesDetails($accountId);

            //Increase our local count of matches
            $count+= count($matches);

            //New accountId
            if( ! $matches->count()){
                $accountId = env('LOL_DEFAULT_ACCOUNT_ID'); //reuse our default
            }
            else{
                $accountId = $matches->pluck('participantIdentities')->map(function($v){return collect($v)->pluck('player')->pluck('accountId');})->flatten()->unique()->diff($treated)->random();
            }

            $this->garbageCollectThrottling();

            $this->line("<fg=red;bg=yellow>".($this->created - $createdBefore)." created ({$this->created} total created)</>");
            $this->line(' ');
        }


    }

    /**
     * Clean the requests array to avoid storing too many requests
     */
    public function garbageCollectThrottling(){
        $now = microtime(true);
        $duration = collect($this->throttles)->map(function($v){return $v[1];})->max();
        $this->requests = collect($this->requests->filter(function($time) use ($duration, $now) {
            return ($now-$time) < $duration;
        })->values());
    }

    /**
     * Check throttling
     * @throws ThrottleException
     */
    public function checkThrottling(){
        //Checking if we can run the request (throttling)
        $now = microtime(true);
        foreach($this->throttles as $k=>$rule){
            $max = $rule[0];
            $duration = $rule[1];
            $reqs = $this->requests->filter(function($time) use ($duration, $now) {
                return ($now-$time) < $duration;
            });

            if($reqs->count()>=$max){

                $nextTime = $reqs->values()[$reqs->count() - $max] + $duration;

                //Compute how long we should wait
                $wait = ceil($nextTime - $now);
                $this->error("Throttling (rule $k) {$wait}s");
                sleep($wait);
                return $this->checkThrottling();
            }
        }

        $this->requests->push(microtime(true));
    }

    /**
     * Get data from Riot
     * Will wait 60s before retry in case of unexpected server error
     * @param $url
     * @return mixed
     */
    public function getRiotDataFromUrl($url){
        try{
            $this->checkThrottling();
            $result = $this->client->get($url);
        }
        catch(ServerException $e){
            //Ignore this match
            $this->error('Sleeping 60s - Server Exception: ' . $e->getMessage());
            sleep(60);
            return $this->getRiotDataFromUrl($url);
        }
        catch(ClientException $e){
            $this->error('Sleeping 60s - Client Exception: ' . $e->getMessage());
            sleep(60);
            return false;
        }
//        catch(ThrottleException $e){
//            //Ignore this match
//            $this->error('Throttling 1s');
//            sleep(1);
//            return $this->getRiotDataFromUrl($url);
//        }

        return json_decode($result->getBody(), true);
    }

    /**
     * @param $accountId
     * @return \Illuminate\Support\Collection
     */
    public function storeMatchesDetails($accountId){

        //Get match list
        $matches = $this->getMatches($accountId);

        //Get elasticsearch client
        $elastic = ElasticSearchClient::get();

        //Our loop store vars
        $matchesDetails = collect();
        $total = count($matches);

        foreach($matches as $k=>$m){
            //The match id
            $matchId = $m['gameId'];

            try{
                //Check if we already created this match
                $exist = $elastic->get([
                    'index' => 'lol_match',
                    'type' => 'lol_match',
                    'id' => $matchId,
                ]);

                $matchesDetails->push($exist['_source']);
            }catch(Missing404Exception $e){
                //If we did not, then we need to fetch the details
//                $time = microtime(true);
                $match = $this->getRiotDataFromUrl('match/v3/matches/' . $matchId);
                if( ! $match){
                    //If no match found, continue to next match
                    continue;
                }
                $matchesDetails->push($match);

                //Store the match
                $elastic->index([
                    'index' => 'lol_match',
                    'type' => 'lol_match',
                    'id' => $matchId,
                    'body' => $match,
                ]);

                //Increment creation counter
                $this->created++;
            }

            //Display progress
//            echo "\r" . ($k+1) . "/$total (" . round(($k+1)/$total*100, 2) . "%)                         ";
            echo $this->line(($k+1) . "/$total (" . round(($k+1)/$total*100, 2) . "%)");
        }

        //New line feed to complete progress display
        echo "\n";
        return $matchesDetails;
    }

    public function getMatches($accountId){
        $beginIndex = 0;
        $matches = collect([]);
        $count = 0;
        $season = env('LOL_SEASON_ID');
        $queues = collect($this->queues)->map(function($v){return "queue=$v";})->implode('&');
        while(++$count<100){ //max 10000 matches taken in account
            $result = $this->getRiotDataFromUrl("match/v3/matchlists/by-account/$accountId?beginIndex=$beginIndex&season=$season&$queues");
            if( ! $result){
                //Break loop if no result
                break;
            }
            $matches = $matches->merge($result['matches']);

                //Loop to retrieve all matches
            if($result['endIndex'] < $result['totalGames'] && count($result['matches'])){
                $beginIndex = $result['endIndex'];
            }
            else{
                break;
            }
        }

        $this->info("{$matches->count()} matches found for $accountId");

        return $matches;
    }
}
