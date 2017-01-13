# Find ’n Cite

A simple bibliographic database to aggregate biodiversity literature.

## Implementation ideas

CouchDB

Cloundant

ElasticSearch

## Services needed

### Fulltext search on metadata

### Match article based on bibliographic string

### Extract list of cited literature

From XML

From HTML

### Given URL for article extract DOI and/or other metadata

#### meta tags
For example, use meta tags to get details of article

- https://www.jstage.jst.go.jp/article/asjaa1936/7/1/7_1_31/_article has DOI 10.2476/asjaa.7.31 but metadata from CrossRef lacks title (it’s in Japanese). Can get title (北海道産一新トタテグモの記載) from meta tags on web page for article.

```javascript
{“status”:”ok”,”message-type”:”work”,”message-version”:”1.0.0”,”message”:{“indexed”:{“date-parts”:[[2016,10,24]],”date-time”:”2016-10-24T21:04:47Z”,”timestamp”:1477343087990},”reference-count”:0,”publisher”:”Arachnological Society of Japan”,”issue”:”1”,”content-domain”:{“domain”:[],”crossmark-restriction”:false},”short-container-title”:[“Acta Arachnologica”],”published-print”:{“date-parts”:[[1942]]},”DOI”:”10.2476\/asjaa.7.31”,”type”:”journal-article”,”created”:{“date-parts”:[[2009,11,9]],”date-time”:”2009-11-09T05:10:28Z”,”timestamp”:1257743428000},”page”:”31-35”,”source”:”CrossRef”,”title”:[“”],”prefix”:”http:\/\/id.crossref.org\/prefix\/10.2476”,”volume”:”7”,”member”:”http:\/\/id.crossref.org\/member\/1314”,”container-title”:[“Acta Arachnologica”],”original-title”:[],”deposited”:{“date-parts”:[[2009,11,9]],”date-time”:”2009-11-09T05:10:41Z”,”timestamp”:1257743441000},”score”:1.0,”subtitle”:[],”short-title”:[],”issued”:{“date-parts”:[[1942]]},”URL”:”http:\/\/dx.doi.org\/10.2476\/asjaa.7.31”,”ISSN”:[“0001-5202”],”subject”:[“Ecology, Evolution, Behavior and Systematics”]}}
```
	
#### Parse URL structure to retrieve DOI

#### Screen scrape to get identifier

### Match articles based on metadata, e.g. hashing numbers

### OpenURL

### Provide exports

CiteProc JSON

RDF

RIS

## Replication

```
curl http://localhost:5984/_replicate -H 'Content-Type: application/json' -d '{ "source": "findncite", "target": "https://<username>:<password>@rdmpage.cloudant.com/findncite" }'
```

