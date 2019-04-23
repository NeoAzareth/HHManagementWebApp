# BillTrack -formerly HHManagementWebApp

Php web application that allows user to manage their household expenses. It was left active to serve as a backup for the Mobile App. 

Author: Israel Santiago, neoazareth@gmail.com   - 2015-2018

Link to deployed application https://neoazareth.com/HHManageWebApp/index.php

GitHub link to android version: <br>
https://github.com/NeoAzareth/HhManagementApp

This web application is one of my first "big" projects. It was developed as an school assignment and it has been updated by me three times.
The first version was designed so that it would create the objects necessary to function at login and store those objects in the session
so that all pages would be able to use them. It was fast but I was concerned with security issues and decided to recode it. 

The second revision of the app can be seen here in github. This version was more compact and removed a lot of the repetition. Unlike
the previous this revision would create the objects every time a page is loaded. Thanks to this the app would be up to date with changes 
done in the database by others users; although, at the cost of processing speed...

The third revision (latest version) was re-coded after working on the android app. The intention was to make it work as close as possible with the mobile app. It also ensures that it does most of the things the mobile app does.

Previously, this web app allowed users to receive email or text notifications; however, since the development of 
the Android app that feature was removed.

The mobile app and this web app share the same connection and database. The MobileApp folder contains all the scripts used by the
mobile app. The scripts were originally less than the ones seen in the folder but I decided to separate them so that it would be easier to debug.

Key features:

1. Mock User registration - actual registration only available through the Android app.
2. Create a household -creator becomes the Admin of such household; or Join an existing household -user becomes a member.
3. Provides an overview of the household status -current bills and other user’s status.
4. Allows users to add, edit or delete current month bills.
5. Create customized reports based on user, category and date (this version provides a list of only dates which have 
bills associated with them).
6. Allows users to change their password within the app and leave/delete their current household.
7. Admin of household has the authority to manage other members or update the household rent.
8. Password retrieval through user’s email. 
9. Creates and emails all the users of a household a spreadsheet with the household report once all the users are done 
adding bills.

The application requires these programs to function credits to their original work:

PHPMailer and all their contributors. <br>
https://github.com/PHPMailer/PHPMailer

PHPExcel and all their contributors. <br>
https://github.com/PHPOffice/PHPExcel

Also credits to:

And SimpleTest. <br>
http://www.simpletest.org/

Bootstrap <br>
https://getbootstrap.com/

