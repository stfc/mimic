mimic
=====

Mimic Is Monitoring Information Concisely

Node page
The node page is constructed of the following files called by /node.php and are stored in /node

node-getName.inc.php                      # Gets and formats the name of the node.
node-header.inc.php                       # Displays header info and links.
node-nagios.inc.php                       # Calls and displays Nagios information.
node-batchsystem.inc.php                  # Displays system state.
node-notes.inc.php                        # Displays and allows users to add notes.
node-requesttracker.inc.php               # Displays tickets.
  |_ node-requesttracker-rest.inc.php     # PHP wrapper for node-requesttracker-rest.py.
      |_ node-requesttracker-rest.py      # Logic for the
node-magdb.inc.php                        # Calls and shows System, Rack Power, and Networking flowchart.
node-aquilon.inc.php                      # Displays Aquilon data.
node-pakiti2-json.inc.php                 # Displays Pakiti data.
  |_ node-pakiti2-json.py                 # Connects to WEBHOST.
