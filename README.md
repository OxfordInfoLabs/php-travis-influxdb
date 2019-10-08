# php-travis-influxdb
PHP library which reads build data from Travis CI and writes it into an influxdb time series database for e.g. use in Grafana

###Overview

A simple PHP library / CLI application designed to read latest build status information from multiple Travis CI repos and insert the results into an Influx DB timeseries database ready 
for use in monitoring applications etc e.g. Grafana.

###Installation

Installation can be done via composer either as a library 

``
composer require oxil/php-travis-influxdb
``

or as a ready to go CLI application

``
composer create-project oxil/php-travis-influxdb
``



###Getting started

The entry point is via the Processor class which is instantiated as follows:


``
$processor = new Oxil\PHPTravisInflux\Processor(CONFIG_PATH);
``

Where CONFIG_PATH is an absolute path to a configuration file which is in JSON format as follows:


````
{
  "useBuildTimestamps": true,
  "influxdb": {
    "host": "localhost",
    "port": 8086,
    "database": "example",
    "username": "username",
    "password": "password"
  },
  "travis": {
    "travis_ci_org": {
      "endpoint": "https://api.travis-ci.org",
      "repositories": [
        "OxfordInfoLabs/kinikit-core",
        "OxfordInfoLabs/kinikit-mvc",
        "OxfordInfoLabs/kinikit-persistence"
      ]
    }
  }
}

```` 

The configuration options are as follows:

- **useBuildTimestamps** - if set to *true*, only new builds will be added to the influx database.  If set to *false* an entry will be written for 
                        each repository on each run.
                        
- **influxdb** - this subset of properties configures the connection to influxdb.  *host*,  *port* and *database* are required with authentication parameters supplied only if 
             your influxdb instance requires them.  
             *NB: If the database does not exist it will be created automatically on the first run.*

- **travis** - this contains the sets of repositories in travis to read the build stati from.  
            Entries within the *travis* top level are indexed by an identifier of your choice
            and typically relate to different endpoints (e.g. when using travis-ci.org and travis-ci.com).
            The identifier chosen will be used as the measurement name in influxdb for all entries inserted 
            for each repo.  Within each entry, the travis ci api endpoint is supplied using the *endpoint* member 
            and an array of repository names to read build statuses from is supplied using the *repositories* member.
            
   

To trigger a run you can simply call the process method on the processor instantiated above.

``
$processor->process();
``
   
 
###Reading data back from InfluxDB

Data read from Travis CI will be inserted into a measurement within the configured database (see above) as identified by 
the identifier specified within the **travis** element of the configuration file.

Each measurement entry will be written with a value of **1** always and with the following tags:

- **repo** - the full string of the repository e.g. *OxfordInfoLabs/kinikit-core*

- **short_name** - the short name of the repository (last bit after slashes) e.g. *kinikit-core*
         
- **status** - the Raw Travis status for the last build (failed,finished etc.)        

- **last_run** - the last run time for the build as obtained from Travis.

### Ready to go CLI Application

There is a ready to go working example CLI script contained in the example directory.

To install this, please use this form of composer installation:


``
composer create-project oxil/php-travis-influxdb
``

And then tweak the example/config.json to your needs. You can then run the app by calling 

``
php example/travis_influx.php
``
