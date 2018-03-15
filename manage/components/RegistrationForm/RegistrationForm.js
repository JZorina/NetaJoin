apple.controller('RegistrationForm', ['$rootScope', '$scope', '$state', 'userService', 'server', function ($rootScope, $scope, $state, userService, server) {

	$scope.nominee = {
		firstname: '',
		lastname: '',
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
		cityother: ''
	}
	$scope.register = function ()
	{
		console.log($scope.nominee);
	};
    $scope.schools = [];
	$scope.GetSchools = function () {
		var data={};
		server.requestPhp(data, "GetSchools").then(function (data) {

			$scope.schools = data;
		});
	}
	$scope.GetSchools();

    $scope.cities = [];
    $scope.GetCities = function () {
        var data={};
        server.requestPhp(data, "GetCities").then(function (data) {
            $scope.cities = data;
        });
    }
    $scope.GetCities();
}
]);