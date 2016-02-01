# Vary, what?

Supports HTTP Cache control using the ```Vary``` header from within WP.

# Why?

Because your cache (i.e. mod_cache, varnish, etc) shouldn't be making up for a poorly build application.

The application is the only thing that can correctly declare the cache validity and cache key for any given response.

# How?

Finds cookies by name and adds them as ```X-``` headers and includes a ```Vary``` header.

# What next?

Allow for configurable header and cookie matching.
