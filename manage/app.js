var apple = angular.module('apple', ['app.directives', 'ui.router'])

apple.service('userService',['$q', '$state','$rootScope', 'server', function($q,$state,$rootScope, server){
}]);

apple.run(function ($rootScope, $timeout, $state, userService, $document, server) {
	$rootScope.$on('$viewContentLoaded', function() {
	      $templateCache.removeAll();
	   });
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
		})
        .state("RegistrationForm", {
            url: "/RegistrationForm/:lang",
            views: {
                "main": {
                    templateUrl: "components/RegistrationForm/RegistrationForm.html",
                    controller: "RegistrationForm"
                }
            }
        })
        .state("EditNetaCityAndSchool", {
            url: "/EditNetaCityAndSchool",
            views: {
                "main": {
                    templateUrl: "components/EditNetaCityAndSchool/EditNetaCityAndSchool.html",
                    controller: "EditNetaCityAndSchool"
                }
            }
        })

        .state("EditClass", {
            url: "/EditClass",
            views: {
                "main": {
                    templateUrl: "components/EditClass/EditClass.html",
                    controller: "EditClass"
                }
            }
        })

        .state("EditHearAboutUs", {
            url: "/EditHearAboutUs",
            views: {
                "main": {
                    templateUrl: "components/EditHearAboutUs/EditHearAboutUs.html",
                    controller: "EditHearAboutUs"
                }
            }
        })
        .state("EditStatus", {
            url: "/EditStatus",
            views: {
                "main": {
                    templateUrl: "components/EditStatus/EditStatus.html",
                    controller: "EditStatus"
                }
            }
        })

        .state("SuccessfulRegistration", {
            url: "/SuccessfulRegistration/:lang",
            views: {
                "main": {
                    templateUrl: "components/SuccessfulRegistration/SuccessfulRegistration.html",
                    controller: "SuccessfulRegistration"
                }
            }
        })

	;




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
