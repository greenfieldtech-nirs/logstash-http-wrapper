# logstash-http-wrapper
What is this code all about exactly?

So, when developing applications on Google AppEngine, many of the norms you are used to can't be used, for example,
invoking normal UDP or TCP traffic is virtually impossible (at least in PHP Free Tier at this point). Now, if you
like log servers (like me - such as LogStash, Greylog, etc), you need a different way to submit your log entries.

In our case, I've create a simple CodeIgniter based REST wrapper, that will insert the GELF message directly to
LogStash/Greylog.

# Installation
Simply drop the application to a web folder on your server, then make sure you run 'composer install' from that
folder.

# Configuration
Edit the constants file in `application/config/constants.php` to point to your GELF server - and you're done.


