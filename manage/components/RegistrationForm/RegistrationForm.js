apple.controller('RegistrationForm', ['$rootScope', '$scope', '$stateParams', '$state', 'userService', 'server', function ($rootScope, $scope, $stateParams, $state, userService, server) {
var dictionary ={
    'he':{
        'firstname':'שם פרטי',
        'lastname':'שם משפחה',
        'gender':'מגדר'
    },
    'ar':{
        'firstname':'שם פרטי ',
        'firstnameinarabic':'firstname in arabic',
        'lastname':'שם משפחה ',
        'lastnameinarabic':'lastname in arabic'
    }
}
$scope.isArabic = $stateParams["lang"]=='ar';
    $scope.dictionary=dictionary[$stateParams["lang"]];
	$scope.nominee = {
		firstname: '',
		lastname: '',
        genderid:'',
		birthday: '',
		email: '',
		phone: '',
		parentsphone: '',
		idnumber: '',
		schoolid: '',
		classid: '',
		cityid: '',
		netacityid: '',
		hearaboutid: '',
		hearaboutother: '',
		schoolother: '',
        cityother:'',
        RegistrationDate:''

	}
	$scope.register = function ()
	{
        var data = {};
        data.nominee=$scope.nominee;
        var birthday =data.nominee.birthday;
		birthday = birthday.split(/([\/\.])+/g);
		console.log(birthday);
		data.nominee.birthday=birthday[4]+"-"+birthday[2]+"-"+birthday[0];
		if(birthday[0]>31||birthday[2]>12||birthday[4]<1900)
        {
            alert("נא להזין תאריך לידה תקין");
            return;
        }
        server.requestPhp(data, "AddNominee").then(function (data) {
            $state.transitionTo('SuccessfulRegistration');
        });

		console.log($scope.nominee);
	};
    $scope.schools = [];
	$scope.GetSchoolsByNetaCityId = function () {
        var data = {};
        data.NetaCityId = $scope.nominee.netacityid;
        server.requestPhp(data, "GetSchoolsByNetaCityId").then(function (data) {
            $scope.schools = data;
        });
    }

    $scope.cities = [];
    $scope.GetCities = function () {
        var data={};
        server.requestPhp(data, "GetCities").then(function (data) {
            $scope.cities = data;
        });
    }
    $scope.GetCities();


    $scope.Netacities = [];
    $scope.GetNetaCities = function () {
        var data={};
        server.requestPhp(data, "GetNetaCities").then(function (data) {
            $scope.Netacities = data;
        });
    }
    $scope.GetNetaCities();

    $scope.Classes = [];
    $scope.GetClasses = function () {
        var data={};
        server.requestPhp(data, "GetClasses").then(function (data) {
            $scope.Classes = data;
        });
    }
    $scope.GetClasses();

    $scope.HearAboutUs = [];
    $scope.GetHearAboutUsOptions = function () {
        var data={};
        server.requestPhp(data, "GetHearAboutUsOptions").then(function (data) {
            $scope.HearAboutUs = data;
        });
    }
    $scope.GetHearAboutUsOptions();

    $scope.genders = [];
    $scope.GetGenders = function () {
        var data={};
        server.requestPhp(data, "GetGenders").then(function (data) {
            $scope.genders = data;
        });
    }
    $scope.GetGenders();



}
]);