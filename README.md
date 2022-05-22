# Matcher microservice Case Study
---

# Table of Contents
  - [Known Issues](#known-issues)
  - [Requirements & Installation](#requirements-installation)
  - [Tests](#Tests)
  - [Match API](#match-api)
---

# Known Issues
- This project is not pushed to a live website
- Reduce complexity of queries by using ELASTICSEARCH
- Filter by direct fields is yet to be implemented
- Write unit tests
---

# Requirements & Installation
- PHP 7.4
- Composer 2.0+
- Mysql 8.x
- Repository link
  - https://github.com/skthon/reo
- Installation and commands
  - Clone the code repository
    ```
    gh repo clone skthon/reo
    git checkout feature/search-profile-matcher-api
    ```
  - After cloning, run the composer command to install packages
    ```
       composer install
    ```
  - Install mysql and configure the database in .env 
    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=reo
    DB_USERNAME=reo
    DB_PASSWORD=reopass
    ```
  - To create local and local testing database, run the below command
    ```
    php artisan database:create
    ``` 
  - Run Migrations
    ```
    php artisan migrate
    ```
  - Setup demo data by executing this code from tinker. Ideally this should be done using database seeders
    ```
    php artisan tinker

    # Create PropertyType model record
    $propertyType = \App\Models\PropertyType::create(['name' => 'BUILDING']);

    # Create Property model record
    $property = \App\Models\Property::create([
        'name' => 'Awesome house in the middle of my town',
        'address' => 'Main street 17, 12456 Berlin',
        'price' => 1500000,
        "area" => "180",
        "year_of_construction" => "2010",
        "rooms" => "5",
        "heating_type" => "gas",
        "parking" => true,
        "return_actual" => "12.8"
    ]);
    $property->property_type()->associate($propertyType);
    $property->save();

    # Create Search Profile record
    \App\Models\SearchProfile::create([
        'name' => "Looking for any Awesome real estate!",
        'min_price' => 0,
        'max_price' => 2000000,
        'min_area' => 150,
        'max_area' => null,
        'min_year_of_construction' => 2010,
        'max_year_of_construction' => null,
        'min_rooms' => 4,
        'max_rooms' => null,
        'min_return_actual' => 15,
        'max_return_actual' => null
    ]);
    ```
---


# Tests
$ php artisan test
```
   PASS  Tests\Feature\MatchApiTest
  ✓ responds with an error if invalid api endpoint is provided
  ✓ responds with an error if incremental property id is provided
  ✓ responds with an error if non existing property id is provided
  ✓ responds with an error when unfilled property is provided
  ✓ responds with empty results when no search profiles are created
  ✓ responds with empty results when search profiles are created with exclusive filter
  ✓ responds with empty results when search profiles min filters is null and max filters is null
  ✓ responds with results when search profiles are created with inclusive filters
  ✓ responds with loose results when search profiles are created with slightly exclusive filters
  ✓ responds with empty results when search profiles min filters is null and max filters is exclusive
  ✓ responds with results when search profiles min filters is null and max filters is inclusive
  ✓ responds with loose results when search profiles min filters is null and slightly exclusive max filters
  ✓ responds with empty results when search profiles min filters is exclusive and max filters is null
  ✓ responds with results when search profiles min filters is inclusive and max filters is null
  ✓ responds with loose results when search profiles min filters is slightly exclusive and max filters is null
  ✓ responds with results sorted by score

   PASS  Tests\Feature\MatchApiPriceFilterTest
  ✓ responds with empty results when search profiles are created with exclusive price filter
  ✓ responds with empty results when search profiles min price is null and max price is null
  ✓ responds with results when search profiles are created with inclusive price filter
  ✓ responds with loose results when search profiles are created with slightly exclusive price filter
  ✓ responds with empty results when search profiles min price is null and max price is exclusive
  ✓ responds with results when search profiles min price is null and max price is inclusive
  ✓ responds with loose results when search profiles min price is null and slightly exclusive max price
  ✓ responds with empty results when search profiles min price is exclusive and max price is null
  ✓ responds with results when search profiles min price is inclusive and max price is null
  ✓ responds with loose results when search profiles min price is slightly exclusive and max price is null

  Tests:  26 passed
  Time:   2.02s
```

# Match API

- GET request
- URLS
  - http://localhost:8081/api/match/<property_id>
- Accepted parameters
  - `property_id`
    - Accepts valid property UUID, This doesn't accept id since its bad to expose primary key to the user
- Response Body
```json
{
    "property": {
        "id": 1,
        "uuid": "964e8976-89cf-4bc6-af10-06e8384a0d6f",
        "user_uuid": null,
        "property_type_uuid": "964e896a-98cb-4c73-b0e3-8ef6a867dcf4",
        "name": "Awesome house in the middle of my town",
        "address": "Main street 17, 12456 Berlin",
        "price": 1500000,
        "area": 180,
        "year_of_construction": 2010,
        "rooms": 5,
        "heating_type": "gas",
        "parking": true,
        "return_actual": "12.80",
        "status": true,
        "created_at": "2022-05-15T18:49:38.000000Z",
        "updated_at": "2022-05-15T18:49:47.000000Z"
    },
    "matching_profiles": [{
        "searchProfileId": "964e898d-cf87-4edf-995e-dfab43a752ed",
        "score": "4.0",
        "strictMatchesCount": 3,
        "looseMatchesCount": 2
    }]
}
```

- Errors
    - Internal error with 500 response
    - Invalid input with 400 response
---
