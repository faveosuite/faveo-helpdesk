var myApp = angular.module('myApp', []);

myApp.controller('MainController', ['$http', '$scope',
    function($http, $scope) {
        //        $http.get('url').success(function(data) {
        //
        //        });

        //titile and content for page 3
        $scope.Databasetitle = 'Database Type';
        $scope.Databasecontent = 'Choose the type of your database';

        $scope.Hosttitle = 'MySQL Host';
        $scope.Hostcontent = 'If your MySQL is installed on the same server as Helpdesk, let it be localhost';

        $scope.Porttitle = 'Database Port number';
        $scope.Portcontent = 'Port number on which your MySQL server is listening. By default, it is 3306';

        $scope.Databasenametitle = 'Database Name';
        $scope.Databasenamecontent = 'The name of the database you want to run Faveo in';

        $scope.Usertitle = 'Database Username';
        $scope.Usercontent = 'Your Database username';

        $scope.Passwordtitle = 'Database Password';
        $scope.Passwordcontent = 'Your Database user password';
        
        //titile and content for page 4

        $scope.Nametitle = 'First Name';
        $scope.Namecontent = 'System administrator first name';
        
        $scope.Lasttitle = 'Last Name';
        $scope.Lastcontent = 'System administrator last name';
        
        $scope.Emailtitle = 'Email';
        $scope.Emailcontent = 'Email Double-check your email address before continuing';
        
        $scope.UserNametitle = 'User Name';
        $scope.UserNamecontent = 'Usernames can have only alphanumeric characters, spaces, underscores, hyphens, periods, and the @ symbol.';
        
        $scope.Passtitle = 'Password';
        $scope.Passcontent = 'Important: You will need this password to log in. Please store it in a secure location.';
        
        $scope.Confirmtitle = 'Confirm Password';
        $scope.Confirmcontent = 'Type the same password as above';
        
        $scope.Languagetitle = 'Faveo Language';
        $scope.Languagecontent = 'The language you want to run Faveo in';
        
        $scope.Timezonetitle = 'Time Zone';
        $scope.Timezonecontent = 'Faveo default time zone';

        $scope.Datetimetitle = 'Faveo Date & Time format';
        $scope.Datetimecontent = 'What format you want to display date & time in Faveo';

        $scope.DummyDataTitle = 'Faveo Dummy Data';
        $scope.DummyDataContent = 'Check this chekbox if you want to install and test Faveo with dummy data. You can clear dummy data and start using Faveo in production anytime.';
                
    }
]);
