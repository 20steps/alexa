vcl 4.0;
## Based on: https://github.com/mattiasgeniar/varnish-4.0-configuration-templates/blob/master/default.vcl
# Corrected & improved for 4.0.2 by jnerin@gmail.com
import std;
import directors;

include "badbot.vcl";

backend alexa1 {
        .host = "192.168.16.3";
        .port = "8080";

        .max_connections = 300; # That's it
        .first_byte_timeout     = 300s;   # How long to wait before we receive a first byte from our backend?
        .connect_timeout        = 5s;     # How long to wait for a backend connection?
        .between_bytes_timeout  = 2s;     # How long to wait between bytes received from our backend?

#        .probe = {
#	       .request =
#                 "GET /robots.txt HTTP/1.1"
#                 "Host: admin.platform.bricks.20steps.de"
#                 "Connection: close";
#            .interval = 5s;
#            .timeout = 1s;
#            .window = 5;
#            .threshold = 3;
#        }
}

#backend alexa2 {
#        .host = "192.168.13.20";
#        .port = "8080";
#
#        .max_connections = 300; # That's it
#        .first_byte_timeout     = 300s;   # How long to wait before we receive a first byte from our backend?
#        .connect_timeout        = 5s;     # How long to wait for a backend connection?
#        .between_bytes_timeout  = 2s;     # How long to wait between bytes received from our backend?
#
#        .probe = {
#            .request =
#                 "GET /robots.txt HTTP/1.1"
#                 "Host: admin.platform.bricks.20steps.de"
#                 "Connection: close";
#            .interval = 5s;
#            .timeout = 1s;
#            .window = 5;
#            .threshold = 3;
#        }
#}


#backend alexaj1 {
#        .host = "192.168.14.17";
#        .port = "8080";
#
#        .max_connections = 300; # That's it
#        .first_byte_timeout     = 300s;   # How long to wait before we receive a first byte from our backend?
#        .connect_timeout        = 5s;     # How long to wait for a backend connection?
#        .between_bytes_timeout  = 2s;     # How long to wait between bytes received from our backend?
#
#        .probe = {
#            .request =
#                 "GET /robots.txt HTTP/1.1"
#                 "Host: admin.platform.bricks.20steps.de"
#                 "Connection: close";
#            .interval = 5s;
#            .timeout = 1s;
#            .window = 5;
#            .threshold = 3;
#        }
#}

acl invalidators {
  # ACL we'll use later to allow purges
  "localhost";
  "127.0.0.1";
  "::1";
  "192.168.0.0"/16;
}

sub vcl_init {
# Called when VCL is loaded, before any requests pass through it. Typically used to initialize VMODs.

  include "director.vcl";

}

