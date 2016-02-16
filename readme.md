# groupcash bank [![Build Status](https://travis-ci.org/groupcash/bank.png?branch=master)](https://travis-ci.org/groupcash/bank)

The purpose of this web application is to facilitate the set-up and running of a complementary currency with *groupcash*.

While groupcash is a distributed system that could be run as a peer-to-peer network, a central web application can lift some administrative burden from the users.

With the *bank* users can

- **establish new currencies** and manage them
- **generate coins** that represent delivery promises of backers
- **purchase coins** through the bank which handles the payment of the backers
- **transfer coins** to other members, online or by printing blanks
- **return coins** to their backers through the bank which handles the payment of the member


## Project status

The project is currently under development. A preliminary user interface for the currently implemented capacities is generated with [domin].

[domin]: http://github.com/rtens/domin


## Installation

Download the project with [git] and build it with [composer]

    git clone https://github.com/groupcash/bank.git
    cd bank
    composer install

Execute the specifications to make sure everything works

    vendor/bin/scrut spec

Start a development server to run the web application on [localhost:8080](http://localhost:8080)

    php -S localhost:8080 web.php

[composer]: http://getcomposer.org
[git]: http://git-scm.org
