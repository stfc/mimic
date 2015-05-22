mimic
=====

Mimic Is Monitoring Information Concisely

Configuration
---------

Mimic pulls data from many different sources. The URLs, database names and passwords to these sources should be stored in a file called `user-config.ini` and placed in `/config`. If `user-config.ini` is not found, `default-config.ini` is called.


The `default-config.ini` below contains the basic structure you should follow.

```
[HOSTS] ; All hosts and their names needed go here.
HOST[server1] = server.example.com
NAME[server1] = example_server1


[LOGIN] ; All User-names and Passwords needed go here.
USER[server1] = userover9000
PASS[server1] = pass123


[URL] ; All URLS needed go here.
GOOGLE  = http://google.co.uk
EXAMPLE = http://example.test.com


[OTHER] ; Any other config options.
LOOKUP[KEY] = /etc/keys/key.py
```

Views
---------

Each page or 'view' displays specific content. If you wish to create a new view, you need to name it `view-NAME.php` and place it in `/views`. It can then be added to the menu section in `index.php`.


Node page
---------

The node page is constructed of the following files called by `node.php` and are stored in `/views/node`

```
node-getName.inc.php                      # Gets and formats the name of the node.
node-header.inc.php                       # Displays header info and links.
node-nagios.inc.php                       # Calls and displays Nagios information.
node-batchsystem.inc.php                  # Displays system state.
node-notes.inc.php                        # Displays and allows users to add notes.
node-requesttracker.inc.php               # Displays tickets.
  |_ node-requesttracker-rest.inc.php       # PHP wrapper for node-requesttracker-rest.py.
      |_ node-requesttracker-rest.py          # Logic for the request tracker.
node-magdb.inc.php                        # Calls and shows System, Rack Power, and Networking flowchart.
node-aquilon.inc.php                      # Displays Aquilon data.
node-pakiti2-json.inc.php                 # Displays Pakiti data.
  |_ node-pakiti2-json.py                   # Connects to WEBHOST.
```
