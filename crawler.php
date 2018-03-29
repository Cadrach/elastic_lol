<?php
include 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

function toJson(Response $r){
    return json_decode($r->getBody(), true);
}

function getMatches(Client $client, $accountId){
    $beginIndex = 0;
    $matches = collect([]);
    $count = 0;
    $season = getenv('SEASON_ID');
    while(++$count<100){ //max 10000 matches taken in account
        $result = toJson($client->get("match/v3/matchlists/by-account/$accountId?beginIndex=$beginIndex&season=$season"));
        $matches = $matches->merge($result['matches']);

        //Loop to retrieve all matches
        if($result['endIndex'] < $result['totalGames'] && count($result['matches'])){
            $beginIndex = $result['endIndex'];
        }
        else{
            break;
        }
    }
    return $matches;
}

//Load config
(new Dotenv\Dotenv(__DIR__))->load();

$summoner = $_GET['s'];

$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://euw1.api.riotgames.com/lol/',
    // You can set any number of default request options.
    'timeout'  => 10.0,
    'verify' => false, //do not check certificates
    'headers' => [
        'X-Riot-Token' => getenv('API_KEY'),
    ]
]);

echo '<pre>';

$accountId = toJson($client->get('summoner/v3/summoners/by-name/' . $summoner))['accountId'];

$matches = getMatches($client, $accountId);

//
echo '>>>>>>>>>> ' . count($matches) . " matches retrieved\n";


$matchId = $matches->first()['gameId'];
$match = toJson($client->get('match/v3/matches/' . $matchId));

print_r(json_encode($match));