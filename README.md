# Kooyara Recommender System PHP SDK

#### Usage
```php
<?php

// Import the RecommenderSystem class.
use Kooyara\RecommenderSystem\RecommenderSystem;

// Instantiate the RecommenderSystem class to gain access to the system.
// $environment is one of ['testing', 'development', 'production']
// $client_id is the OAuth2 client id as configured in the
//    recommnder system backend.
// $client_secret is the OAuth2 client secret as configured
//    in the recommender system backend,.
$rs = new RecommenderSystem($environment, $client_id, $client_secret);
```

Build and review the documentation using phpdoc to see available methods.
