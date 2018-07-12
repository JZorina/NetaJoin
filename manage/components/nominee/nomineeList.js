apple.controller('nomineeList', ['$rootScope', '$scope', '$state', '$stateParams','userService', 'server', function ($rootScope, $scope, $state, $stateParams, userService, server) {
	$scope.checkedList=new Array(null);

	$scope.search=$stateParams.search;
	$scope.sortingField=$stateParams.sorting?$stateParams.sorting:"studentid";
	$scope.reverseOrder=$stateParams.desc;
	$scope.pageIndex = $stateParams.page;
	$scope.pageCount;
	$scope.nominees=[];
	$scope.filter={};
    $scope.filter.netacityid=null;
    $scope.filter.nomineestatusid=null;

	$scope.alertcontrol={};
	$scope.show=false;
	
	$scope.Type="";
	$scope.GetMyType = function()
	{
		var data ={};
		server.requestPhp(data, 'GetMyType').then(function (data) {
		    $scope.Type = data;
		});
	}
	$scope.GetMyType();
	
	$scope.getNominees = function() {
		$scope.loading=true;
		var search = $scope.search;
		var netaCityFilter = $scope.filter.netacityid;
		var nomineeStatusFilter = $scope.filter.nomineestatusid;
		var sorting = $scope.sortingField;
		var desc = $scope.reverseOrder;
		var userstatus = $scope.studentStatus;
		var page = $scope.pageIndex;

		var data ={'search': search, 'netaCityFilter': netaCityFilter, 'nomineeStatusFilter':nomineeStatusFilter, 'sorting': sorting, 'desc':desc, 'userstatus': userstatus, 'page': page};
		server.requestPhp(data, 'SearchNominees').then(function (data) {
			$scope.nominees = data.nominees;
			$scope.pageCount = parseInt(data.pages);
			$scope.loading=false;
			$scope.checkedList=new Array($scope.pageCount) ;
			$scope.checkedList.fill(false);

		});
	}
	$scope.getNominees();
	
	$scope.refreshResults=function()
	{
		$state.go('.', {
			search : $scope.search,
			sorting : $scope.sortingField,
			desc : $scope.reverseOrder,
			page: $scope.pageIndex
		},
		{
			notify: false
		});
		$scope.getNominees();
	}
	
	$scope.goToActiveTab = function()
	{
		$scope.pageIndex=0;
		$scope.studentStatus=1;
		$scope.getNominees();
	}
	
	$scope.goToInactiveTab = function()
	{
		$scope.pageIndex=0;
		$scope.studentStatus=0;
		$scope.getNominees();
	}
	
	$scope.goToPage = function(pageNum)
	{
		if(pageNum>=0&&pageNum<=$scope.pageCount)
		{
			$scope.pageIndex=pageNum;
			$scope.refreshResults();
		}
	}

	$scope.sortBy = function(sortIndex)
	{
		console.log(sortIndex);
		if($scope.sortingField==sortIndex)
		{
			$scope.reverseOrder=!$scope.reverseOrder;
		}
		else
		{
			$scope.reverseOrder=false;
		}
		$scope.sortingField=sortIndex;
		$scope.refreshResults();
	}
    $scope.NomineesStatuses = [];

    $scope.GetStatuses = function () {
        var data={};
        server.requestPhp(data, "GetStatuses").then(function (data) {
            $scope.NomineesStatuses = data;
        });
    }
    $scope.GetStatuses();

    $scope.UpdateNomineeStatus = function (nominee) {
        var data={};
        data.nomineeid= nominee.nomineeid;
        data.nomineestatusid= nominee.nomineestatusid;
        server.requestPhp(data, "UpdateNomineeStatus").then(function (data) {}
        );
    }

    $scope.Netacities = [];
    $scope.GetNetaCities = function () {
        var data={};
        server.requestPhp(data, "GetNetaCities").then(function (data) {
            $scope.Netacities = data;
        });
    }
    $scope.GetNetaCities();

    $scope.UpdateNomineeComments = function (nominee) {
        var data={};
        data.nomineeid= nominee.nomineeid;
        data.comments= nominee.comments;
        server.requestPhp(data, "UpdateNomineeComments").then(function (data) {}
        );
    }
    $scope.ChangeStatusByComboBox=function(index) {
    	console.log("im here! "+ index);
		$scope.checkedList[index]=!$scope.checkedList[index];
	}

	$scope.changeStatusByComboBox=function(s){
        console.log("before: s: "+s);
    	$scope.checkedList.forEach(function(el,index) {
    		if(el==true)
			{
				$scope.nominees[index].nomineestatusid= s;
				console.log("s: "+s);
                $scope.UpdateNomineeStatus($scope.nominees[index]);
			}
		});
	}
} ]);