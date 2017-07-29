# Loadbalancing group for this zone
# File /etc/varnish/director.vcl

new zone = directors.round_robin();
zone.add_backend(alexa1);
#zone.add_backend(alexa2);

# To temporarily remove a node from the director (loadbalancing) group of this zoone
# 1) Comment out one of the lines above (config is synched to all nodes)
# 2) Check config using varnishd -C -f /etc/varnish/default.vcl
# 3) Execute cluster-reload-varnish (reloads config on all hosts)

# To directly access one node circumventing the loadbalancer
# 1) Install Chrome Plugin ModHeader (once)
# 2) Set Header "X-Server-Select" with name of node as value