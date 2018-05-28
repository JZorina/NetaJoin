apple.controller('EditNetaCityAndSchool', ['$rootScope', '$scope', '$state', 'userService', 'server', function ($rootScope, $scope, $state, userService, server) {

    $scope.NetaCities = [];
    $scope.alertcontrol={};
    $scope.GetNetaCities = function () {
        var data={};
        server.requestPhp(data, "GetNetaCities").then(function (data) {
            $scope.NetaCities = data;
        });
    }
    $scope.GetNetaCities();

    $scope.schools= [];
    $scope.GetSchools = function () {
        var data={};
        server.requestPhp(data, "GetSchools").then(function (data) {
            $scope.schools = data;
        });
    }
    $scope.GetSchools();




    $scope.SaveData = function () {
        var data={};
        data.NetaCities = $scope.NetaCities;
        server.requestPhp(data, "AddNetaCities").then(function (data) {
            if(data.error!=null)
            {
                alert(data.error);
            }else
            {
               // $scope.alertcontrol.open();
                $scope.GetNetaCities();
            }
        });
    }

    $scope.ClearData = function () {
        $scope.GetNetaCities();
    }

    $scope.CreateData = function()
    {
        $scope.NetaCities.push({
            "CityName": '',
            "ArabicCityName":''
        });
    }

} ]);