sub vcl_recv {
# Called at the beginning of a request, after the complete request has been received and parsed. Its purpose is to decide whether or not to serve the request, how to do it, and, if applicable, which backend to use.
# also used to modify the request

  call badbot;

  ## REQUEST NORMALIZATION
  # Normalize the header, remove the port (in case you're testing this on various TCP ports)
  set req.http.Host = regsub(req.http.Host, ":[0-9]+", "");

  # Normalize the query arguments except for phpmyadmin
  if (req.http.Host ~ "phpmyadmin") {
     # no sorting here
  } else if (req.url !~ "wp-admin") {
    set req.url = std.querysort(req.url);
  }

  if (req.restarts == 0) {
    if (req.http.X-Forwarded-For) { # set or append the client.ip to X-Forwarded-For header
      set req.http.X-Forwarded-For = req.http.X-Forwarded-For + ", " + client.ip;
    } else {
      set req.http.X-Forwarded-For = client.ip;
    }
  }

  # For mod_pagespeed (cp. https://developers.google.com/speed/pagespeed/module/downstream-caching)
  # Tell PageSpeed not to use optimizations specific to this request.
  set req.http.PS-CapabilityList = "fully general optimizations only";

  # Don't allow external entities to force beaconing.
  unset req.http.PS-ShouldBeacon;

  ## letsencrypt
  if (req.url ~ "acme-challenge") {
     return (pipe);
  }

  ## http to https

  if (
     (req.http.Host ~ "alexa.20steps.de")
      && req.http.X_FORWARDED_PROTO !~ "(?i)https") {
    if (
      # req.url !~ "wp-admin"
      # && req.url !~ "wp-login"
      # && req.url !~ "secure"
      # && req.url !~ "well-known"
      req.url !~ "well-known"
      ) {
        return (synth(750, ""));
     }
  }

  ## HOST MODIFICATION AND BACKEND SELECTION

  # default backend director
  set req.backend_hint = zone.backend(); # send all traffic to the apache_director

  # direct access to backend depending on Header
  if (req.http.X-Server-Select == "alexa1") {
     set req.backend_hint = alexa1;
  } else if (req.http.X-Server-Select == "alexa2") {
     # set req.backend_hint = alexa2;
  } else if (req.http.X-Server-Select == "alexaj1") {
     #set req.backend_hint = alexaj1;
  }

  if (req.http.Host ~ "^job\.") {
     #set req.backend_hint = alexaj1;
  }


  # modify host and/or backend depending on url for further processing

  # Normalize Accept-Encoding header
  # straight from the manual: https://www.varnish-cache.org/docs/3.0/tutorial/vary.html
  # TODO: Test if it's still needed, Varnish 4 now does this by itself if http_gzip_support = on
  # https://www.varnish-cache.org/docs/trunk/users-guide/compression.html
  # https://www.varnish-cache.org/docs/trunk/phk/gzip.html
  if (req.http.Accept-Encoding) {
    if (req.url ~ "\.(avi|bmp|bz2|cssgz|doc|divx|eot|flv|gif|gz|ico|jpeg|jpg|jsgz|mp3|mp4|mpg|mpeg|mk[av]|mov|ogg|ogv|pdf|png|qt|rar|swf|tbz|woff|xz|zip|webp)$") {
      # No point in compressing these
      unset req.http.Accept-Encoding;
    } elsif (req.http.Accept-Encoding ~ "gzip") {
      set req.http.Accept-Encoding = "gzip";
    } elsif (req.http.Accept-Encoding ~ "deflate") {
      set req.http.Accept-Encoding = "deflate";
    } else {
      # unkown algorithm
      unset req.http.Accept-Encoding;
    }
  }


  ## HANDLING DEPENDING ON HTTP METHOD

  # Provide PURGE HTTP method
  if (req.method == "PURGE") {
    if (!client.ip ~ invalidators) { # purge is the ACL defined at the begining
      # Not from an allowed IP? Then die with an error.
      return (synth(405, "Not allowed"));
    }
    # If you got this stage (and didn't error out above), purge the cached result
    return (purge);
  }

  if (req.method == "BAN") {
     if (!client.ip ~ invalidators) {
         return (synth(405, "Not allowed"));
      }

      if (req.http.X-Cache-Tags) {
            ban("obj.http.X-Host ~ " + req.http.X-Host
                + " && obj.http.X-Url ~ " + req.http.X-Url
                + " && obj.http.content-type ~ " + req.http.X-Content-Type
                + " && obj.http.X-Cache-Tags ~ " + req.http.X-Cache-Tags
            );
      } else {
	    ban("obj.http.X-Host ~ " + req.http.X-Host
                + " && obj.http.X-Url ~ " + req.http.X-Url
         	+ " && obj.http.content-type ~ " + req.http.X-Content-Type
      	    );
      }

      return (synth(200, "Banned"));
  }

  

  # Only deal with "normal" types
  if (req.method != "GET" &&
      req.method != "HEAD" &&
      req.method != "PUT" &&
      req.method != "POST" &&
      req.method != "TRACE" &&
      req.method != "OPTIONS" &&
      req.method != "PATCH" &&
      req.method != "DELETE") {
    /* Non-RFC2616 or CONNECT which is weird. */
    return (pipe);
  }

  # Implementing websocket support (https://www.varnish-cache.org/docs/4.0/users-guide/vcl-example-websockets.html)
  if (req.http.Upgrade ~ "(?i)websocket") {
    return (pipe);
  }

  # Only cache GET or HEAD requests. This makes sure the POST requests are always passed.
  if (req.method != "GET" && req.method != "HEAD") {
    return (pass);
  }

  ## COOKIE HANDLING

  # Some generic URL manipulation, useful for all templates that follow
  # First remove the Google Analytics added parameters, useless for our backend
  if (req.url ~ "(\?|&)(utm_source|utm_medium|utm_campaign|utm_content|gclid|cx|ie|cof|siteurl)=") {
    set req.url = regsuball(req.url, "&(utm_source|utm_medium|utm_campaign|utm_content|gclid|cx|ie|cof|siteurl)=([A-z0-9_\-\.%25]+)", "");
    set req.url = regsuball(req.url, "\?(utm_source|utm_medium|utm_campaign|utm_content|gclid|cx|ie|cof|siteurl)=([A-z0-9_\-\.%25]+)", "?");
    set req.url = regsub(req.url, "\?&", "?");
    set req.url = regsub(req.url, "\?$", "");
  }

  # Strip hash, server doesn't need it.
  if (req.url ~ "\#") {
    set req.url = regsub(req.url, "\#.*$", "");
  }

  # Strip a trailing ? if it exists
  if (req.url ~ "\?$") {
    set req.url = regsub(req.url, "\?$", "");
  }

  # Some generic cookie manipulation, useful for all templates that follow
  # Remove the "has_js" cookie
  set req.http.Cookie = regsuball(req.http.Cookie, "has_js=[^;]+(; )?", "");

  # Remove any Google Analytics based cookies
  set req.http.Cookie = regsuball(req.http.Cookie, "__utm.=[^;]+(; )?", "");
  set req.http.Cookie = regsuball(req.http.Cookie, "_ga=[^;]+(; )?", "");
  set req.http.Cookie = regsuball(req.http.Cookie, "utmctr=[^;]+(; )?", "");
  set req.http.Cookie = regsuball(req.http.Cookie, "utmcmd.=[^;]+(; )?", "");
  set req.http.Cookie = regsuball(req.http.Cookie, "utmccn.=[^;]+(; )?", "");

  # Remove DoubleClick offensive cookies
  set req.http.Cookie = regsuball(req.http.Cookie, "__gads=[^;]+(; )?", "");

  # Remove the Quant Capital cookies (added by some plugin, all __qca)
  set req.http.Cookie = regsuball(req.http.Cookie, "__qc.=[^;]+(; )?", "");

  # Remove the AddThis cookies
  set req.http.Cookie = regsuball(req.http.Cookie, "__atuv.=[^;]+(; )?", "");

  # Remove a ";" prefix in the cookie if present
  set req.http.Cookie = regsuball(req.http.Cookie, "^;\s*", "");

  # Are there cookies left with only spaces or that are empty?
  if (req.http.cookie ~ "^\s*$") {
    unset req.http.cookie;
  }

  ## Allow for refreshing using <shift>+<reload> in browser
  if (req.http.Cache-Control ~ "(?i)no-cache" && client.ip ~ invalidators) { 
    if (! (req.http.Via || req.http.User-Agent ~ "(?i)bot" || req.http.X-Purge)) {
      #set req.hash_always_miss = true; # Doesn't seems to refresh the object in the cache
      #return(purge); # Couple this with restart in vcl_purge and X-Purge header to avoid loops
    }
  }

  ## DO NOT CACHE APPLE APP SITE ASSOCIATIONS
  if (req.url ~ "apple-app-site-association") {
     return (pass);
  }

  ## DO NOT CACHE DEPENDING ON HOST/URL

  # do not cache depending on host/url
  if (
    req.url ~ "^/(wp-admin|admin)"
    || req.url ~ "(secure)"
    || req.url ~ "(raten|rechner|eAntrag)"
  ) {
      return (pass);
  }

  ## REMOVE COOKIES AS PREREQUESTITE FOR CACHING DEPENDING ON TYPE/URL/HOST

  # Remove all cookies for static files
  # A valid discussion could be held on this line: do you really need to cache static files that don't cause load? Only if you have memory left.
  # Sure, there's disk I/O, but chances are your OS will already have these files in their buffers (thus memory).
  # Before you blindly enable this, have a read here: http://mattiasgeniar.be/2012/11/28/stop-caching-static-files/
  if (req.url ~ "^[^?]*\.(avi|bmp|bz2|css|cssgz|doc|divx|eot|flv|gif|gz|ico|jpeg|jpg|js|jsgz|less|mp3|mp4|mpg|mpeg|mk[av]|mov|ogg|ogv|pdf|png|qt|rar|rtf|swf|txt|tbz|woff|xml|xz|zip|webp)$") {
    unset req.http.Cookie;
    return (hash);
  } else if (req.url ~ "timthumb.php") {
    unset req.http.Cookie;
    return (hash);
  }

  # Send Surrogate-Capability headers to announce ESI support to backend
  set req.http.Surrogate-Capability = "key=ESI/1.0";

  # allow caching even when using Authorization
  #if (req.http.Authorization) {
  #  # Not cacheable by default
  #  return (pass);
  #}

  return (hash);
}

