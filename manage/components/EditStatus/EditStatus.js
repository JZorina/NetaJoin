apple.controller('EditStatus', ['$rootScope', '$scope', '$state', 'userService', 'server', function ($rootScope, $scope, $state, userService, server) {

    $scope.statuses = [];
    $scope.alertcontrol={};
    $scope.GetStatuses = function () {
        var data={};
        server.requestPhp(data, "GetStatuses").then(function (data) {
            $scope.statuses = data;
        });
    }
    $scope.GetStatuses();

    $scope.SaveData = function () {
        var data={};
        data.status=$scope.statuses;
        server.requestPhp(data, "AddStatus").then(function (data) {
            if(data.error!=null)
            {
                alert(data.error);
            }else
            {
                // $scope.alertcontrol.open();
                $scope.GetStatuses();
            }
        });
    }

    $scope.ClearData = function () {
        $scope.GetStatuses();
    }

    $scope.CreateData = function()
    {
        $scope.statuses.push({
            "nominneestatus": ''
        });
    }

} ]);