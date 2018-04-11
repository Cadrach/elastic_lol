<?php

namespace App\Http\Controllers;

use App\Models\ElasticSearchClient;

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

    public function participants(){
        if( ! env('ELASTICSEARCH_HOST')) {
            return json_decode(file_get_contents(public_path('json/test-participants.json')), true);
        }

        $client = ElasticSearchClient::get();

        $params = json_decode('{
            "size": 20,
            "query": {
                "bool": {
                    "must": [
                        {"match": {"championId": 56}},
                        {"match": {"win": true}}
                    ]
                }
            }
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
