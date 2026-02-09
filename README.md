# Orbiting Carbon Observatory 3 (OCO-3) Snapshot Area Map (SAM) Tool

This is for the SAMs website that was developed for NASA's OCO-3 mission.

[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.18485822.svg)](https://doi.org/10.5281/zenodo.18485822)

[![Language](https://img.shields.io/packagist/dependency-v/ufo-tech/json-rpc-client-sdk/php?logo=PHP&logoColor=white)](#) [![MySQL](https://img.shields.io/badge/mysql-8.4-blue)](#) [![Language](https://img.shields.io/badge/Bootstrap_5-563D7C?logo=bootstrap&logoColor=white)](#) [![Language](https://img.shields.io/badge/python-3.7-blue)](#)

## Overview

The OCO-3 Snapshot Area Mapping(SAM) was built using a [LAMP](https://en.wikipedia.org/wiki/LAMP_(software_bundle)) configuration.  The site employs very basic styling from Bootstrap 5, so you are left to style the site the way you would like.  

## Setup

Authentication to the database, port numbers, and server names are stored in a 'config.json' file that in the 'private' directory.  You should move this directory one level above the webroot.  Make sure to update that path in any pages that need to access it.

## Directories

Several directories store reports, images, and other files.  Some may need to be added as empty directories manually as they are not included in the repo.

### Directory Structure

<table>
<thead>
<tr>
<th>Directory</th>
<th>Purpose</th>
</thead>
<tbody>
<tr>
<td>authentication/</td>
<td>Code for the login area of the site</td>
</tr>
<tr>
<td>db/</td>
<td>Contains a database file with the expected structure of the backend database</td>
</tr>
<tr>
<td>favorites/</td>
<td>Area of the sites where users can selet their 'favorite' sites and get email alerts about them.</td>
</tr>
<tr>
<td>includes/</td>
<td>Shared files used througout the site</td>
</tr>
<tr>
<td>includes/files/</td>
<td>Shared header and footer files</td>
</tr>
<tr>
<td>includes/js/</td>
<td>Shared Javascript functions</td>
</tr>
<tr>
<td>plots/</td>
<td>Top directory for all the different types of plots</td>
</tr>
<tr>
<td>plots/co/</td>
<td>CO plots</td>
</tr>
<tr>
<td>plots/dp/</td>
<td>DP plots</td>
</tr>
<tr>
<td>plots/no2/</td>
<td>NO2 plots</td>
</tr>
<tr>
<td>plots/none/</td>
<td>Geostationary plots</td>
</tr>
<tr>
<td>plots/o2_radiance/</td>
<td>O2 Radiance plots</td>
</tr>
<tr>
<td>plots/sif_757nm/</td>
<td>SIF 757nm plots</td>
</tr>
<tr>
<td>plots/xco2/</td>
<td>XCO2 plots</td>
</tr>
<tr>
<td>plots/xco2_bc_qf/</td>
<td>XCO2 bias corrected and quality filtered plots</td>
</tr>
<tr>
<td>plots/xco2_bc_qf_goes/</td>
<td>XCO2 bias corrected and quality filtered geostatoinary plots</td>
</tr>
<tr>
<td>private/</td>
<td>Any credentials for the site; should move this out of webroot folder for production</td>
</tr>
<tr>
<td>requests/</td>
<td>Users can submit requests for SAMs in this area</td>
</tr>
<tr>
<td>requests/disposition/</td>
<td>Team members can approve or reject SAM requests in this area</td>
</tr>
<tr>
<td>utils/</td>
<td>Utility script for the site</td>
</tr>
</tbody>
</table>

## The Website

The website is powered by a LAMP configuration. Any credentials should be in the `private/config.json` file.  Remember to move the `private` folder out of your webroot directory for production.

The front end of the site icludes the following pages:

    - Homepage: Page with introductory information about the site.  It also inludes a map showing all target sites that have collected data.  Users can search for specific SAMs on a form, and the results of this search will show up in a DataTables area for the user to scroll through.  In the results, users can click on a SAM to display all available plots for that SAM.
    - Favorites: This area allows users to create a list of 'favorite' sites from all available target sites.  They will receive email alerts whenever new SAMs for their favorite sites become available.  They can also use the search form to search for SAMs within their favorite sites.  The results of this search are shown in a DataTable where they can view all plots for a SAM in their results, and request subsetted XCO2 data for that SAM.
    - Request a SAM: Users can use this form to request an observation (a SAM or Target observation) by drawing the area of interest on the map, selecting a time range, and entering a science justification.  This request will then be approved or rejected by the team.
    - Login: This page allows the user to login to the site so they can submit requests or create a list of favorite sites.

There are places throught this repository that contain placholder URLs and email addresses.  You will need to go through the site and update those as needed.

## Database

This website is backed by a local MySQL database. The structure of the database can be found in `db/sams_db_structure.sql`.

## Security

Users are responsible for implementing any site security mechanisms.  Recommend ones include, but are not limited to, reCAPTCHA and CSRF tokens.  Please also review cleaning user inputs on forms and consider if you should clean inputs further.

## Utilities

The scripts located in the `utils` directory are meant to be run in a crontab to sent emails out when news SAMs for sites are added to the database or when a subsetting job is complete.  These are Python scripts.
