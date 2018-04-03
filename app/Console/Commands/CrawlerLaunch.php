<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class CrawlerLaunch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:launch {server=euw1 : br1, eun1, euw1, jp1, kr, la1, la2, na1, oc1, tr1, ru, pbe1}';

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
        $server = trim($this->argument('server'));

        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://'.$server.'.api.riotgames.com/lol/',
            // You can set any number of default request options.
            'timeout'  => 10.0,
            'verify' => false, //do not check certificates
            'headers' => [
                'X-Riot-Token' => env('LOL_API_KEY'),
            ]
        ]);

        $accountId = env('LOL_DEFAULT_ACCOUNT_ID');

        $matches = $this->getMatches($accountId);
    }

    public function toJson(Response $r){
        return json_decode($r->getBody(), true);
    }

    public function getMatches($accountId){
        $beginIndex = 0;
        $matches = collect([]);
        $count = 0;
        $season = env('LOL_SEASON_ID');
        while(++$count<100){ //max 10000 matches taken in account
            $result = $this->toJson($this->client->get("match/v3/matchlists/by-account/$accountId?beginIndex=$beginIndex&season=$season"));
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
