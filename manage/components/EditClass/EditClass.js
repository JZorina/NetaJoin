apple.controller('EditClass', ['$rootScope', '$scope', '$state', 'userService', 'server', function ($rootScope, $scope, $state, userService, server) {

    $scope.classes = [];
    $scope.alertcontrol={};
    $scope.GetClasses = function () {
        var data={};
        server.requestPhp(data, "GetClasses").then(function (data) {
            $scope.classes = data;
        });
    }
    $scope.GetClasses();

    $scope.SaveData = function () {
        var data={};
        data.classes=$scope.classes;
        server.requestPhp(data, "AddClass").then(function (data) {
            if(data.error!=null)
            {
                alert(data.error);
            }else
            {
               // $scope.alertcontrol.open();
                $scope.GetClasses();
            }
        });
    }

    $scope.ClearData = function () {
        $scope.GetClasses();
    }

    $scope.CreateData = function()
    {
        $scope.classes.push({
            "classname": '',
            "ArabicClassName":''

        });
    }

} ]);