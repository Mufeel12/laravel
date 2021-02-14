## Amazon parser.

Usage:
```
AmazonUrl::read($url);
```

```$url``` can be single listing or seller page. If ```$url``` is seller page, grab first 8 items and process them. 
In this case return Collection.

Return single AmazonListing class or Collection.

AmazonListing and Collection implements array access interface.

### toArray()

Convert class to array.

### toJson()

Convert class to json