apple.controller('EditHearAboutUs', ['$rootScope', '$scope', '$state', 'userService', 'server', function ($rootScope, $scope, $state, userService, server) {

    $scope.hearabout = [];
    $scope.alertcontrol={};
    $scope.GetHearAbout = function () {
        var data={};
        server.requestPhp(data, "GetHearAboutUsOptions").then(function (data) {
            $scope.hearabout = data;
        });
    }
    $scope.GetHearAbout();

    $scope.SaveData = function () {
        var data={};
        data.hearabout=$scope.hearabout;
        server.requestPhp(data, "AddHearAbout").then(function (data) {
            if(data.error!=null)
            {
                alert(data.error);
            }else
            {
                // $scope.alertcontrol.open();
                $scope.GetHearAbout();
            }
        });
    }

    $scope.ClearData = function () {
        $scope.GetHearAbout();
    }

    $scope.CreateData = function()
    {
        $scope.hearabout.push({
            "hearaboutoption": '',
            "ArabicHearAbout":''

        });
    }

} ]);