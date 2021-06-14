#!/usr/bin/python
# vim:noai:sw=2:ts=2:et
from cookielib import LWPCookieJar
from urllib import urlencode, quote
from urllib2 import urlopen, build_opener, HTTPCookieProcessor, install_opener, Request
from sys import argv, exit, exc_info
import json
import ConfigParser

def parse_ticket_lines(lines):
    return dict([[w.replace("'","").replace('"','').strip() for w in l[:-1].split(':', 1)] for l in lines if ":" in l])

# Calling ini file
config = ConfigParser.ConfigParser()
config.read("../config/user-config.ini")

# connection details
uri = config.get("HELPDESK", "URL")
access_user = config.get("HELPDESK", "USER")
access_password = config.get("HELPDESK", "PASS")

if len(argv) == 2 or len(argv) == 3:
    subject = argv[1]

    history = False;

    if len(argv) == 3:
        if argv[2] == "history":
            history = True;

    # trying login on rt server, need a cookie jar to to store connection bits
    cj = LWPCookieJar()

    opener = build_opener(HTTPCookieProcessor(cj))

    install_opener(opener)

    credentials = {'user':access_user,'pass':access_password}
    credentials = urlencode(credentials)

    login  = Request(uri, credentials)

    # make query using TicketSQL
    query = "Subject like '%s'" % subject
    if not history:
        query = query + " AND ( Status = 'new' OR Status = 'open' )"
    query = quote(query)
    search = Request(uri + "/REST/1.0/search/ticket/" + '?query=' + query, headers={"Referer": uri})

    try:
        response = urlopen(login)

        try:
            response = urlopen(search)
            tickets = response.readlines()
            tickets = parse_ticket_lines(tickets[2:])

            for id, subject in tickets.iteritems():
                details = urlopen(Request(uri + "/REST/1.0/ticket/" + id + "/show", headers={"Referer": uri})).readlines()[2:-1]
                details = parse_ticket_lines(details)
                tickets[id] = (tickets[id], details["Queue"], details["Created"], details["Status"])

            print(json.dumps(tickets))
        except:
            print('{}')
            #print(exc_info()[0])
    except:
        print('{}')
        #print(exc_info()[0])
else:
    # no search requested
    print('{}')
    exit(1)
