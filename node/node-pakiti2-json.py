#!/usr/bin/python
import httplib
from sys import argv, exit
import ConfigParser

# Calling ini file
config = ConfigParser.ConfigParser()
config.read("../config/config.ini")

WEBHOST  = config.get("PAKITI", "URL")
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
    print('[]')

else:
  # no search requested
  print('[]')
  exit(1)
