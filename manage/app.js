var apple = angular.module('apple', ['app.directives', 'ui.router'])

apple.service('userService',['$q', '$state','$rootScope', 'server', function($q,$state,$rootScope, server){
}]);

apple.run(function ($rootScope, $timeout, $state, userService, $document, server) {
})

/**** UI Router ****/
apple.config(function ($stateProvider, $urlRouterProvider,$httpProvider) {
	$urlRouterProvider.otherwise("/gender");

	$stateProvider
		.state("city", {
			url: "/city",
			views: {
				"main": {
					templateUrl: "components/city/city.html",
					controller: "city"
				}
			}
		})
		.state("gender", {
			url: "/gender",
			views: {
				"main": {
					templateUrl: "components/gender/gender.html",
					controller: "gender"
				}
			}
		})
		.state("religion", {
			url: "/religion",
			views: {
				"main": {
					templateUrl: "components/religion/religion.html",
					controller: "religion"
				}
			}
		})
		.state("nomineeList", {
			url: "/nomineeList?:search&:sorting&{desc:bool}&{page:int}",
			params: {
				search: {
				   dynamic: true,
				   value:""
				},
				sorting:
				{
					dynamic: true,
					value:""
				},
				desc:
				{
					dynamic: true,
					value:false
				},
				page:
				{
					dynamic: true,
					value:0
				}
			},
			views: {
				"main": {
					templateUrl: "components/nominee/nomineeList.html",
					controller: "nomineeList"
				}
			}
		})
		.state("singleNominee", {
			url: "/singleNominee/:nomineeId",
			views: {
				"main": {
					templateUrl: "components/nominee/singleNominee.html",
					controller: "singleNominee"
				}
			}
		});
		
	$httpProvider.interceptors.push(function($document,$rootScope) {
		return {
			'request': function(config) {
				return config;
			},
			'response': function(response) {
				if(response && response.data && response.data.error && response.data.error=="user not found")
					location.reload();
				return response;
			},
		  	'responseError': function(response) {
			  	return response;
		  	}
		};
	});
});
