![mimic](http://i.imgur.com/fCSbo0m.png)
Mimic (1.4.0) [![Code Climate](https://codeclimate.com/github/stfc/mimic/badges/gpa.svg)](https://codeclimate.com/github/stfc/mimic)
=====
Mimic is a framework for building visual aggregations of data from many different monitoring systems as highly visual web application.
It allows administrators to build custom overviews of physical and virtual infrastructures which provide a intuitive way to quickly drill down into detailed information from each data source.

It is currently being used and developed by the Tier 1 group at STFC RAL.

License
-------

* Mimic is licensed under the [Apache 2.0 License](http://www.apache.org/licenses/LICENSE-2.0).
* Mimic includes [Hipku](http://gabrielmartin.net/projects/hipku/) which is licensed under the [MIT License](https://opensource.org/licenses/MIT).

Dependences
-----------

Tested on Scientific Linux release 6.4 (Carbon)

| Package                               | Description                                         |
| ------------------------------------- | --------------------------------------------------- |
| php                                   |                                                     |
| php-mysql                             | MySQL module for php.                               |
| php-pgsql                             | PostgreSQL module for php.                          |
| php-pear                              | PEAR - PHP Extension and Application Repository.    |
| php-pear-Text-Diff                    | Engine for performing and rendering text diffs.     |
| php-horde-Horde-Text-Diff             | Engine for performing and rendering text diffs.     |
| nc                                    | Reads and writes data across network connections.   |
| python-argparse                       | For writing user friendly command line interfaces.  |
| python-ldap                           | LDAP interface module for Python.                   |
| graphviz                              | Graph drawing tools.                                |

If you wish to build Mimic from this repository, you will also need [npm](https://www.npmjs.com/).

Downloads
-----------


Build
-----------
With npm installed, navigate to mimic's root directory (the one with `package.json` in) and run `npm install` once that has completed you can run `bower install`. The final step is simply to run `grunt buildmimic`.

Configuration
-----------
Mimic pulls data from many different sources. The URLs, database names and passwords to these sources should be stored in a file called `user-config.ini` and placed in `/config`. If `user-config.ini` is not found, `default-config.ini` is called.

This file also includes the list of 'plugins' at the top to show on the node/host window.


Here is an example of the basic structure you should follow.

```
[NODE_PLUGINS]
0 = nagios
1 = batchsystem
2 = notes
3 = requesttracker
4 = magdb
5 = aquilon
6 = pakiti2-json
7 = scd-cloud

[URL] ; All URLS needed go here
AQUILON     = URL
ES          = URL
HARDTRACK   = URL
HELPDESK    = URL
NAGIOS      = URL
NUBES_RL    = URL
NUBES_STFC  = URL
OBSERVIUM   = URL
OVERWATCH   = URL
PAKITI      = URL
STFC        = URL
NAGIOS1     = URL
NAGGER      = URL

[PORT]
ES_PORT = :PORT

[DB_GEN]
HOST = URL
USER = USERNAME
PASS = PASSWORD
BATCH_NAME = NAME
```

The main menu is configured separately in `config/menu-config.php`.

If you are using any of the files to do with cloud data, the xmlrpc login details should be stored in `config/xmlrpc.config`.

Because of to how PHP's include paths work, all your mimic files and folders will need to be in your server root directory. In addition you may need to add a line similar to `include_path = ".:/usr/share/pear/:/var/www/html/"` to `/etc/php.d/mimic.ini` for the include paths to work.

Views
---------

Each page or 'view' displays specific content. If you wish to create a new view, you need to name it `view-NAME.php` and place it in `/views`. The view files should only be used to get the data and arrange it into the correct `JSON` structure (see below) - the code that renders the page is located in `index.php`.

```
{
  "GROUP":{
    "PANEL":{
      "CLUSTER":{
        "NODE":{
          "nagios": "data",
          "extra": "data",
          "status": {
            "STATE":"SOURCE"
          }
        },
        "NODE":{
          "nagios": "data",
          "extra": "data",
          "status": {
            "STATE":"SOURCE"
          }
        }
      },
      "CLUSTER":{
        "NODE":{
          "nagios": "data",
          "extra": "data",
          "status": {
            "STATE":"SOURCE"
          }
        },
        "NODE":{
          "nagios": "data",
          "extra": "data",
          "status": {
            "STATE":"SOURCE"
          }
        }
      }
    },
    "PANEL":{
      "CLUSTER":{
        "NODE":{
          "nagios": "data",
          "extra": "data",
          "status": {
            "STATE":"SOURCE"
          }
        },
        "NODE":{
          "nagios": "data",
          "extra": "data",
          "status": {
            "STATE":"SOURCE"
          }
        }
      },
      "CLUSTER":{
        "NODE":{
          "nagios": "data",
          "extra": "data",
          "status": {
            "STATE":"SOURCE"
          }
        },
        "NODE":{
          "nagios": "data",
          "extra": "data",
          "status": {
            "STATE":"SOURCE"
          }
        }
      }
    }
  }
}
```

You can add then customise it's menu link by editing `config/menu-config.php`.


Node page
---------

The node page offers a way to display detailed information about a node/host. This information is delivered via multiple files stored in `/node` and then called by `node.php`.
