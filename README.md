edgegrid-php
============
php library for Akamai EdgeGrid Client Authentication


Summary
-------

Abstraction php class to handle new Akamai REST API using Akamai EdgeGrid Client Authentification.


Usage 
-----

- put your data in conf.php
- include the AkamaiRestApi class



Example 
-------

  
    include('AkamaiRestApi/AkamaiRestApi.php');
    
    $api = new AkamaiRestApi();

    $response = $api->call('GET', '/billing-usage/v1/reportSources');


with parameters:

    $response = $api->call(
        'POST', 
        '/billing-usage/v1/products',
        [
            'reportSources'=>$reportSources,
            'startDate'=>$date, 
            'endDate'=>$date
        ]);

