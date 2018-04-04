<?php

namespace App\Http\Controllers;

use App\Models\ElasticSearchClient;

class MatchController extends Controller
{
    public function search(){
		if( ! env('ELASTICSEARCH_HOST')){
			return json_decode(file_get_contents(resource_path('assets/json/test-matches.json')), true);
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
}
