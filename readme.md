## Environnement
#### ElasticSearch
Use ElasticSearch + Docker:
https://www.elastic.co/guide/en/elasticsearch/reference/current/docker.html

Run server using 
```sh
docker run -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:6.2.3
```

Install elasticdump for backups
```sh
npm install elasticdump -g
```

#### PHP
Use PHP >=7.1

## Installation

```sh
composer install
```

```sh
npm install
```

```sh
php artisan key:generate
```

## Development

Run server on localhost:8000
```sh
php artisan serve
```

Watch changes on js to compile react
```sh
npm run watch
```

Backup ElasticSearch
```sh
elasticdump --input=http://127.0.0.1:9200/lol_match --output=180405_dump.json
```
