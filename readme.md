# WP API Idempotence

Allow WordPress REST API clients to specify an idempotency key for API requests. This allows for API clients to safely retry requests in case of network errors without risk of the request being processed ``twice.