sub vcl_pipe {
# Called upon entering pipe mode. In this mode, the request is passed on to the backend, and any further data from both the client and backend is passed on unaltered until either end closes the connection. Basically, Varnish will degrade into a simple TCP proxy, shuffling bytes back and forth. For a connection in pipe mode, no other VCL subroutine will ever get called after vcl_pipe.

  # Note that only the first request to the backend will have
  # X-Forwarded-For set.  If you use X-Forwarded-For and want to
  # have it set for all requests, make sure to have:
  # set bereq.http.connection = "close";
  # here.  It is not set by default as it might break some broken web
  # applications, like IIS with NTLM authentication.

  #set bereq.http.Connection = "Close";

  # Implementing websocket support (https://www.varnish-cache.org/docs/4.0/users-guide/vcl-example-websockets.html)
       if (req.http.upgrade) {
          set bereq.http.upgrade = req.http.upgrade;
       }

  return (pipe);
}

sub vcl_pass {
# Called upon entering pass mode. In this mode, the request is passed on to the backend, and the backend's response is passed on to the client, but is not entered into the cache. Subsequent requests submitted over the same client connection are handled normally.

  # return (pass);
}

# The data on which the hashing will take place
sub vcl_hash {
# Called after vcl_recv to create a hash value for the request. This is used as a key to look up the object in Varnish.

  hash_data(req.url);

  if (req.http.host) {
    hash_data(req.http.host);
  } else {
    hash_data(server.ip);
  }

  # hash cookies for requests that have them
  if (req.http.Cookie) {
    hash_data(req.http.Cookie);
  }

}

