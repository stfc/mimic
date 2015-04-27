#!/usr/bin/python
import httplib
from sys import argv, exit

WEBHOST  = 'pakiti.example.com'
KEYFILE = '/etc/grid-security/hostkey.pem'
CERTFILE = '/etc/grid-security/hostcert.pem'

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
