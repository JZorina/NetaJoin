apple.controller('RegistrationForm', ['$rootScope', '$scope', '$state', 'userService', 'server', function ($rootScope, $scope, $state, userService, server) {

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
        cityother:''

	}
	$scope.register = function ()
	{
        var data = {};
        data.nominee=$scope.nominee;
		//var birthday = data.nominee.birthday.split(/([\/\.])+/g);
		//data.nominee.birthday=birthday[2]+"-"+birthday[1]+"-"+birthday[0];
        server.requestPhp(data, "AddNominee").then(function (data) {
            alert("ההרשמה הצליחה");
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