sub vcl_hit {
  # Called when a cache lookup is successful.

  # mod_pagespeed
  # 5% of the time ignore that we got a cache hit and send the request to the
  # backend anyway for instrumentation.
  if (std.random(0, 100) < 5) {
    set req.http.PS-ShouldBeacon = "futuresteps25";
    return (pass);
  }

  if (obj.ttl >= 0s) {
    # A pure unadultered hit, deliver it
    return (deliver);
  }

  # https://www.varnish-cache.org/docs/trunk/users-guide/vcl-grace.html
  # When several clients are requesting the same page Varnish will send one request to the backend and place the others on hold while fetching one copy from the backend. In some products this is called request coalescing and Varnish does this automatically.
  # If you are serving thousands of hits per second the queue of waiting requests can get huge. There are two potential problems - one is a thundering herd problem - suddenly releasing a thousand threads to serve content might send the load sky high. Secondly - nobody likes to wait. To deal with this we can instruct Varnish to keep the objects in cache beyond their TTL and to serve the waiting requests somewhat stale content.

#  if (!std.healthy(req.backend_hint) && (obj.ttl + obj.grace > 0s)) {
#    return (deliver);
#  } else {
#    return (fetch);
#  }

  # We have no fresh fish. Lets look at the stale ones.
  if (std.healthy(req.backend_hint)) {
    # Backend is healthy. Limit age to 10s.
        if (obj.ttl + 10s > 0s) {
            #set req.http.grace = "normal(limited)";
            return (deliver);
        } else {
            # No candidate for grace. Fetch a fresh object.
	    return(fetch);
       }
  } else {
    # backend is sick - use full grace
        if (obj.ttl + obj.grace > 0s) {
            #set req.http.grace = "full";
      return (deliver);
    } else {
      # no graced object.
      return (fetch);
    }
  }


  # fetch & deliver once we get the result
  return (fetch);  # Dead code, keep as a safeguard
}

