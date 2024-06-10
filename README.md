# SurveyWeb
Survey engine with web design


## Initial Setup
1. Clone repository to "<path>/XAMPP/htdocs"  
    cd into the directory with `cd "<path>/XAMPP/htdocs"` *NOTE: REPLACE <path> with your path!   
    `git clone https://github.com/matthewnex12/SurveyWeb.git`  
    You should have "<path>/XAMPP/htdocs/SurveyWeb" when done cloning.
2. Open Netbeans
3. Click "New Project... (Ctrl+Shift+N)"
4. On the New Project Wizard -> Choose Project page
  5. Under Categories select "PHP"
  6. Under Projects select "PHP Application with Existing Sources"
  7. Click Next
8. On the New Project Wizard -> Name and Location page
  9. Browse to "htdocs/SurveyWeb"
  10. Confirm project name is "Surveyweb"
  11. Confirm PHP Version is "PHP 7.4"
  12. Click Next
13. On the New Project Wizard -> Run Configuration page
  14. Confirm Run As is set to "Local Web Site (running on local web server)
  15. Confirm Project URL is set to "http://localhost/SurveyWeb/"
  16. Index File is set to "Home.php"
  17. Click Finish
18. Open MySQL Workbench
19. Select your localhost db to connect to it
20. On the left sidebar, under the "Administration" tab, select "Data Import/Restore"
21. On the localhost Data Import page, select the "Import from Self-Contained File" radio button and set the file path to "<path>/xampp/htdocs/SurveyWeb/DB/SurveyEngineDB.sql"
22. Click Start Import
23. Refresh the Schemas on the left side and confirm that a schema called 'personalsurvey' was created.
24. Complete the steps in the Run section to test that everything is set up correctly

## Run
1. Start XAMPP Apache & MySQL servers
2. In Netbeans, right-click the Home.php file and select "run"  
    The start page to the Survey Engine project should open on your browser.  
    You should NOT see a 404 or any other error.
