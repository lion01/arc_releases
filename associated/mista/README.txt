Overview
========

MIStA is a command-line php program to extract data from SIMS according to a queue of jobs on an Arc site. It must run on the same machine as a SIMS installation. For greatest reliability this should be a server or other always-on device.

The procedure to get MIStA up and running is outlined below. Each of these steps is covered in its own section in the rest of this document.

1) set up reports in SIMS
2) install php (and required extensions)
3) install MIStA
4) configure MIStA for your environment
5) run MIStA manually once to check everything works as expected
6) set up a scheduled task to get and run import jobs
7) set up a scheduled task to get and apply updates

If any of the steps below do not go smoothly, or if your MIStA behaves erratically contact Punnet (http://pun.net) for support.


1) SIMS reports
===============

MIStA gets data from SIMS by using its reporting engine. The format of the generated data files must conform to the expectations of the Arc installation that will receive them. For this reason Punnet provide a set of report definitions in the "reports" directory. These reports must be imported into SIMS and owned by a user with sufficient privileges to view all relevant data about pupils, staff, and timetable. We recommend creating a "reports" user for this sole purpose.



2) Install php
==============

You will need to install the CLI version of php.
Instructions on how to do this can be found at:
  http://www.php.net/manual/en/install.windows.manual.php
  http://php.net/manual/en/install.windows.commandline.php

MIStA relies on the oauth and curl extensions.
Curl is part of the default package, but oauth must be installed separately.
Various pre-compiled dlls can be found at:
  http://downloads.php.net/pierre/

and instructions on installation at:
  http://php.net/manual/en/install.windows.extensions.php


3) Install MIStA
================

MIStA requires no installation, just copying to an appropriate directory on the host machine. We recommend creating a directory called "C:\Program Files\Mista" and copying all the files from the zip into there except the report definitions.


4) Configuration
================

MIStA can be configured by running the following commands, substituting in appropriate values where required

php "C:\Program Files\Mista\setup.php" -settimezone "Europe/London"
php "C:\Program Files\Mista\setup.php" -checkweb
php "C:\Program Files\Mista\setup.php" -setapi
php "C:\Program Files\Mista\setup.php" -checkapi
php "C:\Program Files\Mista\setup.php" -oauthrequest
php "C:\Program Files\Mista\setup.php" -oauthaccess
php "C:\Program Files\Mista\setup.php" -oauthtest
php "C:\Program Files\Mista\setup.php" -setrptuser "your_sims_report_user"
php "C:\Program Files\Mista\setup.php" -setrptpwd "your_sims_report_pwd"
php "C:\Program Files\Mista\setup.php" -setrptclr "C:\Program Files\SIMS\SIMS .net\CommandReporter.exe"
php "C:\Program Files\Mista\setup.php" -setrptoutdir "C:\arc_exports"
/* optional */ php "C:\Program Files\Mista\setup.php" -resetapi
/* optional */ php "C:\Program Files\Mista\setup.php" -resetrpt

Although it is possible to directly edit the config.php file, this is not the recommended method as it allows wider scope to break something than using the setup script.


5) Run
======

Performing a manual execution of the import procedure is not required, but is highly recommended to allow any problems to come to the surface and be dealt with.

The following calls will (in order) retrieve a list of queued import jobs from your Arc installation, run the reports on your SIMS instance, then upload and clean up the generated data files. It should be noted that for these to do anything you need to first queue up an import job (or several) in your Arc instance. Instructions on that can be found in the documentation for Arc's Import / Synchronisation pages.
The -runon argument means that when the called action completes the remaining actions will be called automatically.

php "C:\Program Files\Mista\poll_control.php" -getjobs [-runon]
php "C:\Program Files\Mista\poll_control.php" -generate [-runon]
php "C:\Program Files\Mista\poll_control.php" -upload [-runon]
php "C:\Program Files\Mista\poll_control.php" -cleanup

or:

php "C:\Program Files\Mista\poll_control.php" -run
This is equivalent to calling
php "C:\Program Files\Mista\poll_control.php" -getjobs -runon

If a job fails to properly execute, the following command will reset it to the state it was in when first retrieved.
php "C:\Program Files\Mista\poll_control.php" -reset [job id]


6) Schedule synchronisation
===========================
If you have a Punnet-hosted instance of Arc then there will be a cron-job already set up to regularly queue up the important imports.

If you are self-hosting then you will need to set up a cron job on your webserver to regularly queue up fundamental import jobs.

With the webserver regularly queueing up jobs, MIStA must be set to regularly check for new jobs. You can use windows' task scheduler or any other mechanism that can make the calls listed in section 5 at regular intervals. By far the easiest way to schedule the tasks is to use the -run flag. Use of that flag will make the individual steps run one after the other. If you want more granular control over what runs when you can call each of the steps (-getjobs etc) in turn. In this case we recommend you set the calls to run a minute apart (to allow time for the requests to complete).

Attempting to call poll_control while a previous call is still running will result in the warning "unable to secure process lock". This is not a problem but means the 2nd attempted call did nothing. To allow you to refine your task schedule, the expected times to execute are as follows. All times will vary with server load and hardware (both SIMS and Arc):
-getjobs : 5 to 30 seconds
-run : 1 to 3600 seconds (no jobs to run Vs all jobs to run and much data to extract from SIMS)
-upload : 1 to 600 seconds (no data to upload Vs all data to upload)
-cleanup : 1 to 5 seconds (no data to remove Vs many MB)

Leaving too little gap between calls to -run or -getjobs may result in that single call hogging the lock file. The others can be as close a few seconds.

suggested schedules
-------------------

For convenience of your Arc administrator the export job should repeat every 10 to 60 minutes. This is so that if they need to import anything out of schedule they can just add the job to Arc and know that it will be processed within that timeframe.

As mentioned before, the easiest way to schedule the tasks is to use the -run flag.

The suggestions below are for those wanting to do things a little differently.

Daily:
 batch: getjobs <60> run <3600> upload
 batch repeats every 24 hours

frequent:
 batch: getjobs <60> run <60> upload
 batch repeats every 5 minutes

hourly:
 batch: getjobs <60> upload <300> run
 batch repeats every hour

Note that the call order doesn't matter for the correct functioning of the system as all calls will be repeated next time. If the getjobs call can't run because something else already is then that just means that the queue won't grow until what's already there has been processed; if there are no jobs to upload one time they'll be uploaded after the next run has generated them; etc.


7) Maintenance
==============

MIStA is equiped with an update mechanism which can either be called manually (using the commands below) or set to check for updates on a schedule in much the same way as the synchronisation jobs.

php "C:\Program Files\Mista\update.php" -get
php "C:\Program Files\Mista\update.php" -apply


Should MIStA ever stop during execution the lock file will be left behind. This will prevent the script running. To remove the lock file use the -freelock argument to any of the scripts. eg:

php "C:\Program Files\Mista\setup.php" -freelock
php "C:\Program Files\Mista\poll_control.php" -freelock
php "C:\Program Files\Mista\update.php" -freelock
