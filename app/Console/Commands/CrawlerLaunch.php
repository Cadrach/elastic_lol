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
//        [20, 1],
//        [100, 125],
//        [3, 10],
        [10000, 600]
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
     * List of complete items
     * @var
     */
    protected $completeItems;

    /**
     * Max level of skills per champ
     * @var
     */
    protected $maxLevelSkills;

    protected $bulkBody = [];

    protected $bulkCount = 0;

    protected $logFile;

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
        $timestart = Carbon::now();
        $maxMatches = intval($this->option('max'));
        $server = trim($this->option('server'));
        $accountId = env('LOL_DEFAULT_ACCOUNT_ID');
        $root = 'https://'.$server.'.api.riotgames.com';
        $this->requests = collect();
        $previousAccounts = collect();

        $this->logFile = storage_path('logs/' . (new \DateTime)->format('Ymd-Hi') . '-crawler.log');

        //Setup list of completed items
        $this->completeItems = collect(json_decode(file_get_contents(public_path('json/items.json')), true)['data'])
            ->filter(function($item){
                return isset($item['depth']) && $item['depth']>=3 && (!isset($item['tags']) || !in_array('Consumable', $item['tags']));
            })
            ->pluck('id')
        ;

        //Setup max level of skills
        $this->maxLevelSkills = collect(json_decode(file_get_contents(public_path('json/champions.json')), true)['data'])
            ->reduce(function($mem, $champ){
                $mem[$champ['id']] = collect($champ['spells'])->pluck('maxrank');
                return $mem;
            }, [])
        ;

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
            if( ! $matches->count() && $previousAccounts->count()){
                //If account did not have any match, but previous account still has some accounts take another one
                $accountId = $previousAccounts->random();
                $previousAccounts = $previousAccounts->diff([$accountId])->diff($treated);
            }
            else if( ! $matches->count() && ! $previousAccounts->count()){
                //If we have no match and no previousAccounts left, use the default
                $accountId = env('LOL_DEFAULT_ACCOUNT_ID'); //reuse our default
            }
            else{
                //Set the list of previousAccounts, and take a new one randomly
                $previousAccounts = $matches->pluck('participantIdentities')->map(function($v){return collect($v)->pluck('player')->pluck('accountId');})->flatten()->unique()->diff($treated);
                $accountId = $previousAccounts->random();
            }

            $this->garbageCollectThrottling();

            $this->line("<fg=red;bg=yellow>".($this->created - $createdBefore)." created ({$this->created} total created, started ".$timestart->diffForHumans().")</>");
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
                $this->log("Throttling (rule $k) {$wait}s");
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
            if($e->getResponse()->getStatusCode() == 503){
                $this->log('Sleeping 5s - Server Exception: ' . $e->getMessage());
                sleep(5);
            }
            else{
                $this->log('Sleeping 60s - Server Exception: ' . $e->getMessage());
                sleep(60);
            }
            return $this->getRiotDataFromUrl($url);
        }
        catch(ClientException $e){
            if($e->getResponse()->getStatusCode() != 404){
                $this->log('Sleeping 10min - ' . count($this->requests ) . ' requests stored currently - Client Exception: ' . $e->getMessage());
                sleep(600);
            }
            else{
                $this->log("404 Not found: $url");
            }
            return false;
        }
        catch(ConnectException $e){
            $this->log('Sleeping 60s - Connect Exception: ' . $e->getMessage());
            sleep(60);
        }
