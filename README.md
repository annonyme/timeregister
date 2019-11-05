# Time Register Module (for aoop-ng)

## Features
* Create groups of customers
* save contact-data to each customer
* invite your team-members (with an invitation-code)
* start, when you arrive at the customer
* stop on leaving your beloved customer
* works also for events

## Install
Copy th modules folder and execute
```
php cli.php aoop:modules:install --instance=default --module=timeregister
```
after this you can use the module.

## WIP
* Save/Edit/Delete message-ouput has to be added to the theme
* keep-session alive via  XHR
* redirect to login-page if session timed out 