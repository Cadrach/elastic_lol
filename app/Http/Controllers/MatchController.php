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
            'runePaths' => json_decode(file_get_contents(public_path('json/rune-paths.json')), true),
            'summonerSpells' => collect(json_decode(file_get_contents(public_path('json/summoner-spells.json')), true)['data'])->keyBy('id'),
            'urls' => [
                'champion' => "http://ddragon.leagueoflegends.com/cdn/$version/img/champion/",
                'item' => "http://ddragon.leagueoflegends.com/cdn/$version/img/item/",
                'summonerSpell' => "http://ddragon.leagueoflegends.com/cdn/$version/img/spell/",
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
            if($value === "") continue;
            if( ! $this->hasMapping($key, $mappings)) continue; //ignore unknowns
            if( ! is_array($value)){
                $query['bool']['must'][] = ['match' => [$key => $value]];
            }
        }

        $client = ElasticSearchClient::get();

        $params = json_decode('{
            "size": 20,
            "query": ' . json_encode($query) . '
        }');

//        print_r($params);die();

        if(json_last_error()){
            throw new \Exception('JSON SYNTAX ERROR: ' . json_last_error_msg());
        }

        return $client->search([
            'index' => 'lol_participant',
            'type' => 'lol_participant',
            'body' => $params
        ]);
    }

    protected function hasMapping($field, $mappings){
        $parts = explode('.', $field);
        if( ! isset($mappings->{$parts[0]})){
            return false;
        }
        else if(count($parts) > 1){
            $currentField = array_shift($parts);
            $newField = implode('.', $parts);
            return $this->hasMapping($newField, $mappings->{$currentField}->properties);
        }
        else{
            return true;
        }

    }
}