sub vcl_miss {
# Called after a cache lookup if the requested document was not found in the cache. Its purpose is to decide whether or not to attempt to retrieve the document from the backend, and which backend to use.

  # mod_pagespeed
  # Instrument 25% of cache misses.
  if (std.random(0, 100) < 25) {
    set req.http.PS-ShouldBeacon = "futuresteps25";
    return (pass);
  }

  return (fetch);
}

# Handle the HTTP request coming from our backend
sub vcl_backend_response {
# Called after the response headers has been successfully retrieved from the backend.

  # Set ban-lurker friendly custom headers
  set beresp.http.X-Url = bereq.url;
  set beresp.http.X-Host = bereq.http.host;

  if (beresp.status >= 400 || beresp.status >= 500) {
     set beresp.uncacheable = true;
     return (deliver);
  }

  # Pause ESI request and remove Surrogate-Control header
  if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
    unset beresp.http.Surrogate-Control;
    set beresp.do_esi = true;
  }

  # send json as text/html instead of application/json as MSIE wants to save the "file" otherwise
  if (bereq.url ~ "bricks/api") {
     # set beresp.http.Content-Type = "text/html; charset=utf-8";
  }
  
  # Enable cache for all static files
  # The same argument as the static caches from above: monitor your cache size, if you get data nuked out of it, consider giving up the static file cache.
  # Before you blindly enable this, have a read here: http://mattiasgeniar.be/2012/11/28/stop-caching-static-files/
  if (bereq.url ~ "^[^?]*\.(avi|bmp|bz2|css|cssgz|doc|divx|eot|flv|gif|gz|ico|jpeg|jpg|js|jsgz|less|mp3|mp4|mpg|mpeg|mk[av]|mov|ogg|ogv|pdf|png|qt|rar|rtf|swf|txt|tbz|woff|xml|xz|zip|webp)$") {
    set beresp.http.cache-control = "max-age=604800";
    unset beresp.http.set-cookie;
    unset beresp.http.expires;
    set beresp.http.cachable = "1";
  }

  # Large static files are delivered directly to the end-user without
  # waiting for Varnish to fully read the file first.
  # Varnish 4 fully supports Streaming, so use streaming here to avoid locking.
  if (bereq.url ~ "^[^?]*\.(mp[34]|rar|tar|tgz|gz|wav|zip|bz2|xz|7z|avi|mov|ogm|mpe?g|mk[av])(\?.*)?$") {
    unset beresp.http.set-cookie;
    set beresp.ttl = 3600s;
    unset beresp.http.expires;
    set beresp.http.cache-control = "max-age=3600";
    set beresp.http.cachable = "1";
    set beresp.do_stream = true;   # Check memory usage it'll grow in fetch_chunksize blocks (128k by default) if
            # the backend doesn't send a Content-Length header, so only enable it for big objects
    set beresp.do_gzip = false;  # Don't try to compress it for storage
  }

  # Sometimes, a 301 or 302 redirect formed via Apache's mod_rewrite can mess with the HTTP port that is being passed along.
  # This often happens with simple rewrite rules in a scenario where Varnish runs on :80 and Apache on :8080 on the same box.
  # A redirect can then often redirect the end-user to a URL on :8080, where it should be :80.
  # This may need finetuning on your setup.
  #
  # To prevent accidental replace, we only filter the 301/302 redirects for now.
  if (beresp.status == 301 || beresp.status == 302) {
    set beresp.http.Location = regsub(beresp.http.Location, ":[0-9]+", "");
  }

  # Set 2min cache if unset for static files
  if (beresp.ttl <= 0s || beresp.http.Set-Cookie || beresp.http.Vary == "*") {
    set beresp.ttl = 120s; # Important, you shouldn't rely on this, SET YOUR HEADERS in the backend
    set beresp.uncacheable = true;
    return (deliver);
  }

  # Allow stale content, in case the backend goes down.
  # make Varnish keep all objects for 6 hours beyond their TTL
  set beresp.grace = 0s;

  return (deliver);
}