//        catch(ThrottleException $e){
//            //Ignore this match
//            $this->log('Throttling 1s');
//            sleep(1);
//            return $this->getRiotDataFromUrl($url);
//        }

        return json_decode($result->getBody(), true);
    }

    /**
     * @param $accountId
     * @return Collection
     * @throws \Exception
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

                //Fetch timeline
                $timeline = $this->getRiotDataFromUrl('match/v3/timelines/by-match/' . $matchId);

                $participants = $this->aggregateParticipantsData($match, $timeline);

                //
                $matchesDetails->push($match);

                $bulkBody = [
                    ['index'=> [
                        '_index' => 'lol_match',
                        '_type' => 'lol_match',
                        '_id' => $matchId,
                    ]],
                    $match,
                    
//                    ['index'=> [
//                        '_index' => 'lol_timeline',
//                        '_type' => 'lol_timeline',
//                        '_id' => $matchId,
//                    ]],
//                    $timeline
                ];

                foreach($participants as $p){
                    $bulkBody[] = ['index'=> [
                        '_index' => 'lol_participant',
                        '_type' => 'lol_participant',
                        '_id' => $matchId . '_' . $p['participantId'],
                    ]];
                    $bulkBody[] = $p;
                }

                $this->bulkSave($bulkBody);

                //Increment creation counter
                $this->created++;
            }

            //Display progress
            echo "\r" . ($k+1) . "/$total (" . round(($k+1)/$total*100, 2) . "%)                         ";
//            echo $this->line(($k+1) . "/$total (" . round(($k+1)/$total*100, 2) . "%)");
        }

        //New line feed to complete progress display
        echo "\n";
        return $matchesDetails;
    }

    public function bulkSave($bulk){

        $bulkSize = 100;

        foreach($bulk as $b){
            $this->bulkBody[] = $b;
        }
        $this->bulkCount++;

        if($this->bulkCount >= $bulkSize){
            $elastic = ElasticSearchClient::get();

            //
            $elastic->bulk([
                'body' => $this->bulkBody
            ]);

            $info = $elastic->transport->getLastConnection()->getLastRequestInfo();

            $responses = json_decode($info['response']['body'], true);

            if($responses['errors']>0){
                $this->log("Error saving to ElasticSearch:" . count($responses['errors']) . "errors. Sleeping 10min and retry", print_r($responses, true));
                sleep(600);
                $this->bulkSave($bulk);
                return;
            }

            $this->line("Inserted $bulkSize matches, took {$responses['took']}");

            $this->bulkBody = [];
            $this->bulkCount = 0;
        }

    }

    /**
     * Returns list of matches for an account id
     * @param $accountId
     * @return Collection|static
     */
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

    /**
     * Aggregate participants statistics before storage
     * @param $match
     * @param $timeline
     * @return array
     * @throws \Exception
     */
    public function aggregateParticipantsData($match, $timeline){

        //Prepare timeline per participant
        $events = [];
        if(count($timeline['frames'])>0){
            foreach($timeline['frames'] as $frame){
                $ts = $frame['timestamp'];
                foreach($frame['events'] as $event){
                    if(isset($event['participantId'])){
                        $event['timestamp']+= $ts;
                        $pId = $event['participantId'];
                        $type = $event['type'];

                        unset($event['participantId']);
                        unset($event['type']);

                        $events[$pId][$type][] = $event;

                        //Special case of undo: remove previous action of ITEM_SOLD or ITEM_PURCHASED
                        if($type == 'ITEM_UNDO'){
                            if($event['afterId']>0 && $event['beforeId'] == 0) $toRemove = 'ITEM_SOLD';
                            else if($event['afterId'] == 0 && $event['beforeId'] > 0) $toRemove = 'ITEM_PURCHASED';
                            else throw new \Exception('Do not know how to manage this case');

                            if(!isset($events[$pId][$toRemove])){
                                //Ugly but sometimes data seems to be stored wrongly for UNDO
                                $toRemove = $toRemove == 'ITEM_PURCHASED' ? 'ITEM_SOLD':'ITEM_PURCHASED';
                            }

                            //Remove event
                            $removed = array_pop($events[$pId][$toRemove]);

                            //print_r(['rem' => $removed, 'undo' => $event, 'type'=>$toRemove]);
                        }
                    }

                }
            }
        }

        //Prepare identities
        $identities = collect($match['participantIdentities'])->pluck('player', 'participantId');

        //Teams stats
        $teams = collect(collect($match['participants'])->groupBy('teamId')->reduce(function($mem, $parts){
            foreach($parts as $part){
                @$mem[$part['teamId']]['totalDamageDealt']               += $part['stats']['totalDamageDealt'];
                @$mem[$part['teamId']]['magicDamageDealt']               += $part['stats']['magicDamageDealt'];
                @$mem[$part['teamId']]['physicalDamageDealt']            += $part['stats']['physicalDamageDealt'];
                @$mem[$part['teamId']]['trueDamageDealt']                += $part['stats']['trueDamageDealt'];

                @$mem[$part['teamId']]['totalDamageDealtToChampions']    += $part['stats']['totalDamageDealtToChampions'];
                @$mem[$part['teamId']]['magicDamageDealtToChampions']    += $part['stats']['magicDamageDealtToChampions'];
                @$mem[$part['teamId']]['physicalDamageDealtToChampions'] += $part['stats']['physicalDamageDealtToChampions'];
                @$mem[$part['teamId']]['trueDamageDealtToChampions']     += $part['stats']['trueDamageDealtToChampions'];
                @$mem[$part['teamId']]['totalHeal']                      += $part['stats']['totalHeal'];
                @$mem[$part['teamId']]['totalHealer']                    += $part['stats']['totalUnitsHealed']>1 ? $part['stats']['totalHeal']:0;
                @$mem[$part['teamId']]['totalLifeSteal']                 += $part['stats']['totalUnitsHealed']<=1 ? $part['stats']['totalHeal']:0;

                @$mem[$part['teamId']]['totalDamageTaken']               += $part['stats']['totalDamageTaken'];
                @$mem[$part['teamId']]['magicalDamageTaken']             += $part['stats']['magicalDamageTaken'];
                @$mem[$part['teamId']]['physicalDamageTaken']            += $part['stats']['physicalDamageTaken'];
                @$mem[$part['teamId']]['trueDamageTaken']                += $part['stats']['trueDamageTaken'];
            }

            return $mem;
        }, []))->map(function($team){
            $team['percentMagicDamageDealt'] = $team['totalDamageDealt'] ? $this->percent($team['magicDamageDealt'] / $team['totalDamageDealt']):0;
            $team['percentPhysicalDamageDealt'] = $team['totalDamageDealt'] ? $this->percent($team['physicalDamageDealt'] / $team['totalDamageDealt']):0;
            $team['percentTrueDamageDealt'] = $team['totalDamageDealt'] ? $this->percent($team['trueDamageDealt'] / $team['totalDamageDealt']):0;

            $team['percentMagicDamageDealtToChampions'] = $team['totalDamageDealtToChampions'] ? $this->percent($team['magicDamageDealtToChampions'] / $team['totalDamageDealtToChampions']):0;
            $team['percentPhysicalDamageDealtToChampions'] = $team['totalDamageDealtToChampions'] ? $this->percent($team['physicalDamageDealtToChampions'] / $team['totalDamageDealtToChampions']):0;
            $team['percentTrueDamageDealtToChampions'] = $team['totalDamageDealtToChampions'] ? $this->percent($team['trueDamageDealtToChampions'] / $team['totalDamageDealtToChampions']):0;

            $team['percentTotalHeal'] = $team['totalDamageTaken'] ? $this->percent($team['totalHeal'] / $team['totalDamageTaken']):0;
            $team['percentMagicalDamageTaken'] = $team['totalDamageTaken'] ? $this->percent($team['magicalDamageTaken'] / $team['totalDamageTaken']):0;
            $team['percentPhysicalDamageTaken'] = $team['totalDamageTaken'] ? $this->percent($team['physicalDamageTaken'] / $team['totalDamageTaken']):0;
            $team['percentTrueDamageTaken'] = $team['totalDamageTaken'] ? $this->percent($team['trueDamageTaken'] / $team['totalDamageTaken']):0;

            $team['damageType'] = 'MIXED';
            if($team['percentMagicDamageDealtToChampions']>60){$team['damageType'] = 'AP';}
            if($team['percentPhysicalDamageDealtToChampions']>60){$team['damageType'] = 'AD';}

            return $team;
        });

        //Now we loop on each participant to aggregate information
        $participants = [];
        $completeItems = $this->completeItems;
        foreach($match['participants'] as $part){

            $pId = $part['participantId'];
            $teamId = $part['teamId'];
            $stats = $part['stats'];
            unset($part['stats']);
            $part = array_merge($part, $stats);
            $maxLevelSkills = $this->maxLevelSkills[$part['championId']];
            $teamIdEnemy = $teamId == 100 ? 200:100;

            //Game information
            $part['gameId'] = $match['gameId'];
            $part['patchVersion'] = floatval($match['gameVersion']);
            $part['gameVersion'] = $match['gameVersion'];
            $part['platformId'] = $match['platformId'];
            $part['gameCreation'] = $match['gameCreation'];
            $part['gameDuration'] = $match['gameDuration'];
            $part['queueId'] = $match['queueId'];
            $part['mapId'] = $match['mapId'];
            $part['seasonId'] = $match['seasonId'];
            $part['gameMode'] = $match['gameMode'];
            $part['gameType'] = $match['gameType'];

            //Identity
            $part['identity'] = $identities[$pId];

            //Versus & With
            $part['playVersus'] = collect($match['participants'])->filter(function($p) use ($teamId){return $teamId != $p['teamId'];})->pluck('championId');
            $part['playWith'] = collect($match['participants'])->filter(function($p) use ($teamId, $pId){return $teamId == $p['teamId'] && $p['participantId'] != $pId;})->pluck('championId');

            //Events
            $part['events'] = [];
            $part['itemBuildOrder'] = [];
            $part['skillOrder'] = [];
            if(isset($events[$pId])){
                $part['events'] =  $events[$pId];

                //Completed items (build order)
                if(isset($events[$pId]['ITEM_PURCHASED'])) {
                    $part['itemBuildOrder'] = collect($events[$pId]['ITEM_PURCHASED'])->reduce(function ($mem, $e) use ($completeItems) {
                        if ($completeItems->contains($e['itemId'])) {
                            $mem['item' . count($mem)] = $e['itemId'];
                        }
                        return $mem;
                    }, []);
                }

                //Skill order
                if(isset($events[$pId]['SKILL_LEVEL_UP'])){
                    $leveled = [];
                    $part['skillOrder'] = collect($events[$pId]['SKILL_LEVEL_UP'])->reduce(function($mem, $e) use($maxLevelSkills, &$leveled){
                        $slot = $e['skillSlot'] - 1;
                        @$leveled[$slot]++;
                        if($leveled[$slot] == $maxLevelSkills[$slot]-1){
                            $mem['skill' . count($mem)] = $slot;
                        }
                        return $mem;
                    }, []);
                }
            }

            //Teams
            $part['team'] = $teams[$teamId];
            $part['enemyTeam'] = $teams[$teamIdEnemy];

            //Damage dealt
            $part['percentMagicDamageDealtToChampions'] = $part['totalDamageDealtToChampions'] ? $this->percent($part['magicDamageDealtToChampions'] / $part['totalDamageDealtToChampions']):0;
            $part['percentPhysicalDamageDealtToChampions'] = $part['totalDamageDealtToChampions'] ? $this->percent($part['physicalDamageDealtToChampions'] / $part['totalDamageDealtToChampions']):0;
            $part['percentTrueDamageDealtToChampions'] = $part['totalDamageDealtToChampions'] && isset($part['trueDamageDealtToChampions']) ? $this->percent($part['trueDamageDealtToChampions'] / $part['totalDamageDealtToChampions']):0;
            
            //Damage type
            $part['damageType'] = 'MIXED';
            if($part['percentMagicDamageDealtToChampions']>60){$part['damageType'] = 'AP';}
            if($part['percentPhysicalDamageDealtToChampions']>60){$part['damageType'] = 'AD';}
            
            //
            $participants[] = $part;
        }

        return $participants;
    }

    protected function percent ($v){
        return round($v * 100, 2);
    }

    protected function log($text, $more = null){
        $time = (new \DateTime)->format('Y/m/d-H:i:s');
        $this->error($text);
        file_put_contents($this->logFile, "$time - $text\n", FILE_APPEND);
        if($more){
            file_put_contents($this->logFile, "$time - $more\n", FILE_APPEND);
        }
    }
}
