<?php

namespace App\Http\Controllers;

use App\Models\ElasticSearchClient;
use Illuminate\Support\Facades\Input;

class MatchController extends Controller
{
    public function search(){

		if( ! env('ELASTICSEARCH_HOST')){
			return json_decode(file_get_contents(public_path('json/test-matches.json')), true);
		}
	
        $client = ElasticSearchClient::get();

        return $client->search([
            'index' => 'lol_match',
            'type' => 'lol_match',
            'body' => json_decode('{
              "size": 5,
              "query": {
                "function_score": {
                  "query": {
                    "match_all": {}
                  },
                  "functions": [
                    {
                      "random_score": {}
                    }
                  ]
                }
              }
            }')
        ]);
    }

    public function dictionnaries(){
        $champions = json_decode(file_get_contents(public_path('json/champions.json')), true);
        $version = $champions['version'];
        return [
            'version' => $version,
            'items' => json_decode(file_get_contents(public_path('json/items.json')), true)['data'],
            'champions' => $champions['data'],
            'runes' => json_decode(file_get_contents(public_path('json/runes.json')), true),
            'urls' => [
                'champion' => "http://ddragon.leagueoflegends.com/cdn/$version/img/champion/"
            ],
        ];
    }

    public function participants(){
        if( ! env('ELASTICSEARCH_HOST')) {
            return json_decode(file_get_contents(public_path('json/test-participants.json')), true);
        }

        $mappings = json_decode(file_get_contents(base_path('elasticsearch/lol_participant.mapping.json')))
            ->lol_participant->mappings->lol_participant->properties;

        $filters = $this->getPayload();

        if( ! count($filters)){
            return [];
        }

        $query = [
            'bool' => ['must' => []],
        ];
        foreach($filters as $key=>$value){
            if( ! isset($mappings->{$key})) continue; //ignore unknowns
            if( ! is_array($value)){
                $query['bool']['must'][] = ['match' => [$key => $value]];
            }
        }

        $client = ElasticSearchClient::get();

        $params = json_decode('{
            "size": 20,
            "query": ' . json_encode($query) . '
        }');

        if(json_last_error()){
            throw new \Exception('JSON SYNTAX ERROR: ' . json_last_error_msg());
        }

        return $client->search([
            'index' => 'lol_participant',
            'type' => 'lol_participant',
            'body' => $params
        ]);
    }
}
