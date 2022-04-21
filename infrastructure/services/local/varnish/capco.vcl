vcl 4.0;
import std;

backend default {
  .host = "localhost";
  .port = "8080";
}

# Called at the beginning of a request, after the complete request has been received and parsed.
sub vcl_recv {

  # Delete cookie for static files
  if (req.url ~ "\.(jpeg|jpg|png|gif|ico|webp|js|css)$") {
    unset req.http.Cookie;
  }

  # Ensure that the Symfony Router generates URLs correctly with Varnish
  if (req.http.X-Forwarded-Proto == "https" ) {
    set req.http.X-Forwarded-Port = "443";
  } else {
    set req.http.X-Forwarded-Port = "80";
  }

  ## local docker only configuration
  # Disable cache for blackfire
  if (req.http.X-Blackfire-Query) {
    return (pass);
  }

  # Allow purge
  if (req.method == "PURGE") {
    return (purge);
  }

  # Allow ban
  if (req.method == "BAN") {
    ban("req.http.host == " + req.http.host);
    return(synth(200, "Ban added"));
  }

  # Disable cache for development
  if (req.http.host == "capco.dev") {
    return (pass);
  }
  ## End local docker only

  # Only cache GET or HEAD requests. This makes sure the POST requests are always passed.
  if (req.method != "GET" && req.method != "HEAD") {
    return (pass);
  }

  # Remove all cookies except the session ID.
  if (req.http.Cookie) {
    set req.http.Cookie = ";" + req.http.Cookie;
    set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
    set req.http.Cookie = regsuball(req.http.Cookie, ";(PHPSESSID)=", "; \1=");
    set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
    set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");

    if (req.http.Cookie == "") {
      # If there are no more cookies, remove the header to get page cached.
      unset req.http.Cookie;
    }
  }
}
