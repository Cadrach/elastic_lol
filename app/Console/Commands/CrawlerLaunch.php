<?php

namespace App\Console\Commands;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use hamburgscleanest\GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitRuleset;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use App\Models\ElasticSearchClient;

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

//        $rules = new RequestLimitRuleset([
//            $root => [
//                [
//                    'max_requests'     => 20,
//                    'request_interval' => 1
//                ],
//                [
//                    'max_requests'     => 100,
//                    'request_interval' => 120
//                ]
//            ]
//        ]);

//        $stack = new HandlerStack();
//        $stack->setHandler(new CurlHandler());
//        $stack->push((new ThrottleMiddleware($rules))->handle());

        $this->client = new Client([
            //Handler to implement the throttling
//            'handler' => $stack,
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

            //Store matches in elasticsearch
            $matches = $this->storeMatchesDetails($accountId);

            //Increase our local count of matches
            $count+= count($matches);

            //New accountId
            $accountId = $matches->pluck('participantIdentities')->map(function($v){return collect($v)->pluck('player')->pluck('accountId');})->flatten()->unique()->diff($treated)->random();

            $this->line("<fg=red;bg=yellow>{$this->created} total matches created.</>");
        }


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
                $match = $this->toJson($this->client->get('match/v3/matches/' . $matchId));
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

                //Extend time taken
//                $timespent = (microtime(true) - $time);
                sleep(2); // max 50 req/min
            }

            //Display progress
            echo "\r" . ($k+1) . " /$total (" . round(($k+1)/$total*100, 2) . "%)                         ";
        }

        //New line feed to complete progress display
        echo "\n";
        return $matchesDetails;
    }

    public function toJson(Response $r){
        return json_decode($r->getBody(), true);
    }

    public function getMatches($accountId){
        $beginIndex = 0;
        $matches = collect([]);
        $count = 0;
        $season = env('LOL_SEASON_ID');
        $queues = collect($this->queues)->map(function($v){return "queue=$v";})->implode('&');
        while(++$count<100){ //max 10000 matches taken in account
            $result = $this->toJson($this->client->get("match/v3/matchlists/by-account/$accountId?beginIndex=$beginIndex&season=$season&$queues"));
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
