# WP API Idempotence

[![Build Status](https://travis-ci.org/iron-bound-designs/wp-api-idempotence.svg?branch=master)](https://travis-ci.org/iron-bound-designs/wp-api-idempotence) ![PHP 5.4](https://img.shields.io/badge/PHP-5.4-lightgrey.svg)

Allow WordPress REST API clients to specify an idempotency key for API requests. This allows for API clients to safely retry requests in case of network errors without risk of the request being processed twice.

## Description

When a network error is encountered, API clients should be able to retry a request without a risk of their request being processed twice.
WP API Idempotence adds support for clients to include an idempotency key that uniquely identifies that request. If the server detects
that a request with the same key has already been processed or is currently being processed, the response for the initial request will
be returned.

For Example:

```json
{
    "title": "My Important Post",
    "content": "This will only go out once!",
    "status": "draft",
    "idempotency_key": "1ced64e9-9537-4b7b-9919-444d9e15e201"
}
```

### Configuration
* Idempotency key can either be passed in the request header or the request body.
* The idempotency key name can be customized.
* Change the HTTP methods the idempotency key is supported for. Defaults to `POST`, `PUT`, `PATCH`.

A sample request interface is included to demonstrate the selected configuration.

### Developers

The plugin includes two actions to modify the dependency injection container (DIC) and insert custom services.

The `wp_api_idempotence_initialize_container_builder` action allows you to modify the Dependency Injection builder itself and
the `wp_api_idempotence_initialize_container` action allows you to override dependencies. For example:

```php
add_action( 'wp_api_idempotence_initialize_container', function( $container ) {
    $container->set( '_dataStore', DI\object( 'YourName\CustomDataStore' ) );
} );
```

Under the hood, the plugin is made up of a `DataStore`, `RequestHasher`, `ResponseSerializer` and `RequestPoller`.

The `DataStore` is primarily responsible for retrieving or storing an idempotent request. By default, requests
are stored in a custom database table. This could be substituted for a custom driver by implementing the `DataStore`
interface. For example a Redis server.

The `RequestHasher` produces a unique hash for a `WP_REST_Request` object. This hash is based off of the contents of
the request, not for the object via `spl_object_hash` or similar. This can also be substituted by implementing the
`RequestHasher` interface.

The `ResponseSerializer` converts a `WP_REST_Response` or `WP_Error` object back and forth from a string representation.
The default JSON serializer supports filtering the serialization process using the `wp_api_idempotence_serialized_response_data`
and `wp_api_idempotence_attach_serialized_response_data` filters. See `src/ResponseSerializer/Filtered.php`. The entire serializer can be substituted by implementing
the `ResponseSerializer` interface.

Finally, the `RequestPoller` class polls the data store for a response if it is determined that an idempotent request
is currently being processed when another request with the same key arrives. By default, the data store is polled
every seconds a maximum of 15 times to try and retrieve a response object. If no response is found, an error with code
`rest_duplicate_idempotency_key` will be returned. This can be adjusted by overwriting the `poll.sleepSeconds` and
`poll.maxQueries` in the DIC. The `RequestPoller` can also be entirely subsituted by implementing the `RequestPoller`
interface.


## Installation

1. Download the plugin from [GitHub](https://github.com/iron-bound-designs/wp-api-idempotence/archive/master.zip).
2. Install dependencies with `composer install --no-dev`.
3. Remove unnecessary files with `./bin/clean.sh`.
4. Upload the plugin files to the `/wp-content/plugins/wp-api-idempotence` directory.
5. Activate the plugin through the 'Plugins' screen in WordPress
6. Use the Settings -> WP API Idempotence screen to modify the idempotency key location or name
7. Ensure the plugin is working by using the "Sample Requests" section.