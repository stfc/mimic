#!/usr/bin/python
from ConfigParser import SafeConfigParser
import xmlrpclib
import urllib2
from xml.etree import ElementTree
import xml.etree.ElementTree as ET
import logging
import subprocess
import sys
from os.path import dirname, join

CWD = dirname(sys.argv[0])

parser = SafeConfigParser()
parser.read(join(CWD, '../config/xmlrpc.config'))
hostname = parser.get('auth', 'hostname')
username = parser.get('auth', 'username')
password = parser.get('auth', 'password')
auth_string = username + ":" + password

# Start xmlrpc client to opennebula server
server=xmlrpclib.ServerProxy(hostname)

response = server.one.user.login(auth_string, username, "dontleaveblank", 1000)
sessionid = response[1]
one_auth = username + ":" + sessionid

xml = server.one.vmpool.info(one_auth,-2,-1,-1,-1)
host_pool = ET.fromstring(xml[1])

print xml[1]