# The routine when we deliver the HTTP request to the user
# Last chance to modify headers that are sent to the client
sub vcl_deliver {
# Called before a cached object is delivered to the client.

  # Keep ban-lurker headers only if debugging is enabled
  if (!resp.http.X-Cache-Debug) {
      # Remove ban-lurker friendly custom headers when delivering to client
      unset resp.http.X-Url;
      unset resp.http.X-Host;
      unset resp.http.X-Cache-Tags;
  }
  # In Varnish 4 the obj.hits counter behaviour has changed, so we use a
  # different method: if X-Varnish contains only 1 id, we have a miss, if it
  # contains more (and therefore a space), we have a hit.
  if (resp.http.X-Cache-Debug) {
    if (resp.http.X-Varnish ~ " ") {
     	set resp.http.X-Cache = "HIT";
    } else {
    	set resp.http.X-Cache = "MISS";
    }
    #if (obj.hits > 0) { # Add debug header to see if it's a HIT/MISS and the number of hits, disable when not needed
    #    set resp.http.X-Cache = "HIT";
    #} else {
    #    set resp.http.X-Cache = "MISS";
    #}
  }

  # Please note that obj.hits behaviour changed in 4.0, now it counts per objecthead, not per object
  # and obj.hits may not be reset in some cases where bans are in use. See bug 1492 for details.
  # So take hits with a grain of salt
  set resp.http.X-Cache-Hits = obj.hits;

  # Remove some headers: PHP version
  unset resp.http.X-Powered-By;

  # Remove some headers: Apache version & OS
  unset resp.http.Server;
  unset resp.http.X-Drupal-Cache;
  unset resp.http.X-Varnish;
  unset resp.http.Via;
  unset resp.http.Link;

  set resp.http.Access-Control-Allow-Credentials = "true";
  set resp.http.Access-Control-Allow-Origin = "*";
  set resp.http.Access-Control-Expose-Headers = "*";
  set resp.http.Access-Control-Allow-Methods = "GET, POST, OPTIONS, PUT, DELETE";
  set resp.http.Access-Control-Allow-Headers = "accept, content-type, authorization, pragma, cache-control, AuthorizationFF";
  set resp.http.Access-Control-Max-Age = "1728000";

  return (deliver);
}

sub vcl_purge {
    # restart request
    #set req.http.X-Purge = "Yes";
    #return(restart);
}

sub vcl_synth {
  if (resp.status == 720) {
    # We use this special error status 720 to force redirects with 301 (permanent) redirects
    # To use this, call the following from anywhere in vcl_recv: error 720 "http://host/new.html"
    set resp.status = 301;
    set resp.http.Location = resp.reason;
    return (deliver);
  } elseif (resp.status == 721) {
    # And we use error status 721 to force redirects with a 302 (temporary) redirect
    # To use this, call the following from anywhere in vcl_recv: error 720 "http://host/new.html"
    set resp.status = 302;
    set resp.http.Location = resp.reason;
    return (deliver);
  } elseif (resp.status == 750) {
      set resp.status = 301;
      set resp.http.Location = "https://" + req.http.Host + req.url;
      return (deliver);
   }

  return (deliver);
}


sub vcl_fini {
# Called when VCL is discarded only after all requests have exited the VCL. Typically used to clean up VMODs.

  return (ok);
}