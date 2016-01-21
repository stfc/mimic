#!/usr/bin/python
import httplib
from sys import argv, exit
import ConfigParser
from os.path import exists

USER_CONFIG = "config/user-config.ini"
DEFAULT_CONFIG = "config/default-config.ini"

# Calling ini file
config = ConfigParser.ConfigParser()
if exists(USER_CONFIG):
    config.read(USER_CONFIG)
else:
    config.read(DEFAULT_CONFIG)

WEBHOST = config.get("PAKITI", "URL")
KEYFILE = config.get("PAKITI", "KEYFILE")
CERTFILE = config.get("PAKITI", "CERTFILE")

if len(argv) == 2:
  host = argv[1]

  # connection details
  uri = '/pakiti/host-json.php?h=' + host

  conn = httplib.HTTPSConnection(
    WEBHOST,
    key_file = KEYFILE,
    cert_file = CERTFILE
  )

  try:
    conn.putrequest('GET', uri)
    conn.endheaders()
    response = conn.getresponse()
    print(response.read())

  except:
    print('Cannot Connect')

else:
  # no search requested
  print('No search requested')
  exit(1)
